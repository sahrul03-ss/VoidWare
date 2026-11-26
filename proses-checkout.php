<?php
// proses-checkout.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ---------- Tampilan halaman (dipakai untuk sukses & error) ----------
function render_page($ok, $judul, $pesan, $rows = [], $primaryHref = 'index.php', $primaryText = 'Kembali ke Beranda', $secondary = null) {
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $ok ? 'Pesanan Berhasil' : 'Checkout Gagal' ?> - VOIDWEAR</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Anton&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    :root { --ink:#111; --paper:#fff; --coral:#f0443f; --green:#2fa84f; --soft:#f4f4f1; --border:3px solid var(--ink); }
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
      min-height:100vh; display:grid; place-items:center; padding:1.5rem; overflow-x:hidden;
      font-family:'DM Sans',sans-serif; color:var(--ink);
      background-color:var(--paper);
      background-image:radial-gradient(rgba(0,0,0,.13) 1.2px, transparent 1.3px);
      background-size:12px 12px;
    }
    :focus-visible { outline:3px dashed var(--coral); outline-offset:3px; }
    .display { font-family:'Anton',sans-serif; font-style:italic; text-transform:uppercase; font-weight:400; letter-spacing:.5px; line-height:1; }

    .box {
      position:relative; width:min(100%, 520px);
      background:var(--paper); border:var(--border);
      box-shadow:8px 8px 0 <?= $ok ? 'var(--green)' : 'var(--coral)' ?>;
      padding:2.2rem 1.6rem 1.8rem; text-align:center;
      animation:pop .4s cubic-bezier(.2,1.4,.4,1);
    }
    @keyframes pop { from { transform:scale(.8) rotate(-3deg); opacity:0; } }

    .stamp {
      position:absolute; top:-22px; left:50%; transform:translateX(-50%) rotate(-4deg);
      background:<?= $ok ? 'var(--green)' : 'var(--coral)' ?>; color:#fff;
      border:var(--border); padding:.4rem 1.2rem; font-size:1.4rem; white-space:nowrap;
      box-shadow:3px 3px 0 var(--ink);
    }
    h1 { font-size:clamp(2rem,8vw,2.8rem); margin:1rem 0 1rem; text-shadow:3px 3px 0 <?= $ok ? 'var(--green)' : 'var(--coral)' ?>; }
    p.msg { line-height:1.6; font-weight:500; margin-bottom:1.2rem; }

    .rows { text-align:left; border:var(--border); border-style:dashed; background:var(--soft); padding:1rem; margin-bottom:1.4rem; display:grid; gap:.5rem; }
    .rows div { display:flex; justify-content:space-between; gap:1rem; font-weight:500; }
    .rows b { font-weight:700; text-align:right; }
    .rows .sum { margin-top:.4rem; padding-top:.6rem; border-top:2px solid var(--ink); font-size:1.15rem; }

    .actions { display:flex; gap:.8rem; justify-content:center; flex-wrap:wrap; }
    .btn {
      display:inline-flex; align-items:center; justify-content:center;
      padding:.7rem 1.4rem; font:700 .95rem 'DM Sans',sans-serif;
      color:var(--ink); text-decoration:none; background:var(--paper);
      border:var(--border); border-radius:999px; box-shadow:3px 3px 0 var(--ink);
      cursor:pointer; transition:transform .12s ease, box-shadow .12s ease;
    }
    .btn:hover { transform:translate(-1px,-1px); box-shadow:4px 4px 0 var(--ink); }
    .btn:active { transform:translate(3px,3px); box-shadow:0 0 0 var(--ink); }
    .btn.primary { background:var(--coral); color:#fff; }

    .confetti { position:fixed; top:-20px; width:10px; height:14px; border:2px solid var(--ink); pointer-events:none; z-index:50; animation:fall linear forwards; }
    @keyframes fall { to { transform:translateY(110vh) rotate(720deg); } }

    @media (prefers-reduced-motion: reduce) { * { animation:none !important; transition:none !important; } }
  </style>
</head>
<body>
  <main class="box">
    <div class="stamp display"><?= $ok ? 'Berhasil!' : 'Oops!' ?></div>
    <h1 class="display"><?= htmlspecialchars($judul) ?></h1>
    <p class="msg"><?= $pesan ?></p>

    <?php if (!empty($rows)): ?>
    <div class="rows">
      <?php foreach ($rows as $i => $r): ?>
        <div class="<?= $i === count($rows) - 1 ? 'sum' : '' ?>"><span><?= htmlspecialchars($r[0]) ?></span><b><?= htmlspecialchars($r[1]) ?></b></div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="actions">
      <a class="btn primary" href="<?= $primaryHref ?>"><?= htmlspecialchars($primaryText) ?></a>
      <?php if ($secondary): ?><a class="btn" href="<?= $secondary[0] ?>"><?= htmlspecialchars($secondary[1]) ?></a><?php endif; ?>
    </div>
  </main>

  <?php if ($ok): ?>
  <script>
    // Confetti sederhana saat pesanan berhasil
    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      const colors = ['#f0443f', '#111', '#fff', '#2fa84f'];
      for (let i = 0; i < 40; i++) {
        const c = document.createElement('div');
        c.className = 'confetti';
        c.style.left = Math.random() * 100 + 'vw';
        c.style.background = colors[i % colors.length];
        c.style.animationDuration = (2 + Math.random() * 2.5) + 's';
        c.style.animationDelay = (Math.random() * .8) + 's';
        document.body.appendChild(c);
        setTimeout(() => c.remove(), 6000);
      }
    }
  </script>
  <?php endif; ?>
</body>
</html>
    <?php
    exit;
}

