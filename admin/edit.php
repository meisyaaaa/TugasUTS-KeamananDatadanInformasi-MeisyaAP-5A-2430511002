<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../crypto/Crypto.php';
require_login();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: (filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0);
$row = get_archive($pdo, $id);
try { $data = decrypt_metadata($row); }
catch (Throwable $ex) { http_response_code(500); exit('Dekripsi metadata gagal. Pastikan private key RSA tersedia dan cocok.'); }
$error = '';
$allowed = ['pdf','doc','docx','xls','xlsx','jpg','jpeg','png'];
$mimeAllowed = ['pdf'=>'application/pdf','doc'=>'application/msword','docx'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document','xls'=>'application/vnd.ms-excel','xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $nomor = trim($_POST['nomor_arsip'] ?? '');
    $kategori = $_POST['kategori'] ?? '';
    $perihal = trim($_POST['perihal'] ?? '');
    $pengirim = trim($_POST['pengirim'] ?? '');
    $penerima = trim($_POST['penerima'] ?? '');
    $tanggal = trim($_POST['tanggal_dokumen'] ?? '');
    $keterangan = trim($_POST['keterangan'] ?? '');
    $file = $_FILES['dokumen_baru'] ?? null;
    if ($nomor === '' || $perihal === '' || !in_array($kategori, ['Surat Masuk','Surat Keluar','Dokumen Lainnya'], true)) {
        $error = 'Nomor arsip, kategori, dan perihal wajib diisi.';
    } else {
        $dup = $pdo->prepare('SELECT id FROM arsip WHERE nomor_arsip=? AND id<>?');
        $dup->execute([$nomor, $id]);
        if ($dup->fetch()) $error = 'Nomor arsip sudah digunakan oleh arsip lain.';
    }
    $replaceFile = $file && $file['error'] !== UPLOAD_ERR_NO_FILE;
    if (!$error && $replaceFile) {
        if ($file['error'] !== UPLOAD_ERR_OK) $error = 'Upload file gagal. Silakan coba kembali.';
        elseif ($file['size'] < 1 || $file['size'] > MAX_UPLOAD_BYTES) $error = 'Ukuran file maksimal 10 MB.';
        else {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
            if (!in_array($ext, $allowed, true) || !isset($mimeAllowed[$ext]) || $mime !== $mimeAllowed[$ext]) $error = 'Format file tidak diizinkan atau tipe file tidak sesuai.';
        }
    }
    if (!$error) {
        $newToken = $row['file_token']; $fileKey = $row['file_key']; $fileIv = $row['file_iv']; $fileTag = $row['file_tag'];
        $extension = $row['file_extension']; $fileSize = (int)$row['file_size']; $newStoredFile = null;
        try {
            $crypto = new Crypto();
            $meta = ['nomor_arsip'=>$nomor,'kategori'=>$kategori,'perihal'=>$perihal,'pengirim'=>$pengirim,'penerima'=>$penerima,'tanggal_dokumen'=>$tanggal,'keterangan'=>$keterangan,'nama_file'=>$data['nama_file'] ?? ''];
            if ($replaceFile) {
                $meta['nama_file'] = $file['name'];
                $raw = file_get_contents($file['tmp_name']);
                if ($raw === false) throw new RuntimeException('File upload tidak bisa dibaca.');
                $encFile = $crypto->encrypt($raw);
                $newToken = bin2hex(random_bytes(16)); $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)); $fileSize = (int)$file['size'];
                if (!is_dir(STORAGE_PATH) && !mkdir(STORAGE_PATH, 0700, true) && !is_dir(STORAGE_PATH)) throw new RuntimeException('Folder storage gagal dibuat.');
                $newStoredFile = STORAGE_PATH.'/'.$newToken.'.enc';
                $decoded = base64_decode($encFile['cipher'], true);
                if ($decoded === false || file_put_contents($newStoredFile, $decoded, LOCK_EX) === false) throw new RuntimeException('File terenkripsi gagal ditulis.');
                $fileKey=$encFile['key']; $fileIv=$encFile['iv']; $fileTag=$encFile['tag'];
            }
            $metaEnc = $crypto->encrypt(json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('UPDATE arsip SET nomor_arsip=?,kategori=?,data_cipher=?,data_key=?,data_iv=?,data_tag=?,file_token=?,file_key=?,file_iv=?,file_tag=?,file_extension=?,file_size=? WHERE id=?');
            $stmt->execute([$nomor,$kategori,$metaEnc['cipher'],$metaEnc['key'],$metaEnc['iv'],$metaEnc['tag'],$newToken,$fileKey,$fileIv,$fileTag,$extension,$fileSize,$id]);
            log_activity($pdo, 'Mengedit arsip '.$row['nomor_arsip'].' menjadi '.$nomor.($replaceFile?' (file diganti)':' (file dipertahankan)'));
            $pdo->commit();
            if ($replaceFile) @unlink(STORAGE_PATH.'/'.$row['file_token'].'.enc');
            flash('success', 'Arsip berhasil diperbarui dan metadata terenkripsi kembali.');
            header('Location: '.url('admin/detail.php?id='.$id)); exit;
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            if ($newStoredFile && is_file($newStoredFile)) @unlink($newStoredFile);
            $error = 'Perubahan gagal disimpan. Periksa RSA key, koneksi database, dan izin folder storage.';
        }
    }
}
// POST values are shown again after validation errors; initial display comes from decrypted metadata.
$val = fn($key) => e($_POST[$key] ?? ($data[$key] ?? ''));
$page_title='Edit Arsip'; $active='arsip'; require __DIR__.'/../includes/header.php';
?>
<div class="card p-4"><h5 class="fw-bold mb-1">Edit Data Arsip</h5><p class="text-secondary small">Metadata akan dienkripsi kembali setelah disimpan. File dokumen dapat dibiarkan tetap atau diganti.</p>
<?php if($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$id ?>">
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Nomor Arsip *</label><input class="form-control" name="nomor_arsip" value="<?= $val('nomor_arsip') ?>" required></div>
<div class="col-md-6"><label class="form-label">Kategori *</label><select class="form-select" name="kategori" required><option value="">Pilih kategori</option><?php foreach(['Surat Masuk','Surat Keluar','Dokumen Lainnya'] as $c): ?><option value="<?= e($c) ?>" <?= ($_POST['kategori']??($data['kategori']??''))===$c?'selected':'' ?>><?= e($c) ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label">Perihal *</label><input class="form-control" name="perihal" value="<?= $val('perihal') ?>" required></div>
<div class="col-md-6"><label class="form-label">Tanggal Dokumen</label><input class="form-control" type="date" name="tanggal_dokumen" value="<?= $val('tanggal_dokumen') ?>"></div>
<div class="col-md-6"><label class="form-label">Pengirim</label><input class="form-control" name="pengirim" value="<?= $val('pengirim') ?>"></div>
<div class="col-md-6"><label class="form-label">Penerima</label><input class="form-control" name="penerima" value="<?= $val('penerima') ?>"></div>
<div class="col-12"><label class="form-label">Keterangan</label><textarea class="form-control" name="keterangan" rows="3"><?= $val('keterangan') ?></textarea></div>
<div class="col-12"><label class="form-label">File Saat Ini</label><div class="form-control bg-light" readonly><?= e($data['nama_file']??('Dokumen '.strtoupper($row['file_extension']))) ?> <span class="text-secondary small">(<?= number_format((int)$row['file_size']/1024,1) ?> KB)</span></div></div>
<div class="col-12"><label class="form-label">Ganti File (opsional; kosongkan untuk mempertahankan file lama)</label><input class="form-control" type="file" name="dokumen_baru" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"><div class="form-text">Format: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG. Maks. 10 MB.</div></div>
</div><div class="mt-4"><button class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Perubahan</button> <a class="btn btn-light" href="<?= url('admin/detail.php?id='.$id) ?>">Batal</a></div></form></div>
</main><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script></body></html>
