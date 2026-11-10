<?php
require __DIR__ . '/config.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
$stmt->execute([$id, current_user()['id']]);
$order = $stmt->fetch();
if (!$order) { flash('ไม่พบคำสั่งซื้อ', 'error'); redirect('account.php'); }

// ลูกค้าแนบสลิปโอนเงิน
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $order['status'] === 'pending' && $order['payment_method'] === 'transfer') {
    check_csrf();
    try {
        $file = upload_image('slip');
        if (!$file) throw new RuntimeException('กรุณาเลือกไฟล์สลิป');
        $pdo->prepare('UPDATE orders SET slip = ? WHERE id = ?')->execute([$file, $id]);
        flash('แนบสลิปเรียบร้อย รอร้านค้าตรวจสอบ');
    } catch (RuntimeException $ex) {
        flash($ex->getMessage(), 'error');
    }
    redirect('order.php?id=' . $id);
}

$stmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
$stmt->execute([$id]);
$items = $stmt->fetchAll();

$title = 'คำสั่งซื้อ ' . $order['order_no'];
include __DIR__ . '/includes/header.php';
?>
<p><a href="<?= url('account.php') ?>">← คำสั่งซื้อของฉัน</a></p>
<h1>คำสั่งซื้อ #<?= e($order['order_no']) ?> <?= status_badge($order['status']) ?></h1>
<div class="two-col">
  <div class="panel">
    <h3>รายการสินค้า</h3>
    <?php foreach ($items as $it): ?>
      <div class="line"><span><?= e($it['product_name']) ?> (<?= e($it['size_name']) ?>) × <?= $it['qty'] ?></span><span><?= money($it['price'] * $it['qty']) ?></span></div>
    <?php endforeach; ?>
    <hr>
    <div class="line"><span>ยอดสินค้า</span><span><?= money($order['subtotal']) ?></span></div>
    <div class="line"><span>ค่าจัดส่ง</span><span><?= $order['shipping'] > 0 ? money($order['shipping']) : 'ฟรี' ?></span></div>
    <div class="line total"><span>ยอดรวม</span><span><?= money($order['total']) ?></span></div>
  </div>
  <div class="panel">
    <h3>การจัดส่ง</h3>
    <p><?= e($order['customer_name']) ?> · <?= e($order['phone']) ?><br><?= nl2br(e($order['address'])) ?></p>
    <?php if ($order['tracking_no']): ?><p><b>เลขพัสดุ:</b> <?= e($order['tracking_no']) ?></p><?php endif; ?>
    <p><b>วันที่สั่ง:</b> <?= e($order['created_at']) ?><br><b>ชำระเงิน:</b> <?= PAYMENT_METHOD[$order['payment_method']] ?></p>

    <?php if ($order['payment_method'] === 'transfer'): ?>
      <h3>การชำระเงิน</h3>
      <?php if ($order['status'] === 'pending'): ?>
        <p class="note">กรุณาโอนเงิน <b><?= money($order['total']) ?></b> มาที่<br><?= e(BANK_INFO) ?></p>
        <form method="post" enctype="multipart/form-data">
          <?= csrf_field() ?>
          <label>แนบสลิปโอนเงิน <input type="file" name="slip" accept="image/*" required></label>
          <button class="btn btn-primary"><?= $order['slip'] ? 'แนบสลิปใหม่' : 'แนบสลิป' ?></button>
        </form>
      <?php endif; ?>
      <?php if ($order['slip']): ?><p>สลิปที่แนบ:</p><img class="slip" src="<?= url('uploads/' . $order['slip']) ?>" alt="slip"><?php endif; ?>
    <?php endif; ?>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
