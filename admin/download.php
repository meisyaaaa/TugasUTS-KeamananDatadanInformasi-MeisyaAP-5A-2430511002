<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../crypto/Crypto.php';
require_unlocked();
$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT)?:0;
$row=get_archive($pdo,$id);
$path=STORAGE_PATH.'/'.$row['file_token'].'.enc';
$encrypted=@file_get_contents($path);
if($encrypted===false) { http_response_code(404); exit('File arsip terenkripsi tidak ditemukan.'); }
try {
    $plain=(new Crypto())->decrypt(base64_encode($encrypted),$row['file_key'],$row['file_iv'],$row['file_tag']);
    $meta=decrypt_metadata($row);
} catch(Throwable $ex) { http_response_code(500); exit('Dekripsi file gagal. Periksa private key atau integritas data.'); }
$filename=basename((string)($meta['nama_file']??('arsip.'.$row['file_extension'])));
$filename=preg_replace('/[^A-Za-z0-9._-]/','_', $filename) ?: 'arsip.'.$row['file_extension'];
$mime=(new finfo(FILEINFO_MIME_TYPE))->buffer($plain) ?: 'application/octet-stream';
log_activity($pdo,'Mengunduh arsip '.$row['nomor_arsip']);
header('Content-Type: '.$mime);
header('Content-Length: '.strlen($plain));
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('X-Content-Type-Options: nosniff');
echo $plain;
exit;
