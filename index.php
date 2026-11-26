<?php
// 1. Koneksi Database (satu koneksi saja untuk semua query)
$conn = new mysqli("localhost", "root", "", "produk");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// 2. Produk Terbaru (6 produk dengan ID tertinggi)
$products_terbaru = [];
$res = $conn->query("SELECT id, nama, deskripsi, harga, gambar FROM produk ORDER BY id DESC LIMIT 6");
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) $products_terbaru[] = $row;
}

// 3. Produk Terlaris (8 produk acak sebagai simulasi)
$products_terlaris = [];
$res = $conn->query("SELECT id, nama, deskripsi, harga, gambar FROM produk ORDER BY RAND() LIMIT 8");
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) $products_terlaris[] = $row;
}

// 4. Total produk & jumlah item keranjang (untuk badge)
$total_produk = 0;
$res = $conn->query("SELECT COUNT(*) AS total FROM produk");
if ($res) $total_produk = (int) $res->fetch_assoc()['total'];

$jumlah_keranjang = 0;
try {
    $res = $conn->query("SELECT id FROM keranjang");
    if ($res) $jumlah_keranjang = $res->num_rows;
} catch (Throwable $e) {
    $jumlah_keranjang = 0;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>VOIDWEAR</title>
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
      text-decoration: none; color: var(--ink);
      font-size: 1.6rem;
    }
    .logo span.mark {
      display: grid; place-items: center;
      width: 48px; height: 48px;
      background: var(--ink); border-radius: 50%;
      overflow: hidden; box-shadow: 3px 3px 0 var(--coral);
    }
    .logo img { width: 100%; height: 100%; object-fit: contain; }

    nav { display: flex; gap: .7rem; align-items: center; }
    .btn {
      position: relative;
      display: inline-flex; align-items: center; gap: .5rem;
      padding: .55rem 1.1rem;
      font: 700 .9rem 'DM Sans', sans-serif;
      color: var(--ink); text-decoration: none;
      background: var(--paper);
      border: var(--border); border-radius: 999px;
      box-shadow: var(--shadow-sm);
      cursor: pointer;
      transition: transform .12s ease, box-shadow .12s ease, background .12s ease;
    }
    .btn:hover { background: var(--soft); transform: translate(-1px,-1px); box-shadow: 4px 4px 0 var(--ink); }
    .btn:active { transform: translate(3px,3px); box-shadow: 0 0 0 var(--ink); }
    .btn.primary { background: var(--coral); color: #fff; }
    .btn.primary:hover { background: #ff5a55; }

    .badge {
      min-width: 22px; height: 22px; padding: 0 6px;
      display: inline-grid; place-items: center;
      background: var(--ink); color: #fff;
      border-radius: 999px; font-size: .75rem;
    }
    .badge.bump { animation: bump .4s ease; }
    @keyframes bump { 40% { transform: scale(1.6) rotate(-10deg); } }

    /* ---------- Hero ---------- */
    .wrap { max-width: 1200px; margin: 0 auto; padding: 0 1.5rem; }

    .hero {
      position: relative;
      margin: 2.5rem auto 1rem;
      background: var(--paper);
      border: var(--border);
      box-shadow: 8px 8px 0 var(--ink);
      overflow: hidden;
      padding: 2.5rem 2rem 2rem;
    }
    .hero::before {
      content: ""; position: absolute; inset: -50%;
      background: repeating-conic-gradient(from 0deg, #fff 0 6deg, #ececec 6deg 12deg);
      z-index: 0;
      animation: spin 90s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .hero > * { position: relative; z-index: 1; }

    .hero-label { font-weight: 700; letter-spacing: 1px; font-size: .85rem; }
    .hero h1 {
      font-size: clamp(3.2rem, 12vw, 7.5rem);
      margin: .4rem 0 1rem;
      color: var(--ink);
      text-shadow: 5px 5px 0 var(--coral);
    }
    .hero p { max-width: 460px; line-height: 1.6; margin-bottom: 1.4rem; font-weight: 500; }

    .bubble {
      position: absolute !important; top: 1.2rem; right: 1.2rem;
      background: #fff; border: var(--border); border-radius: 18px;
      padding: .6rem 1.1rem; font-size: 1.6rem;
      box-shadow: var(--shadow-sm);
      transform: rotate(5deg);
      animation: wobble 3s ease-in-out infinite;
      cursor: pointer; user-select: none;
    }
    .bubble::after {
      content: ""; position: absolute; left: 18px; bottom: -16px;
      border: 10px solid transparent; border-top-color: var(--ink); border-bottom: 0;
    }
    @keyframes wobble { 0%,100% { transform: rotate(5deg); } 50% { transform: rotate(-4deg) scale(1.05); } }

    .stats { display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 1.6rem; }
    .stat {
      background: var(--paper); border: var(--border); box-shadow: var(--shadow-sm);
      padding: .6rem 1rem;
    }
    .stat b { display: block; font-size: 2rem; }
    .stat small { font-weight: 700; font-size: .75rem; }

    /* ---------- Search & Section ---------- */
    .search {
      display: flex; gap: .7rem; margin: 2rem 0 .5rem; flex-wrap: wrap;
    }
    .search input {
      flex: 1 1 260px;
      padding: .8rem 1.1rem;
      font: 500 1rem 'DM Sans', sans-serif;
      border: var(--border); border-radius: 999px;
      box-shadow: var(--shadow-sm);
      background: var(--paper);
    }
    .search input:focus { outline: none; box-shadow: 5px 5px 0 var(--coral); }

    .section-head {
      display: flex; align-items: baseline; justify-content: space-between;
      margin: 2.5rem 0 1rem;
    }
    .section-head h2 { font-size: 2.2rem; }
    .section-head span { color: var(--coral); font-weight: 700; font-size: .9rem; }

    /* ---------- Produk ---------- */
    .products {
      display: flex; gap: 1.2rem; overflow-x: auto;
      padding: .5rem .5rem 1.5rem .2rem;
      scroll-snap-type: x mandatory;
    }
    .products-vertical {
      display: grid; gap: 1.4rem;
      grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
      padding: .5rem .5rem 1rem .2rem;
    }
    .products::-webkit-scrollbar { height: 12px; }
    .products::-webkit-scrollbar-track { background: var(--soft); border: 2px solid var(--ink); }
    .products::-webkit-scrollbar-thumb { background: var(--coral); border: 2px solid var(--ink); }

    .product {
      min-width: 270px; flex: 0 0 auto; scroll-snap-align: start;
      background: var(--paper);
      border: var(--border);
      box-shadow: var(--shadow);
      transition: transform .12s ease, box-shadow .12s ease;
    }
    .products-vertical .product { min-width: 0; }
    .product a { display: block; color: inherit; text-decoration: none; padding: .8rem; }
    .product:hover { transform: translate(-3px,-3px) rotate(-.6deg); box-shadow: 9px 9px 0 var(--ink); }
    .product:active { transform: translate(5px,5px); box-shadow: 1px 1px 0 var(--ink); }

    .thumb { position: relative; border: var(--border); overflow: hidden; background: var(--soft); }
    .thumb img {
      display: block; width: 100%; height: 260px; object-fit: cover;
      transition: transform .3s ease;
    }
    .product:hover .thumb img { transform: scale(1.07); }
    .tag {
      position: absolute; top: 8px; left: 8px;
      background: var(--coral); color: #fff; border: 2px solid var(--ink);
      padding: .15rem .55rem; font-size: .95rem; transform: rotate(-4deg);
    }

    .product-name { margin-top: .9rem; font-size: 1.3rem; }
    .product-row { display: flex; align-items: center; justify-content: space-between; margin-top: .6rem; gap: .5rem; }
    .product-price { font-weight: 700; font-size: 1.05rem; }
    .go {
      width: 38px; height: 38px; display: grid; place-items: center;
      background: var(--coral); color: #fff; border: var(--border); border-radius: 50%;
      font-weight: 700; transition: transform .15s ease;
    }
    .product:hover .go { transform: rotate(90deg); }

    .empty {
      grid-column: 1 / -1; padding: 1.2rem; text-align: center; font-weight: 700;
      border: var(--border); border-style: dashed; background: var(--paper);
    }

    /* Reveal saat scroll */
    .fade-in { opacity: 0; transform: translateY(24px); transition: opacity .5s ease, transform .5s ease, box-shadow .12s ease; }
    .fade-in.show { opacity: 1; transform: none; }
    .product.fade-in.show:hover { transform: translate(-3px,-3px) rotate(-.6deg); }
    .product.fade-in.show:active { transform: translate(5px,5px); }

    /* ---------- Tentang ---------- */
    .about-section {
      display: flex; flex-wrap: wrap; gap: 2rem; align-items: center;
      margin: 4rem auto 2rem;
    }
    .about-image {
      flex: 1 1 280px; padding: 2rem;
      background: var(--ink); border: var(--border);
      box-shadow: 8px 8px 0 var(--coral);
      transform: rotate(-1.5deg);
    }
    .about-image img { width: 100%; display: block; }
    .about-text {
      flex: 1 1 380px; background: var(--paper);
      border: var(--border); box-shadow: var(--shadow); padding: 1.8rem;
      line-height: 1.7;
    }
    .about-text h2 { font-size: 2.2rem; margin-bottom: 1rem; text-shadow: 3px 3px 0 var(--coral); }

    footer {
      margin-top: 3rem; padding: 1.4rem; text-align: center;
      background: var(--ink); color: #fff; font-weight: 700;
    }

    /* ---------- Bottom nav (mobile) ---------- */
    .bottom-nav { display: none; }

    @media (max-width: 768px) {
      header { padding: .7rem 1rem; }
      header nav { display: none; }
      .hero { padding: 2rem 1.2rem 1.5rem; }
      .bubble { font-size: 1.2rem; }
      .thumb img { height: 220px; }
      .products-vertical { grid-template-columns: repeat(2, 1fr); gap: .9rem; }
      .products-vertical .product a { padding: .5rem; }
      .products-vertical .thumb img { height: 150px; }
      .products-vertical .product-name { font-size: 1rem; }
      .products-vertical .go { display: none; }

      .bottom-nav {
        position: fixed; left: 0; right: 0; bottom: 0; z-index: 30;
        display: flex; justify-content: space-around; align-items: flex-end;
        background: var(--paper); border-top: var(--border);
        padding: .5rem .5rem .6rem; border-radius: 22px 22px 0 0;
      }
      .bottom-nav a {
        display: flex; flex-direction: column; align-items: center; gap: 2px;
        color: #777; text-decoration: none; font-size: .78rem; font-weight: 700;
        padding: .3rem .8rem; transition: transform .12s ease;
      }
      .bottom-nav a svg { width: 24px; height: 24px; }
      .bottom-nav a.active { color: var(--ink); }
      .bottom-nav a:active { transform: scale(.9); }
      .bottom-nav a.center {
        position: relative; top: -22px; color: var(--ink);
      }
      .bottom-nav a.center .circle {
        display: grid; place-items: center; width: 62px; height: 62px;
        background: var(--coral); color: #fff;
        border: var(--border); border-radius: 50%; box-shadow: 3px 3px 0 var(--ink);
        position: relative;
      }
      .bottom-nav a.center .badge { position: absolute; top: -6px; right: -6px; }
    }

    @media (prefers-reduced-motion: reduce) {
      * { animation: none !important; transition: none !important; scroll-behavior: auto !important; }
      .fade-in { opacity: 1; transform: none; }
    }
  </style>
</head>
<body>

<header>
  <a class="logo display" href="#home">
    <span class="mark"><img src="img/png/logo.png" alt="VOIDWEAR"></span>
    VOIDWEAR
  </a>
  <nav>
    <a class="btn" href="#home">Home</a>
    <a class="btn" href="#tentang">Tentang</a>
    <a class="btn primary" href="simpan-keranjang.php">Keranjang <span class="badge" id="cartBadge"><?= $jumlah_keranjang ?></span></a>
  </nav>
</header>

<main class="wrap" id="home">

  <!-- Hero -->
  <section class="hero">
    <div class="bubble display" id="bubble">Hemat!</div>
    <div class="hero-label">KOLEKSI TERBARU</div>
    <h1 class="display">VOIDWEAR</h1>
    <p>Fashion dengan desain modern dan kualitas terbaik. Pilih gayamu, bayar, selesai.</p>
    <a class="btn primary" href="#terbaru">Lihat produk</a>

    <div class="stats">
      <div class="stat"><b class="display" data-count="<?= $total_produk ?>">0</b><small>PRODUK</small></div>
      <div class="stat"><b class="display" data-count="<?= count($products_terbaru) ?>">0</b><small>BARU MASUK</small></div>
      <div class="stat"><b class="display" id="cartStat" data-count="<?= $jumlah_keranjang ?>">0</b><small>DI KERANJANG</small></div>
    </div>
  </section>

  <!-- Pencarian -->
  <div class="search">
    <input type="search" id="searchInput" placeholder="Cari produk..." aria-label="Cari produk">
  </div>

  <!-- Produk Terbaru -->
  <div class="section-head" id="terbaru">
    <h2 class="display">Produk Terbaru</h2>
    <span>Geser &rarr;</span>
  </div>
  <div class="products">
    <?php if (!empty($products_terbaru)): ?>
      <?php foreach ($products_terbaru as $i => $product): ?>
        <div class="product fade-in" data-name="<?= htmlspecialchars(strtolower($product['nama'])) ?>">
          <a href="detail-produk.php?id=<?= htmlspecialchars($product['id']) ?>">
            <div class="thumb">
              <img src="img/<?= htmlspecialchars($product['gambar']) ?>" alt="<?= htmlspecialchars($product['nama']) ?>" loading="lazy">
              <?php if ($i === 0): ?><span class="tag display">New!</span><?php endif; ?>
            </div>
            <div class="product-name display"><?= htmlspecialchars($product['nama']) ?></div>
            <div class="product-row">
              <div class="product-price">Rp <?= number_format($product['harga'], 0, ',', '.') ?></div>
              <span class="go">&rarr;</span>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="empty">Belum ada produk terbaru yang tersedia.</p>
    <?php endif; ?>
  </div>

  <!-- Produk Terlaris -->
  <div class="section-head">
    <h2 class="display">Produk Terlaris</h2>
  </div>
  <div class="products-vertical">
    <?php if (!empty($products_terlaris)): ?>
      <?php foreach ($products_terlaris as $product): ?>
        <div class="product fade-in" data-name="<?= htmlspecialchars(strtolower($product['nama'])) ?>">
          <a href="detail-produk.php?id=<?= htmlspecialchars($product['id']) ?>">
            <div class="thumb">
              <img src="img/<?= htmlspecialchars($product['gambar']) ?>" alt="<?= htmlspecialchars($product['nama']) ?>" loading="lazy">
              <span class="tag display">Hot!</span>
            </div>
            <div class="product-name display"><?= htmlspecialchars($product['nama']) ?></div>
            <div class="product-row">
              <div class="product-price">Rp <?= number_format($product['harga'], 0, ',', '.') ?></div>
              <span class="go">&rarr;</span>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="empty">Belum ada produk terlaris yang tersedia.</p>
    <?php endif; ?>
  </div>
  <p class="empty" id="noResult" hidden>Produk tidak ditemukan. Coba kata kunci lain.</p>

  <!-- Tentang -->
  <section id="tentang" class="about-section">
    <div class="about-image">
      <img src="img/PNG/logo.png" alt="Tentang VOIDWEAR" />
    </div>
    <div class="about-text">
      <h2 class="display">Tentang VOIDWEAR</h2>
      <p>
        VOIDWEAR adalah toko online fashion yang menghadirkan produk dengan desain modern dan kualitas terbaik.
        Kami berkomitmen memberikan pengalaman belanja yang mudah dan menyenangkan bagi setiap pelanggan.
        Koleksi kami selalu diperbarui dengan tren terbaru untuk memenuhi kebutuhan gaya Anda.
        Setiap produk dipilih dengan cermat agar memenuhi standar kualitas dan kenyamanan.
        Kami bangga menjadi pilihan utama bagi pelanggan yang mengutamakan gaya dan kualitas.
      </p>
    </div>
  </section>
</main>

<footer>&copy; <?= date('Y') ?> VOIDWEAR</footer>

<!-- Navigasi bawah (mobile) -->
<nav class="bottom-nav" aria-label="Navigasi utama">
  <a href="#home" class="active" data-link="home">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/></svg>
    Beranda
  </a>
  <a href="#terbaru" data-link="terbaru">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2l-3 5v14h18V7l-3-5z"/><path d="M3 7h18"/><path d="M16 11a4 4 0 0 1-8 0"/></svg>
    Produk
  </a>
  <a href="simpan-keranjang.php" class="center">
    <span class="circle">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg>
      <span class="badge" id="cartBadgeMobile"><?= $jumlah_keranjang ?></span>
    </span>
    Keranjang
  </a>
  <a href="#tentang" data-link="tentang">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
    Tentang
  </a>
</nav>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // 1. Angka menghitung naik saat halaman dibuka
  document.querySelectorAll('[data-count]').forEach(el => {
    const target = parseInt(el.dataset.count, 10) || 0;
    if (reduce || target === 0) { el.textContent = target; return; }
    const start = performance.now(), dur = 900;
    (function tick(now) {
      const p = Math.min((now - start) / dur, 1);
      el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3)));
      if (p < 1) requestAnimationFrame(tick);
    })(start);
  });

  // 2. Kartu muncul saat di-scroll
  const cards = document.querySelectorAll('.fade-in');
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (e.isIntersecting) { e.target.classList.add('show'); io.unobserve(e.target); }
      });
    }, { threshold: 0.1 });
    cards.forEach((c, i) => { c.style.transitionDelay = (i % 4) * 60 + 'ms'; io.observe(c); });
  } else {
    cards.forEach(c => c.classList.add('show'));
  }

  // 3. Cari produk secara langsung
  const input = document.getElementById('searchInput');
  const noResult = document.getElementById('noResult');
  const products = document.querySelectorAll('.product');
  input.addEventListener('input', () => {
    const q = input.value.trim().toLowerCase();
    let visible = 0;
    products.forEach(p => {
      const match = p.dataset.name.includes(q);
      p.style.display = match ? '' : 'none';
      if (match) { visible++; p.classList.add('show'); }
    });
    noResult.hidden = visible > 0;
  });

  // 4. Balon "Hemat!" bisa diketuk
  const bubble = document.getElementById('bubble');
  const words = ['Hemat!', 'Keren!', 'Gaskeun!', 'Wow!'];
  let w = 0;
  bubble.addEventListener('click', () => {
    w = (w + 1) % words.length;
    bubble.textContent = words[w];
  });

  // 5. Badge keranjang bergetar saat halaman dibuka (jika ada isi)
  document.querySelectorAll('.badge').forEach(b => {
    if (parseInt(b.textContent, 10) > 0) b.classList.add('bump');
  });

  // 6. Status aktif navigasi bawah mengikuti scroll
  const links = document.querySelectorAll('.bottom-nav a[data-link]');
  const sections = { home: '#home', terbaru: '#terbaru', tentang: '#tentang' };
  window.addEventListener('scroll', () => {
    let current = 'home';
    Object.entries(sections).forEach(([key, sel]) => {
      const el = document.querySelector(sel);
      if (el && el.getBoundingClientRect().top < window.innerHeight * 0.4) current = key;
    });
    links.forEach(a => a.classList.toggle('active', a.dataset.link === current));
  }, { passive: true });
});
</script>

</body>
</html>