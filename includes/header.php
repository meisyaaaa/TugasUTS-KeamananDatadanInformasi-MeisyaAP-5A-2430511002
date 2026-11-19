<?php
$page_title = $page_title ?? 'Dashboard';
$active = $active ?? '';
$unlocked = is_unlocked();
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $unlocked ? e($page_title).' | '.e(APP_NAME) : 'Terenkripsi' ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
:root{--navy:#14213d;--blue:#2457c5;--bg:#f4f6fb}
body{background:var(--bg);color:#1e293b;font-family:Inter,system-ui,-apple-system,"Segoe UI",sans-serif}
.sidebar{width:250px;min-height:100vh;background:var(--navy);position:fixed;inset:0 auto 0 0;padding:24px 16px;z-index:10}
.brand{color:#fff;font-weight:750;font-size:1.08rem;padding:8px 12px 26px;display:block;text-decoration:none}
.sidebar .nav-link{color:#cbd5e1;padding:11px 13px;border-radius:10px;margin:3px 0}
.sidebar .nav-link:hover,.sidebar .nav-link.active{background:#263b63;color:#fff}
.main{margin-left:250px;padding:28px;max-width:1600px}
.topline{display:flex;justify-content:space-between;align-items:center;margin-bottom:25px}
.card{border:0;border-radius:16px;box-shadow:0 5px 24px rgba(15,23,42,.055)}
.stat-icon{width:46px;height:46px;display:grid;place-items:center;border-radius:13px;background:#eaf0ff;color:var(--blue);font-size:1.3rem}
.btn-primary{background:var(--blue);border-color:var(--blue)}
.table>:not(caption)>*>*{padding:13px 12px}
.cipher{font-family:ui-monospace,SFMono-Regular,Consolas,monospace;font-size:.8rem;color:#8a6414;background:#fff6e0;padding:2px 8px;border-radius:6px;word-break:break-all;display:inline-block}
/* [BARU] gaya untuk label antarmuka yang dienkripsi */
.cipher-ui{font-size:.78rem;padding:1px 6px;vertical-align:middle}
.sidebar .cipher-ui{background:rgba(255,255,255,.1);color:#cbd5e1}
.brand .cipher-ui{color:#fff;font-size:.9rem}
.topline h4 .cipher-ui{font-size:1.05rem;padding:2px 8px}
.sidebar .nav-link.locked{color:#64748b;cursor:not-allowed}
.sidebar .nav-link.locked:hover{background:transparent;color:#64748b}
.keystate{display:inline-flex;align-items:center;gap:6px;font-size:.78rem;font-weight:600;padding:3px 10px;border-radius:999px;margin-top:4px}
.keystate.ok{background:#e3f6ec;color:#17794f}
.keystate.no{background:#fff1d6;color:#8a6414}
@media(max-width:850px){.sidebar{position:static;width:100%;min-height:auto}.main{margin-left:0;padding:18px}.sidebar .nav{display:flex;flex-wrap:wrap}}
</style>
</head>
<body>
<aside class="sidebar">
<a class="brand" href="<?= url('admin/dashboard.php') ?>"><i class="bi bi-shield-lock me-2"></i><?= ct('ARSIP DIGITAL') ?></a>
<div class="text-uppercase small text-secondary px-3 mb-2"><?= ct('Menu utama') ?></div>
<nav class="nav flex-column">
<a class="nav-link <?= $active==='dashboard'?'active':'' ?>" href="<?= url('admin/dashboard.php') ?>"><i class="bi bi-grid me-2"></i><?= ct('Dashboard') ?></a>
<a class="nav-link <?= $active==='arsip'?'active':'' ?>" href="<?= url('admin/arsip.php') ?>"><i class="bi bi-folder2-open me-2"></i><?= ct('Data Arsip') ?></a>
<?php if ($unlocked): ?><a class="nav-link <?= $active==='tambah'?'active':'' ?>" href="<?= url('admin/tambah.php') ?>"><i class="bi bi-file-earmark-plus me-2"></i><?= ct('Tambah Arsip') ?></a>
<?php else: ?><span class="nav-link locked" title="Terkunci: private key tidak cocok"><i class="bi bi-lock me-2"></i><?= ct('Tambah Arsip') ?></span><?php endif; ?>
<?php if ($unlocked): ?><a class="nav-link <?= $active==='aktivitas'?'active':'' ?>" href="<?= url('admin/aktivitas.php') ?>"><i class="bi bi-clock-history me-2"></i><?= ct('Riwayat Aktivitas') ?></a>
<?php else: ?><span class="nav-link locked" title="Terkunci: private key tidak cocok"><i class="bi bi-lock me-2"></i><?= ct('Riwayat Aktivitas') ?></span><?php endif; ?>
</nav>
<hr class="border-secondary my-4">
<a class="nav-link" href="<?= url('logout.php') ?>"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a>
</aside>
<main class="main">
<div class="topline"><div><div class="text-secondary small"><?= ct('Sistem Informasi Pengarsipan') ?></div><h4 class="mb-0 fw-bold"><?= ct($page_title) ?></h4></div>
<div class="text-end"><div class="fw-semibold"><?= ct($_SESSION['admin_username'] ?? 'Admin') ?></div><small class="text-secondary d-block"><?= ct('Administrator') ?></small>
<?php if ($unlocked): ?><span class="keystate ok"><i class="bi bi-unlock"></i>Kunci valid</span><?php else: ?><span class="keystate no"><i class="bi bi-lock"></i>Mode terenkripsi</span><?php endif; ?></div></div>
<?php show_flash(); ?>