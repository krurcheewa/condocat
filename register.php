<?php
require __DIR__ . '/config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $pass = $_POST['password'] ?? '';

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'กรุณากรอกชื่อและอีเมลให้ถูกต้อง';
    } elseif (strlen($pass) < 6) {
        $error = 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร';
    } elseif ($pass !== ($_POST['password2'] ?? '')) {
        $error = 'รหัสผ่านทั้งสองช่องไม่ตรงกัน';
    } else {
        $stmt = $pdo->prepare('SELECT 1 FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'อีเมลนี้ถูกใช้งานแล้ว';
        } else {
            $pdo->prepare('INSERT INTO users (name, email, password, phone) VALUES (?,?,?,?)')
                ->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT), $phone]);
            session_regenerate_id(true);
            $_SESSION['user'] = ['id' => $pdo->lastInsertId(), 'name' => $name, 'email' => $email, 'phone' => $phone, 'address' => null, 'role' => 'customer'];
            flash('สมัครสมาชิกสำเร็จ');
            redirect('index.php');
        }
    }
}

$title = 'สมัครสมาชิก';
include __DIR__ . '/includes/header.php';
?>
<form method="post" class="panel narrow">
  <h1>สมัครสมาชิก</h1>
  <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
  <?= csrf_field() ?>
  <label>ชื่อ-นามสกุล <input name="name" required value="<?= e($_POST['name'] ?? '') ?>"></label>
  <label>อีเมล <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"></label>
  <label>เบอร์โทร <input name="phone" value="<?= e($_POST['phone'] ?? '') ?>"></label>
  <label>รหัสผ่าน <input type="password" name="password" required minlength="6"></label>
  <label>ยืนยันรหัสผ่าน <input type="password" name="password2" required minlength="6"></label>
  <button class="btn btn-primary btn-block">สมัครสมาชิก</button>
  <p>มีบัญชีแล้ว? <a href="<?= url('login.php') ?>">เข้าสู่ระบบ</a></p>
</form>
<?php include __DIR__ . '/includes/footer.php'; ?>
