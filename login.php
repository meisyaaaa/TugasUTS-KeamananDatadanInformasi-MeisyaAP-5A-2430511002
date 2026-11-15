<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
if (!empty($_SESSION['admin_id'])) { header('Location: ' . url('admin/dashboard.php')); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $q = $pdo->prepare('SELECT id, username, password FROM admins WHERE username=? LIMIT 1');
    $q->execute([$username]);
    $admin = $q->fetch();
    if ($admin && password_verify($password, $admin['password'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int)$admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        header('Location: ' . url('admin/dashboard.php')); exit;
    }
    $error = 'Username atau password salah.';
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><title>Login Admin - Arsip Digital</title>
<style>body{background:linear-gradient(135deg,#14213d,#2457c5);min-height:100vh;display:grid;place-items:center}.login{width:min(430px,92vw);border:0;border-radius:20px}</style></head>
<body><div class="card login shadow-lg p-4 p-md-5"><div class="text-center mb-4"><div class="fs-1 text-primary"><i class="bi bi-shield-lock"></i></div><h3 class="fw-bold">Arsip Digital</h3><p class="text-secondary">Silakan masuk ke panel administrator</p></div>
<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<form method="post"><label class="form-label">Username</label><input class="form-control form-control-lg mb-3" name="username" autocomplete="username" required>
<label class="form-label">Password</label><input class="form-control form-control-lg mb-4" type="password" name="password" autocomplete="current-password" required>
<button class="btn btn-primary btn-lg w-100">Masuk</button></form></div>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"></body></html>
