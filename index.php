<?php
require_once __DIR__ . '/includes/functions.php';
if (!empty($_SESSION['admin_id'])) {
    header('Location: ' . url('admin/dashboard.php'));
} else {
    header('Location: ' . url('login.php'));
}
exit;
