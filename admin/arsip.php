<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();
$unlocked = is_unlocked();

// Pencarian dan filter bekerja pada kolom yang tidak terenkripsi, jadi dimatikan saat mode terenkripsi.
$search = $unlocked ? trim($_GET['q'] ?? '') : '';
$category = $unlocked ? ($_GET['kategori'] ?? '') : '';

$sql = 'SELECT id,nomor_arsip,kategori,data_cipher,data_key,data_iv,data_tag,created_at,file_extension,file_size
         FROM arsip WHERE 1=1';
$params = [];

if ($search !== '') {
    $sql .= ' AND nomor_arsip LIKE ?';
    $params[] = '%' . $search . '%';
}

if (in_array($category, ['Surat Masuk','Surat Keluar','Dokumen Lainnya'], true)) {
    $sql .= ' AND kategori=?';
    $params[] = $category;
}

$sql .= ' ORDER BY id DESC';

$q = $pdo->prepare($sql);
$q->execute($params);
$rows = $q->fetchAll();

$page_title = 'Data Arsip';
$active = 'arsip';
require __DIR__ . '/../includes/header.php';
?>

<?php if (!$unlocked): ?>
<div class="alert alert-warning d-flex align-items-center gap-2 py-2"><i class="bi bi-lock-fill"></i><div>Private key tidak cocok. Daftar arsip ditampilkan dalam bentuk terenkripsi, dan pencarian serta filter dimatikan.</div></div>
<?php endif; ?>

<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h5 class="fw-bold mb-1">Daftar Arsip</h5>
            <p class="text-secondary mb-0 small">
                Metadata dan file disimpan dalam bentuk terenkripsi.
            </p>
        </div>

        <?php if ($unlocked): ?>
        <a class="btn btn-primary" href="<?= url('admin/tambah.php') ?>">
            <i class="bi bi-plus-lg me-1"></i>Tambah Arsip
        </a>
        <?php else: ?>
        <span class="btn btn-primary disabled"><i class="bi bi-lock me-1"></i>Tambah Arsip</span>
        <?php endif; ?>
    </div>

    <form class="row g-2 mb-3">
        <div class="col-md-5">
            <input class="form-control"
                   name="q"
                   placeholder="Cari nomor arsip..."
                   value="<?= e($search) ?>" <?= $unlocked ? '' : 'disabled' ?>>
        </div>

        <div class="col-md-4">
            <select class="form-select" name="kategori" <?= $unlocked ? '' : 'disabled' ?>>
                <option value="">Semua kategori</option>
                <?php foreach(['Surat Masuk','Surat Keluar','Dokumen Lainnya'] as $c): ?>
                    <option value="<?= e($c) ?>" <?= $category === $c ? 'selected' : '' ?>>
                        <?= e($c) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3">
            <button class="btn btn-outline-primary w-100" <?= $unlocked ? '' : 'disabled' ?>>Filter</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Nomor Arsip</th>
                    <th>Kategori</th>
                    <th>File</th>
                    <th>Ukuran</th>
                    <th>Tanggal simpan</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach($rows as $r): ?>
                    <tr>
                        <?php if ($unlocked): ?>
                        <td class="fw-semibold"><?= e($r['nomor_arsip']) ?></td>
                        <td><?= e($r['kategori']) ?></td>
                        <td>
                            <span class="badge text-bg-secondary">
                                <?= e(strtoupper($r['file_extension'])) ?>
                            </span>
                        </td>
                        <td><?= number_format((int)$r['file_size']/1024,1) ?> KB</td>
                        <?php else: ?>
                        <td><span class="cipher"><?= e(cipher_preview($r, 0, 18)) ?></span></td>
                        <td><span class="cipher"><?= e(cipher_preview($r, 18, 14)) ?></span></td>
                        <td><span class="cipher"><?= e(cipher_preview($r, 32, 6)) ?></span></td>
                        <td><span class="cipher"><?= e(cipher_preview($r, 38, 6)) ?></span></td>
                        <?php endif; ?>

                        <td><?= e($r['created_at']) ?></td>

                        <td class="text-nowrap">
                            <?php if ($unlocked): ?>
                            <a class="btn btn-sm btn-outline-primary"
                               href="<?= url('admin/detail.php?id='.(int)$r['id']) ?>">
                                Detail
                            </a>

                            <a class="btn btn-sm btn-outline-warning"
                               href="<?= url('admin/edit.php?id='.(int)$r['id']) ?>">
                                <i class="bi bi-pencil-square me-1"></i>Edit
                            </a>
                            <?php else: ?>
                            <span class="btn btn-sm btn-outline-secondary disabled" title="Terkunci"><i class="bi bi-lock me-1"></i>Terkunci</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if(!$rows): ?>
                    <tr>
                        <td colspan="6" class="text-center text-secondary py-4">
                            Arsip tidak ditemukan.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
