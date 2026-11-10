<?php
require __DIR__ . '/../config.php';
require_admin();

$customers = $pdo->query("SELECT u.*, COUNT(o.id) AS order_count,
        COALESCE(SUM(CASE WHEN o.status <> 'cancelled' THEN o.total END), 0) AS spent
    FROM users u LEFT JOIN orders o ON o.user_id = u.id
    WHERE u.role = 'customer' GROUP BY u.id ORDER BY u.id DESC")->fetchAll();

$title = 'ลูกค้า';
include __DIR__ . '/_header.php';
?>
<h1>ลูกค้า</h1>
<div class="table-wrap">
<table class="table">
  <tr><th>ชื่อ</th><th>อีเมล</th><th>เบอร์โทร</th><th>ที่อยู่</th><th>คำสั่งซื้อ</th><th>ยอดซื้อรวม</th><th>สมัครเมื่อ</th></tr>
  <?php foreach ($customers as $c): ?>
    <tr>
      <td><?= e($c['name']) ?></td>
      <td><?= e($c['email']) ?></td>
      <td><?= e($c['phone']) ?></td>
      <td><small><?= e($c['address']) ?></small></td>
      <td><?= (int)$c['order_count'] ?></td>
      <td><?= money($c['spent']) ?></td>
      <td><small><?= e($c['created_at']) ?></small></td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$customers): ?><tr><td colspan="7" class="muted">ยังไม่มีลูกค้า</td></tr><?php endif; ?>
</table>
</div>
<?php include __DIR__ . '/_footer.php'; ?>
