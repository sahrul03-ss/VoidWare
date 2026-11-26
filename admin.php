<?php
// admin.php — panel admin VOIDWEAR (login, kelola katalog, daftar pesanan)
session_start();

// ====== GANTI KREDENSIAL INI SEBELUM DIPAKAI ======
$ADMIN_USER = 'sahrul';
$ADMIN_PASS = 'admin';
// ==================================================

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function flash($type, $msg) { $_SESSION['flash'] = [$type, $msg]; }
function kembali($tab = 'produk') { header("Location: admin.php?tab=" . $tab); exit; }

if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));

// ---------- Logout ----------
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

// ---------- Login ----------
$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';
    if (hash_equals($ADMIN_USER, $u) && hash_equals($ADMIN_PASS, $p)) {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
        header("Location: admin.php");
        exit;
    }
    $loginError = 'Username atau password salah.';
}

// ---------- CSS bersama ----------
function css() { ?>
<style>
  :root { --ink:#111; --paper:#fff; --coral:#f0443f; --soft:#f4f4f1; --green:#2fa84f; --blue:#2f7df0;
          --shadow:6px 6px 0 var(--ink); --shadow-sm:3px 3px 0 var(--ink); --border:3px solid var(--ink); }
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family:'DM Sans',sans-serif; color:var(--ink); background-color:var(--paper);
         background-image:radial-gradient(rgba(0,0,0,.13) 1.2px, transparent 1.3px); background-size:12px 12px; padding-bottom:3rem; }
  :focus-visible { outline:3px dashed var(--coral); outline-offset:3px; }
  .display { font-family:'Anton',sans-serif; font-style:italic; text-transform:uppercase; font-weight:400; letter-spacing:.5px; line-height:1; }
  .wrap { max-width:1150px; margin:0 auto; padding:0 1.5rem; }
  .btn { display:inline-flex; align-items:center; justify-content:center; gap:.5rem; padding:.55rem 1.1rem;
         font:700 .9rem 'DM Sans',sans-serif; color:var(--ink); text-decoration:none; background:var(--paper);
         border:var(--border); border-radius:999px; box-shadow:var(--shadow-sm); cursor:pointer;
         transition:transform .12s ease, box-shadow .12s ease, background .12s ease; }
  .btn:hover { transform:translate(-1px,-1px); box-shadow:4px 4px 0 var(--ink); }
  .btn:active { transform:translate(3px,3px); box-shadow:0 0 0 var(--ink); }
  .btn.primary { background:var(--coral); color:#fff; }
  .btn.blue { background:var(--blue); color:#fff; }
  .btn.danger { color:var(--coral); }
  .btn.danger:hover { background:var(--coral); color:#fff; }
  .btn.small { padding:.35rem .8rem; font-size:.8rem; }
  input[type=text], input[type=password], input[type=number], textarea {
    width:100%; margin-top:.4rem; padding:.7rem .9rem; font:500 1rem 'DM Sans',sans-serif; color:var(--ink);
    background:var(--paper); border:var(--border); border-radius:10px; }
  input:focus, textarea:focus { outline:none; box-shadow:4px 4px 0 var(--coral); }
  label.f { display:block; margin-top:1rem; font-weight:700; }
  @media (prefers-reduced-motion: reduce) { * { animation:none !important; transition:none !important; } }
</style>
<?php }

// ---------- Halaman login ----------
if (empty($_SESSION['admin'])) { ?>
<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Admin - VOIDWEAR</title>
<link href="https://fonts.googleapis.com/css2?family=Anton&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
<?php css(); ?>
<style>
  body { min-height:100vh; display:grid; place-items:center; padding:1.5rem; }
  .box { position:relative; width:min(100%,400px); background:var(--paper); border:var(--border); box-shadow:8px 8px 0 var(--coral); padding:2.2rem 1.6rem 1.6rem; animation:pop .35s cubic-bezier(.2,1.4,.4,1); }
  @keyframes pop { from { transform:scale(.85) rotate(-3deg); opacity:0; } }
  .stamp { position:absolute; top:-22px; left:50%; transform:translateX(-50%) rotate(-4deg); background:var(--ink); color:#fff; border:var(--border); padding:.4rem 1.2rem; font-size:1.3rem; white-space:nowrap; box-shadow:3px 3px 0 var(--coral); }
  h1 { font-size:2.4rem; margin:1rem 0 .4rem; text-shadow:3px 3px 0 var(--coral); text-align:center; }
  .err { margin-top:1rem; padding:.7rem; border:var(--border); background:#fff0ef; font-weight:700; text-align:center; }
  .btn.big { width:100%; margin-top:1.4rem; padding:.9rem; font-size:1rem; }
</style></head>
<body>
  <form class="box" method="POST" autocomplete="off">
    <div class="stamp display">Khusus admin</div>
    <h1 class="display">Login</h1>
    <input type="hidden" name="action" value="login">
    <label class="f" for="u">Username</label>
    <input type="text" id="u" name="username" required autofocus>
    <label class="f" for="p">Password</label>
    <input type="password" id="p" name="password" required>
    <?php if ($loginError): ?><div class="err" role="alert"><?= e($loginError) ?></div><?php endif; ?>
    <button class="btn primary big" type="submit">Masuk</button>
    <p style="text-align:center;margin-top:1rem"><a href="index.php" style="color:var(--ink);font-weight:700">&larr; Ke toko</a></p>
  </form>
</body></html>
<?php exit; }

// ====================== SUDAH LOGIN ======================
$conn = new mysqli("localhost", "root", "", "produk");
if ($conn->connect_error) die("Koneksi gagal: " . $conn->connect_error);
$conn->set_charset('utf8mb4');

// Simpan gambar produk. Mengembalikan: nama file | null (tidak ada upload) | false (error, lihat $err)
function simpan_gambar($file, &$err) {
    if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) { $err = 'Upload gambar gagal.'; return false; }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) { $err = 'Format gambar harus jpg, png, webp, atau gif.'; return false; }
    if ($file['size'] > 3 * 1024 * 1024) { $err = 'Ukuran gambar maksimal 3 MB.'; return false; }
    if (function_exists('finfo_open')) {
        $fi = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($fi, $file['tmp_name']);
        finfo_close($fi);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true)) { $err = 'Isi file bukan gambar yang valid.'; return false; }
    }
    $dir = __DIR__ . '/img/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $name = 'p_' . bin2hex(random_bytes(6)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $dir . $name)) { $err = 'Gagal menyimpan gambar.'; return false; }
    return $name;
}

