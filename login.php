<?php
require __DIR__ . '/config.php';

$error = '';
$next = $_GET['next'] ?? '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([trim($_POST['email'] ?? '')]);
    $user = $stmt->fetch();
    if ($user && password_verify($_POST['password'] ?? '', $user['password'])) {
        session_regenerate_id(true);
        unset($user['password']);
        $_SESSION['user'] = $user;
        flash('ยินดีต้อนรับ ' . $user['name']);
        // อนุญาตให้ redirect เฉพาะภายในเว็บเดียวกัน
        $next = $_POST['next'] ?? '';
        if ($next !== '' && str_starts_with($next, BASE_URL . '/') && !str_starts_with($next, '//')) {
            header('Location: ' . $next);
            exit;
        }
        redirect($user['role'] === 'admin' ? 'admin/index.php' : 'index.php');
    }
    $error = 'อีเมลหรือรหัสผ่านไม่ถูกต้อง';
}

$title = 'เข้าสู่ระบบ';
include __DIR__ . '/includes/header.php';
?>
<form method="post" class="panel narrow">
  <h1>เข้าสู่ระบบ</h1>
  <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
  <?= csrf_field() ?>
  <input type="hidden" name="next" value="<?= e($next) ?>">
  <label>อีเมล <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"></label>
  <label>รหัสผ่าน <input type="password" name="password" required></label>
  <button class="btn btn-primary btn-block">เข้าสู่ระบบ</button>
  <p>ยังไม่มีบัญชี? <a href="<?= url('register.php') ?>">สมัครสมาชิก</a></p>
</form>
<?php include __DIR__ . '/includes/footer.php'; ?>
