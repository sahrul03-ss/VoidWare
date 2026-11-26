<?php
// proses-belanja.php

// ---------- Tampilan halaman kecil (sukses / error) ----------
function render_page($ok, $judul, $pesan, $href, $tombol, $redirectMs = 0) {
    $warna = $ok ? 'var(--green)' : 'var(--coral)';
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $ok ? 'Berhasil' : 'Gagal' ?> - VOIDWEAR</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Anton&family=DM+Sans:wght@500;700&display=swap" rel="stylesheet">
  <style>
    :root { --ink:#111; --paper:#fff; --coral:#f0443f; --green:#2fa84f; --border:3px solid var(--ink); }
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
      min-height:100vh; display:grid; place-items:center; padding:1.5rem;
      font-family:'DM Sans',sans-serif; color:var(--ink); background-color:var(--paper);
      background-image:radial-gradient(rgba(0,0,0,.13) 1.2px, transparent 1.3px); background-size:12px 12px;
    }
    :focus-visible { outline:3px dashed var(--coral); outline-offset:3px; }
    .display { font-family:'Anton',sans-serif; font-style:italic; text-transform:uppercase; font-weight:400; letter-spacing:.5px; line-height:1; }
    .box {
      position:relative; width:min(100%,460px); text-align:center;
      background:var(--paper); border:var(--border); box-shadow:8px 8px 0 <?= $warna ?>;
      padding:2.2rem 1.6rem 1.8rem; animation:pop .35s cubic-bezier(.2,1.4,.4,1);
    }
    @keyframes pop { from { transform:scale(.8) rotate(-3deg); opacity:0; } }
    .stamp {
      position:absolute; top:-22px; left:50%; transform:translateX(-50%) rotate(-4deg);
      background:<?= $warna ?>; color:#fff; border:var(--border); padding:.4rem 1.2rem;
      font-size:1.4rem; white-space:nowrap; box-shadow:3px 3px 0 var(--ink);
    }
    h1 { font-size:clamp(1.9rem,8vw,2.6rem); margin:1rem 0; text-shadow:3px 3px 0 <?= $warna ?>; }
    p { line-height:1.6; font-weight:500; margin-bottom:1.3rem; }
    .btn {
      display:inline-flex; padding:.7rem 1.4rem; font:700 .95rem 'DM Sans',sans-serif;
      color:#fff; text-decoration:none; background:var(--coral);
      border:var(--border); border-radius:999px; box-shadow:3px 3px 0 var(--ink);
      transition:transform .12s ease, box-shadow .12s ease;
    }
    .btn:hover { transform:translate(-1px,-1px); box-shadow:4px 4px 0 var(--ink); }
    .btn:active { transform:translate(3px,3px); box-shadow:0 0 0 var(--ink); }
    .bar { height:10px; margin-top:1.3rem; border:2px solid var(--ink); background:#fff; overflow:hidden; }
    .bar i { display:block; height:100%; width:0; background:var(--coral); animation:fill <?= (int)$redirectMs ?>ms linear forwards; }
    @keyframes fill { to { width:100%; } }
    @media (prefers-reduced-motion: reduce) { * { animation:none !important; transition:none !important; } .bar { display:none; } }
  </style>
</head>
<body>
  <main class="box" role="status">
    <div class="stamp display"><?= $ok ? 'Berhasil!' : 'Oops!' ?></div>
    <h1 class="display"><?= htmlspecialchars($judul) ?></h1>
    <p><?= htmlspecialchars($pesan) ?></p>
    <a class="btn" href="<?= htmlspecialchars($href) ?>"><?= htmlspecialchars($tombol) ?></a>
    <?php if ($redirectMs > 0): ?>
      <div class="bar"><i></i></div>
      <script>setTimeout(function () { location.href = <?= json_encode($href) ?>; }, <?= (int)$redirectMs ?>);</script>
    <?php endif; ?>
  </main>
</body>
</html>
    <?php
    exit;
}

function gagal($pesan, $href = 'javascript:history.back()', $tombol = 'Kembali') {
    render_page(false, 'Gagal', $pesan, $href, $tombol);
}

// Akses langsung tanpa form: arahkan ke beranda
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

// ---------- Ambil & validasi input ----------
$id_produk = isset($_POST['id_produk']) ? (int)$_POST['id_produk'] : 0;
$size      = isset($_POST['size']) ? trim($_POST['size']) : '';
$jumlah    = isset($_POST['jumlah']) ? (int)$_POST['jumlah'] : 0;
$action    = isset($_POST['action']) ? $_POST['action'] : '';

if ($id_produk <= 0 || $jumlah < 1) {
    gagal('Data produk atau jumlah tidak valid.');
}
if (!in_array($size, ['S', 'M', 'L', 'XL', 'XXL'], true)) {
    gagal('Silakan pilih ukuran terlebih dahulu.');
}

if ($action === 'checkout') {
    // Redirect ke checkout.php dengan parameter
    $query = http_build_query([
        'id_produk' => $id_produk,
        'size'      => $size,
        'jumlah'    => $jumlah,
    ]);
    header("Location: checkout.php?" . $query);
    exit;

} elseif ($action === 'keranjang') {
    // Koneksi database
    $conn = new mysqli("localhost", "root", "", "produk");
    if ($conn->connect_error) {
        gagal('Koneksi database gagal: ' . $conn->connect_error);
    }

    // Ambil nama produk dari database (bukan dari input form)
    $stmtNama = $conn->prepare("SELECT nama FROM produk WHERE id = ?");
    $stmtNama->bind_param("i", $id_produk);
    $stmtNama->execute();
    $produk = $stmtNama->get_result()->fetch_assoc();
    $stmtNama->close();

    if (!$produk) {
        $conn->close();
        gagal('Produk tidak ditemukan.', 'index.php', 'Kembali ke Beranda');
    }
    $nama_produk = $produk['nama'];

    // Simpan ke keranjang
    $stmt = $conn->prepare("INSERT INTO keranjang (id_produk, nama_produk, size, jumlah) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $id_produk, $nama_produk, $size, $jumlah);
    $berhasil = $stmt->execute();
    $error = $stmt->error;
    $stmt->close();
    $conn->close();

    if (!$berhasil) {
        gagal('Gagal menyimpan ke keranjang: ' . $error);
    }

    render_page(
        true,
        'Masuk keranjang!',
        $nama_produk . ' (' . $size . ' × ' . $jumlah . ') berhasil ditambahkan ke keranjang.',
        'simpan-keranjang.php',
        'Lihat keranjang',
        1500
    );

} else {
    gagal('Aksi tidak valid.');
}