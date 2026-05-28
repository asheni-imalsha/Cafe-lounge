<?php
require_once __DIR__ . '/../../app/models/Database.php';
$spaceTypes = [];
try{
  $pdo = Database::get();
  $stmt = $pdo->query("SELECT type_name FROM space_types ORDER BY type_name");
  $spaceTypes = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch(Exception $e){
  // fallback to parse enum from bookings table
  try{
    $pdo = Database::get();
    $stmt = $pdo->prepare("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bookings' AND COLUMN_NAME = 'space_type'");
    $stmt->execute(); $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!empty($row['COLUMN_TYPE'])){ preg_match_all("/'([^']+)'/", $row['COLUMN_TYPE'], $m); if (!empty($m[1])) $spaceTypes = $m[1]; }
  } catch(Exception $e2){ }
}
if (empty($spaceTypes)) $spaceTypes = ['Desk','Meeting Room','Study Desks','Rooftop Lounge','Outdoor Space','Group Space','Outdoor Swing'];
require_once __DIR__ . '/../partials/header.php';
?>
<div class="max-w-7xl mx-auto mt-8">
  <div class="flex items-center justify-between mb-4">
    <div>
      <h1 class="text-2xl font-bold" style="color:var(--espresso)"><?= ($filter === 'my') ? 'My Bookings' : 'All Bookings' ?></h1>
      <form id="filterForm" method="get" class="mt-2">
        <label class="mr-4"><input type="radio" name="filter" value="all" <?= ($filter === 'all') ? 'checked' : '' ?>> All Bookings</label>
        <label><input type="radio" name="filter" value="my" <?= ($filter === 'my') ? 'checked' : '' ?>> My Bookings</label>
      </form>
    </div>
    <div>
      <a href="booking_create.php" class="cl-btn px-4 py-2 rounded">&plus; Book Space</a>
    </div>
  </div>
  <div class="overflow-x-auto bg-white rounded-lg shadow-sm">
    <table class="min-w-full">
      <thead>
        <tr>
          <th class="text-left px-4 py-3">User</th>
          <th class="text-left px-4 py-3">Space</th>
          <th class="text-left px-4 py-3">Type</th>
          <th class="text-left px-4 py-3">When</th>
          <th class="text-left px-4 py-3">Actions</th>
        </tr>
      </thead>
      <tbody id="bookingsTbody">
        <?php foreach($bookings as $b): ?>
          <tr class="border-t">
            <td class="px-4 py-3\"><?= htmlspecialchars($b['username'] ?? '') ?></td>
            <td class="px-4 py-3\"><?= htmlspecialchars($b['space_name']) ?></td>
            <td class="px-4 py-3\"><?= htmlspecialchars(ucwords(strtolower($b['space_type_name'] ?? $b['space_type']))) ?></td>
            <td class="px-4 py-3\"><?= htmlspecialchars($b['booking_date']) ?> <div class="text-xs text-gray-600"><?= htmlspecialchars(($b['start_time'] ?? '') . ' - ' . ($b['end_time'] ?? '')) ?></div></td>
            <td class="px-4 py-3">
                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $b['user_id']): ?>
                  <a href="booking_edit.php?id=<?= (int)$b['id'] ?>" class="edit-booking px-3 py-1.5 rounded-lg text-sm transition-colors" style="border:1px solid var(--sand);color:var(--espresso);background:white;text-decoration:none">Edit</a>
                  <form method="post" action="booking_delete.php" style="display:inline;margin:0">
                    <?php echo csrfInputField(); ?>
                    <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                    <button type="button" class="px-3 py-1.5 rounded-lg text-sm transition-colors booking-delete-btn" style="background:#f8d7da;color:#842029;border:none;cursor:pointer">Delete</button>
                  </form>
                <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-40 hidden flex items-center justify-center z-50 p-6">
  <div class="glass-card rounded-3xl p-6 w-full max-w-lg shadow-lg">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold">Edit Booking</h3>
      <button id="closeEditModal" class="text-gray-500">✕</button>
    </div>
    <form id="editForm" method="post" action="" class="space-y-3">
      <?php echo csrfInputField(); ?>
      <input type="hidden" name="id" id="edit_id">
      <label class="block text-sm">Space name
        <select id="edit_space_name" name="space_name" required class="w-full border p-3 rounded">
          <?php foreach($spaceTypes as $s): for($i=1;$i<=3;$i++): $n = $s . ' ' . $i; ?>
            <option value="<?= htmlspecialchars($n) ?>"><?= htmlspecialchars($n) ?></option>
          <?php endfor; endforeach; ?>
          <option value="Other">Other (specify...)</option>
        </select>
        <input id="edit_space_name_other" name="space_name_other" class="w-full border p-3 rounded mt-2 hidden" placeholder="Specify space name">
      </label>
      <label class="block text-sm">Space type
        <div id="edit_space_type_display" class="w-full border p-3 rounded bg-white text-gray-700"></div>
        <select id="edit_space_type_select" name="space_type_select" class="w-full border p-3 rounded hidden mt-2">
          <?php foreach($spaceTypes as $s): $display = ucwords($s); ?>
            <option value="<?= htmlspecialchars($s) ?>"><?= htmlspecialchars($display) ?></option>
          <?php endforeach; ?>
        </select>
        <input type="hidden" name="space_type" id="edit_space_type_hidden" value="<?= htmlspecialchars($spaceTypes[0] ?? 'Desk') ?>">
      </label>
      <label class="block text-sm">Date
        <input type="date" name="booking_date" id="edit_booking_date" required class="w-full border p-3 rounded" min="<?= date('Y-m-d') ?>">
      </label>
      <div class="grid grid-cols-2 gap-3">
        <label class="block text-sm">Start time
          <input type="time" name="start_time" id="edit_start_time" required class="w-full border p-3 rounded">
        </label>
        <label class="block text-sm">End time
          <input type="time" name="end_time" id="edit_end_time" required class="w-full border p-3 rounded">
        </label>
      </div>
      <div class="flex items-center gap-3 mt-2">
        <button type="submit" class="cl-btn px-4 py-2 rounded">Save Changes</button>
        <button type="button" id="editCancel" class="px-3 py-2 border rounded">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
  // delete confirm - replaced by custom modal handlers below

  // auto-submit filter form
  document.getElementById('filterForm')?.addEventListener('change', function(e){ e.preventDefault(); const f = new FormData(this); const v = f.get('filter') || 'all'; fetchAndRender(v); });

  async function fetchAndRender(filter){
    try{
      const res = await fetch('bookings_ajax.php?filter='+encodeURIComponent(filter));
      const json = await res.json();
      if (json.error){ if (json.error === 'not_logged_in') { window.location.href = 'login.php'; return; } alert('Error loading bookings'); return; }
      const tbody = document.getElementById('bookingsTbody');
      tbody.innerHTML = '';
      json.forEach(b => {
        const tr = document.createElement('tr'); tr.className='border-t';
        const userTd = document.createElement('td'); userTd.className='px-4 py-3'; userTd.textContent = b.username || '';
        const spaceTd = document.createElement('td'); spaceTd.className='px-4 py-3'; spaceTd.textContent = b.space_name || '';
        const typeTd = document.createElement('td'); typeTd.className='px-4 py-3'; typeTd.textContent = (b.space_type_name || b.space_type || '').toString().split(' ').map(w=> w.charAt(0).toUpperCase()+w.slice(1)).join(' ');
        const whenTd = document.createElement('td'); whenTd.className='px-4 py-3'; whenTd.innerHTML = (b.booking_date || '')+ ' <div class="text-xs text-gray-600">' + ((b.start_time||'') + ' - ' + (b.end_time||'')) + '</div>';
        const actionsTd = document.createElement('td'); actionsTd.className='px-4 py-3';
        if (b.is_owner){
          const editA = document.createElement('a'); editA.href = 'booking_edit.php?id='+encodeURIComponent(b.id); editA.className='edit-booking px-3 py-1.5 rounded-lg text-sm transition-colors'; editA.style.border='1px solid var(--sand)'; editA.style.color='var(--espresso)'; editA.style.background='white'; editA.style.textDecoration='none'; editA.textContent='Edit';
          // create POST delete form with CSRF token from meta
          const delForm = document.createElement('form'); delForm.method='post'; delForm.action='booking_delete.php'; delForm.style.display='inline'; delForm.style.margin='0';
          const meta = document.querySelector('meta[name="csrf-token"]');
          const csrfInput = document.createElement('input'); csrfInput.type='hidden'; csrfInput.name='csrf_token'; csrfInput.value = meta ? meta.getAttribute('content') : '';
          const idInput = document.createElement('input'); idInput.type='hidden'; idInput.name='id'; idInput.value = b.id;
          const delBtn = document.createElement('button'); delBtn.type='button'; delBtn.className='px-3 py-1.5 rounded-lg text-sm transition-colors booking-delete-btn'; delBtn.style.background='#f8d7da'; delBtn.style.color='#842029'; delBtn.style.border='none'; delBtn.style.cursor='pointer'; delBtn.textContent='Delete';
          delBtn.addEventListener('click', function(e){ e.preventDefault(); const f = this.closest('form'); if (f && window.showBookingDeleteModalForForm) window.showBookingDeleteModalForForm(f); });
          delForm.appendChild(csrfInput); delForm.appendChild(idInput); delForm.appendChild(delBtn);
          actionsTd.appendChild(editA); actionsTd.appendChild(document.createTextNode(' ')); actionsTd.appendChild(delForm);
        }
        tr.appendChild(userTd); tr.appendChild(spaceTd); tr.appendChild(typeTd); tr.appendChild(whenTd); tr.appendChild(actionsTd);
        tbody.appendChild(tr);
      });
      // update title
      document.querySelector('h1').textContent = (filter==='my') ? 'My Bookings' : 'All Bookings';
      // reattach handlers (edit modal interception retained below)
      // reattach edit modal interception
      document.querySelectorAll('a[href^="booking_edit.php?id="]').forEach(a=>{
        a.addEventListener('click', async function(e){
          e.preventDefault();
          const href = this.getAttribute('href');
          const id = new URLSearchParams(href.split('?')[1]).get('id');
          if (!id) return;
          try{
            const res = await fetch('booking_fetch.php?id='+encodeURIComponent(id));
            const json = await res.json();
            if (json.error) { alert('Could not load booking'); return; }
            // reuse existing prefill logic
            editId.value = json.id;
            editForm.action = 'booking_edit.php?id='+encodeURIComponent(json.id);
            const opts = Array.from(editSpaceSelect.options).map(o=>o.value);
            if (opts.includes(json.space_name)) { editSpaceSelect.value = json.space_name; editSpaceOther.classList.add('hidden'); editSpaceOther.required=false; }
            else { editSpaceSelect.value = 'Other'; editSpaceOther.classList.remove('hidden'); editSpaceOther.required=true; editSpaceOther.value = json.space_name; }
            document.getElementById('edit_space_type_hidden').value = json.space_type ?? '';
            document.getElementById('edit_space_type_display').textContent = (json.space_type_name ?? json.space_type ?? '').toString().split(' ').map(w=>w.charAt(0).toUpperCase()+w.slice(1)).join(' ');
            if (json.booking_date) document.getElementById('edit_booking_date').value = json.booking_date.split(' ')[0];
            document.getElementById('edit_start_time').value = json.start_time ?? '';
            document.getElementById('edit_end_time').value = json.end_time ?? '';
            showEditModal();
          } catch(err){ console.error(err); alert('Error loading booking'); }
        });
      });
    } catch(err){ console.error(err); alert('Error fetching bookings'); }
  }

  // initial AJAX load using current filter
  fetchAndRender('<?= $filter ?>');

  // modal handlers
  const editModal = document.getElementById('editModal');
  const closeEditModal = document.getElementById('closeEditModal');
  const editCancel = document.getElementById('editCancel');
  const editForm = document.getElementById('editForm');
  const editId = document.getElementById('edit_id');
  const editSpaceSelect = document.getElementById('edit_space_name');
  const editSpaceOther = document.getElementById('edit_space_name_other');
  function showEditModal(){ editModal.classList.remove('hidden'); setTimeout(()=>{ editSpaceSelect.focus(); deriveTypeFromEditSpace(); },120); }
  function hideEditModal(){ editModal.classList.add('hidden'); }
  closeEditModal.addEventListener('click', hideEditModal);
  editCancel.addEventListener('click', hideEditModal);
  editModal.addEventListener('click',(e)=>{ if (e.target===editModal) hideEditModal(); });

  // derive type for edit
  function deriveTypeFromEditSpace(){
    const val = editSpaceSelect?.value || '';
    if (!val || val === 'Other') return;
    const typeName = val.replace(/\s+\d+$/,'');
    let matched = null;
    Array.from(document.querySelectorAll('#edit_space_type_select option')).forEach(opt=>{ if (opt.textContent.toLowerCase() === typeName.toLowerCase()) matched = opt.value; });
    if (!matched) matched = typeName.toLowerCase();
    document.getElementById('edit_space_type_hidden').value = matched;
    document.getElementById('edit_space_type_display').textContent = typeName.split(' ').map(w=>w.charAt(0).toUpperCase()+w.slice(1)).join(' ');
  }
  editSpaceSelect?.addEventListener('change', function(){ if (this.value === 'Other') { editSpaceOther.classList.remove('hidden'); editSpaceOther.required = true; document.getElementById('edit_space_type_select').classList.remove('hidden'); document.getElementById('edit_space_type_display').classList.add('hidden'); } else { editSpaceOther.classList.add('hidden'); editSpaceOther.required = false; document.getElementById('edit_space_type_select').classList.add('hidden'); document.getElementById('edit_space_type_display').classList.remove('hidden'); deriveTypeFromEditSpace(); } });
  document.getElementById('edit_space_type_select')?.addEventListener('change', function(){ document.getElementById('edit_space_type_hidden').value = this.value; document.getElementById('edit_space_type_display').textContent = this.options[this.selectedIndex].textContent || this.value; });

  // fetch booking and prefill modal
  document.querySelectorAll('a[href^="booking_edit.php?id="]').forEach(a=>{
    a.addEventListener('click', async function(e){
      e.preventDefault();
      const href = this.getAttribute('href');
      const id = new URLSearchParams(href.split('?')[1]).get('id');
      if (!id) return;
      try{
        const res = await fetch('booking_fetch.php?id='+encodeURIComponent(id));
        const json = await res.json();
        if (json.error) { alert('Could not load booking'); return; }
        // prefill
        editId.value = json.id;
        // set form action to include id
        editForm.action = 'booking_edit.php?id='+encodeURIComponent(json.id);
        // choose or add space_name
        const opts = Array.from(editSpaceSelect.options).map(o=>o.value);
        if (opts.includes(json.space_name)) { editSpaceSelect.value = json.space_name; editSpaceOther.classList.add('hidden'); editSpaceOther.required=false; }
        else { editSpaceSelect.value = 'Other'; editSpaceOther.classList.remove('hidden'); editSpaceOther.required=true; editSpaceOther.value = json.space_name; }
        // type
        document.getElementById('edit_space_type_hidden').value = json.space_type ?? '';
        document.getElementById('edit_space_type_display').textContent = (json.space_type_name ?? json.space_type ?? '').toString().split(' ').map(w=>w.charAt(0).toUpperCase()+w.slice(1)).join(' ');
        // date and times
        if (json.booking_date) document.getElementById('edit_booking_date').value = json.booking_date.split(' ')[0];
        document.getElementById('edit_start_time').value = json.start_time ?? '';
        document.getElementById('edit_end_time').value = json.end_time ?? '';
        showEditModal();
      } catch(err){ console.error(err); alert('Error loading booking'); }
    });
  });

  // availability check for edit modal
  async function checkEditAvailability(){
    const date = document.getElementById('edit_booking_date').value;
    const start = document.getElementById('edit_start_time').value;
    const end = document.getElementById('edit_end_time').value;
    const excludeId = document.getElementById('edit_id').value || null;
    if (!date || !start || !end) return;
    try{
      const params = new URLSearchParams({booking_date: date, start_time: start, end_time: end});
      const res = await fetch('booking_availability.php?'+params.toString());
      const json = await res.json();
      if (json.booked){
        Array.from(editSpaceSelect.options).forEach(opt=>{
          if (json.booked.includes(opt.value) && opt.value !== (document.getElementById('edit_space_name').value)) { opt.disabled = true; opt.classList.add('text-gray-400'); }
          else { opt.disabled = false; opt.classList.remove('text-gray-400'); }
        });
      }
    } catch(e){ console.error(e); }
  }
  document.getElementById('edit_booking_date')?.addEventListener('change', checkEditAvailability);
  document.getElementById('edit_start_time')?.addEventListener('change', checkEditAvailability);
  document.getElementById('edit_end_time')?.addEventListener('change', checkEditAvailability);

