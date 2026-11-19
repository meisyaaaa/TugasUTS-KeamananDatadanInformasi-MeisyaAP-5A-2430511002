<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
if (!empty($_SESSION['admin_id'])) { header('Location: ' . url('admin/dashboard.php')); exit; }

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim($_POST['username'] ?? '');
    $privateKey = (string)($_POST['private_key'] ?? '');

    // Alternatif: file .pem yang diunggah (dibaca sekali, tidak disimpan).
    if (trim($privateKey) === '' && !empty($_FILES['key_file']) && $_FILES['key_file']['error'] === UPLOAD_ERR_OK && $_FILES['key_file']['size'] <= 16384) {
        $content = @file_get_contents($_FILES['key_file']['tmp_name']);
        if ($content !== false) $privateKey = $content;
    }

    if ($username === '' || trim($privateKey) === '') {
        $error = 'Username dan private key wajib diisi.';
    } else {
        $q = $pdo->prepare('SELECT id, username FROM admins WHERE username=? LIMIT 1');
        $q->execute([$username]);
        $admin = $q->fetch();

        if (!$admin) {
            $error = 'Username tidak terdaftar.';
        } else {
            // Kunci cocok -> data arsip tampil terdekripsi. Kunci salah -> tetap masuk, tetapi data tampil terenkripsi.
            $valid = verify_private_key($privateKey);
            unset($privateKey);

            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int)$admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['key_verified'] = $valid;
            log_activity($pdo, $valid
                ? 'Masuk dengan private key valid (data didekripsi)'
                : 'Masuk dengan private key tidak cocok (data tampil terenkripsi)');
            header('Location: ' . url('admin/dashboard.php'));
            exit;
        }
    }
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><title>Login Admin - Arsip Digital</title>
<style>
body{background:linear-gradient(135deg,#14213d,#2457c5);min-height:100vh;display:grid;place-items:center;padding:24px 0}
.login{width:min(460px,92vw);border:0;border-radius:20px}
.keybox{font-family:ui-monospace,SFMono-Regular,Consolas,monospace;font-size:.78rem;line-height:1.35;resize:vertical}
.back{color:#64748b;text-decoration:none;font-size:.92rem}
.back:hover{color:#2457c5}
</style></head>
<body><div class="card login shadow-lg p-4 p-md-5"><div class="text-center mb-4"><div class="fs-1 text-primary"><i class="bi bi-shield-lock"></i></div><h3 class="fw-bold">Arsip Digital</h3><p class="text-secondary mb-0">Masuk ke panel administrator dengan private key</p></div>
<?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" autocomplete="off">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<label class="form-label" for="username">Username</label>
<input class="form-control form-control-lg mb-3" id="username" name="username" value="<?= e($username) ?>" autocomplete="username" required autofocus>
<label class="form-label" for="private_key">Private key</label>
<textarea class="form-control keybox mb-2" id="private_key" name="private_key" rows="6" spellcheck="false" autocomplete="off" autocapitalize="off" placeholder="-----BEGIN PRIVATE KEY-----&#10;...&#10;-----END PRIVATE KEY-----"></textarea>
<div class="d-flex align-items-center gap-2 mb-2">
  <label class="btn btn-sm btn-outline-secondary mb-0" for="key_file"><i class="bi bi-upload me-1"></i>Pilih file .pem</label>
  <input type="file" id="key_file" name="key_file" accept=".pem,.key,.txt" hidden>
  <span class="small text-secondary text-truncate" id="key_file_name">Tempel kunci, atau pilih file.</span>
</div>
<div class="form-text mb-4">Private key hanya dipakai untuk pemeriksaan saat masuk dan tidak disimpan. Jika kunci tidak cocok, data arsip tampil dalam bentuk terenkripsi.</div>
<button class="btn btn-primary btn-lg w-100">Masuk</button></form>
<div class="text-center mt-4"><a class="back" href="<?= url('index.php') ?>"><i class="bi bi-arrow-left me-1"></i>Kembali ke beranda</a></div></div>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<script>
document.getElementById('key_file').addEventListener('change', function () {
  var f = this.files[0];
  if (!f) return;
  document.getElementById('key_file_name').textContent = f.name;
  var r = new FileReader();
  r.onload = function () { document.getElementById('private_key').value = String(r.result || ''); };
  r.readAsText(f);
});
</script></body></html>
