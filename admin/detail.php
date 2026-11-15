<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();
$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT)?:0;
$row=get_archive($pdo,$id);
try { $data=decrypt_metadata($row); }
catch(Throwable $ex) { http_response_code(500); exit('Dekripsi metadata gagal. Pastikan private key RSA tersedia dan cocok.'); }
log_activity($pdo,'Melihat detail arsip '.$row['nomor_arsip']);
$page_title='Detail Arsip'; $active='arsip'; require __DIR__.'/../includes/header.php';
?>
<div class="card p-4"><div class="d-flex justify-content-between flex-wrap gap-2"><div><h5 class="fw-bold mb-1"><?= e($data['perihal']??'Detail Arsip') ?></h5><div class="text-secondary small"><?= e($row['nomor_arsip']) ?></div></div><span class="badge text-bg-success align-self-start"><i class="bi bi-shield-check me-1"></i>Berhasil didekripsi</span></div><hr>
<div class="row g-3">
<?php foreach(['nomor_arsip'=>'Nomor Arsip','kategori'=>'Kategori','perihal'=>'Perihal','tanggal_dokumen'=>'Tanggal Dokumen','pengirim'=>'Pengirim','penerima'=>'Penerima','nama_file'=>'Nama File','keterangan'=>'Keterangan'] as $key=>$label): ?>
<div class="col-md-6"><div class="text-secondary small"><?= e($label) ?></div><div class="fw-semibold"><?= nl2br(e((string)($data[$key]??'-'))) ?></div></div>
<?php endforeach; ?>
<div class="col-md-6"><div class="text-secondary small">Ukuran file</div><div><?= number_format((int)$row['file_size']/1024,1) ?> KB</div></div>
<div class="col-md-6"><div class="text-secondary small">Dibuat</div><div><?= e($row['created_at']) ?></div></div>
</div><hr><div class="d-flex flex-wrap gap-2"><a class="btn btn-primary" href="<?= url('admin/download.php?id='.(int)$row['id']) ?>"><i class="bi bi-download me-1"></i>Dekripsi & Unduh Dokumen</a>
<form method="post" action="<?= url('admin/hapus.php') ?>" onsubmit="return confirm('Yakin ingin menghapus arsip ini?')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><button class="btn btn-outline-danger"><i class="bi bi-trash me-1"></i>Hapus</button></form>
<a class="btn btn-light" href="<?= url('admin/arsip.php') ?>">Kembali</a></div></div></main><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script></body></html>