</script>
<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-40 hidden flex items-center justify-center z-50 p-6">
  <div class="glass-card rounded-3xl p-6 w-full max-w-sm shadow-lg">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold">Delete Booking</h3>
      <button id="closeDeleteModal" class="text-gray-500">✕</button>
    </div>
    <p class="mb-6 text-gray-700">Are you sure you want to delete this booking? This action cannot be undone.</p>
    <div class="flex items-center gap-3">
      <button id="confirmDeleteBtn" class="px-4 py-2 rounded" style="background:#f8d7da;color:#842029;font-weight:500">Delete</button>
      <button id="cancelDeleteBtn" class="px-4 py-2 border rounded">Cancel</button>
    </div>
  </div>
</div>

<script>
  // Delete modal logic for bookings list
  (function(){
    const deleteModal = document.getElementById('deleteModal');
    const closeDeleteBtn = document.getElementById('closeDeleteModal');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    let pendingForm = null;
    function showDeleteModalForForm(form){ pendingForm = form; deleteModal.classList.remove('hidden'); }
    function hideDeleteModal(){ pendingForm = null; deleteModal.classList.add('hidden'); }
    // expose helper so dynamically-created buttons can open the modal
    window.showBookingDeleteModalForForm = showDeleteModalForForm;
    window.hideBookingDeleteModal = hideDeleteModal;
    // attach to existing delete buttons
    document.querySelectorAll('.booking-delete-btn').forEach(btn=>{ btn.addEventListener('click', function(e){ e.preventDefault(); const f = this.closest('form'); if (f) showDeleteModalForForm(f); }); });
    if (closeDeleteBtn) closeDeleteBtn.addEventListener('click', hideDeleteModal);
    if (cancelDeleteBtn) cancelDeleteBtn.addEventListener('click', hideDeleteModal);
    if (deleteModal) deleteModal.addEventListener('click', (e)=>{ if (e.target === deleteModal) hideDeleteModal(); });
    if (confirmDeleteBtn) confirmDeleteBtn.addEventListener('click', function(){ if (pendingForm) pendingForm.submit(); hideDeleteModal(); });
  })();
</script>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
