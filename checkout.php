<?php 
// checkout.php

session_start();

// Halaman pesan error bergaya sama (dipakai untuk validasi di bawah)
function tampilkan_error($judul) {
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
       . '<title>Checkout - VOIDWEAR</title>'
       . '<link href="https://fonts.googleapis.com/css2?family=Anton&family=DM+Sans:wght@700&display=swap" rel="stylesheet">'
       . '<style>'
       . 'body{margin:0;min-height:100vh;display:grid;place-items:center;padding:1.5rem;font-family:"DM Sans",sans-serif;color:#111;background:#fff radial-gradient(rgba(0,0,0,.13) 1.2px,transparent 1.3px) 0 0/12px 12px}'
       . '.box{max-width:420px;text-align:center;background:#fff;border:3px solid #111;box-shadow:8px 8px 0 #f0443f;padding:2rem}'
       . 'h1{font-family:Anton,sans-serif;font-style:italic;text-transform:uppercase;font-weight:400;font-size:2.2rem;margin-bottom:1rem;text-shadow:3px 3px 0 #f0443f}'
       . 'a{display:inline-block;margin-top:.5rem;padding:.6rem 1.2rem;background:#f0443f;color:#fff;text-decoration:none;font-weight:700;border:3px solid #111;border-radius:999px;box-shadow:3px 3px 0 #111}'
       . '</style></head><body><div class="box"><h1>' . htmlspecialchars($judul) . '</h1>'
       . '<a href="index.php">&larr; Kembali ke beranda</a></div></body></html>';
    exit;
}

// Ambil data dari POST atau GET
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_produk = isset($_POST['id_produk']) ? (int)$_POST['id_produk'] : 0;
    $size = isset($_POST['size']) ? $_POST['size'] : '';
    $jumlah = isset($_POST['jumlah']) ? (int)$_POST['jumlah'] : 1;
} else {
    $id_produk = isset($_GET['id_produk']) ? (int)$_GET['id_produk'] : 0;
    $size = isset($_GET['size']) ? $_GET['size'] : '';
    $jumlah = isset($_GET['jumlah']) ? (int)$_GET['jumlah'] : 1;
}

// Validasi
if ($id_produk <= 0 || $size === '') {
    tampilkan_error('Data produk tidak valid!');
}

// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "produk");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil data produk
$sql = "SELECT id, nama, harga, gambar FROM produk WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_produk);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    tampilkan_error('Produk tidak ditemukan!');
}

