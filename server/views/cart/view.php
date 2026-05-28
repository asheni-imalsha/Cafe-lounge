<?php require_once __DIR__ . '/../../views/partials/header.php'; ?>
<?php
require_once __DIR__ . '/../../src/auth.php';
$user_id = getCurrentUserId();
?>
<div class="max-w-6xl mx-auto mt-8 px-4 md:px-6">
  <div class="mb-8 pb-3 border-b" style="border-bottom-color:#e8e0d8">
    <h2 class="text-3xl font-bold tracking-tight" style="color:var(--espresso)">Your Cart</h2>
  </div>
  
  <?php if (empty($items)): ?>
    <div class="bg-white rounded-xl shadow-sm p-12 text-center">
      <div class="text-gray-500 text-lg">Your cart is empty.</div>
      <a href="menu.php" class="cl-btn inline-block mt-4 px-6 py-2 rounded-lg" style="background:var(--espresso);color:white;text-decoration:none">Browse Menu</a>
    </div>
  <?php else: ?>
    <div class="flex items-center justify-between mb-4 pb-2">
      <label class="inline-flex items-center gap-2 cursor-pointer">
        <input type="checkbox" id="selectAllCart" class="w-4 h-4" style="accent-color:var(--espresso)" />
        <span class="text-gray-700">Select all items</span>
      </label>
    </div>
    
    <div class="space-y-4">
      <?php foreach($items as $it):
        $img = !empty($it['image']) ? (filter_var($it['image'], FILTER_VALIDATE_URL) ? $it['image'] : 'uploads/'.ltrim($it['image'],'/')) : 'images/menu_placeholder.png';
        $cartRowId = $it['cart_id'] ?? null;
        $itemId = $it['item_id'] ?? ($it['id'] ?? null);
      ?>
      <div class="cart-card bg-white rounded-xl shadow-sm border p-4 flex flex-col sm:flex-row gap-4 items-center" style="border-color:#e8e0d8" data-unit-price="<?= (int)$it['price'] ?>">
        
        <!-- Checkbox -->
        <div class="flex-shrink-0">
          <input type="checkbox" class="cart-select w-5 h-5" style="accent-color:var(--espresso)" value="" data-cart-id="<?= htmlspecialchars($cartRowId) ?>" data-item-id="<?= htmlspecialchars($itemId) ?>" />
        </div>
        
        <!-- Image -->
        <div class="flex-shrink-0 w-24 h-24 rounded-lg overflow-hidden bg-gray-100">
          <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($it['name']) ?>" class="w-full h-full object-cover" />
        </div>
        
        <!-- Content -->
        <div class="flex-1 text-center sm:text-left">
          <div class="font-semibold text-lg" style="color:var(--espresso)"><?= htmlspecialchars($it['name']) ?></div>
          <div class="text-sm text-gray-500 mt-1">LKR <?= number_format($it['price'],0) ?> per item</div>
        </div>
        
        <!-- Quantity Controls -->
        <div class="flex items-center gap-3">
          <button class="qty-decrease w-8 h-8 rounded-full flex items-center justify-center transition-colors" data-cart-id="<?= htmlspecialchars($cartRowId) ?>" data-item-id="<?= htmlspecialchars($itemId) ?>" style="background:#f0ebe5;color:var(--espresso);border:none;cursor:pointer">−</button>
          <div class="qty-display">
            <span class="qty-value font-semibold text-lg min-w-[30px] text-center inline-block"><?= (int)$it['quantity'] ?></span>
          </div>
          <button class="qty-increase w-8 h-8 rounded-full flex items-center justify-center transition-colors" data-cart-id="<?= htmlspecialchars($cartRowId) ?>" data-item-id="<?= htmlspecialchars($itemId) ?>" style="background:var(--espresso);color:white;border:none;cursor:pointer">+</button>
        </div>
        
        <!-- Line Total & Remove -->
        <div class="text-right min-w-[120px]">
          <div class="font-bold text-lg" style="color:var(--espresso)">LKR <span class="line-total"><?= number_format($it['price'] * $it['quantity'],0) ?></span></div>
          <a href="cart.php?remove=<?= $cartRowId ? (int)$cartRowId : (int)$itemId ?>" class="text-sm inline-block mt-2" style="color:#c0392b;text-decoration:none">Remove</a>
        </div>
        
      </div>
      <?php endforeach; ?>
      
      <!-- Cart Summary -->
      <div class="bg-white rounded-xl shadow-sm border p-5 mt-6" style="border-color:#e8e0d8">
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
          <div class="text-lg font-bold" style="color:var(--espresso)">
            Total: LKR <span id="cartTotal" class="text-2xl"><?= number_format($total,0) ?></span>
          </div>
          <button type="button" id="checkoutSelected" class="cl-btn px-6 py-2.5 rounded-lg font-medium transition-all hover:shadow-md" style="background:var(--espresso);color:white;border:none;cursor:pointer">Proceed to Checkout</button>
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
        const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const res = await fetch('cart_update.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'id='+encodeURIComponent(idToSend)+'&quantity='+encodeURIComponent(qty)+'&csrf_token='+encodeURIComponent(token)});
        const json = await res.json();
        if (json.success){
          qtyEl.textContent = qty;
          const unitPrice = parseInt(row.dataset.unitPrice) || 0;
          const lineTotalEl = row.querySelector('.line-total');
          if (lineTotalEl) lineTotalEl.textContent = (unitPrice * qty).toLocaleString();
          const totalEl = document.getElementById('cartTotal');
          if (totalEl && typeof json.total !== 'undefined') totalEl.textContent = json.total.toLocaleString();
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
  document.querySelectorAll('.cart-select').forEach(cb=> cb.addEventListener('change', computeDisplayedTotal));

  function computeDisplayedTotal(){
    const totalEl = document.getElementById('cartTotal');
    if (!totalEl) return;
    const checked = Array.from(document.querySelectorAll('.cart-select:checked'));
    let sum = 0;
    if (checked.length === 0){
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

  computeDisplayedTotal();
</script>

<!-- Checkout Modal -->
<div id="checkoutModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50 p-6" style="backdrop-filter:blur(4px)">
  <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-2xl">
    <div class="flex items-center justify-between mb-5 pb-2 border-b" style="border-bottom-color:#e8e0d8">
      <h3 class="text-xl font-bold" style="color:var(--espresso)">Checkout</h3>
      <button id="checkoutClose" class="text-gray-400 hover:text-gray-600 transition-colors text-2xl leading-none">&times;</button>
    </div>

    <div class="space-y-4">
      <label class="block text-sm font-medium mb-1" style="color:var(--espresso)">Selected Items</label>
      <div id="modalSelectedList" class="w-full border rounded-lg p-3 max-h-48 overflow-auto" style="border-color:#e8e0d8;background:#faf8f5"></div>

      <label class="block text-sm font-medium mb-1" style="color:var(--espresso)">Select Booking</label>
      <select id="modalBooking" class="w-full border rounded-lg p-3 focus:outline-none focus:ring-2 transition-all" style="border-color:#d4c5b5">
        <option value="">-- choose booking --</option>
      </select>

      <div id="modalBookingDetails" class="w-full border rounded-lg p-3 bg-gray-50 text-gray-700" style="border-color:#e8e0d8;display:none"></div>

      <div class="flex items-center gap-3 pt-3">
        <button id="modalConfirm" class="cl-btn px-5 py-2.5 rounded-lg font-medium transition-all hover:shadow-md" style="background:var(--espresso);color:white;border:none;cursor:pointer">Confirm Checkout</button>
        <button id="modalCancel" class="px-5 py-2.5 rounded-lg font-medium transition-colors border" style="border-color:#d4c5b5;color:#5a4a3a;background:white;cursor:pointer">Cancel</button>
      </div>

      <div id="modalResult" style="display:none;margin-top:12px;text-align:center;padding:10px;border-radius:8px;background:#f0f7f0;color:#2e7d32"></div>
    </div>
  </div>
</div>

<script>
  const modal = document.getElementById('checkoutModal');
  const modalClose = document.getElementById('checkoutClose');
  const modalCancel = document.getElementById('modalCancel');
  function openModal(){ modal.classList.remove('hidden'); setTimeout(()=>{ document.getElementById('modalBooking')?.focus(); },120); }
  function closeModal(){ modal.classList.add('hidden'); document.getElementById('modalResult').style.display='none'; }
  if(modalClose) modalClose.addEventListener('click', closeModal);
  if(modalCancel) modalCancel.addEventListener('click', closeModal);
  if(modal) modal.addEventListener('click', (e)=>{ if (e.target === modal) closeModal(); });

  document.getElementById('checkoutSelected').addEventListener('click', async function(e){
    const selectedEls = Array.from(document.querySelectorAll('.cart-select:checked'));
    if (selectedEls.length === 0){ alert('Please select at least one item.'); return; }
    const list = document.getElementById('modalSelectedList'); 
    if(list) list.innerHTML='';
    selectedEls.forEach(cb=>{
      const row = cb.closest('.cart-card');
      const name = row.querySelector('.font-semibold')?.textContent || 'Item';
      const qty = row.querySelector('.qty-value')?.textContent || '1';
      const unit = row.dataset.unitPrice || '0';
      const li = document.createElement('div'); 
      li.textContent = name.trim() + '  •  Qty: ' + qty + '  •  LKR ' + Number(unit).toLocaleString(); 
      li.className = 'py-1 border-b last:border-0';
      li.style.borderBottomColor = '#e8e0d8';
      if(list) list.appendChild(li);
    });
    const res = await fetch('bookings_list.php');
    if (res.ok){
      const data = await res.json();
      const sel = document.getElementById('modalBooking'); 
      if(sel) {
        sel.innerHTML='<option value="">-- choose booking --</option>';
        data.forEach(b=>{ const o = document.createElement('option'); o.value=b.id; o.textContent = 'Booking #'+b.id+' — '+(b.booking_date||''); sel.appendChild(o); });
      }
    }
    openModal();
  });

  document.getElementById('modalBooking').addEventListener('change', function(){
    const id = this.value; 
    const details = document.getElementById('modalBookingDetails'); 
    if (!id){ 
      if(details) details.style.display='none'; 
      if(details) details.innerHTML=''; 
      return; 
    }
    fetch('bookings_list.php').then(r=>r.json()).then(list=>{ 
      const b = list.find(x=>String(x.id)===String(id)); 
      if (b && details){ 
        details.style.display='block'; 
        details.innerHTML = '<div class="space-y-1"><strong style="color:var(--espresso)">Booking #'+b.id+'</strong><br>Space: '+(b.space_name||b.space_type||'-')+'<br>Date: '+b.booking_date+'<br>Time: '+(b.start_time||'-')+' to '+(b.end_time||'-')+'<br>User: '+b.username+'</div>'; 
      } 
    });
  });

  document.getElementById('modalConfirm').addEventListener('click', async function(){
    const selEls = Array.from(document.querySelectorAll('.cart-select:checked'));
    selEls.forEach(cb=>{
      if (!cb.value || cb.value === ''){
        if (cb.dataset.cartId && cb.dataset.cartId !== '') cb.value = 'cart:'+cb.dataset.cartId;
        else cb.value = 'item:'+cb.dataset.itemId;
      }
    });
    const selected = selEls.map(cb=> cb.value);
    const booking_id = document.getElementById('modalBooking').value;
    if (!booking_id){ alert('Please select a booking'); return; }
    const fd = new FormData(); 
    selected.forEach(s=> fd.append('selected[]', s)); 
    fd.append('booking_id', booking_id);
    const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
    fd.append('csrf_token', token);
    const resp = await fetch('checkout_process.php',{method:'POST',body:fd});
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
    if (json.receipt_page){
      closeModal();
      window.location.href = json.receipt_page;
      return;
    }
    const result = document.getElementById('modalResult'); 
    if(result) result.style.display='block';
    if (json.pdf_url && result){ 
      result.innerHTML = '<p>Success - receipt ready.</p><p><a href="'+json.pdf_url+'" target="_blank" style="color:var(--espresso)">Download PDF Receipt</a></p>'; 
      window.open(json.pdf_url,'_blank'); 
    }
    else if (json.html && result){ 
      const w = window.open('','_blank'); 
      w.document.write(json.html); 
      w.document.close(); 
      w.print(); 
      result.innerHTML = '<p>Success - opened printable receipt.</p>'; 
    }
    setTimeout(()=> location.reload(), 800);
  });
</script>