<?php
// Fetch menu items and space types from database
require_once __DIR__ . '/../../app/models/Database.php';
$pdo = Database::get();

// Menu items
try {
    $stmt = $pdo->query("SELECT id, name, price, image FROM cafe_items ORDER BY id ASC");
    $menuItems = $stmt->fetchAll();
} catch (Exception $e) {
    $menuItems = [];
}

// Space types (from space_types table if exists)
try {
    $stmt = $pdo->query("SELECT id, type_name, image FROM space_types ORDER BY id ASC");
    $spaceTypes = $stmt->fetchAll();
} catch (Exception $e) {
    $spaceTypes = [];
}
?>

<!-- Hero -->
<section class="relative mb-8">
  <div class="big-hero reveal">
    <img src="images/cafe.png" alt="Cafe" class="big-hero" />
  </div>
  <div class="absolute left-6 top-16 md:top-28 max-w-xl">
    <div class="reveal" style="background:transparent;padding:1rem">
      <h1 class="large-title font-extrabold mb-4" style="color:var(--espresso)">Welcome to Cafe Lounge</h1>
      <p class="mb-6 text-lg" style="color:rgba(58,45,40,.9)">A relaxed space to work, meet and enjoy great coffee. Book a workspace, order from our cafe menu, or manage your bookings — all in one place.</p>
      <div class="flex gap-3">
        <a href="booking_create.php" class="px-4 py-3 cl-btn rounded-lg transition-smooth shadow">Book a Space</a>
        <a href="menu.php" class="px-4 py-3 border rounded-lg" style="background:transparent;border-color:var(--sand);color:var(--espresso)">Browse Menu</a>
      </div>
    </div>
  </div>
</section>

<!-- Menu preview -->
<section class="mt-12">
  <h2 class="text-2xl font-semibold mb-4" style="color:var(--espresso)">Cafe Menu</h2>
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
    <?php if (!empty($menuItems)): foreach ($menuItems as $item):
        $img = $item['image'] ? 'images/' . $item['image'] : 'images/menu_placeholder.png';
    ?>
      <div class="p-4 rounded-lg bg-white shadow-sm reveal">
        <div class="h-40 overflow-hidden rounded-md mb-3">
          <img src="<?= $img ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width:100%;height:100%;object-fit:cover" />
        </div>
        <h3 class="font-semibold" style="color:var(--espresso)"><?= htmlspecialchars($item['name']) ?></h3>
        <div class="mt-2 text-sm text-gray-700">Price: LKR <?= number_format($item['price'],0) ?></div>
      </div>
    <?php endforeach; else: ?>
      <div class="p-6 bg-white rounded">No menu items available.</div>
    <?php endif; ?>
  </div>
</section>

<!-- Spaces (horizontal scroll) -->
<section class="mt-12">
  <h2 class="text-2xl font-semibold mb-4" style="color:var(--espresso)">Spaces</h2>
  <div class="overflow-x-auto py-2">
    <div class="flex gap-4" style="width:max-content;">
      <?php
      if (!empty($spaceTypes)){
        foreach($spaceTypes as $s){
          $img = $s['image'] ? 'images/' . $s['image'] : 'images/space_placeholder.png';
          echo "<div class=\"w-64 rounded-lg cl-card p-3 reveal\">";
          echo "<div class='h-36 overflow-hidden rounded-md mb-2'><img src='{$img}' style='width:100%;height:100%;object-fit:cover' alt='".htmlspecialchars($s['type_name'])."' /></div>";
          echo "<div class='font-semibold' style='color:var(--espresso)'>".htmlspecialchars($s['type_name'])."</div>";
          echo "</div>";
        }
      } else {
        $defaults = ['Meeting Room','Study Desks','Rooftop Lounge','Outdoor Space','Group Space','Outdoor Swing'];
        foreach($defaults as $d){
          echo "<div class=\"w-64 rounded-lg cl-card p-3 reveal\">";
          echo "<div class='h-36 overflow-hidden rounded-md mb-2'><img src='images/space_placeholder.png' style='width:100%;height:100%;object-fit:cover' alt='".htmlspecialchars($d)."' /></div>";
          echo "<div class='font-semibold' style='color:var(--espresso)'>".htmlspecialchars($d)."</div>";
          echo "</div>";
        }
      }
      ?>
    </div>
  </div>
</section>

<!-- About (brief) -->
<section class="mt-12 reveal grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
  <div>
    <h2 class="text-2xl font-semibold mb-4" style="color:var(--espresso)">About Us</h2>
    <p class="text-gray-800 mb-3">Welcome to CafeLounge, your cozy escape in the heart of Malabe. Established in 2026 near SLIIT, CafeLounge brings students, professionals, and coffee lovers together in a calm and welcoming atmosphere.</p>
    <p class="text-gray-800 mb-3">We provide a comfortable workspace, meeting rooms, and a curated menu — perfect for studying, meetings, or relaxing with friends.</p>

    <ul class="list-disc pl-5 text-gray-800">
      <li>Calm and cozy atmosphere</li>
      <li>Free high-speed Wi-Fi</li>
      <li>Student-friendly environment</li>
      <li>Fresh coffee and quality food</li>
    </ul>
  </div>
  <div>
    <img src="images/cafe.jpg" alt="Cafe Lounge" style="width:100%;height:260px;object-fit:cover;border-radius:12px" />
  </div>
</section>

<!-- Quick actions -->
<section class="mt-10 grid grid-cols-1 md:grid-cols-3 gap-4">
  <a href="booking_create.php" class="p-4 rounded-lg cl-card transition-smooth reveal">Book a Space</a>
  <a href="menu.php" class="p-4 rounded-lg cl-card transition-smooth reveal">Browse Menu</a>
  <a href="bookings.php" class="p-4 rounded-lg cl-card transition-smooth reveal">Manage Bookings</a>
</section>