function gagal($pesan) {
    render_page(false, 'Checkout gagal', $pesan, [], 'javascript:history.back()', 'Kembali ke Form Checkout', ['index.php', 'Beranda']);
}

// ---------- Koneksi database ----------
$conn = new mysqli("localhost", "root", "", "produk");
if ($conn->connect_error) {
    gagal('Koneksi database gagal: ' . htmlspecialchars($conn->connect_error));
}

// Bersihkan input (tanpa htmlspecialchars di sini; escape dilakukan saat ditampilkan,
// supaya data di database tidak ter-encode dua kali)
function clean_input($data) {
    return trim(strip_tags($data));
}

// ---------- Tangkap data POST ----------
$id_produk = isset($_POST['id_produk']) ? (int)$_POST['id_produk'] : 0;
$size      = isset($_POST['size']) ? clean_input($_POST['size']) : '';
$jumlah    = isset($_POST['jumlah']) ? (int)$_POST['jumlah'] : 1;
$nama      = isset($_POST['nama']) ? clean_input($_POST['nama']) : '';
$alamat    = isset($_POST['alamat']) ? clean_input($_POST['alamat']) : '';
$telepon   = isset($_POST['telepon']) ? clean_input($_POST['telepon']) : '';
$catatan   = isset($_POST['catatan']) ? clean_input($_POST['catatan']) : '';

// ---------- Validasi ----------
if ($id_produk <= 0 || $size === '' || $jumlah <= 0 || $nama === '' || $alamat === '' || $telepon === '') {
    gagal('Data tidak lengkap atau tidak valid. Pastikan semua kolom wajib sudah diisi.');
}
if (!in_array($size, ['S', 'M', 'L', 'XL', 'XXL'], true)) {
    gagal('Ukuran tidak valid.');
}

// ---------- Hitung total dari harga di database (bukan dari input pembeli) ----------
$stmtHarga = $conn->prepare("SELECT nama, harga FROM produk WHERE id = ?");
$stmtHarga->bind_param("i", $id_produk);
$stmtHarga->execute();
$produk = $stmtHarga->get_result()->fetch_assoc();
$stmtHarga->close();

if (!$produk) {
    gagal('Produk tidak ditemukan.');
}
$total = (float)$produk['harga'] * $jumlah;

// ---------- Upload bukti transfer ----------
if (!isset($_FILES['bukti']) || $_FILES['bukti']['error'] !== UPLOAD_ERR_OK) {
    gagal('File bukti transfer wajib diupload.');
}

$fileTmpPath   = $_FILES['bukti']['tmp_name'];
$fileName      = $_FILES['bukti']['name'];
$fileSize      = $_FILES['bukti']['size'];
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// Format file yang diizinkan
$allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
if (!in_array($fileExtension, $allowedfileExtensions, true)) {
    gagal('Format file tidak didukung. Gunakan jpg, png, gif, atau pdf.');
}

// Batas ukuran 5 MB
if ($fileSize > 5 * 1024 * 1024) {
    gagal('Ukuran file terlalu besar. Maksimal 5 MB.');
}

// Cek isi file yang sebenarnya (bukan hanya ekstensi)
if (function_exists('finfo_open')) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $fileTmpPath);
    finfo_close($finfo);
    $allowedMime = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    if (!in_array($mime, $allowedMime, true)) {
        gagal('Isi file tidak sesuai. Gunakan gambar atau PDF yang valid.');
    }
}

// Buat nama file baru agar unik
$newFileName = md5(time() . $fileName . mt_rand()) . '.' . $fileExtension;

// Folder upload
$uploadFileDir = './uploads/';
if (!is_dir($uploadFileDir)) {
    mkdir($uploadFileDir, 0755, true);
}
$dest_path = $uploadFileDir . $newFileName;

if (!move_uploaded_file($fileTmpPath, $dest_path)) {
    gagal('Gagal mengupload file bukti transfer.');
}

// ---------- Simpan data ke tabel `pesanan` ----------
$stmt = $conn->prepare("INSERT INTO pesanan (id_produk, size, jumlah, total, nama_customer, alamat, telepon, catatan, bukti_transfer, tanggal_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("isidsssss", $id_produk, $size, $jumlah, $total, $nama, $alamat, $telepon, $catatan, $newFileName);

if ($stmt->execute()) {
    $idPesanan = $stmt->insert_id;
    $stmt->close();
    $conn->close();

    render_page(
        true,
        'Pesanan terkirim!',
        'Terima kasih, <strong>' . htmlspecialchars($nama) . '</strong>. Pesanan Anda sudah kami terima dan segera diproses.',
        [
            ['Nomor pesanan', '#' . $idPesanan],
            ['Produk', $produk['nama']],
            ['Ukuran', $size],
            ['Jumlah', $jumlah . ' pcs'],
            ['Total', 'Rp ' . number_format($total, 0, ',', '.')],
        ],
        'index.php',
        'Kembali ke Beranda'
    );
} else {
    $err = $stmt->error;
    $stmt->close();
    $conn->close();
    // Hapus file yang sudah terlanjur diupload supaya tidak menumpuk
    if (file_exists($dest_path)) unlink($dest_path);
    gagal('Terjadi kesalahan saat menyimpan data: ' . htmlspecialchars($err));
}