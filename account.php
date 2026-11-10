<?php
require __DIR__ . '/config.php';
require_login();

$stmt = $pdo->prepare('SELECT o.*, (SELECT SUM(qty) FROM order_items WHERE order_id = o.id) AS item_count FROM orders o WHERE user_id = ? ORDER BY id DESC');
$stmt->execute([current_user()['id']]);
$orders = $stmt->fetchAll();

$title = 'คำสั่งซื้อของฉัน';
include __DIR__ . '/includes/header.php';
?>
<h1>คำสั่งซื้อของฉัน</h1>
<?php if (!$orders): ?>
  <p class="empty">ยังไม่มีคำสั่งซื้อ <a href="<?= url('index.php') ?>">เริ่มช้อปเลย</a></p>
<?php else: ?>
<div class="table-wrap">
<table class="table">
  <tr><th>เลขที่</th><th>วันที่</th><th>จำนวนชิ้น</th><th>ยอดรวม</th><th>สถานะ</th><th></th></tr>
  <?php foreach ($orders as $o): ?>
    <tr>
      <td><?= e($o['order_no']) ?></td>
      <td><?= e($o['created_at']) ?></td>
      <td><?= (int)$o['item_count'] ?></td>
      <td><?= money($o['total']) ?></td>
      <td><?= status_badge($o['status']) ?></td>
      <td><a class="btn btn-sm" href="<?= url('order.php?id=' . $o['id']) ?>">ดูรายละเอียด</a></td>
    </tr>
  <?php endforeach; ?>
</table>
</div>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
