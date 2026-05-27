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
      <button id="checkoutSelected" class="cl-btn px-4 py-2" style="border-radius:10px">Checkout selected</button>
    </div>
    <div class="grid grid-cols-1 gap-6">
      <?php foreach($items as $it):
        $img = !empty($it['image']) ? (filter_var($it['image'], FILTER_VALIDATE_URL) ? $it['image'] : 'uploads/'.ltrim($it['image'],'/')) : 'images/menu_placeholder.png';
        $cartRowId = $it['cart_id'] ?? null;
        $itemId = $it['item_id'] ?? ($it['id'] ?? null);
      ?>
      <div class="cart-card" data-unit-price="<?= (int)$it['price'] ?>">
        <div style="flex:0 0 40px;display:flex;align-items:center;justify-content:center">
          <input type="checkbox" class="cart-select" data-cart-id="<?= htmlspecialchars($cartRowId) ?>" data-item-id="<?= htmlspecialchars($itemId) ?>" />
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
      <div style="max-width:900px;margin:0 auto;text-align:right;font-weight:700">Total: LKR <?= number_format($total,0) ?></div>
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
          if (lineTotalEl) lineTotalEl.textContent = (unitPrice * qty).toString();
          // update total display
          const totalEl = document.getElementById('cartTotal');
          if (totalEl && typeof json.total !== 'undefined') totalEl.textContent = json.total;
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
    });
  }
  if (checkoutBtn){
    checkoutBtn.addEventListener('click', ()=>{
      const selected = Array.from(document.querySelectorAll('.cart-select:checked')).map(cb=>{
        const cid = cb.dataset.cartId || '';
        const iid = cb.dataset.itemId || '';
        if (cid) return 'cart:'+cid;
        return 'item:'+iid;
      });
      if (selected.length === 0){ alert('Please select at least one item.'); return; }
      // create and submit form
      const f = document.createElement('form'); f.method='POST'; f.action='checkout.php';
      selected.forEach(s=>{ const i = document.createElement('input'); i.type='hidden'; i.name='selected[]'; i.value = s; f.appendChild(i); });
      document.body.appendChild(f); f.submit();
    });
  }
</script>
