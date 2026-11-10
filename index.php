<?php
require __DIR__ . '/config.php';

$q = trim($_GET['q'] ?? '');
$cat = (int)($_GET['cat'] ?? 0);
$size = (int)($_GET['size'] ?? 0);
$sort = $_GET['sort'] ?? 'new';

$where = ['p.is_active = 1'];
$params = [];
if ($q !== '') { $where[] = 'p.name LIKE ?'; $params[] = "%$q%"; }
if ($cat) { $where[] = 'p.category_id = ?'; $params[] = $cat; }
if ($size) { $where[] = 'EXISTS (SELECT 1 FROM product_variants x WHERE x.product_id = p.id AND x.size_id = ?)'; $params[] = $size; }
$order = match ($sort) { 'price_asc' => 'min_price ASC', 'price_desc' => 'min_price DESC', default => 'p.id DESC' };

$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name, MIN(v.price) AS min_price, MAX(v.price) AS max_price,
        SUM(v.stock) AS total_stock, GROUP_CONCAT(s.code ORDER BY s.sort_order SEPARATOR ' ') AS size_codes
    FROM products p
    LEFT JOIN categories c ON c.id = p.category_id
    JOIN product_variants v ON v.product_id = p.id
    JOIN sizes s ON s.id = v.size_id
    WHERE " . implode(' AND ', $where) . "
    GROUP BY p.id ORDER BY $order");
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$sizes = $pdo->query('SELECT * FROM sizes ORDER BY sort_order')->fetchAll();

$title = 'หน้าร้าน';
include __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <h1>คอนโดแมว ทุกขนาด สำหรับทุกบ้าน</h1>
  <p>เลือกขนาดที่ใช่สำหรับน้องแมวของคุณ ตั้งแต่ S ถึง XL · ส่งฟรีเมื่อซื้อครบ <?= money(FREE_SHIPPING_MIN) ?></p>
</section>

<form class="filters" method="get">
  <input type="search" name="q" value="<?= e($q) ?>" placeholder="ค้นหาสินค้า...">
  <select name="cat">
    <option value="0">ทุกหมวดหมู่</option>
    <?php foreach ($categories as $c): ?>
      <option value="<?= $c['id'] ?>" <?= $cat == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="size">
    <option value="0">ทุกขนาด</option>
    <?php foreach ($sizes as $s): ?>
      <option value="<?= $s['id'] ?>" <?= $size == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="sort">
    <option value="new">ใหม่ล่าสุด</option>
    <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>ราคาต่ำ → สูง</option>
    <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>ราคาสูง → ต่ำ</option>
  </select>
  <button class="btn">กรอง</button>
</form>

<?php if (!$products): ?>
  <p class="empty">ไม่พบสินค้าที่ค้นหา</p>
<?php endif; ?>
<div class="grid">
  <?php foreach ($products as $p): ?>
    <a class="card" href="<?= url('product.php?id=' . $p['id']) ?>">
      <img src="<?= product_image($p['image']) ?>" alt="<?= e($p['name']) ?>">
      <div class="card-body">
        <small class="muted"><?= e($p['category_name'] ?? '-') ?></small>
        <h3><?= e($p['name']) ?></h3>
        <div class="sizes"><?php foreach (explode(' ', $p['size_codes']) as $sc): ?><span class="chip"><?= e($sc) ?></span><?php endforeach; ?></div>
        <div class="price">
          <?= money($p['min_price']) ?><?= $p['max_price'] > $p['min_price'] ? ' - ' . money($p['max_price']) : '' ?>
        </div>
        <?php if ($p['total_stock'] <= 0): ?><span class="badge badge-cancelled">สินค้าหมด</span><?php endif; ?>
      </div>
    </a>
  <?php endforeach; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
