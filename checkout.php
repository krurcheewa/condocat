<?php
require __DIR__ . '/config.php';
require_login();

$items = cart_items($pdo);
if (!$items) redirect('cart.php');
$subtotal = array_sum(array_column($items, 'line_total'));
$shipping = shipping_fee($subtotal);
$u = current_user();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $name = trim($_POST['customer_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $note = trim($_POST['note'] ?? '');
    $payment = ($_POST['payment_method'] ?? '') === 'cod' ? 'cod' : 'transfer';

    if ($name === '' || $phone === '' || $address === '') {
        $error = 'กรุณากรอกชื่อ เบอร์โทร และที่อยู่ให้ครบถ้วน';
    } else {
        try {
            $pdo->beginTransaction();
            // ล็อกแถวสต็อกและตรวจสอบอีกครั้งก่อนตัดสต็อก
            $lock = $pdo->prepare('SELECT stock FROM product_variants WHERE id = ? FOR UPDATE');
            foreach ($items as $it) {
                $lock->execute([$it['id']]);
                if ($lock->fetchColumn() < $it['qty']) throw new RuntimeException('สินค้า ' . $it['product_name'] . ' (' . $it['size_name'] . ') มีไม่พอ');
            }
            $orderNo = 'CC' . date('ymdHis') . random_int(10, 99);
            $pdo->prepare('INSERT INTO orders (order_no, user_id, customer_name, phone, address, note, payment_method, subtotal, shipping, total)
                           VALUES (?,?,?,?,?,?,?,?,?,?)')
                ->execute([$orderNo, $u['id'], $name, $phone, $address, $note, $payment, $subtotal, $shipping, $subtotal + $shipping]);
            $orderId = $pdo->lastInsertId();
            $ins = $pdo->prepare('INSERT INTO order_items (order_id, variant_id, product_name, size_name, price, qty) VALUES (?,?,?,?,?,?)');
            $dec = $pdo->prepare('UPDATE product_variants SET stock = stock - ? WHERE id = ?');
            foreach ($items as $it) {
                $ins->execute([$orderId, $it['id'], $it['product_name'], $it['size_name'], $it['price'], $it['qty']]);
                $dec->execute([$it['qty'], $it['id']]);
            }
            // จำที่อยู่ไว้ใช้ครั้งถัดไป
            $pdo->prepare('UPDATE users SET phone = ?, address = ? WHERE id = ?')->execute([$phone, $address, $u['id']]);
            $pdo->commit();
            $_SESSION['user']['phone'] = $phone;
            $_SESSION['user']['address'] = $address;
            unset($_SESSION['cart']);
            flash('สั่งซื้อสำเร็จ! เลขที่คำสั่งซื้อ ' . $orderNo);
            redirect('order.php?id=' . $orderId);
        } catch (RuntimeException $ex) {
            $pdo->rollBack();
            $error = $ex->getMessage();
        }
    }
}

$title = 'สั่งซื้อสินค้า';
include __DIR__ . '/includes/header.php';
?>
<h1>สั่งซื้อสินค้า</h1>
<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
<div class="two-col">
  <form method="post" class="panel">
    <?= csrf_field() ?>
    <h3>ข้อมูลการจัดส่ง</h3>
    <label>ชื่อผู้รับ <input name="customer_name" required value="<?= e($_POST['customer_name'] ?? $u['name']) ?>"></label>
    <label>เบอร์โทร <input name="phone" required value="<?= e($_POST['phone'] ?? $u['phone']) ?>"></label>
    <label>ที่อยู่จัดส่ง <textarea name="address" rows="3" required><?= e($_POST['address'] ?? $u['address']) ?></textarea></label>
    <label>หมายเหตุ <textarea name="note" rows="2"><?= e($_POST['note'] ?? '') ?></textarea></label>
    <h3>วิธีชำระเงิน</h3>
    <label class="inline"><input type="radio" name="payment_method" value="transfer" checked> <?= PAYMENT_METHOD['transfer'] ?></label>
    <label class="inline"><input type="radio" name="payment_method" value="cod"> <?= PAYMENT_METHOD['cod'] ?></label>
    <button class="btn btn-primary btn-block">ยืนยันการสั่งซื้อ</button>
  </form>
  <div class="panel">
    <h3>สรุปรายการ</h3>
    <?php foreach ($items as $it): ?>
      <div class="line"><span><?= e($it['product_name']) ?> (<?= e($it['size_name']) ?>) × <?= $it['qty'] ?></span><span><?= money($it['line_total']) ?></span></div>
    <?php endforeach; ?>
    <hr>
    <div class="line"><span>ยอดสินค้า</span><span><?= money($subtotal) ?></span></div>
    <div class="line"><span>ค่าจัดส่ง</span><span><?= $shipping ? money($shipping) : 'ฟรี' ?></span></div>
    <div class="line total"><span>ยอดรวม</span><span><?= money($subtotal + $shipping) ?></span></div>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
