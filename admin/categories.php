<?php
require __DIR__ . '/../config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    if ($action === 'delete') {
        $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
        flash('ลบหมวดหมู่แล้ว (สินค้าในหมวดนี้จะกลายเป็นไม่ระบุหมวด)');
    } elseif ($name === '') {
        flash('กรุณากรอกชื่อหมวดหมู่', 'error');
    } elseif ($action === 'update') {
        $pdo->prepare('UPDATE categories SET name = ?, description = ? WHERE id = ?')->execute([$name, $desc, $id]);
        flash('บันทึกหมวดหมู่แล้ว');
    } else {
        $pdo->prepare('INSERT INTO categories (name, description) VALUES (?, ?)')->execute([$name, $desc]);
        flash('เพิ่มหมวดหมู่แล้ว');
    }
    redirect('admin/categories.php');
}

$categories = $pdo->query('SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id) AS used FROM categories c ORDER BY name')->fetchAll();

$title = 'หมวดหมู่';
include __DIR__ . '/_header.php';
?>
<h1>หมวดหมู่สินค้า</h1>
<div class="table-wrap">
<table class="table">
  <tr><th>ชื่อหมวดหมู่</th><th>คำอธิบาย</th><th>จำนวนสินค้า</th><th>จัดการ</th></tr>
  <?php foreach ($categories as $c): $f = 'cat' . $c['id']; ?>
    <tr>
      <td><input form="<?= $f ?>" name="name" value="<?= e($c['name']) ?>"></td>
      <td><input form="<?= $f ?>" name="description" value="<?= e($c['description']) ?>" style="width:100%"></td>
      <td><?= (int)$c['used'] ?></td>
      <td class="actions">
        <form id="<?= $f ?>" method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $c['id'] ?>"><input type="hidden" name="action" value="update">
          <button class="btn btn-sm">บันทึก</button></form>
        <form method="post" onsubmit="return confirm('ยืนยันลบหมวดหมู่นี้?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $c['id'] ?>"><input type="hidden" name="action" value="delete">
          <button class="btn btn-sm btn-danger">ลบ</button></form>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
</div>
<form method="post" class="panel" style="margin-top:20px">
  <?= csrf_field() ?>
  <h3 style="margin-top:0">เพิ่มหมวดหมู่</h3>
  <div class="form-grid">
    <label>ชื่อหมวดหมู่ <input name="name" required></label>
    <label>คำอธิบาย <input name="description"></label>
  </div>
  <button class="btn btn-primary">เพิ่มหมวดหมู่</button>
</form>
<?php include __DIR__ . '/_footer.php'; ?>
