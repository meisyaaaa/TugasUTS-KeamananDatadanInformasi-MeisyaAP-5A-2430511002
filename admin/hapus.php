<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_unlocked();
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit('Metode tidak diizinkan.');}
verify_csrf();
$id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT)?:0;
$row=get_archive($pdo,$id);
$pdo->beginTransaction();
try {
    $q=$pdo->prepare('DELETE FROM arsip WHERE id=?'); $q->execute([$id]);
    log_activity($pdo,'Menghapus arsip '.$row['nomor_arsip']);
    $pdo->commit();
    @unlink(STORAGE_PATH.'/'.$row['file_token'].'.enc');
    flash('success','Arsip berhasil dihapus.');
} catch(Throwable $ex) {
    $pdo->rollBack(); flash('danger','Arsip gagal dihapus.');
}
header('Location: '.url('admin/arsip.php')); exit;
