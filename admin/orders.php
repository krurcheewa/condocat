<?php
require __DIR__ . '/../config.php';
require_admin();

$status = $_GET['status'] ?? '';
$q = trim($_GET['q'] ?? '');
$where = [];
$params = [];
if (isset(ORDER_STATUS[$status])) { $where[] = 'status = ?'; $params[] = $status; }
if ($q !== '') { $where[] = '(order_no LIKE ? OR customer_name LIKE ? OR phone LIKE ?)'; array_push($params, "%$q%", "%$q%", "%$q%"); }
$stmt = $pdo->prepare('SELECT * FROM orders' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY id DESC');
$stmt->execute($params);
$orders = $stmt->fetchAll();
$counts = $pdo->query('SELECT status, COUNT(*) FROM orders GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);

$title = 'คำสั่งซื้อ';
include __DIR__ . '/_header.php';
?>
<h1>คำสั่งซื้อ</h1>
<div class="row" style="margin-bottom:14px">
  <a class="btn btn-sm <?= $status === '' ? 'btn-primary' : '' ?>" href="orders.php">ทั้งหมด (<?= array_sum($counts) ?>)</a>
  <?php foreach (ORDER_STATUS as $k => $label): ?>
    <a class="btn btn-sm <?= $status === $k ? 'btn-primary' : '' ?>" href="?status=<?= $k ?>"><?= $label ?> (<?= (int)($counts[$k] ?? 0) ?>)</a>
  <?php endforeach; ?>
</div>
<form class="filters" method="get">
  <input type="hidden" name="status" value="<?= e($status) ?>">
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="ค้นหาเลขที่ / ชื่อ / เบอร์โทร">
  <button class="btn">ค้นหา</button>
</form>
<div class="table-wrap">
<table class="table">
  <tr><th>เลขที่</th><th>วันที่</th><th>ลูกค้า</th><th>ชำระเงิน</th><th>ยอดรวม</th><th>สถานะ</th><th></th></tr>
  <?php foreach ($orders as $o): ?>
    <tr>
      <td><?= e($o['order_no']) ?></td>
      <td><?= e($o['created_at']) ?></td>
      <td><?= e($o['customer_name']) ?><br><small class="muted"><?= e($o['phone']) ?></small></td>
      <td><?= PAYMENT_METHOD[$o['payment_method']] ?><?= $o['slip'] ? '<br><small class="badge badge-paid">มีสลิป</small>' : '' ?></td>
      <td><?= money($o['total']) ?></td>
      <td><?= status_badge($o['status']) ?></td>
      <td><a class="btn btn-sm" href="order_view.php?id=<?= $o['id'] ?>">จัดการ</a></td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$orders): ?><tr><td colspan="7" class="muted">ไม่พบคำสั่งซื้อ</td></tr><?php endif; ?>
</table>
</div>
<?php include __DIR__ . '/_footer.php'; ?>
