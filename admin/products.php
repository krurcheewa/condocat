<?php
require __DIR__ . '/../config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $id = (int)$_POST['id'];
    if (($_POST['action'] ?? '') === 'toggle') {
        $pdo->prepare('UPDATE products SET is_active = 1 - is_active WHERE id = ?')->execute([$id]);
        flash('เปลี่ยนสถานะการขายแล้ว');
    } elseif (($_POST['action'] ?? '') === 'delete') {
        $img = $pdo->prepare('SELECT image FROM products WHERE id = ?');
        $img->execute([$id]);
        $img = $img->fetchColumn();
        $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
        if ($img && is_file(UPLOAD_DIR . $img)) unlink(UPLOAD_DIR . $img);
        flash('ลบสินค้าแล้ว');
    }
    redirect('admin/products.php');
}

$products = $pdo->query('SELECT p.*, c.name AS category_name,
        GROUP_CONCAT(CONCAT(s.code, ":", v.stock) ORDER BY s.sort_order SEPARATOR "  ") AS stock_info,
        MIN(v.price) AS min_price, MAX(v.price) AS max_price
    FROM products p LEFT JOIN categories c ON c.id = p.category_id
    LEFT JOIN product_variants v ON v.product_id = p.id LEFT JOIN sizes s ON s.id = v.size_id
    GROUP BY p.id ORDER BY p.id DESC')->fetchAll();

$title = 'จัดการสินค้า';
include __DIR__ . '/_header.php';
?>
<div class="row" style="justify-content:space-between;margin-top:0">
  <h1>จัดการสินค้า</h1>
  <a class="btn btn-primary" href="product_form.php">+ เพิ่มสินค้า</a>
</div>
<div class="table-wrap">
<table class="table">
  <tr><th>สินค้า</th><th>หมวดหมู่</th><th>ราคา</th><th>สต็อกตามขนาด</th><th>สถานะ</th><th>จัดการ</th></tr>
  <?php foreach ($products as $p): ?>
    <tr>
      <td class="item-cell"><img class="thumb" src="<?= product_image($p['image']) ?>" alt=""><?= e($p['name']) ?></td>
      <td><?= e($p['category_name'] ?? '-') ?></td>
      <td><?= $p['min_price'] !== null ? money($p['min_price']) . ($p['max_price'] > $p['min_price'] ? ' - ' . money($p['max_price']) : '') : '-' ?></td>
      <td><small><?= e($p['stock_info'] ?? 'ยังไม่กำหนดขนาด') ?></small></td>
      <td><?= $p['is_active'] ? '<span class="badge badge-completed">เปิดขาย</span>' : '<span class="badge badge-cancelled">ปิดขาย</span>' ?></td>
      <td class="actions">
        <a class="btn btn-sm" href="product_form.php?id=<?= $p['id'] ?>">แก้ไข</a>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $p['id'] ?>"><input type="hidden" name="action" value="toggle">
          <button class="btn btn-sm"><?= $p['is_active'] ? 'ปิดขาย' : 'เปิดขาย' ?></button></form>
        <form method="post" onsubmit="return confirm('ยืนยันลบสินค้านี้?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $p['id'] ?>"><input type="hidden" name="action" value="delete">
          <button class="btn btn-sm btn-danger">ลบ</button></form>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
</div>
<?php include __DIR__ . '/_footer.php'; ?>
