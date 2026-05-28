<?php require_once __DIR__ . '/../partials/header.php'; ?>
<div class="max-w-7xl mx-auto mt-8 px-4 md:px-6">
  <div class="flex items-center justify-between mb-8 pb-2 border-b" style="border-bottom-color: rgba(0,0,0,0.08)">
    <h1 class="text-3xl font-bold tracking-tight" style="color:var(--espresso)">Menu</h1>
  </div>
  
  <div style="max-width:1200px;margin:0 auto;display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:30px;align-items:stretch">
    <?php if (empty($items)): ?>
      <div class="p-8 bg-white rounded-xl text-center" style="color:var(--latte);grid-column:1/-1">No menu items found.</div>
    <?php else: foreach($items as $it): ?>
      <div class="glass-card rounded-xl overflow-hidden transition-all duration-300 hover:shadow-xl" style="background:white;display:flex;flex-direction:column;height:100%">
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
        
        <!-- Image Section -->
        <div style="height:200px;overflow:hidden;background:#f5f2ef;position:relative">
          <?php if ($imgSrc): ?>
            <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($it['name']) ?>" style="width:100%;height:100%;object-fit:cover;display:block" />
          <?php else: ?>
            <div style="height:100%;display:flex;align-items:center;justify-content:center;color:#b8a99a;background:linear-gradient(135deg, #faf7f2, #f0ebe4);font-size:14px;letter-spacing:0.5px">No image</div>
          <?php endif; ?>
        </div>
        
        <!-- Content Section -->
        <div style="padding:20px 20px 16px 20px;flex:1;display:flex;flex-direction:column">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">
            <div class="font-semibold text-lg" style="color:var(--espresso);line-height:1.3"><?= htmlspecialchars($it['name']) ?></div>
            <div class="text-base font-bold" style="color:var(--espresso);white-space:nowrap;margin-left:12px">LKR <?= number_format($it['price'],0) ?></div>
          </div>
          
          <?php if (!empty($it['description'])): ?>
            <div class="text-sm" style="color:#6b5b4f;line-height:1.5;margin-bottom:16px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical"><?= htmlspecialchars($it['description']) ?></div>
          <?php else: ?>
            <div style="flex:1"></div>
          <?php endif; ?>
        </div>
        
        <!-- Button Section -->
        <div style="padding:0 20px 20px 20px">
          <button data-id="<?= (int)$it['id'] ?>" class="add-to-cart w-full py-2.5 rounded-lg font-medium transition-colors duration-200" style="background:var(--espresso);color:white;border:none;cursor:pointer">Add to Cart</button>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<!-- Add-to-cart confirmation modal -->
<div id="cartModalOverlay" style="display:none;position:fixed;inset:0;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);z-index:9999;backdrop-filter:blur(4px)">
  <div style="background:white;padding:28px 24px;border-radius:16px;max-width:400px;width:90%;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25)">
    <div style="font-weight:700;color:var(--espresso);font-size:20px;margin-bottom:8px">Added to Cart</div>
    <div id="cartModalMsg" style="color:#5a4a3a;margin-bottom:24px;line-height:1.5">Item added to your cart. Would you like to view your cart now?</div>
    <div style="display:flex;gap:12px;justify-content:flex-end">
      <button id="cartModalContinue" class="px-5 py-2 rounded-lg font-medium transition-colors" style="background:transparent;border:1px solid #d4c5b5;color:#5a4a3a;cursor:pointer">Continue Shopping</button>
      <button id="cartModalView" class="px-5 py-2 rounded-lg font-medium transition-colors" style="background:var(--espresso);color:white;border:none;cursor:pointer">View Cart</button>
    </div>
  </div>
</div>

<script>
  document.querySelectorAll('.add-to-cart').forEach(btn=>{
    btn.addEventListener('click', async function(){
      const id = this.dataset.id;
      const originalText = this.textContent;
      this.textContent = 'Adding...';
      this.style.opacity = '0.7';
      
      try{
        const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const res = await fetch('cart_add.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'item_id='+encodeURIComponent(id)+'&quantity=1&csrf_token='+encodeURIComponent(token)});
        const json = await res.json();
        if (json.success){
          const badge = document.getElementById('cartCountBadge');
          if (badge) badge.textContent = (parseInt(badge.textContent||'0') + 1).toString();
          showCartModal();
        } else if (json.error){ alert(json.error); }
      } catch(err){ console.error(err); alert('Error adding to cart'); }
      finally {
        this.textContent = originalText;
        this.style.opacity = '1';
      }
    });
  });

  function showCartModal(){
    const overlay = document.getElementById('cartModalOverlay');
    const viewBtn = document.getElementById('cartModalView');
    const contBtn = document.getElementById('cartModalContinue');
    if (!overlay) return;
    overlay.style.display = 'flex';
    
    const newView = viewBtn.cloneNode(true);
    const newCont = contBtn.cloneNode(true);
    viewBtn.parentNode.replaceChild(newView, viewBtn);
    contBtn.parentNode.replaceChild(newCont, contBtn);
    
    newView.addEventListener('click', ()=>{ window.location = 'cart.php'; });
    newCont.addEventListener('click', ()=>{ overlay.style.display = 'none'; });
  }
</script>