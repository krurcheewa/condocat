<?php
// การตั้งค่าหลักของระบบ
session_start();

const DB_HOST = 'localhost';
const DB_NAME = 'condocat';
const DB_USER = 'root';
const DB_PASS = '';
const SHOP_NAME = 'CondoCat';
const SHIPPING_FEE = 100;          // ค่าส่งต่อออเดอร์
const FREE_SHIPPING_MIN = 2000;    // ซื้อครบเท่านี้ส่งฟรี
const BANK_INFO = 'ธนาคารกสิกรไทย 123-4-56789-0 ชื่อบัญชี ร้าน CondoCat';
const UPLOAD_DIR = __DIR__ . '/uploads/';

// URL ฐานของโปรเจกต์ (เช่น /condocat)
define('BASE_URL', rtrim(str_replace('\\', '/', substr(__DIR__, strlen(rtrim($_SERVER['DOCUMENT_ROOT'], '/\\')))), '/'));

try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $ex) {
    die('เชื่อมต่อฐานข้อมูลไม่ได้: ' . htmlspecialchars($ex->getMessage()) . '<br>กรุณา import ไฟล์ database/condocat.sql');
}

const ORDER_STATUS = [
    'pending'   => 'รอชำระเงิน',
    'paid'      => 'ชำระเงินแล้ว',
    'shipping'  => 'กำลังจัดส่ง',
    'completed' => 'สำเร็จ',
    'cancelled' => 'ยกเลิก',
];
const PAYMENT_METHOD = ['transfer' => 'โอนเงินผ่านธนาคาร', 'cod' => 'เก็บเงินปลายทาง'];

function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function money($n): string { return '฿' . number_format((float)$n, 0); }
function url(string $path = ''): string { return BASE_URL . '/' . ltrim($path, '/'); }
function redirect(string $path): never { header('Location: ' . url($path)); exit; }

function flash(?string $msg = null, string $type = 'success') {
    if ($msg !== null) { $_SESSION['flash'] = [$msg, $type]; return null; }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function check_csrf(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) die('CSRF token ไม่ถูกต้อง');
}

function current_user(): ?array { return $_SESSION['user'] ?? null; }
function is_admin(): bool { return (current_user()['role'] ?? '') === 'admin'; }
function require_login(): void { if (!current_user()) { flash('กรุณาเข้าสู่ระบบก่อน', 'error'); redirect('login.php?next=' . urlencode($_SERVER['REQUEST_URI'])); } }
function require_admin(): void { if (!is_admin()) redirect('admin/login.php'); }

function product_image(?string $img): string {
    return $img ? url('uploads/' . $img) : url('assets/condo.svg');
}

// อัปโหลดรูปภาพ คืนชื่อไฟล์ หรือ null ถ้าไม่ได้เลือกไฟล์
function upload_image(string $field): ?string {
    if (empty($_FILES[$field]['name']) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('อัปโหลดไฟล์ไม่สำเร็จ');
    $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true) || !getimagesize($_FILES[$field]['tmp_name'])) {
        throw new RuntimeException('รองรับเฉพาะไฟล์รูปภาพ jpg, png, gif, webp');
    }
    if ($_FILES[$field]['size'] > 5 * 1024 * 1024) throw new RuntimeException('ไฟล์ต้องไม่เกิน 5MB');
    $name = date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    move_uploaded_file($_FILES[$field]['tmp_name'], UPLOAD_DIR . $name);
    return $name;
}

// ตะกร้าสินค้าเก็บใน session: [variant_id => qty]
function cart(): array { return $_SESSION['cart'] ?? []; }
function cart_count(): int { return array_sum(cart()); }
function cart_items(PDO $pdo): array {
    $cart = cart();
    if (!$cart) return [];
    $ids = implode(',', array_map('intval', array_keys($cart)));
    $rows = $pdo->query("SELECT v.*, p.name AS product_name, p.image, s.name AS size_name
        FROM product_variants v JOIN products p ON p.id = v.product_id JOIN sizes s ON s.id = v.size_id
        WHERE v.id IN ($ids) AND p.is_active = 1")->fetchAll();
    foreach ($rows as &$r) {
        $r['qty'] = $cart[$r['id']];
        $r['line_total'] = $r['qty'] * $r['price'];
    }
    return $rows;
}
function shipping_fee(float $subtotal): float { return $subtotal >= FREE_SHIPPING_MIN || $subtotal == 0 ? 0 : SHIPPING_FEE; }
function status_badge(string $s): string { return '<span class="badge badge-' . e($s) . '">' . e(ORDER_STATUS[$s] ?? $s) . '</span>'; }
