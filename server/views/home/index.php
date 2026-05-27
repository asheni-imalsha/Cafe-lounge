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
<section class="mt-12">
  <h2 class="text-2xl font-semibold mb-4" style="color:var(--espresso)">Cafe Menu</h2>
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
    <?php if (!empty($menuItems)): foreach ($menuItems as $item):
      $img = !empty($item['image']) ? (strpos($item['image'],'http')===0 ? $item['image'] : 'images/' . $item['image']) : 'images/menu_placeholder.png';
    ?>
      <div class="p-4 rounded-lg bg-white shadow-sm reveal">
        <div class="h-40 overflow-hidden rounded-md mb-3">
          <img src="<?= $img ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width:100%;height:100%;object-fit:cover" />
        </div>
        <h3 class="font-semibold" style="color:var(--espresso)"><?= htmlspecialchars($item['name']) ?></h3>
        <?php if (!empty($item['description'])): ?>
          <p class="text-sm text-gray-600 mt-2 line-clamp-2"><?= htmlspecialchars($item['description']) ?></p>
        <?php endif; ?>
        <div class="mt-2 text-sm text-gray-700">Price: LKR <?= number_format($item['price'],0) ?></div>
      </div>
    <?php endforeach; else: ?>
      <div class="p-6 bg-white rounded">No menu items available.</div>
    <?php endif; ?>
  </div>
</section>

<!-- Spaces (horizontal scroller with left/right arrows) -->
<section class="mt-12">
  <h2 class="text-2xl font-semibold mb-4" style="color:var(--espresso)">Spaces</h2>
  <div class="relative">
    <button id="spacesPrev" class="scroller-btn left" aria-label="Previous">‹</button>
    <div id="spaceScroller" class="horizontal-scroller">
      <div class="scroller-list">
      <?php
      // map space type names (lowercase) to external image URLs
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
          echo "<div class=\"space-card rounded-lg cl-card p-3 reveal\">";
          echo "<div class='h-36 overflow-hidden rounded-md mb-2'><img src='{$img}' style='width:100%;height:100%;object-fit:cover' alt='".htmlspecialchars($s['type_name'] ?? $s['type'])."' /></div>";
          echo "<div class='font-semibold' style='color:var(--espresso)'>".htmlspecialchars($s['type_name'] ?? $s['type'])."</div>";
          echo "</div>";
        }
      } else {
        $defaults = ['Meeting Room','Study Desks','Rooftop Lounge','Outdoor Space','Group Space','Outdoor Swing','desk'];
        foreach($defaults as $d){
          $key = strtolower($d);
          $img = $spaceImageMap[$key] ?? 'images/space_placeholder.png';
          echo "<div class=\"space-card rounded-lg cl-card p-3 reveal\">";
          echo "<div class='h-36 overflow-hidden rounded-md mb-2'><img src='{$img}' style='width:100%;height:100%;object-fit:cover' alt='".htmlspecialchars($d)."' /></div>";
          echo "<div class='font-semibold' style='color:var(--espresso)'>".htmlspecialchars($d)."</div>";
          echo "</div>";
        }
      }
      ?>
      </div>
    </div>
    <button id="spacesNext" class="scroller-btn right" aria-label="Next">›</button>
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
    prev.addEventListener('click', ()=>{ scroller.scrollBy({left: -Math.round(scroller.clientWidth*0.6), behavior:'smooth'}); setTimeout(updateButtons,420); });
    next.addEventListener('click', ()=>{ scroller.scrollBy({left: Math.round(scroller.clientWidth*0.6), behavior:'smooth'}); setTimeout(updateButtons,420); });
    scroller.addEventListener('scroll', updateButtons);
    // initialize
    setTimeout(updateButtons,100);
  })();
</script>

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
    <img src="https://images.pexels.com/photos/2253643/pexels-photo-2253643.jpeg" alt="Cafe Lounge" style="width:100%;height:260px;object-fit:cover;border-radius:12px" />
  </div>
</section>

<!-- Quick actions -->
<section class="mt-10 grid grid-cols-1 md:grid-cols-3 gap-4">
  <a href="booking_create.php" class="p-4 rounded-lg cl-card transition-smooth reveal">Book a Space</a>
  <a href="menu.php" class="p-4 rounded-lg cl-card transition-smooth reveal">Browse Menu</a>
  <a href="bookings.php" class="p-4 rounded-lg cl-card transition-smooth reveal">Manage Bookings</a>
</section>
