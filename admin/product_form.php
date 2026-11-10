<?php
require __DIR__ . '/../config.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$product = ['name' => '', 'description' => '', 'material' => '', 'category_id' => null, 'image' => null, 'is_active' => 1];
$variants = []; // size_id => row
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch() ?: redirect('admin/products.php');
    $stmt = $pdo->prepare('SELECT * FROM product_variants WHERE product_id = ?');
    $stmt->execute([$id]);
    foreach ($stmt as $v) $variants[$v['size_id']] = $v;
}
$sizes = $pdo->query('SELECT * FROM sizes ORDER BY sort_order')->fetchAll();
$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $product['name'] = trim($_POST['name'] ?? '');
    $product['description'] = trim($_POST['description'] ?? '');
    $product['material'] = trim($_POST['material'] ?? '');
    $product['category_id'] = (int)($_POST['category_id'] ?? 0) ?: null;
    $product['is_active'] = isset($_POST['is_active']) ? 1 : 0;
    $posted = $_POST['v'] ?? [];
    $enabled = array_filter($posted, fn($v) => !empty($v['on']));

    if ($product['name'] === '') {
        $error = 'กรุณากรอกชื่อสินค้า';
    } elseif (!$enabled) {
        $error = 'กรุณาเลือกอย่างน้อย 1 ขนาด';
    } else {
        foreach ($enabled as $v) {
            if (!is_numeric($v['price'] ?? '') || $v['price'] <= 0) { $error = 'กรุณากรอกราคาให้ถูกต้องทุกขนาดที่เลือก'; break; }
        }
    }

    if (!$error) {
        try {
            $newImg = upload_image('image');
            $pdo->beginTransaction();
            if ($newImg) {
                if ($product['image'] && is_file(UPLOAD_DIR . $product['image'])) unlink(UPLOAD_DIR . $product['image']);
                $product['image'] = $newImg;
            }
            $vals = [$product['category_id'], $product['name'], $product['description'], $product['material'], $product['image'], $product['is_active']];
            if ($id) {
                $pdo->prepare('UPDATE products SET category_id=?, name=?, description=?, material=?, image=?, is_active=? WHERE id=?')->execute([...$vals, $id]);
            } else {
                $pdo->prepare('INSERT INTO products (category_id, name, description, material, image, is_active) VALUES (?,?,?,?,?,?)')->execute($vals);
                $id = (int)$pdo->lastInsertId();
            }
            $upsert = $pdo->prepare('INSERT INTO product_variants (product_id, size_id, dimension, levels, price, stock) VALUES (?,?,?,?,?,?)
                ON DUPLICATE KEY UPDATE dimension=VALUES(dimension), levels=VALUES(levels), price=VALUES(price), stock=VALUES(stock)');
            $del = $pdo->prepare('DELETE FROM product_variants WHERE product_id = ? AND size_id = ?');
            foreach ($sizes as $s) {
                $v = $posted[$s['id']] ?? [];
                if (!empty($v['on'])) {
                    $upsert->execute([$id, $s['id'], trim($v['dimension'] ?? ''), ($v['levels'] ?? '') === '' ? null : (int)$v['levels'], (float)$v['price'], max(0, (int)($v['stock'] ?? 0))]);
                } else {
                    $del->execute([$id, $s['id']]);
                }
            }
            $pdo->commit();
            flash('บันทึกสินค้าเรียบร้อย');
            redirect('admin/products.php');
        } catch (RuntimeException $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = $ex->getMessage();
        }
    }
    // แสดงค่าที่กรอกไว้เมื่อเกิดข้อผิดพลาด
    $variants = [];
    foreach ($enabled as $sid => $v) $variants[$sid] = $v;
}

$title = $id ? 'แก้ไขสินค้า' : 'เพิ่มสินค้า';
include __DIR__ . '/_header.php';
?>
<p><a href="products.php">← รายการสินค้า</a></p>
<h1><?= $title ?></h1>
<?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="panel">
  <?= csrf_field() ?>
  <div class="form-grid">
    <label>ชื่อสินค้า * <input name="name" required value="<?= e($product['name']) ?>"></label>
    <label>หมวดหมู่
      <select name="category_id">
        <option value="">- ไม่ระบุ -</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $product['category_id'] == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>วัสดุ <input name="material" value="<?= e($product['material']) ?>"></label>
    <label>รูปสินค้า <input type="file" name="image" accept="image/*"></label>
  </div>
  <label>รายละเอียด <textarea name="description" rows="4"><?= e($product['description']) ?></textarea></label>
  <?php if ($product['image']): ?><p><img class="thumb" style="width:120px;height:90px" src="<?= product_image($product['image']) ?>" alt=""> รูปปัจจุบัน</p><?php endif; ?>
  <label class="inline"><input type="checkbox" name="is_active" <?= $product['is_active'] ? 'checked' : '' ?>> เปิดขายสินค้านี้</label>

  <h3>ขนาด ราคา และสต็อก</h3>
  <p class="muted">ติ๊กเลือกขนาดที่สินค้านี้มีขาย แล้วกรอกรายละเอียดของแต่ละขนาด</p>
  <div class="table-wrap">
  <table class="table variant-table">
    <tr><th>มีขาย</th><th>ขนาด</th><th>ขนาดจริง (กxยxส)</th><th>จำนวนชั้น</th><th>ราคา (บาท) *</th><th>สต็อก</th></tr>
    <?php foreach ($sizes as $s): $v = $variants[$s['id']] ?? null; ?>
      <tr>
        <td><input type="checkbox" name="v[<?= $s['id'] ?>][on]" value="1" <?= $v ? 'checked' : '' ?>></td>
        <td><b><?= e($s['name']) ?></b><br><small class="muted"><?= e($s['detail']) ?></small></td>
        <td><input name="v[<?= $s['id'] ?>][dimension]" value="<?= e($v['dimension'] ?? '') ?>" placeholder="เช่น 50x50x100 ซม."></td>
        <td><input type="number" min="1" name="v[<?= $s['id'] ?>][levels]" value="<?= e($v['levels'] ?? '') ?>"></td>
        <td><input type="number" min="1" step="0.01" name="v[<?= $s['id'] ?>][price]" value="<?= e($v['price'] ?? '') ?>"></td>
        <td><input type="number" min="0" name="v[<?= $s['id'] ?>][stock]" value="<?= e($v['stock'] ?? 0) ?>"></td>
      </tr>
    <?php endforeach; ?>
  </table>
  </div>
  <div class="row"><button class="btn btn-primary">บันทึกสินค้า</button><a class="btn" href="products.php">ยกเลิก</a></div>
</form>
<?php include __DIR__ . '/_footer.php'; ?>
