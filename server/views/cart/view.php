<?php require_once __DIR__ . '/../../views/partials/header.php'; ?>
<?php
require_once __DIR__ . '/../../src/auth.php';
$user_id = getCurrentUserId();
?>
<div class="max-w-7xl mx-auto mt-8">
  <h2 class="text-2xl font-semibold mb-6" style="color:var(--espresso)">Your Cart</h2>
  <?php if (empty($items)): ?>
    <div class="p-6 bg-white rounded w-full">Your cart is empty.</div>
  <?php else: ?>
    <div class="flex items-center justify-between mb-4" style="max-width:900px;margin:0 auto;">
      <label style="display:flex;align-items:center;gap:8px"><input type="checkbox" id="selectAllCart" /> Select all</label>
    </div>
    <div class="grid grid-cols-1 gap-6">
      <?php foreach($items as $it):
        $img = !empty($it['image']) ? (filter_var($it['image'], FILTER_VALIDATE_URL) ? $it['image'] : 'uploads/'.ltrim($it['image'],'/')) : 'images/menu_placeholder.png';
        $cartRowId = $it['cart_id'] ?? null;
        $itemId = $it['item_id'] ?? ($it['id'] ?? null);
      ?>
      <div class="cart-card" data-unit-price="<?= (int)$it['price'] ?>">
        <div style="flex:0 0 40px;display:flex;align-items:center;justify-content:center">
          <input type="checkbox" class="cart-select" value="" data-cart-id="<?= htmlspecialchars($cartRowId) ?>" data-item-id="<?= htmlspecialchars($itemId) ?>" />
        </div>
        <div class="cart-img">
          <div class="cart-img-inner">
            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($it['name']) ?>" style="width:100%;height:100%;object-fit:cover" />
          </div>
        </div>
        <div class="cart-content">
          <div>
            <div class="font-semibold" style="color:var(--espresso)"><?= htmlspecialchars($it['name']) ?></div>
          </div>
          <div class="mt-3 flex items-center gap-4" style="align-items:center">
            <div style="font-weight:600;color:var(--latte)">LKR <?= number_format($it['price'],0) ?></div>
            <div style="display:flex;align-items:center;gap:8px;">
              <button class="qty-decrease qty-btn" data-cart-id="<?= htmlspecialchars($cartRowId) ?>" data-item-id="<?= htmlspecialchars($itemId) ?>">−</button>
              <div class="qty-display"> <span class="qty-value"><?= (int)$it['quantity'] ?></span> </div>
              <button class="qty-increase qty-btn" data-cart-id="<?= htmlspecialchars($cartRowId) ?>" data-item-id="<?= htmlspecialchars($itemId) ?>">+</button>
            </div>
          </div>
        </div>
        <div class="cart-right">
          <div class="text-sm" style="font-weight:600">Line: LKR <span class="line-total"><?= number_format($it['price'] * $it['quantity'],0) ?></span></div>
          <div class="mt-2"><a href="cart.php?remove=<?= $cartRowId ? (int)$cartRowId : (int)$itemId ?>" class="text-red-600">Remove</a></div>
        </div>
      </div>
      <?php endforeach; ?>
      <div style="max-width:900px;margin:0 auto;text-align:right;font-weight:700;display:flex;justify-content:flex-end;gap:16px;align-items:center">
        <div>Total: LKR <span id="cartTotal"><?= number_format($total,0) ?></span></div>
          <div>
            <button type="button" id="checkoutSelected" class="cl-btn px-4 py-2" style="border-radius:10px">Checkout selected</button>
          </div>
      </div>
    </div>
  <?php endif; ?>
</div>

  <?php require_once __DIR__ . '/../../views/partials/footer.php'; ?>

