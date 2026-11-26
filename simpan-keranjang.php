<?php
// Koneksi ke database
$conn = new mysqli("localhost", "root", "", "produk");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Hapus item dari keranjang jika ada parameter 'hapus'
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    $stmt = $conn->prepare("DELETE FROM keranjang WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: simpan-keranjang.php");
    exit;
}

// Menyelesaikan pesanan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selesai_id'])) {
    $selesaiId = (int)$_POST['selesai_id'];
    $stmtSelesai = $conn->prepare("DELETE FROM pesanan WHERE id = ?");
    $stmtSelesai->bind_param("i", $selesaiId);
    $stmtSelesai->execute();
    $stmtSelesai->close();
    header("Location: simpan-keranjang.php");
    exit;
}

// Ambil isi keranjang beserta data produk
$sql = "SELECT k.id, k.id_produk, k.nama_produk, k.size, k.jumlah, p.harga, p.gambar 
        FROM keranjang k 
        JOIN produk p ON k.id_produk = p.id";
$result = $conn->query($sql);
$jumlahItem = $result ? $result->num_rows : 0;

// Ambil daftar pesanan
$sqlPesanan = "SELECT 
                 p.id, p.nama_customer, p.alamat, p.telepon,
                 pr.nama AS nama_produk,
                 p.size, p.jumlah, p.total, p.bukti_transfer, p.catatan, p.created_at
               FROM pesanan p
               LEFT JOIN produk pr ON p.id_produk = pr.id
               ORDER BY p.created_at DESC";
