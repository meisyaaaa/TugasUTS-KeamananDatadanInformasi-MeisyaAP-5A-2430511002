<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_login();
$rows=$pdo->query('SELECT a.aktivitas,a.created_at,ad.username FROM aktivitas a JOIN admins ad ON ad.id=a.admin_id ORDER BY a.id DESC LIMIT 200')->fetchAll();
$page_title='Riwayat Aktivitas'; $active='aktivitas'; require __DIR__.'/../includes/header.php';
?>
<div class="card p-4"><h5 class="fw-bold mb-3">Riwayat Aktivitas Admin</h5><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Admin</th><th>Aktivitas</th><th>Waktu</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><?= e($r['username']) ?></td><td><?= e($r['aktivitas']) ?></td><td><?= e($r['created_at']) ?></td></tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="3" class="text-center text-secondary">Belum ada aktivitas.</td></tr><?php endif; ?>
</tbody></table></div><div class="small text-secondary">Menampilkan maksimal 200 aktivitas terbaru.</div></div></main><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script></body></html>
