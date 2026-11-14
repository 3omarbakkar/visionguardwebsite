<?php
define('SITE_URL','http://localhost');define('IMAGES_DIR',dirname(__DIR__,2).'/images');define('IMAGES_URL_FROM_ADMIN','../images');define('IMAGES_URL_FROM_API','../images');
function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $f = dirname(__DIR__,2) . '/demo.sqlite';
        $new = !is_file($f);
        $pdo = new PDO('sqlite:' . $f, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
        if ($new) {
            $pdo->exec("CREATE TABLE settings (meta_key TEXT PRIMARY KEY, meta_value TEXT)");
            $pdo->exec("INSERT INTO settings VALUES ('vg_schema_v2','1')");
            $pdo->exec("CREATE TABLE products (id INTEGER PRIMARY KEY, slug TEXT, category TEXT, placement TEXT, brand TEXT, name_ar TEXT, name_en TEXT, price REAL, icon TEXT, in_stock INT, stock_qty INT, discount_percent INT, sort_order INT, details_ar TEXT, details_en TEXT, specs TEXT)");
            $pdo->exec("CREATE TABLE vg_discount_state (id INT, active INT, percent INT)");
            $pdo->exec("INSERT INTO vg_discount_state VALUES (1,1,10)");
            $pdo->exec("CREATE TABLE hero_slides (id INTEGER PRIMARY KEY, image TEXT, image_dark TEXT, link TEXT, alt TEXT, sort_order INT, active INT)");
            $pdo->exec("INSERT INTO hero_slides (image,image_dark,link,alt,sort_order,active) VALUES ('/og-image.png','','/shop','عروض',1,1)");
            $rows = [
              [4,'DH-HAC-1','cat_hd','out','Dahua','كاميرا دوم داهوا 2MP','Dahua 2MP Dome Camera',850,'do',1,null,15],
              [8,'HK-IP-4','cat_ip','out','Hikvision','كاميرا IP هيكفيجن 4MP','Hikvision 4MP IP Bullet',1650,'bu',1,2,null],
              [17,'IMOU-WF','cat_wf','in','Imou','كاميرا واي فاي إيمو دوارة','Imou Wi-Fi Pan/Tilt Camera',1250,'pt',1,null,10],
              [19,'DVR-8','cat_dvr','both','Dahua','مسجل DVR 8 قنوات','Dahua 8-Channel DVR',2400,'dv',1,null,null],
              [31,'WD-1TB','cat_hdd','both','WD','هارد ديسك 1 تيرا WD Purple','WD Purple 1TB HDD',2100,'hd',0,null,null],
              [40,'PSU-12','cat_psu','both','Generic','باور سبلاي 12V 10A','12V 10A Power Supply',380,'ps',1,null,null],
              [44,'CAB-100','cat_cab','both','Generic','كابل كاميرات 100 متر','100m CCTV Cable',1450,'ca',1,null,5],
              [46,'MC-128','cat_mem','both','SanDisk','كارت ميموري 128GB','SanDisk 128GB microSD',420,'mc',1,null,null],
            ];
            $st = $pdo->prepare("INSERT INTO products (id,slug,category,placement,brand,name_ar,name_en,price,icon,in_stock,stock_qty,discount_percent,sort_order,details_ar,details_en,specs) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            foreach ($rows as $i => $r) { $st->execute([$r[0],$r[1],$r[2],$r[3],$r[4],$r[5],$r[6],$r[7],$r[8],$r[9],$r[10],$r[11],$i,'تفاصيل المنتج هنا.','Product details here.',"الدقة: 2MP\nالعدسة: 2.8mm"]); }
        }
    }
    return $pdo;
}
function setting(string $key, string $default = ''): string {
    static $cache = null;
    if ($cache === null) { $cache = []; foreach (db()->query('SELECT meta_key, meta_value FROM settings') as $r) $cache[$r['meta_key']] = $r['meta_value']; }
    return $cache[$key] ?? $default;
}
