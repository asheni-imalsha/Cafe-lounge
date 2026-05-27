<?php
require_once __DIR__ . '/../../src/auth.php';
require_once __DIR__ . '/../../app/models/Booking.php';
require_once __DIR__ . '/../../app/models/Database.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (!isLoggedIn()) { header('Location: login.php'); exit; }

$bookingModel = new Booking();
$allBookings = $bookingModel->all();
$userId = getCurrentUserId();
$myBookings = array_filter($allBookings, function($b) use ($userId){ return $b['user_id']==$userId; });

// fetch space types (prefer space_types table, fall back to enum in bookings.space_type)
$pdo = Database::get();
$spaceTypes = [];
try{
  $stmt = $pdo->query("SELECT type_name FROM space_types ORDER BY type_name");
  $spaceTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch(Exception $e){ $spaceTypes = []; }
if (empty($spaceTypes)) {
  try {
    $stmt = $pdo->prepare("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bookings' AND COLUMN_NAME = 'space_type'");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!empty($row['COLUMN_TYPE'])){
      // COLUMN_TYPE looks like: enum('desk','Meeting Room',...)
      preg_match_all("/'([^']+)'/", $row['COLUMN_TYPE'], $m);
      if (!empty($m[1])) $spaceTypes = $m[1];
    }
  } catch (Exception $e) { /* ignore and fall back */ }
}
if (empty($spaceTypes)) {
  $spaceTypes = ['desk','Meeting Room','Study Desks','Rooftop Lounge','Outdoor Space','Group Space','Outdoor Swing'];
}

?>
<?php require_once __DIR__ . '/../partials/header.php'; ?>

<div class="max-w-5xl mx-auto mt-8">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold" style="color:var(--espresso)">Book a Space</h1>
    <div class="flex items-center gap-3">
      <button id="openModal" class="cl-btn px-4 py-2 rounded micro-cta">
        <svg xmlns="http://www.w3.org/2000/svg" class="inline-block mr-2" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
        New Booking
      </button>
      <a href="bookings.php?filter=my" class="px-3 py-2 border rounded nav-link">My Bookings</a>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="glass-card reveal">
      <h3 class="font-semibold mb-2">How it works</h3>
      <p class="text-sm" style="color:var(--latte)">Choose a space name, pick a type and select date/time for your booking. You can view and manage your bookings from the 'My Bookings' page.</p>
    </div>

    <div class="glass-card reveal">
      <h3 class="font-semibold mb-2">Available space types</h3>
      <div class="flex flex-wrap gap-2">
        <?php foreach($spaceTypes as $s): $display = ucwords($s); ?>
          <span class="px-3 py-1 rounded-full" style="background:var(--sand);color:var(--espresso)"><?= htmlspecialchars($display) ?></span>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="mt-8">
    <h2 class="text-xl font-semibold mb-3">Your upcoming bookings</h2>
    <?php if (empty($myBookings)): ?>
      <div class="p-6 rounded-lg cl-card">You have no bookings yet. Click <strong>New Booking</strong> to create one.</div>
    <?php else: ?>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php foreach($myBookings as $b): ?>
          <div class="p-4 rounded-lg glass-card reveal">
            <div class="flex items-start justify-between">
              <div>
                <div class="text-sm text-gray-600"><?= htmlspecialchars(ucwords(strtolower($b['space_type_name'] ?? $b['space_type']))) ?></div>
                <div class="text-lg font-semibold" style="color:var(--espresso)"><?= htmlspecialchars($b['space_name']) ?></div>
                <div class="text-sm" style="color:var(--latte)"><?= htmlspecialchars($b['booking_date']) ?></div>
                <div class="text-sm" style="color:var(--latte)">Time: <?= htmlspecialchars($b['start_time'] ?? '') ?> - <?= htmlspecialchars($b['end_time'] ?? '') ?></div>
              </div>
              <div class="flex flex-col gap-2">
                <a href="booking_edit.php?id=<?= (int)$b['id'] ?>" class="px-3 py-1 border rounded-full text-sm nav-link">Edit</a>
                <a href="booking_delete.php?id=<?= (int)$b['id'] ?>" class="px-3 py-1 rounded-full text-sm confirm-delete" style="background:#f8d7da;color:#842029;text-align:center">Delete</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Modal (centered) -->
