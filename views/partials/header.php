<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Cafe Lounge</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-900">
<header class="bg-white p-4 shadow">
  <div class="container mx-auto flex justify-between items-center">
    <a href="index.php" class="text-xl font-bold">Cafe Lounge</a>
    <nav>
      <a href="menu.php" class="mr-4">Menu</a>
      <a href="bookings.php" class="mr-4">Bookings</a>
      <div class="flex items-center gap-3">
        <?php
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        require_once __DIR__ . '/../../server/src/auth.php';
        $cartCount = 0;
        if (isLoggedIn()){
          $userId = getCurrentUserId();
          require_once __DIR__ . '/../../server/app/models/Cart.php';
          $cm = new Cart();
          $items = $cm->itemsForUser($userId);
          $cartCount = array_sum(array_column($items,'quantity'));
        } else {
          $sess = $_SESSION['cart'] ?? [];
          $cartCount = array_sum($sess ?: []);
        }
        ?>
        <a href="/Cafe-lounge/server/public/cart.php" class="btn-icon cl-btn transition-smooth" style="text-decoration:none">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 6H21L20 12H8L6 6Z" stroke="white" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          <span>Cart</span>
          <span class="badge"><?= (int)$cartCount ?></span>
        </a>

        <div class="relative">
          <?php if (!isLoggedIn()): ?>
            <a href="/Cafe-lounge/server/public/login.php" class="btn-icon transition-smooth" style="background:transparent;border:1px solid var(--sand);color:var(--espresso);">Login</a>
          <?php else: ?>
            <button id="profileBtn" class="btn-icon transition-smooth" style="background:transparent;border:1px solid var(--sand);">
              <span style="color:var(--espresso);"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M6 9L12 15L18 9" stroke="var(--espresso)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <div id="profileMenu" class="profile-menu absolute right-0 mt-2 hidden" aria-hidden="true">
              <a href="/Cafe-lounge/server/public/settings.php" class="block px-3 py-2 text-sm nav-link">Settings</a>
              <a href="/Cafe-lounge/server/public/bookings.php" class="block px-3 py-2 text-sm nav-link">Bookings</a>
              <a href="/Cafe-lounge/server/public/logout.php" class="block px-3 py-2 text-sm nav-link">Logout</a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </nav>
  </div>
</header>
<main class="container mx-auto p-4">
