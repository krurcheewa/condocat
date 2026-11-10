<?php
require __DIR__ . '/../config.php';
if (is_admin()) redirect('admin/index.php');
// ใช้หน้าเข้าสู่ระบบเดียวกับหน้าร้าน แล้วกลับมาหลังร้าน
if (current_user()) flash('บัญชีนี้ไม่มีสิทธิ์เข้าหลังร้าน', 'error');
redirect('login.php?next=' . urlencode(url('admin/index.php')));
