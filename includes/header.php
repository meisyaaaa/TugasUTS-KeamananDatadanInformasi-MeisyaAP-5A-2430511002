<?php
$page_title = $page_title ?? 'Dashboard';
$active = $active ?? '';
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page_title) ?> | <?= e(APP_NAME) ?></title>
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
@media(max-width:850px){.sidebar{position:static;width:100%;min-height:auto}.main{margin-left:0;padding:18px}.sidebar .nav{display:flex;flex-wrap:wrap}}
</style>
</head>
<body>
<aside class="sidebar">
<a class="brand" href="<?= url('admin/dashboard.php') ?>"><i class="bi bi-shield-lock me-2"></i>ARSIP DIGITAL</a>
<div class="text-uppercase small text-secondary px-3 mb-2">Menu utama</div>
<nav class="nav flex-column">
<a class="nav-link <?= $active==='dashboard'?'active':'' ?>" href="<?= url('admin/dashboard.php') ?>"><i class="bi bi-grid me-2"></i>Dashboard</a>
<a class="nav-link <?= $active==='arsip'?'active':'' ?>" href="<?= url('admin/arsip.php') ?>"><i class="bi bi-folder2-open me-2"></i>Data Arsip</a>
<a class="nav-link <?= $active==='tambah'?'active':'' ?>" href="<?= url('admin/tambah.php') ?>"><i class="bi bi-file-earmark-plus me-2"></i>Tambah Arsip</a>
<a class="nav-link <?= $active==='aktivitas'?'active':'' ?>" href="<?= url('admin/aktivitas.php') ?>"><i class="bi bi-clock-history me-2"></i>Riwayat Aktivitas</a>
</nav>
<hr class="border-secondary my-4">
<a class="nav-link" href="<?= url('logout.php') ?>"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a>
</aside>
<main class="main">
<div class="topline"><div><div class="text-secondary small">Sistem Informasi Pengarsipan</div><h4 class="mb-0 fw-bold"><?= e($page_title) ?></h4></div>
<div class="text-end"><div class="fw-semibold"><?= e($_SESSION['admin_username'] ?? 'Admin') ?></div><small class="text-secondary">Administrator</small></div></div>
<?php show_flash(); ?>
