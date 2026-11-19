<?php
require_once __DIR__ . '/includes/functions.php';
$loggedIn = !empty($_SESSION['admin_id']);
$ctaUrl = $loggedIn ? url('admin/dashboard.php') : url('login.php');
$ctaText = $loggedIn ? 'Buka dashboard' : 'Masuk sebagai admin';
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Arsip Digital | Dokumen tersimpan terenkripsi</title>
<meta name="description" content="Sistem arsip digital dengan enkripsi hibrida AES-256-GCM dan RSA-OAEP. Isi arsip hanya terbaca oleh pemegang private key.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700&family=Source+Sans+3:wght@400;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
:root{
  --ink:#14213d; --blue:#2457c5; --mist:#f4f6fb; --line:#dbe2ef; --text:#2b3a55; --muted:#5d6b86;
  --amber-bg:#fff4d9; --amber:#8a6414; --green-bg:#e3f6ec; --green:#17794f;
  --display:"Bricolage Grotesque","Segoe UI",system-ui,sans-serif;
  --body:"Source Sans 3","Segoe UI",system-ui,-apple-system,sans-serif;
  --mono:ui-monospace,SFMono-Regular,Consolas,"Liberation Mono",monospace;
}
*{box-sizing:border-box}
html{scroll-behavior:smooth}
body{margin:0;background:var(--mist);color:var(--text);font-family:var(--body);font-size:1.0625rem;line-height:1.6}
a{color:inherit}
:focus-visible{outline:3px solid #7aa2ff;outline-offset:3px;border-radius:6px}
.wrap{width:min(1120px,100% - 40px);margin-inline:auto}

.top{display:flex;align-items:center;justify-content:space-between;padding:22px 0}
.brand{display:flex;align-items:center;gap:10px;font-family:var(--display);font-weight:700;font-size:1.2rem;color:var(--ink);text-decoration:none}
.brand i{color:var(--blue);font-size:1.5rem}
.top nav{display:flex;align-items:center;gap:22px}
.top nav a.plain{text-decoration:none;color:var(--muted);font-weight:600}
.top nav a.plain:hover{color:var(--ink)}

.btn{display:inline-flex;align-items:center;gap:8px;padding:12px 22px;border-radius:12px;font-weight:600;text-decoration:none;border:2px solid transparent;font-size:1rem}
.btn-main{background:var(--blue);color:#fff}
.btn-main:hover{background:#1d49a8}
.btn-ghost{border-color:var(--line);color:var(--ink);background:#fff}
.btn-ghost:hover{border-color:var(--blue);color:var(--blue)}
.btn-sm{padding:9px 16px;font-size:.95rem}

.hero{display:grid;grid-template-columns:1.02fr 1fr;gap:56px;align-items:center;padding:48px 0 84px}
.hero h1{font-family:var(--display);font-weight:700;color:var(--ink);font-size:clamp(2.2rem,4.6vw,3.7rem);line-height:1.06;letter-spacing:-.02em;margin:0 0 20px}
.hero p.lead{max-width:34em;color:var(--text);font-size:1.15rem;margin:0 0 30px}
.hero .actions{display:flex;flex-wrap:wrap;gap:12px}

/* demo kunci benar / salah */
.demo{background:var(--ink);color:#e8edf8;border-radius:22px;padding:22px;box-shadow:0 24px 60px rgba(20,33,61,.28)}
.demo-head{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:16px}
.demo-head h2{font-family:var(--display);font-size:1.1rem;margin:0;font-weight:600}
.seg{display:inline-flex;background:#0d1730;border-radius:12px;padding:4px}
.seg button{font:inherit;font-size:.92rem;font-weight:600;color:#a9b6d3;background:none;border:0;border-radius:9px;padding:7px 14px;cursor:pointer}
.seg button[aria-pressed="true"]{background:#fff;color:var(--ink)}
.seg button:focus-visible{outline-color:#9db9ff}
.rows{display:grid;gap:10px}
.row{background:#1b2c52;border-radius:14px;padding:14px 16px;display:grid;gap:4px}
.row .k{font-size:.82rem;color:#9fb0d3}
.row .v{font-size:1rem;overflow-wrap:anywhere;min-height:1.6em}
.row .v.cipher{font-family:var(--mono);font-size:.86rem;color:#ffd47a}
.row .v.plain{color:#fff;font-weight:600}
.state{margin:16px 2px 0;display:flex;gap:10px;align-items:flex-start;font-size:.97rem;min-height:3.2em}
.state i{margin-top:3px}
.state.locked i{color:#ffd47a}
.state.open i{color:#6fe0a8}

.how{background:#fff;border-block:1px solid var(--line);padding:76px 0}
.how h2{font-family:var(--display);color:var(--ink);font-size:clamp(1.7rem,3vw,2.3rem);line-height:1.15;letter-spacing:-.015em;margin:0 0 40px;max-width:20em}
.steps{display:grid;grid-template-columns:repeat(3,1fr);gap:40px;list-style:none;padding:0;margin:0;counter-reset:s}
.steps li{counter-increment:s;border-top:3px solid var(--ink);padding-top:18px}
.steps li::before{content:counter(s);font-family:var(--display);font-weight:700;font-size:2.4rem;color:var(--blue);line-height:1;display:block;margin-bottom:12px}
.steps h3{font-family:var(--display);color:var(--ink);font-size:1.25rem;margin:0 0 8px}
.steps p{margin:0;color:var(--muted)}

.closing{padding:72px 0;display:flex;justify-content:space-between;align-items:center;gap:24px;flex-wrap:wrap}
.closing h2{font-family:var(--display);color:var(--ink);font-size:clamp(1.5rem,2.6vw,2rem);margin:0;max-width:22em;line-height:1.2}

footer{border-top:1px solid var(--line);padding:26px 0;color:var(--muted);font-size:.95rem}
footer .wrap{display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap}

@media(max-width:900px){
  .hero{grid-template-columns:1fr;gap:40px;padding:24px 0 60px}
  .steps{grid-template-columns:1fr;gap:28px}
  .top nav a.plain{display:none}
}
@media(prefers-reduced-motion:reduce){html{scroll-behavior:auto}}
</style>
</head>
<body>
<div class="wrap">
  <header class="top">
    <a class="brand" href="<?= url('index.php') ?>"><i class="bi bi-shield-lock"></i>Arsip Digital</a>
    <nav>
      <a class="plain" href="#cara-kerja">Cara kerja</a>
      <a class="btn btn-main btn-sm" href="<?= e($ctaUrl) ?>"><?= e($ctaText) ?></a>
    </nav>
  </header>

  <main>
    <section class="hero">
      <div>
        <h1>Arsip yang hanya terbuka bagi pemegang kunci.</h1>
        <p class="lead">Surat masuk, surat keluar, dan dokumen lainnya disimpan terenkripsi dengan AES-256-GCM. Kunci enkripsinya dilindungi RSA-OAEP, sehingga tanpa private key yang benar isi arsip hanya terlihat sebagai karakter acak.</p>
        <div class="actions">
          <a class="btn btn-main" href="<?= e($ctaUrl) ?>"><i class="bi bi-key"></i><?= e($ctaText) ?></a>
          <a class="btn btn-ghost" href="#cara-kerja">Lihat cara kerja</a>
        </div>
      </div>

      <div class="demo" aria-labelledby="demo-title">
        <div class="demo-head">
          <h2 id="demo-title">Contoh arsip di dashboard</h2>
          <div class="seg" role="group" aria-label="Pilih kunci yang dimasukkan">
            <button type="button" id="btn-wrong" aria-pressed="true">Kunci salah</button>
            <button type="button" id="btn-right" aria-pressed="false">Kunci benar</button>
          </div>
        </div>
        <div class="rows">
          <div class="row"><span class="k">Nomor arsip</span><span class="v cipher" data-plain="007/SK/XI/2026" data-cipher="q7Hn2Lw5xT0bVd8yA1">q7Hn2Lw5xT0bVd8yA1</span></div>
          <div class="row"><span class="k">Kategori</span><span class="v cipher" data-plain="Surat Keluar" data-cipher="Zk3mP9vE4rUs7c">Zk3mP9vE4rUs7c</span></div>
          <div class="row"><span class="k">Perihal</span><span class="v cipher" data-plain="Undangan rapat koordinasi" data-cipher="Rg5tY1nB8kQw2eJc6uXo9a">Rg5tY1nB8kQw2eJc6uXo9a</span></div>
        </div>
        <p class="state locked" id="state" aria-live="polite"><i class="bi bi-lock-fill"></i><span>Kunci tidak cocok. Dashboard menampilkan data sebagai ciphertext.</span></p>
      </div>
    </section>
  </main>
</div>

<section class="how" id="cara-kerja">
  <div class="wrap">
    <h2>Dari unggahan sampai dibuka kembali, kuncinya selalu ada di tangan admin.</h2>
    <ol class="steps">
      <li>
        <h3>Unggah dokumen</h3>
        <p>Admin mengisi data arsip dan memilih file PDF, Word, Excel, atau gambar dengan ukuran maksimal 10 MB.</p>
      </li>
      <li>
        <h3>Enkripsi otomatis</h3>
        <p>Isi dokumen dan data arsip dienkripsi dengan AES-256-GCM. Kunci AES-nya lalu dikunci lagi dengan public key RSA.</p>
      </li>
      <li>
        <h3>Buka dengan private key</h3>
        <p>Saat masuk, admin memasukkan username dan private key. Kunci yang cocok membuka data arsip, kunci yang salah hanya menampilkan data terenkripsi.</p>
      </li>
    </ol>
  </div>
</section>

<div class="wrap">
  <section class="closing">
    <h2>Siap membuka arsip? Siapkan username dan file private key Anda.</h2>
    <a class="btn btn-main" href="<?= e($ctaUrl) ?>"><i class="bi bi-box-arrow-in-right"></i><?= e($ctaText) ?></a>
  </section>
</div>

<footer>
  <div class="wrap">
    <span>Sistem Manajemen Arsip Digital</span>
    <span>Hybrid Cryptography: AES-256-GCM dan RSA-OAEP</span>
  </div>
</footer>

<script>
(function () {
  var wrong = document.getElementById('btn-wrong');
  var right = document.getElementById('btn-right');
  var state = document.getElementById('state');
  var cells = Array.prototype.slice.call(document.querySelectorAll('.demo .v'));
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
  var timer = null;

  function rnd(n) { var s = ''; for (var i = 0; i < n; i++) s += alphabet[Math.floor(Math.random() * alphabet.length)]; return s; }

  function setMode(open) {
    wrong.setAttribute('aria-pressed', String(!open));
    right.setAttribute('aria-pressed', String(open));
    state.className = 'state ' + (open ? 'open' : 'locked');
    state.innerHTML = open
      ? '<i class="bi bi-unlock-fill"></i><span>Kunci cocok. Dashboard menampilkan data dalam bentuk aslinya.</span>'
      : '<i class="bi bi-lock-fill"></i><span>Kunci tidak cocok. Dashboard menampilkan data sebagai ciphertext.</span>';
    if (timer) { clearInterval(timer); timer = null; }

    function finish() {
      cells.forEach(function (c) {
        c.textContent = open ? c.dataset.plain : c.dataset.cipher;
        c.className = 'v ' + (open ? 'plain' : 'cipher');
      });
    }
    if (reduce) { finish(); return; }

    // Karakter berganti satu per satu, dari kiri ke kanan.
    var frame = 0, total = 16;
    timer = setInterval(function () {
      frame++;
      cells.forEach(function (c) {
        var target = open ? c.dataset.plain : c.dataset.cipher;
        var done = Math.floor(target.length * frame / total);
        c.textContent = target.slice(0, done) + rnd(Math.max(0, target.length - done));
        c.className = 'v cipher';
      });
      if (frame >= total) { clearInterval(timer); timer = null; finish(); }
    }, 32);
  }

  wrong.addEventListener('click', function () { setMode(false); });
  right.addEventListener('click', function () { setMode(true); });
})();
</script>
</body>
</html>
