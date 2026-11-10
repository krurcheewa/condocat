<?php
require __DIR__ . '/../config.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
$stmt->execute([$id]);
$order = $stmt->fetch();
if (!$order) { flash('ไม่พบคำสั่งซื้อ', 'error'); redirect('admin/orders.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $new = $_POST['status'] ?? $order['status'];
    if (!isset(ORDER_STATUS[$new])) $new = $order['status'];
    $pdo->beginTransaction();
    // คืน/ตัดสต็อกเมื่อยกเลิกหรือกู้คืนคำสั่งซื้อ
    if ($new !== $order['status'] && ($new === 'cancelled' || $order['status'] === 'cancelled')) {
        $sign = $new === 'cancelled' ? '+' : '-';
        $pdo->prepare("UPDATE product_variants v JOIN order_items oi ON oi.variant_id = v.id
                       SET v.stock = GREATEST(0, v.stock $sign oi.qty) WHERE oi.order_id = ?")->execute([$id]);
    }
    $pdo->prepare('UPDATE orders SET status = ?, tracking_no = ? WHERE id = ?')
        ->execute([$new, trim($_POST['tracking_no'] ?? '') ?: null, $id]);
    $pdo->commit();
    flash('อัปเดตคำสั่งซื้อแล้ว');
    redirect('admin/order_view.php?id=' . $id);
}

$stmt = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ?');
$stmt->execute([$id]);
$items = $stmt->fetchAll();

$title = 'คำสั่งซื้อ ' . $order['order_no'];
include __DIR__ . '/_header.php';
?>
<p><a href="orders.php">← รายการคำสั่งซื้อ</a></p>
<h1>คำสั่งซื้อ #<?= e($order['order_no']) ?> <?= status_badge($order['status']) ?></h1>
<div class="two-col">
  <div class="panel">
    <h3 style="margin-top:0">รายการสินค้า</h3>
    <div class="table-wrap"><table class="table">
      <tr><th>สินค้า</th><th>ขนาด</th><th>ราคา</th><th>จำนวน</th><th>รวม</th></tr>
      <?php foreach ($items as $it): ?>
        <tr><td><?= e($it['product_name']) ?></td><td><?= e($it['size_name']) ?></td><td><?= money($it['price']) ?></td>
            <td><?= (int)$it['qty'] ?></td><td><?= money($it['price'] * $it['qty']) ?></td></tr>
      <?php endforeach; ?>
    </table></div>
    <div class="summary">
      <div>ยอดสินค้า: <?= money($order['subtotal']) ?></div>
      <div>ค่าจัดส่ง: <?= $order['shipping'] > 0 ? money($order['shipping']) : 'ฟรี' ?></div>
      <div class="total">ยอดรวม: <?= money($order['total']) ?></div>
    </div>
    <h3>ข้อมูลลูกค้า</h3>
    <p><?= e($order['customer_name']) ?> · <?= e($order['phone']) ?><br><?= nl2br(e($order['address'])) ?></p>
    <?php if ($order['note']): ?><p><b>หมายเหตุ:</b> <?= nl2br(e($order['note'])) ?></p><?php endif; ?>
    <p class="muted">สั่งเมื่อ <?= e($order['created_at']) ?></p>
  </div>
  <div class="panel">
    <h3 style="margin-top:0">การชำระเงิน</h3>
    <p><?= PAYMENT_METHOD[$order['payment_method']] ?></p>
    <?php if ($order['slip']): ?>
      <a href="<?= url('uploads/' . $order['slip']) ?>" target="_blank"><img class="slip" src="<?= url('uploads/' . $order['slip']) ?>" alt="slip"></a>
    <?php elseif ($order['payment_method'] === 'transfer'): ?>
      <p class="muted">ลูกค้ายังไม่แนบสลิป</p>
    <?php endif; ?>
    <h3>อัปเดตสถานะ</h3>
    <form method="post">
      <?= csrf_field() ?>
      <label>สถานะ
        <select name="status">
          <?php foreach (ORDER_STATUS as $k => $label): ?>
            <option value="<?= $k ?>" <?= $order['status'] === $k ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>เลขพัสดุ <input name="tracking_no" value="<?= e($order['tracking_no']) ?>"></label>
      <p class="muted"><small>เมื่อเปลี่ยนเป็น "ยกเลิก" ระบบจะคืนสต็อกสินค้าอัตโนมัติ</small></p>
      <button class="btn btn-primary btn-block">บันทึก</button>
    </form>
  </div>
</div>
<?php include __DIR__ . '/_footer.php'; ?>
