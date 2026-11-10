<?php
require __DIR__ . '/../config.php';
require_admin();

$stats = $pdo->query("SELECT
    (SELECT COUNT(*) FROM orders) AS orders,
    (SELECT COUNT(*) FROM orders WHERE status = 'pending') AS pending,
    (SELECT COALESCE(SUM(total),0) FROM orders WHERE status IN ('paid','shipping','completed')) AS revenue,
    (SELECT COUNT(*) FROM products WHERE is_active = 1) AS products,
    (SELECT COUNT(*) FROM users WHERE role = 'customer') AS customers")->fetch();

$recent = $pdo->query('SELECT * FROM orders ORDER BY id DESC LIMIT 8')->fetchAll();
$lowStock = $pdo->query('SELECT p.id, p.name, s.name AS size_name, v.stock FROM product_variants v
    JOIN products p ON p.id = v.product_id JOIN sizes s ON s.id = v.size_id
    WHERE v.stock <= 3 ORDER BY v.stock, p.name')->fetchAll();
$bySize = $pdo->query('SELECT oi.size_name, SUM(oi.qty) AS qty, SUM(oi.qty * oi.price) AS amount
    FROM order_items oi JOIN orders o ON o.id = oi.order_id WHERE o.status <> \'cancelled\'
    GROUP BY oi.size_name ORDER BY qty DESC')->fetchAll();

$title = 'แดชบอร์ด';
include __DIR__ . '/_header.php';
?>
<h1>แดชบอร์ด</h1>
<div class="stats">
  <div class="stat"><div class="muted">ยอดขาย (ชำระแล้ว)</div><div class="num"><?= money($stats['revenue']) ?></div></div>
  <div class="stat"><div class="muted">คำสั่งซื้อทั้งหมด</div><div class="num"><?= (int)$stats['orders'] ?></div></div>
  <div class="stat"><div class="muted">รอชำระ/ตรวจสอบ</div><div class="num"><?= (int)$stats['pending'] ?></div></div>
  <div class="stat"><div class="muted">สินค้าที่เปิดขาย</div><div class="num"><?= (int)$stats['products'] ?></div></div>
  <div class="stat"><div class="muted">ลูกค้า</div><div class="num"><?= (int)$stats['customers'] ?></div></div>
</div>

<div class="two-col">
  <div class="panel">
    <h3 style="margin-top:0">คำสั่งซื้อล่าสุด</h3>
    <div class="table-wrap"><table class="table">
      <tr><th>เลขที่</th><th>ลูกค้า</th><th>ยอดรวม</th><th>สถานะ</th></tr>
      <?php foreach ($recent as $o): ?>
        <tr><td><a href="order_view.php?id=<?= $o['id'] ?>"><?= e($o['order_no']) ?></a></td><td><?= e($o['customer_name']) ?></td>
            <td><?= money($o['total']) ?></td><td><?= status_badge($o['status']) ?></td></tr>
      <?php endforeach; ?>
      <?php if (!$recent): ?><tr><td colspan="4" class="muted">ยังไม่มีคำสั่งซื้อ</td></tr><?php endif; ?>
    </table></div>
  </div>
  <div>
    <div class="panel">
      <h3 style="margin-top:0">ยอดขายตามขนาด</h3>
      <?php foreach ($bySize as $b): ?>
        <div class="line"><span><?= e($b['size_name']) ?> (<?= (int)$b['qty'] ?> ชิ้น)</span><span><?= money($b['amount']) ?></span></div>
      <?php endforeach; ?>
      <?php if (!$bySize): ?><p class="muted">ยังไม่มีข้อมูล</p><?php endif; ?>
    </div>
    <div class="panel" style="margin-top:20px">
      <h3 style="margin-top:0">สินค้าใกล้หมด (≤ 3)</h3>
      <?php foreach ($lowStock as $l): ?>
        <div class="line"><a href="product_form.php?id=<?= $l['id'] ?>"><?= e($l['name']) ?> · <?= e($l['size_name']) ?></a>
          <span class="<?= $l['stock'] == 0 ? 'text-danger' : '' ?>"><?= (int)$l['stock'] ?></span></div>
      <?php endforeach; ?>
      <?php if (!$lowStock): ?><p class="muted">สต็อกเพียงพอทุกรายการ</p><?php endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/_footer.php'; ?>
