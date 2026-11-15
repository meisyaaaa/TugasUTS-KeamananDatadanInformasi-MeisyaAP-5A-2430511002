<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../crypto/Crypto.php';
require_login();
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $nomor=trim($_POST['nomor_arsip']??'');
    $kategori=$_POST['kategori']??'';
    $perihal=trim($_POST['perihal']??'');
    $pengirim=trim($_POST['pengirim']??'');
    $penerima=trim($_POST['penerima']??'');
    $tanggal=trim($_POST['tanggal_dokumen']??'');
    $keterangan=trim($_POST['keterangan']??'');
    $file=$_FILES['dokumen']??null;
    $allowed=['pdf','doc','docx','xls','xlsx','jpg','jpeg','png'];
    if($nomor===''||$perihal===''||!in_array($kategori,['Surat Masuk','Surat Keluar','Dokumen Lainnya'],true)) {
        $error='Nomor arsip, kategori, dan perihal wajib diisi.';
    } elseif(!$file || $file['error']!==UPLOAD_ERR_OK) {
        $error='Pilih dokumen yang valid untuk diunggah.';
    } elseif($file['size']<1 || $file['size']>MAX_UPLOAD_BYTES) {
        $error='Ukuran file maksimal 10 MB.';
    } else {
        $ext=strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));
        $mime=(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $mimeAllowed=['pdf'=>'application/pdf','doc'=>'application/msword','docx'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document','xls'=>'application/vnd.ms-excel','xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png'];
        if(!in_array($ext,$allowed,true) || !isset($mimeAllowed[$ext]) || $mime!==$mimeAllowed[$ext]) {
            $error='Format file tidak diizinkan atau tipe file tidak sesuai.';
        } else {
            $token=bin2hex(random_bytes(16));
            $target=STORAGE_PATH.'/'.$token.'.enc';
            try {
                $metadata=['nomor_arsip'=>$nomor,'kategori'=>$kategori,'perihal'=>$perihal,'pengirim'=>$pengirim,'penerima'=>$penerima,'tanggal_dokumen'=>$tanggal,'keterangan'=>$keterangan,'nama_file'=>$file['name']];
                $crypto=new Crypto();
                $metaEnc=$crypto->encrypt(json_encode($metadata,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR));
                $raw=file_get_contents($file['tmp_name']);
                if($raw===false) throw new RuntimeException('File unggahan tidak dapat dibaca.');
                $fileEnc=$crypto->encrypt($raw);
                if(!is_dir(STORAGE_PATH) && !mkdir(STORAGE_PATH,0700,true) && !is_dir(STORAGE_PATH)) throw new RuntimeException('Folder penyimpanan tidak dapat dibuat.');
                if(file_put_contents($target,base64_decode($fileEnc['cipher'],true),LOCK_EX)===false) throw new RuntimeException('File terenkripsi gagal disimpan.');
                try {
                    $stmt=$pdo->prepare('INSERT INTO arsip (nomor_arsip,kategori,data_cipher,data_key,data_iv,data_tag,file_token,file_key,file_iv,file_tag,file_extension,file_size,created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
                    $stmt->execute([$nomor,$kategori,$metaEnc['cipher'],$metaEnc['key'],$metaEnc['iv'],$metaEnc['tag'],$token,$fileEnc['key'],$fileEnc['iv'],$fileEnc['tag'],$ext,(int)$file['size'],(int)$_SESSION['admin_id']]);
                } catch(Throwable $dbError) { @unlink($target); throw $dbError; }
                log_activity($pdo,'Menambahkan arsip '.$nomor);
                flash('success','Arsip berhasil dienkripsi dan disimpan.');
                header('Location: '.url('admin/arsip.php')); exit;
            } catch(Throwable $ex) { $error='Gagal menyimpan arsip. Periksa RSA key dan izin folder storage.'; }
        }
    }
}
$page_title='Tambah Arsip'; $active='tambah'; require __DIR__.'/../includes/header.php';
?>
<div class="card p-4"><h5 class="fw-bold mb-1">Formulir Arsip Baru</h5><p class="text-secondary small">Dokumen asli akan dienkripsi sebelum disimpan ke folder penyimpanan.</p>
<?php if($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<div class="row g-3">
<div class="col-md-6"><label class="form-label">Nomor Arsip *</label><input class="form-control" name="nomor_arsip" value="<?= e($_POST['nomor_arsip']??'') ?>" required></div>
<div class="col-md-6"><label class="form-label">Kategori *</label><select class="form-select" name="kategori" required><option value="">Pilih kategori</option><?php foreach(['Surat Masuk','Surat Keluar','Dokumen Lainnya'] as $c): ?><option <?= ($_POST['kategori']??'')===$c?'selected':'' ?>><?= e($c) ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label">Perihal *</label><input class="form-control" name="perihal" value="<?= e($_POST['perihal']??'') ?>" required></div>
<div class="col-md-6"><label class="form-label">Tanggal Dokumen</label><input class="form-control" type="date" name="tanggal_dokumen" value="<?= e($_POST['tanggal_dokumen']??'') ?>"></div>
<div class="col-md-6"><label class="form-label">Pengirim</label><input class="form-control" name="pengirim" value="<?= e($_POST['pengirim']??'') ?>"></div>
<div class="col-md-6"><label class="form-label">Penerima</label><input class="form-control" name="penerima" value="<?= e($_POST['penerima']??'') ?>"></div>
<div class="col-12"><label class="form-label">Keterangan</label><textarea class="form-control" name="keterangan" rows="3"><?= e($_POST['keterangan']??'') ?></textarea></div>
<div class="col-12"><label class="form-label">File Dokumen * (PDF, DOC, DOCX, XLS, XLSX, JPG, PNG; maks. 10 MB)</label><input class="form-control" type="file" name="dokumen" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" required></div>
</div><div class="mt-4"><button class="btn btn-primary"><i class="bi bi-lock me-1"></i>Enkripsi & Simpan Arsip</button> <a class="btn btn-light" href="<?= url('admin/arsip.php') ?>">Batal</a></div>
</form></div></main><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script></body></html>
