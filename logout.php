<?php
require __DIR__ . '/config.php';
unset($_SESSION['user']);
session_regenerate_id(true);
flash('ออกจากระบบแล้ว');
redirect('index.php');
