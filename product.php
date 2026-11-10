<?php
require __DIR__ . '/config.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE p.id = ? AND p.is_active = 1');
$stmt->execute([$id]);
$p = $stmt->fetch();
if (!$p) { http_response_code(404); flash('ไม่พบสินค้า', 'error'); redirect('index.php'); }

$stmt = $pdo->prepare('SELECT v.*, s.name AS size_name, s.code, s.detail FROM product_variants v JOIN sizes s ON s.id = v.size_id WHERE v.product_id = ? ORDER BY s.sort_order');
$stmt->execute([$id]);
$variants = $stmt->fetchAll();
$first = null;
foreach ($variants as $v) { if ($v['stock'] > 0) { $first = $v; break; } }

$title = $p['name'];
include __DIR__ . '/includes/header.php';
?>
<p><a href="<?= url('index.php') ?>">← กลับหน้าร้าน</a></p>
<div class="product">
  <img class="product-img" src="<?= product_image($p['image']) ?>" alt="<?= e($p['name']) ?>">
  <div>
    <small class="muted"><?= e($p['category_name'] ?? '') ?></small>
    <h1><?= e($p['name']) ?></h1>
    <p><?= nl2br(e($p['description'])) ?></p>
    <?php if ($p['material']): ?><p><b>วัสดุ:</b> <?= e($p['material']) ?></p><?php endif; ?>

    <form method="post" action="<?= url('cart.php') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add">
      <h3>เลือกขนาด</h3>
      <div class="size-options">
        <?php foreach ($variants as $v): ?>
          <label class="size-option <?= $v['stock'] <= 0 ? 'disabled' : '' ?>">
            <input type="radio" name="variant_id" value="<?= $v['id'] ?>" <?= $v['stock'] <= 0 ? 'disabled' : '' ?>
                   <?= $first && $first['id'] == $v['id'] ? 'checked' : '' ?>>
            <span>
              <b><?= e($v['size_name']) ?></b> — <span class="price"><?= money($v['price']) ?></span><br>
              <small><?= e($v['dimension']) ?><?= $v['levels'] ? ' · ' . (int)$v['levels'] . ' ชั้น' : '' ?> · <?= e($v['detail']) ?></small><br>
              <small class="<?= $v['stock'] > 0 ? 'muted' : 'text-danger' ?>"><?= $v['stock'] > 0 ? 'คงเหลือ ' . (int)$v['stock'] . ' ชิ้น' : 'สินค้าหมด' ?></small>
            </span>
          </label>
        <?php endforeach; ?>
      </div>
      <?php if ($first): ?>
        <div class="row">
          <label>จำนวน <input type="number" name="qty" value="1" min="1" max="99" style="width:80px"></label>
          <button class="btn btn-primary">หยิบใส่ตะกร้า</button>
        </div>
      <?php else: ?>
        <p class="text-danger">สินค้าหมดทุกขนาด</p>
      <?php endif; ?>
    </form>
  </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
