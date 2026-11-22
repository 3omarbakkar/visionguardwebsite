<?php
// Public lead endpoint. Order prices and item names are rebuilt from products table.
require_once __DIR__ . '/_customers.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
function vg_json_error($status, $code) { http_response_code($status); echo json_encode(['ok'=>false,'error'=>$code], JSON_UNESCAPED_UNICODE); exit; }
function vg_lead_phone($p) {
    $p = strtr((string)$p, ['٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9']);
    $p = preg_replace('/\D+/', '', $p);
    if (strpos($p,'0020')===0) $p=substr($p,4); elseif (strpos($p,'20')===0 && strlen($p)===12) $p=substr($p,2);
    if (strlen($p)===10 && $p[0]==='1') $p='0'.$p;
    return $p;
}
function vg_lead_rate($type, $phone, $ip) {
    $pdo=db(); $ph=hash('sha256',$phone); $ih=hash('sha256',$ip);
    $window = $type==='order' ? 3600 : 3600;
    $limitIp = $type==='order' ? 5 : 12;
    $limitPhone = $type==='order' ? 5 : 10;
    $since=date('Y-m-d H:i:s',time()-$window);
    $s=$pdo->prepare('SELECT SUM(ip_hash = ?) AS by_ip, SUM(phone_hash = ?) AS by_phone FROM vg_public_lead_attempts WHERE lead_type = ? AND attempted_at >= ?');
    $s->execute([$ih,$ph,$type,$since]); $r=$s->fetch(PDO::FETCH_ASSOC)?:[];
    if ((int)($r['by_ip']??0)>=$limitIp || ($phone!=='' && (int)($r['by_phone']??0)>=$limitPhone)) return false;
    $pdo->prepare('INSERT INTO vg_public_lead_attempts (lead_type,phone_hash,ip_hash,attempted_at) VALUES (?,?,?,NOW())')->execute([$type,$ph,$ih]);
    if (random_int(1,100)===1) $pdo->exec('DELETE FROM vg_public_lead_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 2 DAY) LIMIT 1000');
    return true;
}
/** Validation failure inside the order transaction. */
class VgLeadReject extends Exception {
    public $status;
    public function __construct(int $status, string $code) {
        parent::__construct($code);
        $this->status = $status;
    }
}
if (($_SERVER['REQUEST_METHOD']??'')!=='POST') vg_json_error(405,'method_not_allowed');
$raw=file_get_contents('php://input',false,null,0,32769);
if (!$raw || strlen($raw)>32768) vg_json_error(413,'payload_too_large');
$data=json_decode($raw,true); if(!is_array($data)) $data=$_POST;
if (!empty($data['website'])) { echo json_encode(['ok'=>true]); exit; }
$type=in_array($data['type']??'', ['order','coverage_plan','newsletter','contact'],true)?$data['type']:'contact';
$name=isset($data['name'])?mb_substr(trim((string)$data['name']),0,120):null;
$phone=isset($data['phone'])?mb_substr(trim((string)$data['phone']),0,40):null;
$email=isset($data['email'])?mb_substr(trim((string)$data['email']),0,150):null;
$message=isset($data['message'])?mb_substr(trim((string)$data['message']),0,2000):null;
$total=isset($data['total'])&&is_numeric($data['total'])?(float)$data['total']:null;
$cartItems=isset($data['cart'])&&is_array($data['cart'])?$data['cart']:null;
$cart=$cartItems!==null?json_encode($cartItems,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES):null;
$paymentMethod=null; $paymentStatus='unverified';
if($type==='order'){
    $paymentMethod=(string)($data['payment_method']??'');
    if(!in_array($paymentMethod,['cod','instapay','ewallet'],true)) vg_json_error(422,'invalid_payment_method');
    if(!$name||!$phone||!$cartItems||count($cartItems)<1||count($cartItems)>30) vg_json_error(422,'incomplete_order');
    $phone=vg_lead_phone($phone);
    if(!preg_match('/^01[0125][0-9]{8}$/',$phone)) vg_json_error(422,'bad_phone');
    $paymentStatus=$paymentMethod==='cod'?'cod_due':'pending';
}
if($type==='newsletter' && (!filter_var($email,FILTER_VALIDATE_EMAIL))) vg_json_error(422,'email_required');
if($type!=='newsletter' && !$name && !$phone && !$message && !$cart) vg_json_error(422,'empty');
if($type==='order' && (!$name || !$message)) vg_json_error(422,'incomplete_order');

