<?php require_once __DIR__ . '/../partials/header.php'; ?>
<div class="max-w-7xl mx-auto mt-8">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold" style="color:var(--espresso)">Menu</h1>
  </div>
  <div style="max-width:1200px;margin:0 auto;display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:28px;align-items:stretch;justify-items:stretch">
    <?php if (empty($items)): ?>
      <div class="p-6 bg-white rounded">No menu items found.</div>
    <?php else: foreach($items as $it): ?>
      <div class="glass-card p-6 rounded-lg" style="height:420px;display:flex;flex-direction:column;justify-content:space-between;width:100%">
        <?php
          $image = isset($it['image']) ? trim($it['image']) : '';
          $imgSrc = '';
          if ($image !== ''){
            if (filter_var($image, FILTER_VALIDATE_URL) || strpos($image, '//') === 0 || strpos($image, '/') === 0) {
              $imgSrc = $image;
            } else {
              $imgSrc = 'uploads/' . ltrim($image, '/');
            }
          }
        ?>
        <div style="height:160px;overflow:hidden;border-radius:12px;margin-bottom:16px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.6);flex-shrink:0">
          <?php if ($imgSrc): ?>
            <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($it['name']) ?>" style="width:100%;height:100%;object-fit:cover;display:block" />
          <?php else: ?>
            <div style="width:80%;height:80%;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#9a8f85;background:linear-gradient(180deg, #fff, rgba(255,255,255,0.9));box-shadow:inset 0 6px 16px rgba(0,0,0,0.04)">No image</div>
          <?php endif; ?>
        </div>
        <div style="flex:1;display:flex;flex-direction:column">
          <div class="font-semibold text-lg" style="color:var(--espresso);margin-bottom:4px"><?= htmlspecialchars($it['name']) ?></div>
          <div class="text-sm" style="color:var(--latte);font-weight:600;margin-bottom:8px">LKR <?= number_format($it['price'],0) ?></div>
          <?php if (!empty($it['description'])): ?>
            <div class="text-sm text-gray-600" style="line-height:1.4;opacity:0.85;overflow:hidden;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;flex:1"><?= htmlspecialchars($it['description']) ?></div>
          <?php endif; ?>
        </div>
        <div class="mt-4" style="display:flex;justify-content:flex-start;flex-shrink:0">
          <button data-id="<?= (int)$it['id'] ?>" class="add-to-cart cl-btn px-4 py-2 rounded">Add to cart</button>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<!-- Add-to-cart confirmation modal -->
<div id="cartModalOverlay" style="display:none;position:fixed;inset:0;align-items:center;justify-content:center;background:rgba(0,0,0,0.35);z-index:9999">
  <div style="background:var(--ivory);padding:20px;border-radius:12px;max-width:420px;width:92%;box-shadow:0 18px 40px rgba(0,0,0,0.18);">
    <div style="font-weight:700;color:var(--espresso);font-size:18px;margin-bottom:8px">Added to cart</div>
    <div id="cartModalMsg" style="color:rgba(58,45,40,0.9);margin-bottom:16px">Item added to your cart. Would you like to view your cart now?</div>
    <div style="display:flex;gap:10px;justify-content:flex-end">
      <button id="cartModalContinue" class="px-4 py-2" style="background:transparent;border:1px solid var(--sand);border-radius:8px">Continue shopping</button>
      <button id="cartModalView" class="px-4 py-2 cl-btn" style="border-radius:8px">View cart</button>
    </div>
  </div>
</div>

<script>
  document.querySelectorAll('.add-to-cart').forEach(btn=>{
    btn.addEventListener('click', async function(){
      const id = this.dataset.id;
      try{
        const res = await fetch('cart_add.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'item_id='+encodeURIComponent(id)+'&quantity=1'});
        const json = await res.json();
        if (json.success){
          const badge = document.getElementById('cartCountBadge');
          if (badge) badge.textContent = (parseInt(badge.textContent||'0') + 1).toString();
          // show modal
          showCartModal();
        } else if (json.error){ alert(json.error); }
      } catch(err){ console.error(err); alert('Error adding to cart'); }
    });
  });

  function showCartModal(){
    const overlay = document.getElementById('cartModalOverlay');
    const viewBtn = document.getElementById('cartModalView');
    const contBtn = document.getElementById('cartModalContinue');
    if (!overlay) return;
    overlay.style.display = 'flex';
    // remove previous handlers by cloning
    viewBtn.replaceWith(viewBtn.cloneNode(true));
    contBtn.replaceWith(contBtn.cloneNode(true));
    const newView = document.getElementById('cartModalView');
    const newCont = document.getElementById('cartModalContinue');
    newView.addEventListener('click', ()=>{ window.location = 'cart.php'; });
    newCont.addEventListener('click', ()=>{ overlay.style.display = 'none'; });
  }
</script>
