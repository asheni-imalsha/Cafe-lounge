<?php
require_once __DIR__ . '/../views/partials/header.php';
?>
<h1 class="text-2xl font-bold mb-4">Welcome to Cafe Lounge</h1>
<p class="mb-4">Use the navigation to browse the menu, manage bookings, and view your cart.</p>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
  <a href="/menu.php" class="bg-white p-4 rounded shadow">Browse Menu</a>
  <a href="/bookings.php" class="bg-white p-4 rounded shadow">Manage Bookings</a>
</div>
<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>
