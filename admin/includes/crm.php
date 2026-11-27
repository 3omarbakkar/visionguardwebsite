<?php
/**
 * Vision Guard - CRM helpers shared by the dashboard and the leads pages.
 * The follow-up columns come from migration-followups.sql, which is run by hand;
 * until then vg_crm_ready() is false and the follow-up features stay hidden.
 */

/** True once leads has followup_at, followup_note and contacted_at. */
function vg_crm_ready(): bool {
    static $ok = null;
    if ($ok !== null) return $ok;
    try { db()->query('SELECT followup_at, followup_note, contacted_at FROM leads LIMIT 0'); $ok = true; }
    catch (Throwable $e) { $ok = false; }
    return $ok;
}

/** Today's date by the database clock (created_at is written by it); PHP's date when unavailable. */
function vg_db_today(): string {
    static $d = null;
    if ($d !== null) return $d;
    try { $v = (string)db()->query('SELECT CURDATE()')->fetchColumn(); } catch (Throwable $e) { $v = ''; }
    $d = preg_match('/^\d{4}-\d\d-\d\d$/', $v) ? $v : date('Y-m-d');
    return $d;
}

/** Current time by the database clock as a Unix timestamp; PHP's clock when unavailable. */
function vg_db_now(): int {
    try { $v = (string)db()->query('SELECT NOW()')->fetchColumn(); $t = strtotime($v); if ($t) return $t; } catch (Throwable $e) {}
    return time();
}

/** Last 10 digits of a phone number, so 01012345678 and +201012345678 match. */
function vg_phone_key($p): string {
    $d = preg_replace('/\D+/', '', (string)$p);
    return strlen($d) >= 10 ? substr($d, -10) : $d;
}

/** wa.me link for an Egyptian mobile number, or '' when there is none. */
function vg_wa_url($p): string {
    $d = preg_replace('/\D+/', '', (string)$p);
    if ($d === '') return '';
    if (strpos($d, '20') === 0 && strlen($d) === 12) return 'https://wa.me/' . $d;
    if ($d[0] === '0') $d = substr($d, 1);
    return 'https://wa.me/20' . $d;
}

/** "3 أيام", "5 ساعات", "دقايق" for an age in seconds. */
function vg_age_ar(int $sec): string {
    if ($sec < 3600) return 'دقايق';
    $h = intdiv($sec, 3600);
    if ($h < 24) return $h === 1 ? 'ساعة' : ($h === 2 ? 'ساعتين' : $h . ($h <= 10 ? ' ساعات' : ' ساعة'));
    $d = intdiv($h, 24);
    return $d === 1 ? 'يوم' : ($d === 2 ? 'يومين' : $d . ($d <= 10 ? ' أيام' : ' يوم'));
}