<script>
  // Quantity buttons
  document.querySelectorAll('.qty-increase, .qty-decrease').forEach(btn=>{
    btn.addEventListener('click', async function(){
      const cartId = this.dataset.cartId || null;
      const itemId = this.dataset.itemId || null;
      const row = this.closest('.cart-card');
      if (!row) return;
      const qtyEl = row.querySelector('.qty-value');
      let qty = parseInt(qtyEl.textContent,10);
      if (this.classList.contains('qty-increase')) qty = qty + 1; else qty = Math.max(0, qty - 1);
      const idToSend = (cartId && cartId !== 'NULL' && cartId !== '') ? cartId : itemId;
      try{
        const res = await fetch('cart_update.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'id='+encodeURIComponent(idToSend)+'&quantity='+encodeURIComponent(qty)});
        const json = await res.json();
        if (json.success){
          qtyEl.textContent = qty;
          const unitPrice = parseInt(row.dataset.unitPrice) || 0;
          const lineTotalEl = row.querySelector('.line-total');
          if (lineTotalEl) lineTotalEl.textContent = (unitPrice * qty).toLocaleString();
          // update total display
          const totalEl = document.getElementById('cartTotal');
          if (totalEl && typeof json.total !== 'undefined') totalEl.textContent = json.total.toLocaleString();
          // recompute selected/visible total
          computeDisplayedTotal();
        } else if (json.error){ alert(json.error); }
      } catch(e){ console.error(e); alert('Failed to update quantity'); }
    });
  });

  // Select all and checkout
  const selectAll = document.getElementById('selectAllCart');
  const checkoutBtn = document.getElementById('checkoutSelected');
  if (selectAll){
    selectAll.addEventListener('change', ()=>{
      document.querySelectorAll('.cart-select').forEach(cb=> cb.checked = selectAll.checked);
      computeDisplayedTotal();
    });
  }
  // update displayed total when any checkbox toggles
  document.querySelectorAll('.cart-select').forEach(cb=> cb.addEventListener('change', computeDisplayedTotal));
  // checkout button handled by modal handler below

  // compute selected total (or full total when none selected)
  function computeDisplayedTotal(){
    const totalEl = document.getElementById('cartTotal');
    if (!totalEl) return;
    const checked = Array.from(document.querySelectorAll('.cart-select:checked'));
    let sum = 0;
    if (checked.length === 0){
      // sum all rows
      document.querySelectorAll('.cart-card').forEach(r=>{
        const unit = parseFloat(r.dataset.unitPrice) || 0;
        const qty = parseInt(r.querySelector('.qty-value')?.textContent || '0',10) || 0;
        sum += unit * qty;
      });
    } else {
      checked.forEach(cb=>{
        const row = cb.closest('.cart-card'); if (!row) return;
        const unit = parseFloat(row.dataset.unitPrice) || 0;
        const qty = parseInt(row.querySelector('.qty-value')?.textContent || '0',10) || 0;
        sum += unit * qty;
      });
    }
    totalEl.textContent = Math.round(sum).toLocaleString();
  }

  // initial compute
  computeDisplayedTotal();
</script>

<!-- Checkout Modal (styled like booking form) -->
<div id="checkoutModal" class="fixed inset-0 bg-black bg-opacity-40 hidden flex items-center justify-center z-50 p-6">
  <div class="bg-white rounded-3xl p-6 w-full max-w-lg shadow-lg relative">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold">Checkout</h3>
      <button id="checkoutClose" class="text-gray-500">✕</button>
    </div>

    <div class="space-y-3">
      <label class="block text-sm">Selected items
        <div id="modalSelectedList" class="w-full border p-3 rounded max-h-48 overflow-auto mt-2"></div>
      </label>

      <label class="block text-sm">Select booking
        <select id="modalBooking" class="w-full border p-3 rounded mt-2">
          <option value="">-- choose booking --</option>
        </select>
      </label>

      <div id="modalBookingDetails" class="w-full border p-3 rounded bg-white text-gray-700" style="display:none"></div>

      <div class="flex items-center gap-3 mt-2">
        <button id="modalConfirm" class="cl-btn px-4 py-2 rounded">Confirm & Checkout</button>
        <button id="modalCancel" class="px-3 py-2 border rounded">Cancel</button>
      </div>

      <div id="modalResult" style="display:none;margin-top:12px;text-align:center"></div>
    </div>
  </div>
</div>

<script>
  // Modal helpers (toggle hidden class like booking modal)
  const modal = document.getElementById('checkoutModal');
  const modalClose = document.getElementById('checkoutClose');
  const modalCancel = document.getElementById('modalCancel');
  function openModal(){ modal.classList.remove('hidden'); setTimeout(()=>{ document.getElementById('modalBooking')?.focus(); },120); }
  function closeModal(){ modal.classList.add('hidden'); document.getElementById('modalResult').style.display='none'; }
  modalClose.addEventListener('click', closeModal); modalCancel.addEventListener('click', closeModal);
  modal.addEventListener('click', (e)=>{ if (e.target === modal) closeModal(); });

  // Override checkout button to open modal
  document.getElementById('checkoutSelected').addEventListener('click', async function(e){
    const selectedEls = Array.from(document.querySelectorAll('.cart-select:checked'));
    if (selectedEls.length === 0){ alert('Please select at least one item.'); return; }
    // populate selected list
    const list = document.getElementById('modalSelectedList'); list.innerHTML='';
    selectedEls.forEach(cb=>{
      const row = cb.closest('.cart-card');
      const name = row.querySelector('.font-semibold')?.textContent || 'Item';
      const qty = row.querySelector('.qty-value')?.textContent || '1';
      const unit = row.dataset.unitPrice || '0';
      const li = document.createElement('div'); li.textContent = name.trim() + ' — Qty: '+qty+' — LKR '+Number(unit).toLocaleString(); list.appendChild(li);
    });
    // fetch bookings
    const res = await fetch('bookings_list.php');
    if (res.ok){
      const data = await res.json();
      const sel = document.getElementById('modalBooking'); sel.innerHTML='<option value="">-- choose booking --</option>';
      data.forEach(b=>{ const o = document.createElement('option'); o.value=b.id; o.textContent = 'Booking #'+b.id+' — '+(b.booking_date||''); sel.appendChild(o); });
    }
    openModal();
  });

  // booking selection details
  document.getElementById('modalBooking').addEventListener('change', function(){
    const id = this.value; const details = document.getElementById('modalBookingDetails'); if (!id){ details.style.display='none'; details.innerHTML=''; return; }
    fetch('bookings_list.php').then(r=>r.json()).then(list=>{ const b = list.find(x=>String(x.id)===String(id)); if (b){ details.style.display='block'; details.innerHTML = '<strong>Booking #'+b.id+'</strong><br>Space: '+(b.space_name||b.space_type||'-')+'<br>Date: '+b.booking_date+'<br>Start: '+(b.start_time||'-')+' End: '+(b.end_time||'-')+'<br>User: '+b.username; } });
  });

  // Confirm action: send to checkout_process.php
  document.getElementById('modalConfirm').addEventListener('click', async function(){
    const selEls = Array.from(document.querySelectorAll('.cart-select:checked'));
    // ensure each checkbox has a value in the form 'cart:ID' or 'item:ID'
    selEls.forEach(cb=>{
      if (!cb.value || cb.value === ''){
        if (cb.dataset.cartId && cb.dataset.cartId !== '') cb.value = 'cart:'+cb.dataset.cartId;
        else cb.value = 'item:'+cb.dataset.itemId;
      }
    });
    const selected = selEls.map(cb=> cb.value);
    const booking_id = document.getElementById('modalBooking').value;
    if (!booking_id){ alert('Please select a booking'); return; }
    const fd = new FormData(); selected.forEach(s=> fd.append('selected[]', s)); fd.append('booking_id', booking_id);
    const resp = await fetch('checkout_process.php',{method:'POST',body:fd});
    // read response as text first (so we can show raw server errors), then parse JSON
    const txt = await resp.text();
    let json;
    try{
      json = JSON.parse(txt);
    } catch(e){
      alert('Server error:\n'+txt);
      console.error('Invalid JSON from checkout_process:', txt);
      return;
    }
    if (json.error){ alert(json.error); return; }
    // If server returned a receipt page, redirect there (it will show download link)
    if (json.receipt_page){
      closeModal();
      window.location.href = json.receipt_page;
      return;
    }
    const result = document.getElementById('modalResult'); result.style.display='block';
    if (json.pdf_url){ result.innerHTML = '<p>Success — receipt ready.</p><p><a href="'+json.pdf_url+'" target="_blank">Download PDF receipt</a></p>'; window.open(json.pdf_url,'_blank'); }
    else if (json.html){ const w = window.open('','_blank'); w.document.write(json.html); w.document.close(); w.print(); result.innerHTML = '<p>Success — opened printable receipt.</p>'; }
    // reload to refresh cart
    setTimeout(()=> location.reload(), 800);
  });
</script>
