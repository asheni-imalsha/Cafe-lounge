  </main>
  <footer class="mt-8" style="background:var(--sand);color:var(--espresso);">
    <div class="max-w-5xl mx-auto px-4 py-6 text-center">
      <div class="mb-3">
        <a href="about.php" class="mx-2 nav-link">About</a>
        <a href="contact.php" class="mx-2 nav-link">Contact</a>
        <a href="menu.php" class="mx-2 nav-link">Menu</a>
      </div>
      <div class="text-sm">&copy; <?php echo date('Y'); ?> Cafe Lounge — All rights reserved.</div>
    </div>
  </footer>

  <script>
    // Profile dropdown toggle
    (function(){
      const btn = document.getElementById('profileBtn');
      const menu = document.getElementById('profileMenu');
      if (!btn || !menu) return;
      btn.addEventListener('click', (e)=>{
        e.stopPropagation();
        menu.classList.toggle('hidden');
      });
      document.addEventListener('click', ()=> menu.classList.add('hidden'));
    })();
    // Enhanced scroll reveal for elements with .reveal
    (function(){
      const reveals = document.querySelectorAll('.reveal');
      const io = new IntersectionObserver((entries)=>{
        entries.forEach(entry=>{
          const el = entry.target;
          if (entry.isIntersecting) {
            el.style.opacity = 1;
            el.style.transform = 'translateY(0) scale(1)';
            el.classList.add('card-hover');
            io.unobserve(el);
          }
        });
      },{threshold:0.08});
      reveals.forEach(r=>{ r.style.opacity=0; r.style.transform='translateY(12px) scale(.99)'; io.observe(r); });
    })();

    // (dark mode removed)

    // Micro interactions for links/buttons
    (function(){
      document.querySelectorAll('a, button').forEach(el=>{
        el.addEventListener('mouseenter', ()=> el.style.transform = 'translateY(-2px)');
        el.addEventListener('mouseleave', ()=> el.style.transform = 'translateY(0)');
      });
    })();
  </script>
</body>
</html>
