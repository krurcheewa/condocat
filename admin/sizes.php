<?php
require __DIR__ . '/../config.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $data = [strtoupper(trim($_POST['code'] ?? '')), trim($_POST['name'] ?? ''), trim($_POST['detail'] ?? ''), (int)($_POST['sort_order'] ?? 0)];
    try {
        if ($action === 'delete') {
            $pdo->prepare('DELETE FROM sizes WHERE id = ?')->execute([$id]);
            flash('ลบขนาดแล้ว');
        } elseif ($data[0] === '' || $data[1] === '') {
            flash('กรุณากรอกรหัสและชื่อขนาด', 'error');
        } elseif ($action === 'update') {
            $pdo->prepare('UPDATE sizes SET code=?, name=?, detail=?, sort_order=? WHERE id=?')->execute([...$data, $id]);
            flash('บันทึกขนาดแล้ว');
        } else {
            $pdo->prepare('INSERT INTO sizes (code, name, detail, sort_order) VALUES (?,?,?,?)')->execute($data);
            flash('เพิ่มขนาดแล้ว');
        }
    } catch (PDOException $ex) {
        flash($action === 'delete' ? 'ลบไม่ได้: ยังมีสินค้าที่ใช้ขนาดนี้อยู่' : 'รหัสขนาดซ้ำกับที่มีอยู่', 'error');
    }
    redirect('admin/sizes.php');
}

$sizes = $pdo->query('SELECT s.*, (SELECT COUNT(*) FROM product_variants WHERE size_id = s.id) AS used FROM sizes s ORDER BY sort_order')->fetchAll();

$title = 'ขนาดคอนโด';
include __DIR__ . '/_header.php';
?>
<h1>ขนาดคอนโด</h1>
<p class="muted">กำหนดขนาดมาตรฐานของร้าน ราคาและขนาดจริงของแต่ละสินค้ากำหนดได้ในหน้าแก้ไขสินค้า</p>
<div class="table-wrap">
<table class="table">
  <tr><th>รหัส</th><th>ชื่อขนาด</th><th>คำอธิบาย</th><th>ลำดับ</th><th>สินค้าที่ใช้</th><th>จัดการ</th></tr>
  <?php foreach ($sizes as $s): ?>
    <?php $f = 'size' . $s['id']; ?>
    <tr>
      <td><input form="<?= $f ?>" name="code" value="<?= e($s['code']) ?>" style="width:70px"></td>
      <td><input form="<?= $f ?>" name="name" value="<?= e($s['name']) ?>"></td>
      <td><input form="<?= $f ?>" name="detail" value="<?= e($s['detail']) ?>"></td>
      <td><input form="<?= $f ?>" type="number" name="sort_order" value="<?= (int)$s['sort_order'] ?>" style="width:70px"></td>
      <td><?= (int)$s['used'] ?></td>
      <td class="actions">
        <form id="<?= $f ?>" method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $s['id'] ?>"><input type="hidden" name="action" value="update">
          <button class="btn btn-sm">บันทึก</button></form>
        <form method="post" onsubmit="return confirm('ยืนยันลบขนาดนี้?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $s['id'] ?>"><input type="hidden" name="action" value="delete">
          <button class="btn btn-sm btn-danger" <?= $s['used'] ? 'disabled title="มีสินค้าใช้อยู่"' : '' ?>>ลบ</button></form>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
</div>
<form method="post" class="panel" style="margin-top:20px">
  <?= csrf_field() ?>
  <h3 style="margin-top:0">เพิ่มขนาดใหม่</h3>
  <div class="form-grid">
    <label>รหัส (เช่น XXL) <input name="code" required></label>
    <label>ชื่อขนาด <input name="name" required></label>
    <label>คำอธิบาย <input name="detail"></label>
    <label>ลำดับการแสดง <input type="number" name="sort_order" value="<?= count($sizes) + 1 ?>"></label>
  </div>
  <button class="btn btn-primary">เพิ่มขนาด</button>
</form>
<?php include __DIR__ . '/_footer.php'; ?>