$produk = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout - <?= htmlspecialchars($produk['nama']) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Anton&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --ink: #111;
      --paper: #fff;
      --coral: #f0443f;
      --soft: #f4f4f1;
      --green: #2fa84f;
      --shadow: 6px 6px 0 var(--ink);
      --shadow-sm: 3px 3px 0 var(--ink);
      --border: 3px solid var(--ink);
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html { scroll-behavior: smooth; }

    body {
      font-family: 'DM Sans', sans-serif;
      color: var(--ink);
      background-color: var(--paper);
      background-image: radial-gradient(rgba(0,0,0,.13) 1.2px, transparent 1.3px);
      background-size: 12px 12px;
      padding-bottom: 3rem;
    }
    :focus-visible { outline: 3px dashed var(--coral); outline-offset: 3px; }

    .display {
      font-family: 'Anton', sans-serif;
      font-style: italic;
      text-transform: uppercase;
      font-weight: 400;
      letter-spacing: .5px;
      line-height: 1;
    }
    .wrap { max-width: 1000px; margin: 0 auto; padding: 0 1.5rem; }

    /* ---------- Header ---------- */
    header {
      position: sticky; top: 0; z-index: 20;
      display: flex; align-items: center; justify-content: space-between;
      padding: .8rem 1.5rem;
      background: var(--paper);
      border-bottom: var(--border);
    }
    .logo { display: flex; align-items: center; gap: .6rem; text-decoration: none; color: var(--ink); font-size: 1.6rem; }
    .logo .mark {
      display: grid; place-items: center; width: 48px; height: 48px;
      background: var(--ink); border-radius: 50%; overflow: hidden;
      box-shadow: 3px 3px 0 var(--coral);
    }
    .logo img { width: 100%; height: 100%; object-fit: contain; }

    .btn {
      display: inline-flex; align-items: center; justify-content: center; gap: .5rem;
      padding: .55rem 1.1rem;
      font: 700 .9rem 'DM Sans', sans-serif;
      color: var(--ink); text-decoration: none;
      background: var(--paper);
      border: var(--border); border-radius: 999px;
      box-shadow: var(--shadow-sm);
      cursor: pointer;
      transition: transform .12s ease, box-shadow .12s ease, background .12s ease;
    }
    .btn:hover { transform: translate(-1px,-1px); box-shadow: 4px 4px 0 var(--ink); }
    .btn:active { transform: translate(3px,3px); box-shadow: 0 0 0 var(--ink); }
    .btn.primary { background: var(--coral); color: #fff; }
    .btn.small { padding: .35rem .8rem; font-size: .8rem; }
    .btn.big { width: 100%; padding: 1rem; font-size: 1.1rem; margin-top: 1.6rem; }
    .btn[disabled] { opacity: .7; cursor: progress; }

    /* ---------- Judul ---------- */
    .page-title { margin: 2.5rem 0 1.8rem; }
    .page-title h1 { font-size: clamp(2.4rem, 8vw, 4rem); text-shadow: 4px 4px 0 var(--coral); }
    .page-title p { margin-top: .8rem; font-weight: 700; }

    /* ---------- Layout ---------- */
    .layout { display: grid; grid-template-columns: 5fr 6fr; gap: 2rem; align-items: start; }

    .card { background: var(--paper); border: var(--border); box-shadow: var(--shadow); }

    /* Ringkasan */
    .summary { position: sticky; top: 100px; padding: 1rem; }
    .summary .thumb { border: var(--border); overflow: hidden; background: var(--soft); position: relative; }
    .summary .thumb img { display: block; width: 100%; height: 280px; object-fit: cover; }
    .summary .tag {
      position: absolute; top: 8px; left: 8px; background: var(--coral); color: #fff;
      border: 2px solid var(--ink); padding: .2rem .6rem; font-size: 1rem; transform: rotate(-4deg);
    }
    .summary h2 { font-size: 1.8rem; margin: 1rem 0 .8rem; }
    .rows { display: grid; gap: .5rem; }
    .row { display: flex; justify-content: space-between; gap: 1rem; font-weight: 500; }
    .row b { font-weight: 700; }
    .chip { background: var(--ink); color: #fff; padding: .1rem .7rem; border-radius: 999px; font-weight: 700; }
    .total {
      margin-top: 1rem; padding: .9rem 1rem;
      background: var(--ink); color: #fff;
      display: flex; justify-content: space-between; align-items: baseline;
      box-shadow: 4px 4px 0 var(--coral);
    }
    .total small { font-weight: 700; letter-spacing: 1px; }
    .total b { font-size: 1.9rem; }

    /* Form */
    .form-card { padding: 1.6rem; }
    .form-card h2 { font-size: 1.8rem; margin-bottom: .4rem; text-shadow: 3px 3px 0 var(--coral); }
    .form-card .hint { font-size: .9rem; color: #555; margin-bottom: .6rem; }

    label { display: block; margin-top: 1.1rem; font-weight: 700; }
    label small { font-weight: 500; color: #555; }
    input[type=text], textarea {
      width: 100%; margin-top: .4rem; padding: .7rem .9rem;
      font: 500 1rem 'DM Sans', sans-serif; color: var(--ink);
      background: var(--paper); border: var(--border); border-radius: 10px;
      transition: box-shadow .12s ease;
    }
    input[type=text]:focus, textarea:focus { outline: none; box-shadow: 4px 4px 0 var(--coral); }
    input.invalid, textarea.invalid { background: #fff0ef; border-color: var(--coral); }
    textarea { resize: vertical; }

    /* Kotak rekening */
    .bank {
      margin-top: 1.4rem; padding: 1rem;
      border: var(--border); border-style: dashed; background: var(--soft);
      display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;
    }
    .bank small { display: block; font-weight: 700; color: #555; }
    .bank b { font-size: 1.15rem; }

    /* Upload */
    .upload {
      display: flex; align-items: center; gap: .9rem; margin-top: .4rem;
      padding: .9rem; border: var(--border); border-style: dashed; background: var(--paper);
      cursor: pointer; transition: background .12s ease, transform .12s ease;
    }
    .upload:hover, .upload.drag { background: #fff3f2; transform: translate(-1px,-1px); }
    .upload.filled { border-style: solid; background: #effaf2; }
    .upload input { position: absolute; width: 1px; height: 1px; opacity: 0; }
    .upload .ico {
      flex: 0 0 auto; width: 52px; height: 52px; display: grid; place-items: center;
      border: var(--border); background: var(--coral); color: #fff; font-size: 1.6rem; font-weight: 700;
      overflow: hidden;
    }
    .upload .ico img { width: 100%; height: 100%; object-fit: cover; }
    .upload .meta { min-width: 0; font-weight: 700; }
    .upload .meta span { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .upload .meta small { font-weight: 500; color: #555; }
    .upload:focus-within { outline: 3px dashed var(--coral); outline-offset: 3px; }

    .counter { text-align: right; font-size: .78rem; color: #555; margin-top: .2rem; }

    .back { margin-top: 1.6rem; text-align: center; }

    @media (max-width: 820px) {
      header { padding: .7rem 1rem; }
      header .btn { display: none; }
      .wrap { padding: 0 1rem; }
      .layout { grid-template-columns: 1fr; }
      .summary { position: static; }
      .summary .thumb img { height: 220px; }
    }
    @media (prefers-reduced-motion: reduce) {
      * { transition: none !important; animation: none !important; scroll-behavior: auto !important; }
    }
  </style>
</head>
<body>

<header>
  <a class="logo display" href="index.php">
    <span class="mark"><img src="img/png/logo.png" alt="VOIDWEAR"></span>
    VOIDWEAR
  </a>
  <a class="btn" href="simpan-keranjang.php">&larr; Kembali ke keranjang</a>
</header>

<main class="wrap">
  <div class="page-title">
    <h1 class="display">Checkout</h1>
    <p>Tinggal isi data pengiriman dan kirim bukti transfer.</p>
  </div>

  <div class="layout">

    <!-- Ringkasan produk -->
    <aside class="card summary">
      <div class="thumb">
        <img src="img/<?= htmlspecialchars($produk['gambar']) ?>" alt="<?= htmlspecialchars($produk['nama']) ?>">
        <span class="tag display">Dipilih!</span>
      </div>
      <h2 class="display"><?= htmlspecialchars($produk['nama']) ?></h2>
      <div class="rows">
        <div class="row"><span>Ukuran</span> <span class="chip"><?= htmlspecialchars($size) ?></span></div>
        <div class="row"><span>Jumlah</span> <b><?= $jumlah ?> pcs</b></div>
        <div class="row"><span>Harga satuan</span> <b>Rp <?= number_format($produk['harga'], 0, ',', '.') ?></b></div>
      </div>
      <div class="total">
        <small>TOTAL</small>
        <b class="display" id="totalNumber" data-total="<?= $produk['harga'] * $jumlah ?>">Rp <?= number_format($produk['harga'] * $jumlah, 0, ',', '.') ?></b>
      </div>
    </aside>

    <!-- Form pengiriman -->
    <section class="card form-card">
      <h2 class="display">Data Pengiriman</h2>

      <form action="proses-checkout.php" method="POST" enctype="multipart/form-data" id="checkoutForm" novalidate>
        <!-- Data tersembunyi untuk dikirim ke proses-checkout -->
        <input type="hidden" name="id_produk" value="<?= $produk['id'] ?>">
        <input type="hidden" name="size" value="<?= htmlspecialchars($size) ?>">
        <input type="hidden" name="jumlah" value="<?= $jumlah ?>">
        <input type="hidden" name="total" value="<?= $produk['harga'] * $jumlah ?>">

        <label for="nama">Nama Lengkap</label>
        <input type="text" id="nama" name="nama" autocomplete="name" required>

        <label for="alamat">Alamat Pengiriman</label>
        <textarea id="alamat" name="alamat" rows="3" autocomplete="street-address" required></textarea>

        <label for="telepon">Nomor Telepon</label>
        <input type="text" id="telepon" name="telepon" inputmode="tel" autocomplete="tel" required>

        <div class="bank">
          <div>
            <small>TRANSFER KE REKENING BCA</small>
            <b id="rek">12345678</b> <small>a/n QWERTY</small>
          </div>
          <button type="button" class="btn small" id="copyRek">Salin</button>
        </div>

        <label for="bukti">Upload Bukti Transfer</label>
        <label class="upload" id="uploadBox" for="bukti" style="margin-top:.4rem; font-weight:400;">
          <span class="ico" id="uploadIco">+</span>
          <span class="meta">
            <span id="uploadName">Pilih atau tarik file ke sini</span>
            <small id="uploadHint">Gambar atau PDF</small>
          </span>
          <input type="file" id="bukti" name="bukti" required accept="image/*,application/pdf">
        </label>

        <label for="catatan">Catatan <small>(opsional)</small></label>
        <textarea id="catatan" name="catatan" rows="2" maxlength="200"></textarea>
        <div class="counter"><span id="catCount">0</span>/200</div>

        <button type="submit" class="btn primary big" id="submitBtn">Kirim Pesanan</button>
      </form>

      <div class="back"><a class="btn small" href="simpan-keranjang.php">&larr; Batal, kembali ke keranjang</a></div>
    </section>

  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // 1. Total menghitung naik saat halaman dibuka
  const total = document.getElementById('totalNumber');
  if (total && !reduce) {
    const target = parseInt(total.dataset.total, 10) || 0;
    const start = performance.now(), dur = 800;
    (function tick(now) {
      const p = Math.min((now - start) / dur, 1);
      total.textContent = 'Rp ' + Math.round(target * (1 - Math.pow(1 - p, 3))).toLocaleString('id-ID');
      if (p < 1) requestAnimationFrame(tick);
    })(start);
  }

  // 2. Salin nomor rekening
  const copyBtn = document.getElementById('copyRek');
  copyBtn.addEventListener('click', async () => {
    const no = document.getElementById('rek').textContent;
    try { await navigator.clipboard.writeText(no); }
    catch (e) {
      const t = document.createElement('textarea'); t.value = no; document.body.appendChild(t);
      t.select(); document.execCommand('copy'); t.remove();
    }
    copyBtn.textContent = 'Tersalin!';
    setTimeout(() => copyBtn.textContent = 'Salin', 1500);
  });

  // 3. Upload bukti: nama file + pratinjau gambar + tarik-lepas
  const file = document.getElementById('bukti');
  const box = document.getElementById('uploadBox');
  const ico = document.getElementById('uploadIco');
  const nameEl = document.getElementById('uploadName');
  const hint = document.getElementById('uploadHint');

  function showFile() {
    const f = file.files[0];
    if (!f) {
      box.classList.remove('filled'); ico.innerHTML = '+';
      nameEl.textContent = 'Pilih atau tarik file ke sini'; hint.textContent = 'Gambar atau PDF';
      return;
    }
    box.classList.add('filled');
    nameEl.textContent = f.name;
    hint.textContent = (f.size / 1024 / 1024).toFixed(2) + ' MB — ketuk untuk ganti';
    if (f.type.startsWith('image/')) {
      const img = document.createElement('img');
      img.alt = 'Pratinjau bukti transfer';
      img.src = URL.createObjectURL(f);
      ico.innerHTML = ''; ico.appendChild(img);
    } else {
      ico.textContent = 'PDF';
      ico.style.fontSize = '.9rem';
    }
  }
  file.addEventListener('change', showFile);
  ['dragenter', 'dragover'].forEach(ev => box.addEventListener(ev, e => { e.preventDefault(); box.classList.add('drag'); }));
  ['dragleave', 'drop'].forEach(ev => box.addEventListener(ev, e => { e.preventDefault(); box.classList.remove('drag'); }));
  box.addEventListener('drop', e => {
    if (e.dataTransfer.files.length) { file.files = e.dataTransfer.files; showFile(); }
  });

  // 4. Nomor telepon hanya angka dan simbol umum
  const tel = document.getElementById('telepon');
  tel.addEventListener('input', () => { tel.value = tel.value.replace(/[^0-9+\-\s]/g, ''); });

  // 5. Penghitung karakter catatan
  const cat = document.getElementById('catatan');
  const count = document.getElementById('catCount');
  cat.addEventListener('input', () => count.textContent = cat.value.length);

  // 6. Validasi saat kirim: tandai kolom kosong, fokus ke yang pertama
  const form = document.getElementById('checkoutForm');
  const btn = document.getElementById('submitBtn');
  form.querySelectorAll('[required]').forEach(el => {
    el.addEventListener('input', () => el.classList.remove('invalid'));
  });
  form.addEventListener('submit', e => {
    let first = null;
    form.querySelectorAll('[required]').forEach(el => {
      const bad = el.type === 'file' ? !el.files.length : !el.value.trim();
      if (el.type === 'file') box.style.borderColor = bad ? 'var(--coral)' : '';
      else el.classList.toggle('invalid', bad);
      if (bad && !first) first = el;
    });
    if (first) {
      e.preventDefault();
      (first.type === 'file' ? box : first).scrollIntoView({ behavior: 'smooth', block: 'center' });
      if (first.type !== 'file') first.focus();
      btn.textContent = 'Lengkapi dulu ya!';
      setTimeout(() => btn.textContent = 'Kirim Pesanan', 1800);
      return;
    }
    btn.disabled = true;
    btn.textContent = 'Mengirim...';
  });
});
</script>

</body>
</html>