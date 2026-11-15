<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();
$total = (int)$pdo->query('SELECT COUNT(*) FROM arsip')->fetchColumn();
$incoming = (int)$pdo->query("SELECT COUNT(*) FROM arsip WHERE kategori='Surat Masuk'")->fetchColumn();
$outgoing = (int)$pdo->query("SELECT COUNT(*) FROM arsip WHERE kategori='Surat Keluar'")->fetchColumn();
$other = (int)$pdo->query("SELECT COUNT(*) FROM arsip WHERE kategori='Dokumen Lainnya'")->fetchColumn();
$recent = $pdo->query('SELECT id,nomor_arsip,kategori,created_at FROM arsip ORDER BY id DESC LIMIT 6')->fetchAll();
$page_title='Dashboard'; $active='dashboard'; require __DIR__.'/../includes/header.php';
?>
<div class="row g-3 mb-4">
<?php foreach ([['Total Arsip',$total,'bi-archive'],['Surat Masuk',$incoming,'bi-inbox'],['Surat Keluar',$outgoing,'bi-send'],['Dokumen Lainnya',$other,'bi-file-earmark-lock']] as $s): ?>
<div class="col-sm-6 col-xl-3"><div class="card p-3"><div class="d-flex justify-content-between align-items-center"><div><div class="text-secondary small"><?= e($s[0]) ?></div><div class="display-6 fw-bold"><?= (int)$s[1] ?></div></div><div class="stat-icon"><i class="bi <?= e($s[2]) ?>"></i></div></div></div></div>
<?php endforeach; ?>
</div>
<div class="row g-3">
<div class="col-lg-8"><div class="card p-4"><div class="d-flex justify-content-between mb-3"><h5 class="fw-bold mb-0">Arsip terbaru</h5><a href="<?= url('admin/arsip.php') ?>">Lihat semua</a></div>
<div class="table-responsive"><table class="table align-middle"><thead><tr><th>Nomor Arsip</th><th>Kategori</th><th>Tanggal</th><th></th></tr></thead><tbody>
<?php foreach($recent as $r): ?><tr><td><?= e($r['nomor_arsip']) ?></td><td><?= e($r['kategori']) ?></td><td><?= e($r['created_at']) ?></td><td><a class="btn btn-sm btn-outline-primary" href="<?= url('admin/detail.php?id='.(int)$r['id']) ?>">Buka</a></td></tr><?php endforeach; ?>
<?php if(!$recent): ?><tr><td colspan="4" class="text-center text-secondary">Belum ada arsip.</td></tr><?php endif; ?>
</tbody></table></div></div></div>
<div class="col-lg-4"><div class="card p-4 h-100"><h5 class="fw-bold">Keamanan arsip</h5><div class="d-flex gap-3 my-3"><i class="bi bi-shield-check text-success fs-3"></i><div><b>AES-256-GCM</b><div class="small text-secondary">Enkripsi metadata dan file dokumen.</div></div></div><div class="d-flex gap-3"><i class="bi bi-key text-primary fs-3"></i><div><b>RSA-OAEP</b><div class="small text-secondary">Melindungi kunci AES dengan public key.</div></div></div><hr><a class="btn btn-primary" href="<?= url('admin/tambah.php') ?>"><i class="bi bi-plus-lg me-1"></i>Tambah Arsip</a></div></div>
</div></main><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script></body></html>
