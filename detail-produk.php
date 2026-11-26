<?php 
// detail-produk.php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "produk";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Query dengan prepared statement (lebih aman)
$stmt = $conn->prepare("SELECT id, nama, deskripsi, harga, gambar FROM produk WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
} else {
    ?>
    <!DOCTYPE html>
    <html lang="id"><head><meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk tidak ditemukan - VOIDWEAR</title>
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=DM+Sans:wght@700&display=swap" rel="stylesheet">
    <style>
      body{margin:0;min-height:100vh;display:grid;place-items:center;padding:1.5rem;font-family:"DM Sans",sans-serif;color:#111;background:#fff radial-gradient(rgba(0,0,0,.13) 1.2px,transparent 1.3px) 0 0/12px 12px}
      .box{max-width:420px;text-align:center;background:#fff;border:3px solid #111;box-shadow:8px 8px 0 #f0443f;padding:2rem}
      h1{font-family:Anton,sans-serif;font-style:italic;text-transform:uppercase;font-weight:400;font-size:2.2rem;margin-bottom:1rem;text-shadow:3px 3px 0 #f0443f}
      a{display:inline-block;margin-top:.5rem;padding:.6rem 1.2rem;background:#f0443f;color:#fff;text-decoration:none;font-weight:700;border:3px solid #111;border-radius:999px;box-shadow:3px 3px 0 #111}
    </style></head>
    <body><div class="box"><h1>Produk tidak ditemukan</h1><a href="index.php">&larr; Kembali ke beranda</a></div></body></html>
    <?php
    exit;
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Detail Produk - <?= htmlspecialchars($product['nama']) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Anton&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --ink: #111;
      --paper: #fff;
      --coral: #f0443f;
      --soft: #f4f4f1;
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
    .wrap { max-width: 1050px; margin: 0 auto; padding: 0 1.5rem; }

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
    .btn.big { width: 100%; padding: .95rem; font-size: 1.05rem; }

    /* ---------- Layout ---------- */
    .breadcrumb { margin: 1.8rem 0 1.4rem; }
    .layout { display: grid; grid-template-columns: 1fr 1fr; gap: 2.2rem; align-items: start; }

    /* Gambar */
    .gallery {
      position: relative; border: var(--border); box-shadow: 8px 8px 0 var(--ink);
      background: var(--soft); overflow: hidden; cursor: zoom-in;
    }
    .gallery img {
      display: block; width: 100%; aspect-ratio: 1 / 1; object-fit: cover;
      transition: transform .4s ease; transform-origin: center;
    }
    .gallery.zoom { cursor: zoom-out; }
    .gallery.zoom img { transform: scale(1.8); }
    .gallery .tag {
      position: absolute; top: 12px; left: 12px; z-index: 1;
      background: var(--coral); color: #fff; border: 2px solid var(--ink);
      padding: .3rem .8rem; font-size: 1.2rem; transform: rotate(-4deg);
    }
    .gallery .hint {
      position: absolute; bottom: 10px; right: 10px; z-index: 1;
      background: var(--paper); border: 2px solid var(--ink); padding: .15rem .6rem;
      font-size: .75rem; font-weight: 700; pointer-events: none;
    }

    /* Info */
    .info h1 { font-size: clamp(2.4rem, 7vw, 3.8rem); text-shadow: 4px 4px 0 var(--coral); margin-bottom: 1.2rem; }
    .price-box {
      display: inline-block; background: var(--ink); color: #fff;
      padding: .6rem 1.1rem; box-shadow: 4px 4px 0 var(--coral); transform: rotate(-1deg);
      font-size: 2rem; margin-bottom: 1.4rem;
    }
    .desc { line-height: 1.7; font-weight: 500; max-width: 60ch; }

    /* Form */
    .buy { margin-top: 1.8rem; background: var(--paper); border: var(--border); box-shadow: var(--shadow); padding: 1.3rem; }
    .field-label { display: block; font-weight: 700; margin-bottom: .6rem; }
    .field-label + .field-label, .group + .group { margin-top: 1.2rem; }

    .sizes { display: flex; flex-wrap: wrap; gap: .6rem; }
    .size { position: relative; }
    .size input { position: absolute; opacity: 0; inset: 0; width: 100%; height: 100%; cursor: pointer; margin: 0; }
    .size span {
      display: grid; place-items: center; min-width: 52px; height: 46px; padding: 0 .8rem;
      font: 700 1rem 'DM Sans', sans-serif;
      background: var(--paper); border: var(--border); border-radius: 12px;
      box-shadow: var(--shadow-sm);
      transition: transform .12s ease, box-shadow .12s ease, background .12s ease;
    }
    .size:hover span { transform: translate(-1px,-1px); box-shadow: 4px 4px 0 var(--ink); }
    .size input:checked + span { background: var(--coral); color: #fff; transform: translate(3px,3px); box-shadow: 0 0 0 var(--ink); }
    .size input:focus-visible + span { outline: 3px dashed var(--coral); outline-offset: 3px; }

    .stepper { display: inline-flex; border: var(--border); border-radius: 12px; overflow: hidden; background: var(--paper); box-shadow: var(--shadow-sm); }
    .stepper button {
      width: 46px; font: 700 1.4rem 'DM Sans', sans-serif; border: 0; background: var(--soft); cursor: pointer;
      transition: background .12s ease;
    }
    .stepper button:hover { background: var(--coral); color: #fff; }
    .stepper button:active { transform: scale(.9); }
    .stepper input {
      width: 70px; height: 46px; text-align: center;
      font: 700 1.1rem 'DM Sans', sans-serif; color: var(--ink);
      border: 0; border-left: var(--border); border-right: var(--border); border-radius: 0;
      -moz-appearance: textfield;
    }
    .stepper input::-webkit-outer-spin-button, .stepper input::-webkit-inner-spin-button { -webkit-appearance: none; }
    .stepper input:focus { outline: none; background: #fff3f2; }

    .live-total {
      margin-top: 1.4rem; padding: .8rem 1rem; border: var(--border); border-style: dashed; background: var(--soft);
      display: flex; justify-content: space-between; align-items: baseline;
    }
    .live-total small { font-weight: 700; letter-spacing: 1px; }
    .live-total b { font-size: 1.7rem; }
    .live-total b.bump { animation: bump .3s ease; }
    @keyframes bump { 40% { transform: scale(1.12); color: var(--coral); } }

    .actions { display: grid; gap: .9rem; margin-top: 1.2rem; }

    .toast {
      position: fixed; left: 50%; bottom: 24px; z-index: 50;
      transform: translate(-50%, 140%); transition: transform .25s ease;
      background: var(--ink); color: #fff; padding: .7rem 1.2rem; font-weight: 700;
      border: var(--border); box-shadow: 4px 4px 0 var(--coral);
    }
    .toast.show { transform: translate(-50%, 0); }

    @media (max-width: 820px) {
      header { padding: .7rem 1rem; }
      header .btn { display: none; }
      .wrap { padding: 0 1rem; }
      .layout { grid-template-columns: 1fr; gap: 1.6rem; }
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
  <a class="btn" href="simpan-keranjang.php">Keranjang</a>
</header>

<main class="wrap">
  <div class="breadcrumb"><a href="index.php" class="btn">&larr; Kembali ke Beranda</a></div>

  <div class="layout">

    <!-- Gambar -->
    <div class="gallery" id="gallery">
      <span class="tag display">Keren!</span>
      <img src="img/<?= htmlspecialchars($product['gambar']) ?>" alt="<?= htmlspecialchars($product['nama']) ?>">
      <span class="hint">Ketuk untuk perbesar</span>
    </div>

    <!-- Info + form -->
    <div class="info">
      <h1 class="display"><?= htmlspecialchars($product['nama']) ?></h1>
      <div class="price-box display">Rp <?= number_format($product['harga'], 0, ',', '.') ?></div>
      <p class="desc">
        <?php if (trim($product['deskripsi']) !== ''): ?>
          <?= nl2br(htmlspecialchars($product['deskripsi'])) ?>
        <?php else: ?>
          Belum ada deskripsi untuk produk ini.
        <?php endif; ?>
      </p>

      <!-- Form untuk checkout dan keranjang -->
      <form method="POST" action="proses-belanja.php" class="buy" id="buyForm">
        <input type="hidden" name="id_produk" value="<?= $product['id'] ?>">
        <input type="hidden" name="nama_produk" value="<?= htmlspecialchars($product['nama']) ?>">

        <div class="group">
          <span class="field-label" id="sizeLabel">Pilih Ukuran</span>
          <div class="sizes" role="radiogroup" aria-labelledby="sizeLabel">
            <?php foreach (['S','M','L','XL','XXL'] as $s): ?>
              <label class="size">
                <input type="radio" name="size" value="<?= $s ?>" <?= $s === 'M' ? 'checked' : '' ?> required>
                <span><?= $s ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="group">
          <label class="field-label" for="jumlah">Jumlah</label>
          <div class="stepper">
            <button type="button" data-step="-1" aria-label="Kurangi">&minus;</button>
            <input type="number" name="jumlah" id="jumlah" min="1" value="1" required>
            <button type="button" data-step="1" aria-label="Tambah">+</button>
          </div>
        </div>

        <div class="live-total">
          <small>TOTAL</small>
          <b class="display" id="liveTotal" data-price="<?= (int)$product['harga'] ?>">Rp <?= number_format($product['harga'], 0, ',', '.') ?></b>
        </div>

        <div class="actions">
          <button type="submit" name="action" value="checkout" class="btn primary big">Ke Pembayaran</button>
          <button type="submit" name="action" value="keranjang" class="btn big">Simpan ke Keranjang</button>
        </div>
      </form>
    </div>
  </div>
</main>

<div class="toast" id="toast" role="status" aria-live="polite"></div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // 1. Gambar bisa diperbesar dengan klik
  const gallery = document.getElementById('gallery');
  const img = gallery.querySelector('img');
  gallery.addEventListener('click', e => {
    gallery.classList.toggle('zoom');
    if (gallery.classList.contains('zoom')) {
      const r = gallery.getBoundingClientRect();
      img.style.transformOrigin = ((e.clientX - r.left) / r.width * 100) + '% ' + ((e.clientY - r.top) / r.height * 100) + '%';
    }
  });
  gallery.addEventListener('mousemove', e => {
    if (!gallery.classList.contains('zoom')) return;
    const r = gallery.getBoundingClientRect();
    img.style.transformOrigin = ((e.clientX - r.left) / r.width * 100) + '% ' + ((e.clientY - r.top) / r.height * 100) + '%';
  });

  // 2. Tombol +/- jumlah dan total yang ikut berubah
  const qty = document.getElementById('jumlah');
  const totalEl = document.getElementById('liveTotal');
  const price = parseInt(totalEl.dataset.price, 10) || 0;

  function updateTotal() {
    const n = Math.max(1, parseInt(qty.value, 10) || 1);
    totalEl.textContent = 'Rp ' + (price * n).toLocaleString('id-ID');
    totalEl.classList.remove('bump'); void totalEl.offsetWidth; totalEl.classList.add('bump');
  }
  document.querySelectorAll('[data-step]').forEach(b => {
    b.addEventListener('click', () => {
      qty.value = Math.max(1, (parseInt(qty.value, 10) || 1) + parseInt(b.dataset.step, 10));
      updateTotal();
    });
  });
  qty.addEventListener('input', updateTotal);

  // 3. Toast saat tombol ditekan (tombol tidak dinonaktifkan agar nilai "action" tetap terkirim)
  const toast = document.getElementById('toast');
  document.getElementById('buyForm').addEventListener('submit', e => {
    const v = e.submitter ? e.submitter.value : '';
    toast.textContent = v === 'keranjang' ? 'Menyimpan ke keranjang...' : 'Menuju pembayaran...';
    toast.classList.add('show');
  });
});
</script>

</body>
</html>