// Hanya hapus gambar yang diunggah lewat admin (awalan "p_"), supaya gambar lama tidak ikut terhapus
function hapus_gambar_admin($name) {
    if (strpos($name, 'p_') === 0 && $name === basename($name)) {
        $path = __DIR__ . '/img/' . $name;
        if (is_file($path)) unlink($path);
    }
}

// ---------- Aksi (POST) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['action'] ?? '';
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        flash('error', 'Sesi tidak valid. Muat ulang halaman.');
        kembali();
    }

    try {
        if ($aksi === 'simpan_produk') {
            $id        = (int)($_POST['id'] ?? 0);
            $nama      = trim(strip_tags($_POST['nama'] ?? ''));
            $deskripsi = trim(strip_tags($_POST['deskripsi'] ?? ''));
            $harga     = (int)($_POST['harga'] ?? 0);

            if ($nama === '' || $harga <= 0) { flash('error', 'Nama wajib diisi dan harga harus lebih dari 0.'); kembali(); }

            $err = '';
            $gambarBaru = simpan_gambar($_FILES['gambar'] ?? null, $err);
            if ($gambarBaru === false) { flash('error', $err); kembali(); }

            if ($id > 0) {
                // Edit produk
                $old = $conn->query("SELECT gambar FROM produk WHERE id = " . $id)->fetch_assoc();
                if (!$old) { flash('error', 'Produk tidak ditemukan.'); kembali(); }
                if ($gambarBaru) {
                    $st = $conn->prepare("UPDATE produk SET nama=?, deskripsi=?, harga=?, gambar=? WHERE id=?");
                    $st->bind_param("ssisi", $nama, $deskripsi, $harga, $gambarBaru, $id);
                } else {
                    $st = $conn->prepare("UPDATE produk SET nama=?, deskripsi=?, harga=? WHERE id=?");
                    $st->bind_param("ssii", $nama, $deskripsi, $harga, $id);
                }
                $st->execute(); $st->close();
                if ($gambarBaru) hapus_gambar_admin($old['gambar']);
                flash('ok', 'Produk "' . $nama . '" diperbarui.');
            } else {
                // Tambah produk
                if (!$gambarBaru) { flash('error', 'Gambar produk wajib diunggah.'); kembali(); }
                $st = $conn->prepare("INSERT INTO produk (nama, deskripsi, harga, gambar) VALUES (?, ?, ?, ?)");
                $st->bind_param("ssis", $nama, $deskripsi, $harga, $gambarBaru);
                $st->execute(); $st->close();
                flash('ok', 'Produk "' . $nama . '" ditambahkan.');
            }
            kembali();

        } elseif ($aksi === 'hapus_produk') {
            $id = (int)($_POST['id'] ?? 0);
            $old = $conn->query("SELECT nama, gambar FROM produk WHERE id = " . $id)->fetch_assoc();
            if ($old) {
                $st = $conn->prepare("DELETE FROM keranjang WHERE id_produk = ?");
                $st->bind_param("i", $id); $st->execute(); $st->close();
                $st = $conn->prepare("DELETE FROM produk WHERE id = ?");
                $st->bind_param("i", $id); $st->execute(); $st->close();
                hapus_gambar_admin($old['gambar']);
                flash('ok', 'Produk "' . $old['nama'] . '" dihapus.');
            }
            kembali();

        } elseif ($aksi === 'selesai_pesanan') {
            $id = (int)($_POST['id'] ?? 0);
            $st = $conn->prepare("DELETE FROM pesanan WHERE id = ?");
            $st->bind_param("i", $id); $st->execute(); $st->close();
            flash('ok', 'Pesanan #' . $id . ' ditandai selesai.');
            kembali('pesanan');
        }
    } catch (Throwable $ex) {
        flash('error', 'Terjadi kesalahan: ' . $ex->getMessage());
        kembali(($aksi === 'selesai_pesanan') ? 'pesanan' : 'produk');
    }
}