vg_boot();
$ratePhone=$type==='order'?$phone:($phone??$email??'');
if(!vg_lead_rate($type,(string)$ratePhone,(string)($_SERVER['REMOTE_ADDR']??'unknown'))) vg_json_error(429,'too_many_requests');

$cust=vg_current_customer();
if($type==='order'){
    // Checkout requires an account with an email address.
    if(!$cust) vg_json_error(401,'login_required');
    if(!empty($cust['needs_email'])) vg_json_error(403,'email_required');
    $email=$cust['email'];
}
$leadId=0; $verifiedItems=[];
if($type==='order'){
    $quantities=[];
    foreach($cartItems as $item){
        if(!is_array($item)||!isset($item['id'],$item['qty'])) vg_json_error(422,'invalid_cart');
        $id=filter_var($item['id'],FILTER_VALIDATE_INT,['options'=>['min_range'=>1]]);
        $qty=filter_var($item['qty'],FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>50]]);
        if(!$id||!$qty) vg_json_error(422,'invalid_cart');
        $quantities[$id]=($quantities[$id]??0)+$qty;
        if($quantities[$id]>50) vg_json_error(422,'invalid_quantity');
    }
    if(!$quantities) vg_json_error(422,'empty_cart');
    $ids=array_keys($quantities); sort($ids);
    $marks=implode(',',array_fill(0,count($ids),'?'));
    $pdo=db();
    $pdo->beginTransaction();
    try {
        // Lock the product rows so two shoppers cannot buy the same last unit.
        $q=$pdo->prepare("SELECT id,name_ar,name_en,price,discount_percent,in_stock,stock_qty FROM products WHERE id IN ($marks) FOR UPDATE");
        $q->execute($ids); $products=[];
        foreach($q->fetchAll(PDO::FETCH_ASSOC) as $row) $products[(int)$row['id']]=$row;
        $serverTotal=0.0;
        foreach($ids as $id){
            $qty=$quantities[$id];
            if(!isset($products[$id])) throw new VgLeadReject(422,'product_not_found');
            $p=$products[$id];
            if(!vg_stock_available($p)) throw new VgLeadReject(409,'product_out_of_stock');
            $left=vg_stock_left($p);
            if($left!==null && $left<$qty) throw new VgLeadReject(409,'insufficient_stock');
            $unit=(float)$p['price']; if($unit<=0) throw new VgLeadReject(409,'invalid_product_price');
            $discount=max(0,min(100,(int)($p['discount_percent']??0)));
            if($discount>0) $unit=round($unit*(1-$discount/100),2);
            $line=round($unit*$qty,2); $serverTotal+=$line;
            $verifiedItems[]=['id'=>(int)$id,'name'=>(string)$p['name_ar'],'name_en'=>(string)$p['name_en'],'qty'=>(int)$qty,'unit_price'=>round($unit,2),'line_total'=>$line];
        }
        $total=round($serverTotal,2); try{$vgE=$pdo->query("SELECT active,percent FROM vg_discount_state WHERE id=1")->fetch(); if($vgE && (int)$vgE['active']===1 && in_array($paymentMethod,['instapay','ewallet'],true)){ $vgP=max(0,min(90,(int)$vgE['percent'])); if($vgP>0) $total=round($total*(1-$vgP/100),2); }}catch(Throwable $vgEx){} $cart=json_encode($verifiedItems,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        $ft=is_array($data['ft']??null)?$data['ft']:[];$hay=strtolower(mb_substr((string)($ft['utm']??''),0,40).' '.mb_substr((string)($ft['ref']??''),0,80)).(($ft['fb']??false)?' facebook':'');if(preg_match('/facebook|fb\.|instagram|meta/',$hay))$trafficSrc='facebook';elseif(strpos($hay,'tiktok')!==false)$trafficSrc='tiktok';elseif(preg_match('/whatsapp|wa\.me/',$hay))$trafficSrc='whatsapp';elseif(preg_match('/google|gclid/',$hay))$trafficSrc='google';elseif(trim($hay)==='')$trafficSrc='direct';else $trafficSrc='other';
        $stmt=$pdo->prepare('INSERT INTO leads (type,name,phone,email,message,cart_json,total,source_url,customer_id,payment_method,payment_status,traffic_src) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute([$type,$name,$phone,$email,$message,$cart,$total,mb_substr((string)($data['source']??($_SERVER['HTTP_REFERER']??'')),0,255),$cust?(int)$cust['id']:null,$paymentMethod,$paymentStatus,$trafficSrc]);
        $leadId=(int)$pdo->lastInsertId();
        /* stock is deducted when the order is confirmed (admin/lead_update.php) */
        $pdo->commit();
    } catch (VgLeadReject $e) {
        if($pdo->inTransaction()) $pdo->rollBack();
        vg_json_error($e->status,$e->getMessage());
    } catch (Throwable $e) {
        if($pdo->inTransaction()) $pdo->rollBack();
        error_log('Vision Guard order failed: '.$e->getMessage());
        vg_json_error(500,'server_error');
    }
} else {
    $ft=is_array($data['ft']??null)?$data['ft']:[];$hay=strtolower(mb_substr((string)($ft['utm']??''),0,40).' '.mb_substr((string)($ft['ref']??''),0,80)).(($ft['fb']??false)?' facebook':'');if(preg_match('/facebook|fb\.|instagram|meta/',$hay))$trafficSrc='facebook';elseif(strpos($hay,'tiktok')!==false)$trafficSrc='tiktok';elseif(preg_match('/whatsapp|wa\.me/',$hay))$trafficSrc='whatsapp';elseif(preg_match('/google|gclid/',$hay))$trafficSrc='google';elseif(trim($hay)==='')$trafficSrc='direct';else $trafficSrc='other';
        $stmt=db()->prepare('INSERT INTO leads (type,name,phone,email,message,cart_json,total,source_url,customer_id,payment_method,payment_status,traffic_src) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
    $stmt->execute([$type,$name,$phone,$email,$message,$cart,$total,mb_substr((string)($data['source']??($_SERVER['HTTP_REFERER']??'')),0,255),$cust?(int)$cust['id']:null,$paymentMethod,$paymentStatus,$trafficSrc]);
    $leadId=(int)db()->lastInsertId();
}
if($type==='order'){
    $notify='3omarbakkar@gmail.com';
    $body="طلب جديد رقم #$leadId\nالاسم: $name\nالهاتف: $phone\n".($message?$message."\n":'')."طريقة الدفع: $paymentMethod\nحالة الدفع: $paymentStatus\nالإجمالي المعتمد: $total ج.م\nالمنتجات: $cart\nالعميل مسجل: ".($cust?'نعم':'زائر')."\n";
    @mail($notify,'=?UTF-8?B?'.base64_encode("طلب جديد #$leadId - Vision Guard").'?=',$body,"Content-Type: text/plain; charset=UTF-8\r\nFrom: orders@visionguardeg.com");
}
$response=['ok'=>true,'id'=>$leadId]; if($type==='order'){$response['total']=$total;$response['items']=$verifiedItems;}
echo json_encode($response,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
