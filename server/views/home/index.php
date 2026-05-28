<?php
// Fetch menu items and space types from database
require_once __DIR__ . '/../../app/models/Database.php';
$pdo = Database::get();

// Menu items
try {
    $stmt = $pdo->query("SELECT id, name, price, image, description FROM cafe_items ORDER BY id ASC");
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
<section class="relative mb-8 overflow-hidden rounded-lg" style="height: 550px;">
  <img src="https://images.pexels.com/photos/23384632/pexels-photo-23384632.jpeg" alt="Cafe" class="absolute inset-0 w-full h-full object-cover" />
  <!-- Gradient overlay across entire image - gradually darkens -->
  <div class="absolute inset-0 w-full h-full" style="background: linear-gradient(to right, rgba(0,0,0,0.65), rgba(0,0,0,0.25));"></div>
  <!-- Welcome text overlay on left side -->
  <div class="absolute inset-0 flex items-center left-0 p-8 md:p-16" style="width: 65%;">
    <div>
      <h1 class="font-extrabold mb-6" style="color:#ffffff;font-size:3.5rem;">Welcome to Cafe Lounge</h1>
      <p class="mb-8 text-lg leading-relaxed" style="color:#ffffff;font-size:1.1rem;">A relaxed space to work, meet and enjoy great coffee. Book a workspace, order from our cafe menu, or manage your bookings — all in one place.</p>
      <div class="flex gap-4">
        <a href="booking_create.php" class="px-6 py-3 cl-btn rounded-lg transition-smooth shadow font-semibold">Book a Space</a>
        <a href="menu.php" class="px-6 py-3 border-2 rounded-lg font-semibold" style="background:rgba(0,0,0,0.3);border-color:#ffffff;color:#ffffff">Browse Menu</a>
      </div>
    </div>
  </div>
</section>

<!-- Menu preview -->
<section class="mt-16 px-4 md:px-0">
  <h2 class="text-3xl font-bold mb-6" style="color:var(--espresso)">Cafe Menu</h2>
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
    <?php if (!empty($menuItems)): 
      $displayItems = array_slice($menuItems, 0, 8);
      foreach ($displayItems as $item):
        $img = !empty($item['image']) ? (strpos($item['image'],'http')===0 ? $item['image'] : 'images/' . $item['image']) : 'images/menu_placeholder.png';
    ?>
      <div class="group bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden hover:-translate-y-1 reveal">
        <div class="h-40 overflow-hidden relative">
          <img src="<?= $img ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
        </div>
        <div class="p-4">
          <h3 class="font-semibold text-lg" style="color:var(--espresso)"><?= htmlspecialchars($item['name']) ?></h3>
          <?php if (!empty($item['description'])): ?>
            <p class="text-sm text-gray-600 mt-2 line-clamp-2"><?= htmlspecialchars($item['description']) ?></p>
          <?php endif; ?>
          <div class="mt-3 text-lg font-bold text-amber-600">LKR <?= number_format($item['price'],0) ?></div>
        </div>
      </div>
    <?php endforeach; else: ?>
      <div class="col-span-full p-8 bg-gray-50 rounded-xl text-center text-gray-500">No menu items available.</div>
    <?php endif; ?>
  </div>
</section>

<!-- Spaces Section -->
<section class="mt-16 px-4 md:px-0">
  <h2 class="text-3xl font-bold mb-6" style="color:var(--espresso)">Our Spaces</h2>
  <div class="relative">
    <button id="spacesPrev" class="absolute left-0 top-1/2 -translate-y-1/2 -translate-x-4 z-10 w-10 h-10 rounded-full bg-white border border-gray-300 shadow-md hover:bg-gray-100 transition-all flex items-center justify-center text-gray-700 font-bold" aria-label="Previous">
      ‹
    </button>
    <div id="spaceScroller" class="overflow-x-auto scrollbar-hide pb-4">
      <div class="flex gap-6" style="min-width: min-content;">
      <?php
      $spaceImageMap = [
        'desk' => 'https://images.pexels.com/photos/12804511/pexels-photo-12804511.jpeg',
        'study desks' => 'https://images.pexels.com/photos/7681682/pexels-photo-7681682.jpeg',
        'meeting room' => 'https://images.pexels.com/photos/14683759/pexels-photo-14683759.jpeg',
        'rooftop lounge' => 'https://images.pexels.com/photos/7214339/pexels-photo-7214339.jpeg',
        'group space' => 'https://images.pexels.com/photos/6399033/pexels-photo-6399033.jpeg',
        'outdoor space' => 'https://images.pexels.com/photos/29898382/pexels-photo-29898382.jpeg',
        'outdoor swing' => 'https://images.pexels.com/photos/31774137/pexels-photo-31774137.jpeg',
      ];

      if (!empty($spaceTypes)){
        foreach($spaceTypes as $s){
          $typeName = strtolower($s['type_name'] ?? $s['type'] ?? '');
          if (!empty($s['image'])){
            $img = strpos($s['image'], 'http') === 0 ? $s['image'] : ('images/' . $s['image']);
          } else if (!empty($spaceImageMap[$typeName])){
            $img = $spaceImageMap[$typeName];
          } else {
            $img = 'images/space_placeholder.png';
          }
          $spaceId = $s['id'] ?? '';
          echo "<a href=\"booking_create.php" . ($spaceId ? "?space_type_id=" . urlencode($spaceId) : "") . "\" class=\"flex-shrink-0 w-64 rounded-xl bg-white shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden hover:-translate-y-1 reveal block group\">";
          echo "<div class='h-40 overflow-hidden relative'><img src='{$img}' class='w-full h-full object-cover group-hover:scale-110 transition-transform duration-500' alt='".htmlspecialchars($s['type_name'] ?? $s['type'])."' /></div>";
          echo "<div class='p-4'><h3 class='font-semibold text-lg' style='color:var(--espresso)'>".htmlspecialchars($s['type_name'] ?? $s['type'])."</h3><p class='text-sm text-amber-600 mt-2 font-medium'>Book Now →</p></div>";
          echo "</a>";
        }
      } else {
        $defaults = ['Meeting Room','Study Desks','Rooftop Lounge','Outdoor Space','Group Space','Outdoor Swing','Desk'];
        foreach($defaults as $d){
          $key = strtolower($d);
          $img = $spaceImageMap[$key] ?? 'images/space_placeholder.png';
          echo "<a href=\"booking_create.php\" class=\"flex-shrink-0 w-64 rounded-xl bg-white shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden hover:-translate-y-1 reveal block group\">";
          echo "<div class='h-40 overflow-hidden relative'><img src='{$img}' class='w-full h-full object-cover hover:scale-110 transition-transform duration-500' alt='".htmlspecialchars($d)."' /></div>";
          echo "<div class='p-4'><h3 class='font-semibold text-lg' style='color:var(--espresso)'>".htmlspecialchars($d)."</h3><p class='text-sm text-amber-600 mt-2 font-medium'>Book Now →</p></div>";
          echo "</a>";
        }
      }
      ?>
      </div>
    </div>
    <button id="spacesNext" class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-4 z-10 w-10 h-10 rounded-full bg-white border border-gray-300 shadow-md hover:bg-gray-100 transition-all flex items-center justify-center text-gray-700 font-bold" aria-label="Next">
      ›
    </button>
  </div>
</section>

<script>
  (function(){
    const scroller = document.getElementById('spaceScroller');
    const prev = document.getElementById('spacesPrev');
    const next = document.getElementById('spacesNext');
    if (!scroller || !prev || !next) return;
    function updateButtons(){
      prev.disabled = scroller.scrollLeft <= 5;
      next.disabled = scroller.scrollLeft + scroller.clientWidth >= scroller.scrollWidth - 5;
    }
    prev.addEventListener('click', ()=>{ scroller.scrollBy({left: -300, behavior:'smooth'}); setTimeout(updateButtons,420); });
    next.addEventListener('click', ()=>{ scroller.scrollBy({left: 300, behavior:'smooth'}); setTimeout(updateButtons,420); });
    scroller.addEventListener('scroll', updateButtons);
    window.addEventListener('resize', updateButtons);
    setTimeout(updateButtons,100);
  })();
</script>

<!-- About Section -->
<section class="mt-16 px-4 md:px-0 reveal">
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
    <div>
      <h2 class="text-3xl font-bold mb-4" style="color:var(--espresso)">About Us</h2>
      <p class="text-gray-700 mb-4 leading-relaxed">Welcome to CafeLounge, your cozy escape in the heart of Malabe. Established in 2026 near SLIIT, CafeLounge brings students, professionals, and coffee lovers together in a calm and welcoming atmosphere.</p>
      <p class="text-gray-700 mb-6 leading-relaxed">We provide a comfortable workspace, meeting rooms, and a curated menu — perfect for studying, meetings, or relaxing with friends.</p>

      <ul class="space-y-2 text-gray-700">
        <li class="flex items-center gap-3"><span class="text-amber-600 font-bold">✓</span> Calm and cozy atmosphere</li>
        <li class="flex items-center gap-3"><span class="text-amber-600 font-bold">✓</span> Free high-speed Wi-Fi</li>
        <li class="flex items-center gap-3"><span class="text-amber-600 font-bold">✓</span> Student-friendly environment</li>
        <li class="flex items-center gap-3"><span class="text-amber-600 font-bold">✓</span> Fresh coffee and quality food</li>
      </ul>
    </div>
    <div>
      <img src="https://images.pexels.com/photos/2253643/pexels-photo-2253643.jpeg" alt="Cafe Lounge" class="w-full h-80 object-cover rounded-2xl shadow-lg" />
    </div>
  </div>
</section>

<!-- Quick Actions -->
<section class="mt-16 mb-12 px-4 md:px-0">
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <a href="booking_create.php" class="group p-6 rounded-xl bg-gradient-to-br from-white to-amber-50 shadow-md hover:shadow-lg transition-all duration-300 hover:-translate-y-1 border border-amber-100 reveal">
      <div class="w-12 h-12 rounded-lg bg-amber-100 flex items-center justify-center mb-4 group-hover:bg-amber-600 transition-colors text-amber-600 group-hover:text-white font-bold text-xl">B</div>
      <h3 class="text-xl font-bold text-gray-800 mb-2">Book a Space</h3>
      <p class="text-gray-600">Reserve your perfect spot for work or study</p>
    </a>
    <a href="menu.php" class="group p-6 rounded-xl bg-gradient-to-br from-white to-amber-50 shadow-md hover:shadow-lg transition-all duration-300 hover:-translate-y-1 border border-amber-100 reveal">
      <div class="w-12 h-12 rounded-lg bg-amber-100 flex items-center justify-center mb-4 group-hover:bg-amber-600 transition-colors text-amber-600 group-hover:text-white font-bold text-xl">M</div>
      <h3 class="text-xl font-bold text-gray-800 mb-2">Browse Menu</h3>
      <p class="text-gray-600">Explore our delicious food and drinks</p>
    </a>
    <a href="bookings.php" class="group p-6 rounded-xl bg-gradient-to-br from-white to-amber-50 shadow-md hover:shadow-lg transition-all duration-300 hover:-translate-y-1 border border-amber-100 reveal">
      <div class="w-12 h-12 rounded-lg bg-amber-100 flex items-center justify-center mb-4 group-hover:bg-amber-600 transition-colors text-amber-600 group-hover:text-white font-bold text-xl">M</div>
      <h3 class="text-xl font-bold text-gray-800 mb-2">Manage Bookings</h3>
      <p class="text-gray-600">View, edit, or cancel your reservations</p>
    </a>
  </div>
</section>

<style>
  .scrollbar-hide::-webkit-scrollbar {
    display: none;
  }
  .scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
  }
  
  .reveal {
    opacity: 0;
    transform: translateY(20px);
    animation: reveal 0.6s ease forwards;
  }
  
  @keyframes reveal {
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
</style>