// ---------- Data tampilan ----------
$tab = (($_GET['tab'] ?? 'produk') === 'pesanan') ? 'pesanan' : 'produk';

$produkList = [];
$res = $conn->query("SELECT id, nama, deskripsi, harga, gambar FROM produk ORDER BY id DESC");
if ($res) while ($r = $res->fetch_assoc()) $produkList[] = $r;

$pesananList = [];
try {
    $res = $conn->query("SELECT p.*, pr.nama AS nama_produk FROM pesanan p LEFT JOIN produk pr ON p.id_produk = pr.id ORDER BY p.id DESC");
    if ($res) while ($r = $res->fetch_assoc()) $pesananList[] = $r;
} catch (Throwable $ex) { /* tabel pesanan belum ada */ }

$nilaiPesanan = 0;
foreach ($pesananList as $p) $nilaiPesanan += (float)($p['total'] ?? 0);

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$csrf = $_SESSION['csrf'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - VOIDWEAR</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Anton&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
  <?php css(); ?>
  <style>
    header { position:sticky; top:0; z-index:20; display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap;
             padding:.8rem 1.5rem; background:var(--paper); border-bottom:var(--border); }
    .logo { display:flex; align-items:center; gap:.6rem; font-size:1.5rem; text-decoration:none; color:var(--ink); }
    .logo .mark { width:44px; height:44px; background:var(--ink); border-radius:50%; overflow:hidden; display:grid; place-items:center; box-shadow:3px 3px 0 var(--coral); }
    .logo .mark img { width:100%; height:100%; object-fit:contain; }
    .logo small { background:var(--coral); color:#fff; border:2px solid var(--ink); padding:.15rem .5rem; font-size:.9rem; transform:rotate(-3deg); }
    .head-actions { display:flex; gap:.6rem; flex-wrap:wrap; }

    .stats { display:flex; gap:1rem; flex-wrap:wrap; margin:2rem 0 1.5rem; }
    .stat { flex:1 1 160px; background:var(--paper); border:var(--border); box-shadow:var(--shadow-sm); padding:.8rem 1rem; }
    .stat b { display:block; font-size:2rem; }
    .stat small { font-weight:700; font-size:.75rem; }

    .tabs { display:flex; gap:.7rem; margin-bottom:1.5rem; flex-wrap:wrap; }
    .tabs .btn.active { background:var(--ink); color:#fff; }

    .toolbar { display:flex; gap:.8rem; flex-wrap:wrap; margin-bottom:1.4rem; }
    .toolbar input { flex:1 1 220px; margin:0; border-radius:999px; box-shadow:var(--shadow-sm); }

    .grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(250px, 1fr)); gap:1.4rem; }
    .card { background:var(--paper); border:var(--border); box-shadow:var(--shadow); padding:.8rem; transition:transform .12s ease, box-shadow .12s ease; }
    .card:hover { transform:translate(-2px,-2px); box-shadow:8px 8px 0 var(--ink); }
    .card .thumb { border:var(--border); background:var(--soft); overflow:hidden; }
    .card .thumb img { display:block; width:100%; height:200px; object-fit:cover; }
    .card h3 { font-size:1.3rem; margin:.8rem 0 .3rem; }
    .card .price { font-weight:700; margin-bottom:.8rem; }
    .card .row { display:flex; gap:.5rem; flex-wrap:wrap; }

    .empty { text-align:center; padding:2.2rem 1.5rem; border:var(--border); border-style:dashed; background:var(--paper); font-weight:700; grid-column:1 / -1; }

    .table-wrap { overflow-x:auto; background:var(--paper); border:var(--border); box-shadow:var(--shadow); }
    table { width:100%; border-collapse:collapse; min-width:950px; }
    th, td { padding:.8rem; text-align:left; vertical-align:top; border-bottom:2px solid var(--ink); font-size:.92rem; }
    th { background:var(--ink); color:#fff; font-family:'Anton',sans-serif; font-style:italic; font-weight:400; text-transform:uppercase; letter-spacing:.5px; white-space:nowrap; }
    tbody tr:hover { background:#fff3f2; }
    td a { color:var(--coral); font-weight:700; }

    dialog { margin:auto; padding:1.5rem; width:min(94vw, 480px); max-height:92vh; overflow:auto; border:var(--border); box-shadow:8px 8px 0 var(--coral); background:var(--paper); }
    dialog::backdrop { background:rgba(0,0,0,.55); }
    dialog[open] { animation:pop .2s ease; }
    @keyframes pop { from { transform:scale(.88) rotate(-2deg); opacity:0; } }
    dialog h3 { font-size:1.9rem; margin-bottom:.4rem; text-shadow:3px 3px 0 var(--coral); }
    dialog .row { display:flex; gap:.7rem; justify-content:flex-end; margin-top:1.4rem; }
    .preview { margin-top:.5rem; border:var(--border); border-style:dashed; background:var(--soft); min-height:120px; display:grid; place-items:center; overflow:hidden; font-weight:700; color:#555; }
    .preview img { width:100%; max-height:220px; object-fit:cover; display:block; }
    input[type=file] { margin-top:.5rem; width:100%; font:500 .9rem 'DM Sans',sans-serif; }

    .toast { position:fixed; left:50%; bottom:24px; z-index:60; transform:translate(-50%,160%); transition:transform .25s ease;
             padding:.7rem 1.2rem; font-weight:700; color:#fff; border:var(--border); box-shadow:4px 4px 0 var(--ink); max-width:92vw; }
    .toast.ok { background:var(--green); } .toast.error { background:var(--coral); }
    .toast.show { transform:translate(-50%,0); }

    @media (max-width:768px) { header { padding:.7rem 1rem; } .wrap { padding:0 1rem; } }
  </style>
</head>
<body>

<header>
  <a class="logo display" href="admin.php">
    <span class="mark"><img src="img/png/logo.png" alt="VOIDWEAR"></span>
    VOIDWEAR <small>Admin</small>
  </a>
  <div class="head-actions">
    <a class="btn small" href="index.php" target="_blank" rel="noopener">Lihat toko</a>
    <a class="btn small danger" href="admin.php?logout=1">Keluar</a>
  </div>
</header>

<main class="wrap">

  <div class="stats">
    <div class="stat"><b class="display"><?= count($produkList) ?></b><small>PRODUK</small></div>
    <div class="stat"><b class="display"><?= count($pesananList) ?></b><small>PESANAN MASUK</small></div>
    <div class="stat"><b class="display">Rp <?= number_format($nilaiPesanan, 0, ',', '.') ?></b><small>NILAI PESANAN</small></div>
  </div>

  <nav class="tabs" aria-label="Menu admin">
    <a class="btn <?= $tab === 'produk' ? 'active' : '' ?>" href="admin.php?tab=produk">Katalog Produk</a>
    <a class="btn <?= $tab === 'pesanan' ? 'active' : '' ?>" href="admin.php?tab=pesanan">Pesanan (<?= count($pesananList) ?>)</a>
  </nav>

<?php if ($tab === 'produk'): ?>

  <div class="toolbar">
    <input type="text" id="cari" placeholder="Cari produk..." aria-label="Cari produk">
    <button type="button" class="btn primary" id="btnTambah">+ Tambah produk</button>
  </div>

  <div class="grid" id="grid">
    <?php if (empty($produkList)): ?>
      <div class="empty">Belum ada produk. Klik "Tambah produk" untuk mulai.</div>
    <?php endif; ?>
    <?php foreach ($produkList as $p): ?>
      <article class="card" data-nama="<?= e(strtolower($p['nama'])) ?>">
        <div class="thumb"><img src="img/<?= e($p['gambar']) ?>" alt="<?= e($p['nama']) ?>" loading="lazy"></div>
        <h3 class="display"><?= e($p['nama']) ?></h3>
        <div class="price">Rp <?= number_format($p['harga'], 0, ',', '.') ?></div>
        <div class="row">
          <button type="button" class="btn small blue btn-edit"
                  data-id="<?= (int)$p['id'] ?>"
                  data-nama="<?= e($p['nama']) ?>"
                  data-deskripsi="<?= e($p['deskripsi']) ?>"
                  data-harga="<?= (int)$p['harga'] ?>"
                  data-gambar="<?= e($p['gambar']) ?>">Edit</button>
          <form method="POST" data-confirm="Hapus produk &quot;<?= e($p['nama']) ?>&quot;? Item produk ini di keranjang juga ikut terhapus.">
            <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
            <input type="hidden" name="action" value="hapus_produk">
            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
            <button type="submit" class="btn small danger">Hapus</button>
          </form>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
  <p class="empty" id="noResult" hidden style="margin-top:1.4rem">Produk tidak ditemukan.</p>

<?php else: ?>

  <?php if (empty($pesananList)): ?>
    <div class="empty">Belum ada pesanan.</div>
  <?php else: ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>ID</th><th>Nama</th><th>Alamat</th><th>Telepon</th><th>Produk</th><th>Ukuran</th><th>Jumlah</th><th>Total</th><th>Bukti</th><th>Catatan</th><th>Waktu</th><th>Aksi</th></tr>
      </thead>
      <tbody>
      <?php foreach ($pesananList as $r): ?>
        <tr>
          <td>#<?= (int)$r['id'] ?></td>
          <td><?= e($r['nama_customer'] ?? '') ?></td>
          <td><?= nl2br(e($r['alamat'] ?? '')) ?></td>
          <td><?= e($r['telepon'] ?? '') ?></td>
          <td><?= e($r['nama_produk'] ?? '(produk dihapus)') ?></td>
          <td><?= e($r['size'] ?? '') ?></td>
          <td><?= (int)($r['jumlah'] ?? 0) ?></td>
          <td>Rp <?= number_format((float)($r['total'] ?? 0), 0, ',', '.') ?></td>
          <td>
            <?php if (!empty($r['bukti_transfer'])): ?>
              <a href="uploads/<?= e($r['bukti_transfer']) ?>" target="_blank" rel="noopener">Lihat</a>
            <?php else: ?>Tidak ada<?php endif; ?>
          </td>
          <td><?= nl2br(e($r['catatan'] ?? '')) ?></td>
          <td><?= e($r['created_at'] ?? $r['tanggal_order'] ?? '-') ?></td>
          <td>
            <form method="POST" data-confirm="Tandai pesanan #<?= (int)$r['id'] ?> sebagai selesai? Pesanan akan dihapus dari daftar.">
              <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
              <input type="hidden" name="action" value="selesai_pesanan">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button type="submit" class="btn small blue">Selesai</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

<?php endif; ?>
</main>

<!-- Dialog tambah / edit produk -->
<dialog id="produkDialog">
  <form method="POST" enctype="multipart/form-data" id="produkForm">
    <h3 class="display" id="dlgTitle">Tambah produk</h3>
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
    <input type="hidden" name="action" value="simpan_produk">
    <input type="hidden" name="id" id="fId" value="0">

    <label class="f" for="fNama">Nama produk</label>
    <input type="text" id="fNama" name="nama" required maxlength="100">

    <label class="f" for="fHarga">Harga (Rp)</label>
    <input type="number" id="fHarga" name="harga" min="1" step="1" required>

    <label class="f" for="fDeskripsi">Deskripsi</label>
    <textarea id="fDeskripsi" name="deskripsi" rows="4"></textarea>

    <label class="f" for="fGambar">Gambar produk <small id="gambarHint" style="font-weight:500;color:#555">(wajib)</small></label>
    <input type="file" id="fGambar" name="gambar" accept="image/jpeg,image/png,image/webp,image/gif">
    <div class="preview" id="preview">Belum ada gambar</div>

    <div class="row">
      <button type="button" class="btn small" id="btnBatal">Batal</button>
      <button type="submit" class="btn small primary">Simpan</button>
    </div>
  </form>
</dialog>

<!-- Dialog konfirmasi -->
<dialog id="confirmDialog">
  <h3 class="display">Yakin?</h3>
  <p id="confirmText" style="line-height:1.5"></p>
  <div class="row">
    <button type="button" class="btn small" id="confirmNo">Batal</button>
    <button type="button" class="btn small primary" id="confirmYes">Lanjut</button>
  </div>
</dialog>

<div class="toast <?= $flash ? e($flash[0]) : '' ?>" id="toast" role="status" aria-live="polite"><?= $flash ? e($flash[1]) : '' ?></div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // Toast dari server
  const toast = document.getElementById('toast');
  if (toast.textContent.trim()) {
    setTimeout(() => toast.classList.add('show'), 100);
    setTimeout(() => toast.classList.remove('show'), 3800);
  }

  // Dialog konfirmasi (hapus produk / selesaikan pesanan)
  const cd = document.getElementById('confirmDialog');
  let pending = null;
  document.querySelectorAll('form[data-confirm]').forEach(f => {
    f.addEventListener('submit', e => {
      if (f.dataset.ok === '1') return;
      e.preventDefault();
      pending = f;
      document.getElementById('confirmText').textContent = f.dataset.confirm;
      cd.showModal();
    });
  });
  document.getElementById('confirmNo').onclick = () => { pending = null; cd.close(); };
  document.getElementById('confirmYes').onclick = () => { cd.close(); if (pending) { pending.dataset.ok = '1'; pending.submit(); } };

  // Hanya di tab produk
  const dlg = document.getElementById('produkDialog');
  const btnTambah = document.getElementById('btnTambah');
  if (!btnTambah) return;

  const $ = id => document.getElementById(id);
  const preview = $('preview');
  const setPreview = src => {
    preview.innerHTML = '';
    if (!src) { preview.textContent = 'Belum ada gambar'; return; }
    const img = document.createElement('img'); img.src = src; img.alt = 'Pratinjau';
    preview.appendChild(img);
  };

  btnTambah.addEventListener('click', () => {
    $('dlgTitle').textContent = 'Tambah produk';
    $('produkForm').reset();
    $('fId').value = 0;
    $('fGambar').required = true;
    $('gambarHint').textContent = '(wajib)';
    setPreview('');
    dlg.showModal();
  });

  document.querySelectorAll('.btn-edit').forEach(b => {
    b.addEventListener('click', () => {
      $('dlgTitle').textContent = 'Edit produk';
      $('produkForm').reset();
      $('fId').value = b.dataset.id;
      $('fNama').value = b.dataset.nama;
      $('fHarga').value = b.dataset.harga;
      $('fDeskripsi').value = b.dataset.deskripsi;
      $('fGambar').required = false;
      $('gambarHint').textContent = '(kosongkan jika tidak diganti)';
      setPreview('img/' + b.dataset.gambar);
      dlg.showModal();
    });
  });

  $('fGambar').addEventListener('change', e => {
    const f = e.target.files[0];
    if (f) setPreview(URL.createObjectURL(f));
  });
  $('btnBatal').addEventListener('click', () => dlg.close());

  // Cari produk langsung
  const cari = $('cari'), noResult = $('noResult');
  cari.addEventListener('input', () => {
    const q = cari.value.trim().toLowerCase();
    let n = 0;
    document.querySelectorAll('#grid .card').forEach(c => {
      const ok = c.dataset.nama.includes(q);
      c.style.display = ok ? '' : 'none';
      if (ok) n++;
    });
    noResult.hidden = n > 0;
  });
});
</script>

</body>
</html>
<?php $conn->close(); ?>