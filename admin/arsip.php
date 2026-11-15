<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();
$search = trim($_GET['q'] ?? '');
$category = $_GET['kategori'] ?? '';
$sql = 'SELECT id,nomor_arsip,kategori,created_at,file_extension,file_size FROM arsip WHERE 1=1';
$params = [];
if ($search !== '') { $sql .= ' AND nomor_arsip LIKE ?'; $params[] = '%'.$search.'%'; }
if (in_array($category,['Surat Masuk','Surat Keluar','Dokumen Lainnya'],true)) { $sql .= ' AND kategori=?'; $params[]=$category; }
$sql .= ' ORDER BY id DESC';
$q=$pdo->prepare($sql); $q->execute($params); $rows=$q->fetchAll();
$page_title='Data Arsip'; $active='arsip'; require __DIR__.'/../includes/header.php';
?>
<div class="card p-4"><div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3"><div><h5 class="fw-bold mb-1">Daftar Arsip</h5><p class="text-secondary mb-0 small">Metadata dan file disimpan dalam bentuk terenkripsi.</p></div><a class="btn btn-primary" href="<?= url('admin/tambah.php') ?>"><i class="bi bi-plus-lg me-1"></i>Tambah Arsip</a></div>
<form class="row g-2 mb-3"><div class="col-md-5"><input class="form-control" name="q" placeholder="Cari nomor arsip..." value="<?= e($search) ?>"></div><div class="col-md-4"><select class="form-select" name="kategori"><option value="">Semua kategori</option><?php foreach(['Surat Masuk','Surat Keluar','Dokumen Lainnya'] as $c): ?><option <?= $category===$c?'selected':'' ?>><?= e($c) ?></option><?php endforeach; ?></select></div><div class="col-md-3"><button class="btn btn-outline-primary w-100">Filter</button></div></form>
<div class="table-responsive"><table class="table align-middle"><thead><tr><th>Nomor Arsip</th><th>Kategori</th><th>File</th><th>Ukuran</th><th>Tanggal simpan</th><th>Aksi</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td class="fw-semibold"><?= e($r['nomor_arsip']) ?></td><td><?= e($r['kategori']) ?></td><td><span class="badge text-bg-secondary"><?= e(strtoupper($r['file_extension'])) ?></span></td><td><?= number_format((int)$r['file_size']/1024,1) ?> KB</td><td><?= e($r['created_at']) ?></td><td class="text-nowrap"><a class="btn btn-sm btn-outline-primary" href="<?= url('admin/detail.php?id='.(int)$r['id']) ?>">Detail</a></td></tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="6" class="text-center text-secondary py-4">Arsip tidak ditemukan.</td></tr><?php endif; ?>
</tbody></table></div></div></main><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script></body></html>
