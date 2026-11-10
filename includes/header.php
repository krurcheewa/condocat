<?php $u = current_user(); ?>
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(($title ?? '') ? $title . ' | ' : '') ?><?= SHOP_NAME ?> - ร้านคอนโดแมว</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= url('assets/style.css') ?>">
</head>
<body>
<header class="topbar">
  <div class="container nav">
    <a class="logo" href="<?= url('index.php') ?>">🐱 <?= SHOP_NAME ?></a>
    <nav>
      <a href="<?= url('index.php') ?>">สินค้า</a>
      <a href="<?= url('cart.php') ?>">ตะกร้า <span class="cart-count"><?= cart_count() ?></span></a>
      <?php if ($u): ?>
        <a href="<?= url('account.php') ?>">คำสั่งซื้อของฉัน</a>
        <?php if (is_admin()): ?><a href="<?= url('admin/index.php') ?>">หลังร้าน</a><?php endif; ?>
        <a href="<?= url('logout.php') ?>">ออกจากระบบ (<?= e($u['name']) ?>)</a>
      <?php else: ?>
        <a href="<?= url('login.php') ?>">เข้าสู่ระบบ</a>
        <a href="<?= url('register.php') ?>">สมัครสมาชิก</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main class="container">
<?php if ($f = flash()): ?><div class="alert alert-<?= e($f[1]) ?>"><?= e($f[0]) ?></div><?php endif; ?>