$resPesanan = $conn->query($sqlPesanan);
$jumlahPesanan = $resPesanan ? $resPesanan->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Keranjang & Pesanan - VOIDWEAR</title>
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
      --blue: #2f7df0;
      --shadow: 6px 6px 0 var(--ink);
      --shadow-sm: 3px 3px 0 var(--ink);
      --border: 3px solid var(--ink);
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    html { scroll-behavior: smooth; scroll-padding-top: 90px; }

    body {
      font-family: 'DM Sans', sans-serif;
      color: var(--ink);
      background-color: var(--paper);
      background-image: radial-gradient(rgba(0,0,0,.13) 1.2px, transparent 1.3px);
      background-size: 12px 12px;
      padding-bottom: 90px;
    }
    :focus-visible { outline: 3px dashed var(--coral); outline-offset: 3px; }

    .display {
      font-family: 'Anton', sans-serif;
      font-style: italic;
      text-transform: uppercase;
      letter-spacing: .5px;
      line-height: 1;
    }
    .wrap { max-width: 1100px; margin: 0 auto; padding: 0 1.5rem; }

    /* ---------- Header ---------- */
    header {
      position: sticky; top: 0; z-index: 20;
      display: flex; align-items: center; justify-content: space-between;
      padding: .8rem 1.5rem;
      background: var(--paper);
      border-bottom: var(--border);
    }
    .logo {
      display: flex; align-items: center; gap: .6rem;
      text-decoration: none; color: var(--ink); font-size: 1.6rem;
    }
    .logo .mark {
      display: grid; place-items: center; width: 48px; height: 48px;
      background: var(--ink); border-radius: 50%; overflow: hidden;
      box-shadow: 3px 3px 0 var(--coral);
    }
    .logo img { width: 100%; height: 100%; object-fit: contain; }

    /* ---------- Tombol ---------- */
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
    .btn.green { background: var(--green); color: #fff; }
    .btn.blue { background: var(--blue); color: #fff; }
    .btn.danger { background: var(--paper); color: var(--coral); }
    .btn.danger:hover { background: var(--coral); color: #fff; }
    .btn.small { padding: .4rem .85rem; font-size: .82rem; }
    .btn.dirty { background: var(--coral); color: #fff; animation: pulse 1s ease infinite; }
    @keyframes pulse { 50% { transform: scale(1.08); } }

    /* ---------- Judul ---------- */
    .page-title {
      display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;
      margin: 2.5rem 0 1.5rem;
    }
    .page-title h1 { font-size: clamp(2.4rem, 8vw, 4rem); text-shadow: 4px 4px 0 var(--coral); }
    .count-chip {
      background: var(--ink); color: #fff; padding: .35rem .9rem;
      border-radius: 999px; font-weight: 700; font-size: .9rem;
    }
    h2.section { font-size: clamp(2rem, 6vw, 3rem); margin: 3.5rem 0 1.5rem; text-shadow: 3px 3px 0 var(--coral); }

    /* ---------- Kartu keranjang ---------- */
    .cart-list { display: grid; gap: 1.4rem; }
    .cart-item {
      display: grid; grid-template-columns: 120px 1fr auto; gap: 1.2rem; align-items: center;
      background: var(--paper); border: var(--border); box-shadow: var(--shadow);
      padding: 1rem;
      transition: transform .15s ease, box-shadow .15s ease, opacity .3s ease;
    }
    .cart-item:hover { transform: translate(-2px,-2px); box-shadow: 8px 8px 0 var(--ink); }
    .cart-item.removing { opacity: 0; transform: translateX(40px) rotate(2deg); }

    .cart-img { border: var(--border); background: var(--soft); overflow: hidden; }
    .cart-img img { display: block; width: 100%; height: 120px; object-fit: cover; }

    .cart-name { font-size: 1.5rem; margin-bottom: .3rem; }
    .cart-price { font-weight: 700; color: #555; margin-bottom: .8rem; }

    .edit-form { display: flex; gap: .6rem; flex-wrap: wrap; align-items: center; }
    select, input[type=number] {
      font: 700 1rem 'DM Sans', sans-serif;
      padding: .45rem .6rem;
      background: var(--paper); color: var(--ink);
      border: var(--border); border-radius: 10px;
    }
    select:focus, input[type=number]:focus { outline: none; box-shadow: 3px 3px 0 var(--coral); }

    .stepper { display: inline-flex; align-items: stretch; border: var(--border); border-radius: 10px; overflow: hidden; background: var(--paper); }
    .stepper button {
      width: 36px; font: 700 1.2rem 'DM Sans', sans-serif; border: 0; background: var(--soft);
      cursor: pointer; transition: background .12s ease;
    }
    .stepper button:hover { background: var(--coral); color: #fff; }
    .stepper button:active { transform: scale(.9); }
    .stepper input { width: 52px; border: 0; border-radius: 0; text-align: center; border-left: var(--border); border-right: var(--border); -moz-appearance: textfield; }
    .stepper input::-webkit-outer-spin-button, .stepper input::-webkit-inner-spin-button { -webkit-appearance: none; }
    .stepper input:focus { box-shadow: none; }

    .cart-side { text-align: right; display: grid; gap: .6rem; justify-items: end; }
    .subtotal-label { font-size: .75rem; font-weight: 700; color: #555; }
    .subtotal { font-size: 1.8rem; }
    .actions { display: flex; gap: .5rem; flex-wrap: wrap; justify-content: flex-end; }
    form.inline { display: inline-block; }

    /* ---------- Total ---------- */
    .total-card {
      margin-top: 1.6rem; margin-left: auto; max-width: 420px;
      background: var(--ink); color: #fff;
      border: var(--border); box-shadow: 6px 6px 0 var(--coral);
      padding: 1.2rem 1.4rem; transform: rotate(-.8deg);
      display: flex; justify-content: space-between; align-items: baseline; gap: 1rem;
    }
    .total-card small { font-weight: 700; letter-spacing: 1px; }
    .total-card b { font-size: 2.2rem; }

    /* ---------- Kosong ---------- */
    .empty {
      text-align: center; padding: 2.5rem 1.5rem;
      background: var(--paper); border: var(--border); border-style: dashed;
      font-weight: 700; font-size: 1.1rem;
    }
    .empty .display { font-size: 2rem; display: block; margin-bottom: .6rem; }
    .empty .btn { margin-top: 1rem; }

    /* ---------- Tabel pesanan ---------- */
    .table-wrap { overflow-x: auto; background: var(--paper); border: var(--border); box-shadow: var(--shadow); }
    table { width: 100%; border-collapse: collapse; min-width: 900px; }
    th, td { padding: .8rem; text-align: left; vertical-align: top; border-bottom: 2px solid var(--ink); font-size: .92rem; }
    th { background: var(--ink); color: #fff; font-family: 'Anton', sans-serif; font-style: italic; font-weight: 400; letter-spacing: .5px; text-transform: uppercase; white-space: nowrap; }
    tbody tr { transition: background .12s ease; }
    tbody tr:hover { background: #fff3f2; }
    td a { color: var(--coral); font-weight: 700; }
    .table-wrap::-webkit-scrollbar { height: 12px; }
    .table-wrap::-webkit-scrollbar-track { background: var(--soft); }
    .table-wrap::-webkit-scrollbar-thumb { background: var(--coral); border: 2px solid var(--ink); }

    .back { margin: 3rem 0 1rem; }

    /* ---------- Dialog konfirmasi ---------- */
    dialog {
      margin: auto; padding: 1.6rem; width: min(90vw, 380px);
      border: var(--border); box-shadow: 8px 8px 0 var(--coral);
      background: var(--paper);
    }
    dialog::backdrop { background: rgba(0,0,0,.55); }
    dialog h3 { font-size: 1.8rem; margin-bottom: .6rem; }
    dialog p { margin-bottom: 1.2rem; line-height: 1.5; }
    dialog .row { display: flex; gap: .7rem; justify-content: flex-end; }
    dialog[open] { animation: pop .2s ease; }
    @keyframes pop { from { transform: scale(.85) rotate(-2deg); opacity: 0; } }

    /* ---------- Navigasi bawah (mobile) ---------- */
    .bottom-nav { display: none; }

    @media (max-width: 768px) {
      header { padding: .7rem 1rem; }
      header .btn.hide-mobile { display: none; }
      .wrap { padding: 0 1rem; }
      .cart-item { grid-template-columns: 90px 1fr; }
      .cart-img img { height: 90px; }
      .cart-side { grid-column: 1 / -1; grid-template-columns: 1fr auto; align-items: center; justify-items: start; text-align: left; }
      .actions { justify-content: flex-start; }
      .total-card { max-width: none; }

      .bottom-nav {
        position: fixed; left: 0; right: 0; bottom: 0; z-index: 30;
        display: flex; justify-content: space-around; align-items: flex-end;
        background: var(--paper); border-top: var(--border);
        padding: .5rem .5rem .6rem; border-radius: 22px 22px 0 0;
      }
      .bottom-nav a {
        display: flex; flex-direction: column; align-items: center; gap: 2px;
        color: #777; text-decoration: none; font-size: .78rem; font-weight: 700; padding: .3rem .8rem;
      }
      .bottom-nav a svg { width: 24px; height: 24px; }
      .bottom-nav a.active { color: var(--ink); }
      .bottom-nav a:active { transform: scale(.9); }
      .bottom-nav a.center { position: relative; top: -22px; color: var(--ink); }
      .bottom-nav a.center .circle {
        display: grid; place-items: center; width: 62px; height: 62px;
        background: var(--coral); color: #fff;
        border: var(--border); border-radius: 50%; box-shadow: 3px 3px 0 var(--ink);
      }
    }

    @media (prefers-reduced-motion: reduce) {
      * { animation: none !important; transition: none !important; scroll-behavior: auto !important; }
    }
  </style>
</head>
<body>

<header>
  <a class="logo display" href="index.php">
    <span class="mark"><img src="img/png/logo.png" alt="VOIDWEAR"></span>
    VOIDWEAR
  </a>
  <a class="btn hide-mobile" href="index.php">&larr; Kembali ke Beranda</a>
</header>

<main class="wrap">

  <!-- ========== KERANJANG ========== -->
  <div class="page-title">
    <h1 class="display">Keranjang Belanja</h1>
    <span class="count-chip"><?= $jumlahItem ?> item</span>
  </div>

  <?php if ($result && $result->num_rows > 0): ?>
  <div class="cart-list">
    <?php
      $totalHarga = 0;
      while ($row = $result->fetch_assoc()):
          $subtotal = $row['harga'] * $row['jumlah'];
          $totalHarga += $subtotal;
    ?>
    <article class="cart-item">
      <div class="cart-img">
        <img src="img/<?= htmlspecialchars($row['gambar']) ?>" alt="<?= htmlspecialchars($row['nama_produk']) ?>">
      </div>

      <div>
        <h3 class="cart-name display"><?= htmlspecialchars($row['nama_produk']) ?></h3>
        <div class="cart-price">Rp <?= number_format($row['harga'], 0, ',', '.') ?> / pcs</div>

        <form method="POST" class="edit-form" action="ubah-keranjang.php">
          <input type="hidden" name="id" value="<?= $row['id'] ?>">
          <select name="size" required aria-label="Ukuran">
            <?php foreach (['S','M','L','XL','XXL'] as $s): ?>
              <option value="<?= $s ?>" <?= $s === $row['size'] ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
          <div class="stepper">
            <button type="button" data-step="-1" aria-label="Kurangi">&minus;</button>
            <input type="number" name="jumlah" value="<?= $row['jumlah'] ?>" min="1" required aria-label="Jumlah">
            <button type="button" data-step="1" aria-label="Tambah">+</button>
          </div>
          <button type="submit" class="btn small">Ubah</button>
        </form>
      </div>

      <div class="cart-side">
        <div>
          <div class="subtotal-label">SUBTOTAL</div>
          <div class="subtotal display">Rp <?= number_format($subtotal, 0, ',', '.') ?></div>
        </div>
        <div class="actions">
          <form method="GET" class="inline" action="simpan-keranjang.php" data-confirm="Hapus item ini dari keranjang?">
            <input type="hidden" name="hapus" value="<?= $row['id'] ?>">
            <button type="submit" class="btn small danger">Hapus</button>
          </form>
          <form method="GET" action="checkout.php" class="inline">
            <input type="hidden" name="id_produk" value="<?= $row['id_produk'] ?>">
            <input type="hidden" name="size" value="<?= htmlspecialchars($row['size']) ?>">
            <input type="hidden" name="jumlah" value="<?= $row['jumlah'] ?>">
            <button type="submit" class="btn small green">Checkout</button>
          </form>
        </div>
      </div>
    </article>
    <?php endwhile; ?>
  </div>

  <div class="total-card">
    <small>TOTAL HARGA</small>
    <b class="display" id="totalNumber" data-total="<?= $totalHarga ?>">Rp <?= number_format($totalHarga, 0, ',', '.') ?></b>
  </div>

  <?php else: ?>
  <div class="empty">
    <span class="display">Keranjang masih kosong</span>
    Yuk belanja dulu!
    <br><a class="btn primary" href="index.php">Mulai belanja</a>
  </div>
  <?php endif; ?>

  <!-- ========== PESANAN ========== -->
  <h2 class="section display">Daftar Pesanan</h2>

  <?php if ($resPesanan && $resPesanan->num_rows > 0): ?>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>ID</th><th>Nama</th><th>Alamat</th><th>Telepon</th><th>Produk</th>
          <th>Ukuran</th><th>Jumlah</th><th>Total</th><th>Bukti</th><th>Catatan</th><th>Waktu</th><th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $resPesanan->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id'] ?></td>
          <td><?= htmlspecialchars($row['nama_customer']) ?></td>
          <td><?= nl2br(htmlspecialchars($row['alamat'])) ?></td>
          <td><?= htmlspecialchars($row['telepon']) ?></td>
          <td><?= htmlspecialchars($row['nama_produk']) ?></td>
          <td><?= htmlspecialchars($row['size']) ?></td>
          <td><?= $row['jumlah'] ?></td>
          <td>Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
          <td>
            <?php if (!empty($row['bukti_transfer'])): ?>
              <a href="uploads/<?= htmlspecialchars($row['bukti_transfer']) ?>" target="_blank">Lihat</a>
            <?php else: ?>
              Tidak ada
            <?php endif; ?>
          </td>
          <td><?= nl2br(htmlspecialchars($row['catatan'])) ?></td>
          <td><?= $row['created_at'] ?></td>
          <td>
            <form method="POST" data-confirm="Tandai pesanan ini sebagai selesai?">
              <input type="hidden" name="selesai_id" value="<?= $row['id'] ?>">
              <button type="submit" class="btn small blue">Selesai</button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
  <div class="empty"><span class="display">Belum ada pesanan</span></div>
  <?php endif; ?>

  <div class="back"><a class="btn" href="index.php">&larr; Kembali ke Beranda</a></div>
</main>

<!-- Dialog konfirmasi -->
<dialog id="confirmDialog">
  <h3 class="display" id="confirmTitle">Yakin?</h3>
  <p id="confirmText"></p>
  <div class="row">
    <button type="button" class="btn small" id="confirmNo">Batal</button>
    <button type="button" class="btn small primary" id="confirmYes">Lanjut</button>
  </div>
</dialog>

<!-- Navigasi bawah (mobile) -->
<nav class="bottom-nav" aria-label="Navigasi utama">
  <a href="index.php">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/></svg>
    Beranda
  </a>
  <a href="simpan-keranjang.php" class="center">
    <span class="circle">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg>
    </span>
    Keranjang
  </a>
  <a href="#pesanan" onclick="document.querySelector('h2.section').scrollIntoView(); return false;" class="active">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
    Pesanan
  </a>
</nav>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // 1. Tombol +/- jumlah, dan tombol "Ubah" menyala kalau ada perubahan
  document.querySelectorAll('.edit-form').forEach(form => {
    const qty = form.querySelector('input[name=jumlah]');
    const size = form.querySelector('select[name=size]');
    const btn = form.querySelector('button[type=submit]');
    const startQty = qty.value, startSize = size.value;

    const check = () => btn.classList.toggle('dirty', qty.value !== startQty || size.value !== startSize);

    form.querySelectorAll('[data-step]').forEach(b => {
      b.addEventListener('click', () => {
        const next = Math.max(1, (parseInt(qty.value, 10) || 1) + parseInt(b.dataset.step, 10));
        qty.value = next;
        check();
      });
    });
    qty.addEventListener('input', check);
    size.addEventListener('change', check);
  });

  // 2. Dialog konfirmasi bergaya komik (pengganti confirm() bawaan browser)
  const dialog = document.getElementById('confirmDialog');
  const text = document.getElementById('confirmText');
  let pending = null;

  document.querySelectorAll('form[data-confirm]').forEach(form => {
    form.addEventListener('submit', e => {
      if (form.dataset.ok === '1') return;
      e.preventDefault();
      pending = form;
      text.textContent = form.dataset.confirm;
      if (dialog.showModal) dialog.showModal();
      else if (confirm(form.dataset.confirm)) { form.dataset.ok = '1'; form.submit(); }
    });
  });
  document.getElementById('confirmNo').addEventListener('click', () => { pending = null; dialog.close(); });
  document.getElementById('confirmYes').addEventListener('click', () => {
    dialog.close();
    if (!pending) return;
    const item = pending.closest('.cart-item');
    const go = () => { pending.dataset.ok = '1'; pending.submit(); };
    if (item) { item.classList.add('removing'); setTimeout(go, 280); } else go();
  });

  // 3. Total menghitung naik saat halaman dibuka
  const total = document.getElementById('totalNumber');
  if (total && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const target = parseInt(total.dataset.total, 10) || 0;
    const fmt = n => 'Rp ' + n.toLocaleString('id-ID');
    const start = performance.now(), dur = 800;
    (function tick(now) {
      const p = Math.min((now - start) / dur, 1);
      total.textContent = fmt(Math.round(target * (1 - Math.pow(1 - p, 3))));
      if (p < 1) requestAnimationFrame(tick);
    })(start);
  }
});
</script>

</body>
</html>
<?php $conn->close(); ?>