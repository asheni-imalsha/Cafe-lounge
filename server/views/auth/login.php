<?php require_once __DIR__ . '/../partials/header.php'; ?>
<div class="max-w-3xl mx-auto mt-12 grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
  <div class="hidden md:block rounded-lg overflow-hidden shadow-lg">
    <img src="https://images.pexels.com/photos/15800347/pexels-photo-15800347.jpeg" alt="Welcome" style="width:100%;height:100%;object-fit:cover;min-height:400px" />
  </div>

  <div class="p-6 bg-white rounded-lg shadow">
    <h2 class="text-2xl font-semibold mb-2" style="color:var(--espresso)">Welcome back</h2>
    <p class="text-sm mb-4" style="color:var(--latte)">Sign in to access your bookings and cart.</p>

    <?php if (!empty($errors)): ?>
      <div class="mb-4 text-red-600">
        <?php foreach($errors as $e) echo '<div>'.htmlspecialchars($e).'</div>'; ?>
      </div>
    <?php endif; ?>

    <form method="post" action="" class="space-y-4">
      <div>
        <label class="block text-sm mb-1">Username or Email</label>
        <input name="username" required class="w-full border p-3 rounded focus:outline-none" placeholder="ash@gmail.com or Asheni" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
      </div>

      <div>
        <label class="block text-sm mb-1">Password</label>
        <div class="relative">
          <input id="pw" type="password" name="password" required class="w-full border p-3 rounded pr-12" placeholder="Your password">
          <button type="button" id="togglePw" class="absolute right-2 top-2 text-sm" style="background:transparent;border:none;color:var(--latte)">Show</button>
        </div>
      </div>

      <div class="flex items-center justify-between">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember"> Remember me</label>
        <a href="#" class="text-sm nav-link">Forgot?</a>
      </div>

      <div class="flex items-center gap-3">
        <button class="cl-btn px-5 py-2 rounded font-medium">Login</button>
        <a href="register.php" class="text-sm nav-link">Create an account</a>
      </div>

      <!-- social login buttons removed (not implemented) -->
    </form>
  </div>
</div>

<script>
document.getElementById('togglePw')?.addEventListener('click', function(){
  var pw = document.getElementById('pw');
  if (!pw) return;
  if (pw.type === 'password'){ pw.type = 'text'; this.textContent = 'Hide'; }
  else { pw.type = 'password'; this.textContent = 'Show'; }
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