<div id="modal" class="fixed inset-0 bg-black bg-opacity-40 hidden flex items-center justify-center z-50 p-6">
    <div class="glass-card rounded-3xl p-6 w-full max-w-lg shadow-lg">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold">Create Booking</h3>
        <button id="closeModal" class="text-gray-500">✕</button>
      </div>
      <form method="post" action="" class="space-y-3">
        <label class="block text-sm">Space name
          <select id="space_name_input" name="space_name" required class="w-full border p-3 rounded">
            <?php foreach($spaceTypes as $s): for($i=1;$i<=3;$i++): $n = $s . ' ' . $i; ?>
              <option value="<?= htmlspecialchars($n) ?>"><?= htmlspecialchars($n) ?></option>
            <?php endfor; endforeach; ?>
            <option value="Other">Other (specify...)</option>
          </select>
          <input id="space_name_other" name="space_name_other" class="w-full border p-3 rounded mt-2 hidden" placeholder="Specify space name">
        </label>
        <label class="block text-sm">Space type
          <div id="space_type_display" class="w-full border p-3 rounded bg-white text-gray-700"></div>
          <select id="space_type_select" name="space_type_select" class="w-full border p-3 rounded hidden mt-2">
            <?php foreach($spaceTypes as $s): $display = ucwords($s); ?>
              <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($display) ?></option>
            <?php endforeach; ?>
          </select>
          <input type="hidden" name="space_type" id="space_type_hidden" value="<?= htmlspecialchars($spaceTypes[0] ?? 'desk') ?>">
        </label>
        <label class="block text-sm">Date
          <input type="date" name="booking_date" required class="w-full border p-3 rounded" min="<?= date('Y-m-d') ?>">
        </label>
        <div class="grid grid-cols-2 gap-3">
          <label class="block text-sm">Start time
            <input type="time" name="start_time" required class="w-full border p-3 rounded">
          </label>
          <label class="block text-sm">End time
            <input type="time" name="end_time" required class="w-full border p-3 rounded">
          </label>
        </div>
        <div class="flex items-center gap-3 mt-2">
          <button type="submit" class="cl-btn px-4 py-2 rounded">
            <svg xmlns="http://www.w3.org/2000/svg" class="inline-block mr-2" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h11l5 5v9a2 2 0 0 1-2 2z"/></svg>
            Save Booking
          </button>
          <button type="button" id="cancel" class="px-3 py-2 border rounded">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<script>
  const modal = document.getElementById('modal');
  const openBtn = document.getElementById('openModal');
  const closeBtn = document.getElementById('closeModal');
  const cancelBtn = document.getElementById('cancel');
  const spaceNameSelect = document.getElementById('space_name_input');
  const spaceNameOther = document.getElementById('space_name_other');
  function showModal(){ modal.classList.remove('hidden'); setTimeout(()=>{ if(spaceNameSelect) { spaceNameSelect.focus(); deriveTypeFromSpace(); } },120); }
  function hideModal(){ modal.classList.add('hidden'); }
  openBtn.addEventListener('click', showModal);
  closeBtn.addEventListener('click', hideModal);
  cancelBtn.addEventListener('click', hideModal);
  modal.addEventListener('click', (e)=>{ if (e.target === modal) hideModal(); });
  // toggle Other input
  spaceNameSelect?.addEventListener('change', function(){
    if (this.value === 'Other') {
      spaceNameOther.classList.remove('hidden'); spaceNameOther.required = true; spaceNameOther.focus();
      document.getElementById('space_type_select').classList.remove('hidden');
      document.getElementById('space_type_display').classList.add('hidden');
    }
    else {
      spaceNameOther.classList.add('hidden'); spaceNameOther.required = false;
      document.getElementById('space_type_select').classList.add('hidden');
      document.getElementById('space_type_display').classList.remove('hidden');
      deriveTypeFromSpace();
    }
  });

  function deriveTypeFromSpace(){
    const val = spaceNameSelect?.value || '';
    if (!val || val === 'Other') return;
    // remove trailing number(s)
    const typeName = val.replace(/\s+\d+$/,'');
    // map to underlying key in spaceTypes (case-insensitive match)
    let matched = null;
    Array.from(document.querySelectorAll('#space_type_select option')).forEach(opt=>{
      if (opt.textContent.toLowerCase() === typeName.toLowerCase()) matched = opt.value;
    });
    // fallback: use lowercased typeName
    if (!matched) matched = typeName.toLowerCase();
    document.getElementById('space_type_hidden').value = matched;
    // display capitalized
    document.getElementById('space_type_display').textContent = typeName.split(' ').map(w=>w.charAt(0).toUpperCase()+w.slice(1)).join(' ');
  }
  // when user selects a type for Other, update hidden and display
  const stSelect = document.getElementById('space_type_select');
  stSelect?.addEventListener('change', function(){
    document.getElementById('space_type_hidden').value = this.value;
    const txt = this.options[this.selectedIndex].textContent || this.value;
    document.getElementById('space_type_display').textContent = txt;
  });
  // before submit, if Other provided, set select value to other text and set hidden space_type from select
  document.querySelector('#modal form')?.addEventListener('submit', function(e){
    if (spaceNameOther && !spaceNameOther.classList.contains('hidden') && spaceNameOther.value.trim()!== ''){
      let inp = document.createElement('input'); inp.type='hidden'; inp.name='space_name'; inp.value = spaceNameOther.value.trim(); this.appendChild(inp);
      // ensure space_type_hidden matches chosen select
      const st = document.getElementById('space_type_select');
      if (st) document.getElementById('space_type_hidden').value = st.value;
    } else {
      // ensure space_name input isn't duplicated
    }
  });
  // availability check: hide options that are booked for selected date/time
  async function checkAvailability(){
    const date = document.querySelector('input[name="booking_date"]').value;
    const start = document.querySelector('input[name="start_time"]').value;
    const end = document.querySelector('input[name="end_time"]').value;
    if (!date || !start || !end) return;
    try{
      const params = new URLSearchParams({booking_date: date, start_time: start, end_time: end});
      const res = await fetch('booking_availability.php?'+params.toString());
      const json = await res.json();
      if (json.booked){
        // disable options that match booked names
        Array.from(spaceNameSelect.options).forEach(opt=>{
          if (json.booked.includes(opt.value)) { opt.disabled = true; opt.classList.add('text-gray-400'); }
          else { opt.disabled = false; opt.classList.remove('text-gray-400'); }
        });
      }
    } catch(e){ console.error(e); }
  }
  document.querySelector('input[name="booking_date"]')?.addEventListener('change', checkAvailability);
  document.querySelector('input[name="start_time"]')?.addEventListener('change', checkAvailability);
  document.querySelector('input[name="end_time"]')?.addEventListener('change', checkAvailability);
  // run check when modal opens
  openBtn.addEventListener('click', ()=> setTimeout(checkAvailability, 200));
  // confirm delete links on the page
  document.querySelectorAll('.confirm-delete').forEach(a=>{
    a.addEventListener('click', function(e){ if (!confirm('Are you sure you want to delete this booking?')) e.preventDefault(); });
  });
</script>
