<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();
$unlocked = is_unlocked();

$total = (int)$pdo->query('SELECT COUNT(*) FROM arsip')->fetchColumn();
$incoming = (int)$pdo->query("SELECT COUNT(*) FROM arsip WHERE kategori='Surat Masuk'")->fetchColumn();
$outgoing = (int)$pdo->query("SELECT COUNT(*) FROM arsip WHERE kategori='Surat Keluar'")->fetchColumn();
$other = (int)$pdo->query("SELECT COUNT(*) FROM arsip WHERE kategori='Dokumen Lainnya'")->fetchColumn();
$recentRaw = $pdo->query('SELECT id,nomor_arsip,kategori,data_cipher,data_key,data_iv,data_tag,created_at FROM arsip ORDER BY id DESC LIMIT 6')->fetchAll();

// Kunci cocok -> tampilkan hasil dekripsi. Kunci salah -> tampilkan ciphertext asli dari database.
$recent = [];
foreach ($recentRaw as $r) {
    $item = ['id' => (int)$r['id'], 'created_at' => $r['created_at'], 'plain' => false];
    if ($unlocked) {
        try {
            $m = decrypt_metadata($r);
            $item['nomor'] = (string)($m['nomor_arsip'] ?? $r['nomor_arsip']);
            $item['kategori'] = (string)($m['kategori'] ?? $r['kategori']);
            $item['perihal'] = (string)($m['perihal'] ?? '-');
            $item['plain'] = true;
        } catch (Throwable $ex) { /* jatuh ke tampilan terenkripsi */ }
    }
    if (!$item['plain']) {
        $item['nomor'] = cipher_preview($r, 0, 18);
        $item['kategori'] = cipher_preview($r, 18, 14);
        $item['perihal'] = cipher_preview($r, 32, 22);
    }
    $recent[] = $item;
}

$page_title='Dashboard'; $active='dashboard'; require __DIR__.'/../includes/header.php';
?>
<?php if ($unlocked): ?>
<div class="alert alert-success d-flex align-items-center gap-2 py-2"><i class="bi bi-unlock-fill"></i><div>Private key cocok. Data arsip ditampilkan dalam bentuk asli (hasil dekripsi).</div></div>
<?php else: ?>
<div class="alert alert-warning d-flex align-items-center gap-2 py-2"><i class="bi bi-lock-fill"></i><div>Private key tidak cocok. Data arsip ditampilkan dalam bentuk terenkripsi dan fitur lain dikunci. <a class="alert-link" href="<?= url('logout.php') ?>">Keluar</a> lalu masuk kembali dengan private key yang benar.</div></div>
<?php endif; ?>

<div class="row g-3 mb-4">
<?php foreach ([['Total Arsip',$total,'bi-archive'],['Surat Masuk',$incoming,'bi-inbox'],['Surat Keluar',$outgoing,'bi-send'],['Dokumen Lainnya',$other,'bi-file-earmark-lock']] as $s): ?>
<div class="col-sm-6 col-xl-3"><div class="card p-3"><div class="d-flex justify-content-between align-items-center"><div><div class="text-secondary small"><?= ct($s[0]) ?></div>
<?php if ($unlocked): ?><div class="display-6 fw-bold"><?= (int)$s[1] ?></div><?php else: ?><div class="display-6 fw-bold text-secondary" title="Terenkripsi"><i class="bi bi-lock"></i></div><?php endif; ?>
</div><div class="stat-icon"><i class="bi <?= e($s[2]) ?>"></i></div></div></div></div>
<?php endforeach; ?>
</div>
<div class="row g-3">
<div class="col-lg-8"><div class="card p-4"><div class="d-flex justify-content-between mb-3"><h5 class="fw-bold mb-0"><?= ct('Arsip terbaru') ?></h5><a href="<?= url('admin/arsip.php') ?>"><?= ct('Lihat semua') ?></a></div>
<div class="table-responsive"><table class="table align-middle"><thead><tr><th><?= ct('Nomor Arsip') ?></th><th><?= ct('Kategori') ?></th><th><?= ct('Perihal') ?></th><th><?= ct('Tanggal') ?></th><th></th></tr></thead><tbody>
<?php foreach($recent as $r): $c = $r['plain'] ? '' : 'cipher'; ?>
<tr>
<td><?php if ($c): ?><span class="cipher"><?= e($r['nomor']) ?></span><?php else: ?><?= e($r['nomor']) ?><?php endif; ?></td>
<td><?php if ($c): ?><span class="cipher"><?= e($r['kategori']) ?></span><?php else: ?><?= e($r['kategori']) ?><?php endif; ?></td>
<td><?php if ($c): ?><span class="cipher"><?= e($r['perihal']) ?></span><?php else: ?><?= e($r['perihal']) ?><?php endif; ?></td>
<td><?= ct($r['created_at']) ?></td>
<td><?php if ($r['plain']): ?><a class="btn btn-sm btn-outline-primary" href="<?= url('admin/detail.php?id='.(int)$r['id']) ?>">Buka</a><?php else: ?><span class="btn btn-sm btn-outline-secondary disabled" title="Terkunci"><i class="bi bi-lock"></i></span><?php endif; ?></td>
</tr>
<?php endforeach; ?>
<?php if(!$recent): ?><tr><td colspan="5" class="text-center text-secondary"><?= ct('Belum ada arsip.') ?></td></tr><?php endif; ?>
</tbody></table></div></div></div>
<div class="col-lg-4"><div class="card p-4 h-100"><h5 class="fw-bold"><?= ct('Keamanan arsip') ?></h5><div class="d-flex gap-3 my-3"><i class="bi bi-shield-check text-success fs-3"></i><div><b><?= ct('AES-256-GCM') ?></b><div class="small text-secondary"><?= ct('Enkripsi metadata dan file dokumen.') ?></div></div></div><div class="d-flex gap-3"><i class="bi bi-key text-primary fs-3"></i><div><b><?= ct('RSA-OAEP') ?></b><div class="small text-secondary"><?= ct('Melindungi kunci AES dengan public key.') ?></div></div></div><hr>
<?php if ($unlocked): ?><a class="btn btn-primary" href="<?= url('admin/tambah.php') ?>"><i class="bi bi-plus-lg me-1"></i>Tambah Arsip</a>
<?php else: ?><span class="btn btn-primary disabled"><i class="bi bi-lock me-1"></i><?= ct('Tambah Arsip') ?></span><?php endif; ?></div></div>
</div></main><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script></body></html>