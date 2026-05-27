<?php if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__ . '/../../src/auth.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Cafe Lounge</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <style>
    :root{
      --ivory: #FAF7F2;
      --sage: #A3B18A;
      --latte: #8B6B4F;
      --sand: #DAD2C8;
      --espresso: #3A2D28;
      --glass-bg: rgba(255,255,255,0.55);
      --glass-blur: 8px;
      --radius-lg: 20px;
    }
    [data-theme="dark"]{
      --ivory: #1f1b18;
      --sage: #89a17a;
      --latte: #a7866b;
      --sand: #2a2623;
      --espresso: #f4efe9;
      --glass-bg: rgba(30,26,24,0.35);
    }
    html { scroll-behavior: smooth; }
    body{transition:background .25s,color .25s;display:flex;flex-direction:column;min-height:100vh}
    main{flex:1}
    .cl-header{background:var(--ivory);color:var(--espresso);}
    .cl-accent{color:var(--sage);} 
    .cl-btn{background:var(--latte);color:var(--ivory);box-shadow:0 6px 18px rgba(138,107,79,0.12);border-radius:14px}
    .cl-card{background:var(--glass-bg);backdrop-filter:blur(var(--glass-blur));border-radius:var(--radius-lg);box-shadow:0 10px 30px rgba(0,0,0,0.08)}
    .nav-link{color:var(--espresso);opacity:.92}
    .nav-link:hover{opacity:1;transform:translateY(-2px)}
    .profile-menu{min-width:200px;background:var(--ivory);box-shadow:0 12px 36px rgba(0,0,0,.12);border-radius:12px;padding:.5rem}
    .btn-icon{display:inline-flex;align-items:center;gap:.5rem;padding:.5rem 0.75rem;border-radius:12px}
    .badge{background:var(--sage);color:var(--ivory);padding:4px 8px;border-radius:999px;font-size:.75rem}
    .transition-smooth{transition:all .22s cubic-bezier(.2,.9,.2,1)}
    .glass-card{background:var(--glass-bg);backdrop-filter:blur(var(--glass-blur));border-radius:16px;padding:1rem}
    .card-hover{transition:transform .28s ease, box-shadow .28s ease}
    .card-hover:hover{transform:translateY(-8px);box-shadow:0 20px 40px rgba(0,0,0,0.12)}
    .rounded-xl-2{border-radius:20px}
    .big-hero{width:100%;height:520px;object-fit:cover;border-radius:20px}
    .bento-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}
    .bento-item{border-radius:16px;overflow:hidden}
    .reveal{transition:opacity .6s ease, transform .6s ease}
    h1,h2,h3{font-family:ui-sans-serif,system-ui,-apple-system,'Segoe UI',Roboto,'Helvetica Neue',Arial}
    .large-title{font-size:clamp(28px,4.5vw,48px);line-height:1.02}
    .micro-cta{transition:transform .18s ease, box-shadow .18s ease}
    .micro-cta:hover{transform:translateY(-3px);box-shadow:0 8px 20px rgba(0,0,0,0.08)}
    .rounded-3xl{border-radius:28px}
    /* Cart card styles for consistent sizing */
    .cart-card{display:flex;gap:20px;align-items:center;max-width:1000px;margin:0 auto;height:220px;min-height:220px;max-height:220px;padding:20px;background:#ffffff;box-shadow:0 8px 26px rgba(0,0,0,0.06);border-radius:14px;box-sizing:border-box;overflow:hidden}
    .cart-img{width:200px;flex:0 0 200px;display:flex;align-items:center}
    .cart-img-inner{height:160px;width:100%;overflow:hidden;border-radius:12px;background:#fff;display:flex;align-items:center;justify-content:center}
    .cart-content{flex:1;display:flex;flex-direction:column;justify-content:center;min-height:160px;padding-right:18px}
    .cart-right{flex:0 0 160px;text-align:right;display:flex;flex-direction:column;justify-content:center}
    .qty-btn{width:34px;height:34px;border-radius:8px;border:1px solid var(--sand);background:#fff;display:inline-flex;align-items:center;justify-content:center;cursor:pointer}
    .qty-display{min-width:36px;height:34px;display:flex;align-items:center;justify-content:center;background:#f3f2f0;border-radius:8px}
    .cart-desc{color:rgba(58,45,40,0.9);opacity:0.95;max-height:22px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;}
    /* Increase default max-widths used across pages for a roomier layout */
    .max-w-4xl{max-width:1100px !important}
    .max-w-5xl{max-width:1400px !important}
    .max-w-6xl{max-width:1700px !important}
    .max-w-7xl{max-width:2000px !important}
    /* dark mode background */
    body{background:linear-gradient(180deg,var(--ivory), #f7f4f1)}
    [data-theme="dark"] body{background:linear-gradient(180deg,#0f0d0c,#12100f)}
  </style>
</head>
<body class="cl-header font-sans">
<header class="shadow-sm">
  <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
    <div class="flex items-center gap-4">
      <div class="w-12 h-12 rounded-md overflow-hidden bg-white flex items-center justify-center">
        <img src="logo.png" alt="Logo" style="width:48px;height:48px;object-fit:contain" />
      </div>
      <a href="index.php" class="text-2xl font-semibold" style="color:var(--espresso)">Cafe Lounge</a>
    </div>

    <nav class="hidden md:flex items-center gap-6">
      <a class="nav-link transition-smooth" href="about.php">About Us</a>
      <a class="nav-link transition-smooth" href="booking_create.php">Book Space</a>
      <a class="nav-link transition-smooth" href="menu.php">Menu</a>
      <a class="nav-link transition-smooth" href="contact.php">Contact Us</a>
    </nav>

    <div class="flex items-center gap-3">
      <?php
      // show cart count (session or DB)
      $cartCount = 0;
      if (isLoggedIn()){
        $userId = getCurrentUserId();
        require_once __DIR__ . '/../../app/models/Cart.php';
        $cm = new Cart();
        $cartItems = $cm->itemsForUser($userId);
        $cartCount = array_sum(array_column($cartItems,'quantity'));
      } else {
        $sess = $_SESSION['cart'] ?? [];
        $cartCount = array_sum($sess ?: []);
      }
      ?>
      <a href="cart.php" class="btn-icon cl-btn transition-smooth" style="text-decoration:none">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 6H21L20 12H8L6 6Z" stroke="white" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <span>Cart</span>
        <span id="cartCountBadge" class="badge"><?= (int)$cartCount ?></span>
      </a>

      <div class="relative">
        <?php if (!isLoggedIn()): ?>
          <a href="login.php" class="btn-icon transition-smooth" style="background:transparent;border:1px solid var(--sand);color:var(--espresso);">Login</a>
        <?php else: ?>
          <button id="profileBtn" class="btn-icon transition-smooth" style="background:transparent;border:1px solid var(--sand);">
            <span style="color:var(--espresso);"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M6 9L12 15L18 9" stroke="var(--espresso)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
          <div id="profileMenu" class="profile-menu absolute right-0 mt-2 hidden" aria-hidden="true">
            <a href="settings.php" class="block px-3 py-2 text-sm nav-link">Settings</a>
            <a href="bookings.php" class="block px-3 py-2 text-sm nav-link">Bookings</a>
            <a href="logout.php" class="block px-3 py-2 text-sm nav-link">Logout</a>
          </div>
        <?php endif; ?>
      </div>
      
    </div>
  </div>
</header>
<main class="max-w-7xl mx-auto px-4 py-6">
<?php if (session_status() !== PHP_SESSION_ACTIVE) session_start(); ?>
<?php if (!empty($_SESSION['flash'])): $f = $_SESSION['flash']; unset($_SESSION['flash']); ?>
  <div id="flashMsg" class="max-w-5xl mx-auto mb-4 px-4">
    <div class="p-3 rounded-lg <?= ($f['type'] ?? '') === 'error' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?>" style="border:1px solid rgba(0,0,0,0.04)">
      <?= htmlspecialchars($f['msg'] ?? '') ?>
    </div>
  </div>
  <script>setTimeout(()=>{ const e=document.getElementById('flashMsg'); if(e) e.style.display='none'; },4000);</script>
<?php endif; ?>
