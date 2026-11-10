<?php
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $action = $_POST['action'] ?? '';
    $cart = cart();

    if ($action === 'add') {
        $vid = (int)($_POST['variant_id'] ?? 0);
        $qty = max(1, (int)($_POST['qty'] ?? 1));
        $stmt = $pdo->prepare('SELECT v.stock FROM product_variants v JOIN products p ON p.id = v.product_id WHERE v.id = ? AND p.is_active = 1');
        $stmt->execute([$vid]);
        $stock = $stmt->fetchColumn();
        if ($stock === false) { flash('กรุณาเลือกขนาดสินค้า', 'error'); redirect('index.php'); }
        $newQty = ($cart[$vid] ?? 0) + $qty;
        if ($newQty > $stock) {
            $newQty = (int)$stock;
            flash('จำนวนเกินสต็อก ปรับเป็น ' . $stock . ' ชิ้น', 'error');
        } else {
            flash('เพิ่มสินค้าลงตะกร้าแล้ว');
        }
        if ($newQty > 0) $cart[$vid] = $newQty;
    } elseif ($action === 'update') {
        foreach ((array)($_POST['qty'] ?? []) as $vid => $qty) {
            $qty = (int)$qty;
            if ($qty <= 0) unset($cart[(int)$vid]); else $cart[(int)$vid] = $qty;
        }
        flash('อัปเดตตะกร้าแล้ว');
    } elseif ($action === 'remove') {
        unset($cart[(int)$_POST['variant_id']]);
        flash('ลบสินค้าออกจากตะกร้าแล้ว');
    }
    $_SESSION['cart'] = $cart;
    redirect('cart.php');
}

$items = cart_items($pdo);
$subtotal = array_sum(array_column($items, 'line_total'));
$shipping = shipping_fee($subtotal);

$title = 'ตะกร้าสินค้า';
include __DIR__ . '/includes/header.php';
?>
<h1>ตะกร้าสินค้า</h1>
<?php if (!$items): ?>
  <p class="empty">ยังไม่มีสินค้าในตะกร้า <a href="<?= url('index.php') ?>">เลือกซื้อสินค้า</a></p>
<?php else: ?>
<form method="post">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="update">
  <div class="table-wrap">
  <table class="table">
    <tr><th>สินค้า</th><th>ขนาด</th><th>ราคา</th><th>จำนวน</th><th>รวม</th><th></th></tr>
    <?php foreach ($items as $it): ?>
      <tr>
        <td class="item-cell"><img class="thumb" src="<?= product_image($it['image']) ?>" alt=""> <?= e($it['product_name']) ?></td>
        <td><?= e($it['size_name']) ?></td>
        <td><?= money($it['price']) ?></td>
        <td>
          <input type="number" name="qty[<?= $it['id'] ?>]" value="<?= $it['qty'] ?>" min="0" max="<?= $it['stock'] ?>" style="width:70px">
          <?php if ($it['qty'] > $it['stock']): ?><br><small class="text-danger">เหลือ <?= $it['stock'] ?> ชิ้น</small><?php endif; ?>
        </td>
        <td><?= money($it['line_total']) ?></td>
        <td><button class="btn btn-sm btn-danger" form="rm<?= $it['id'] ?>">ลบ</button></td>
      </tr>
    <?php endforeach; ?>
  </table>
  </div>
  <div class="summary">
    <div>ยอดสินค้า: <b><?= money($subtotal) ?></b></div>
    <div>ค่าจัดส่ง: <b><?= $shipping ? money($shipping) : 'ฟรี' ?></b></div>
    <div class="total">ยอดรวม: <?= money($subtotal + $shipping) ?></div>
    <div class="row">
      <button class="btn">อัปเดตจำนวน</button>
      <a class="btn btn-primary" href="<?= url('checkout.php') ?>">สั่งซื้อสินค้า</a>
    </div>
  </div>
</form>
<?php foreach ($items as $it): ?>
  <form id="rm<?= $it['id'] ?>" method="post"><?= csrf_field() ?><input type="hidden" name="action" value="remove"><input type="hidden" name="variant_id" value="<?= $it['id'] ?>"></form>
<?php endforeach; ?>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php'; ?>
