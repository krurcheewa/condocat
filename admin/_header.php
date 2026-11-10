<?php
$page = basename($_SERVER['SCRIPT_NAME']);
$menu = [
    'index.php' => '📊 แดชบอร์ด',
    'orders.php' => '🧾 คำสั่งซื้อ',
    'products.php' => '🏠 สินค้า',
    'sizes.php' => '📏 ขนาดคอนโด',
    'categories.php' => '🗂️ หมวดหมู่',
    'customers.php' => '👥 ลูกค้า',
];
$active = ['product_form.php' => 'products.php', 'order_view.php' => 'orders.php'][$page] ?? $page;
?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'หลังร้าน') ?> | <?= SHOP_NAME ?> Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= url('assets/style.css') ?>">
</head>
<body>
<div class="admin-layout">
  <aside class="sidebar">
    <a class="logo" href="<?= url('admin/index.php') ?>">🐱 <?= SHOP_NAME ?> Admin</a>
    <?php foreach ($menu as $file => $label): ?>
      <a class="menu <?= $active === $file ? 'active' : '' ?>" href="<?= url('admin/' . $file) ?>"><?= $label ?></a>
    <?php endforeach; ?>
    <a class="menu" href="<?= url('index.php') ?>" target="_blank">🛒 ดูหน้าร้าน</a>
    <a class="menu" href="<?= url('logout.php') ?>">🚪 ออกจากระบบ</a>
  </aside>
  <div class="admin-main">
  <?php if ($f = flash()): ?><div class="alert alert-<?= e($f[1]) ?>"><?= e($f[0]) ?></div><?php endif; ?>
