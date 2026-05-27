<?php require_once __DIR__ . '/../partials/header.php'; ?>
<div class="max-w-3xl mx-auto mt-12 grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
  <div class="p-6 bg-white rounded-lg shadow">
    <h2 class="text-2xl font-semibold mb-2" style="color:var(--espresso)">Create your account</h2>
    <p class="text-sm mb-4" style="color:var(--latte)">Join Cafe Lounge to save bookings and access your cart from any device.</p>

    <?php if (!empty($errors)): ?>
      <div class="mb-4 text-red-600">
        <?php foreach($errors as $e) echo '<div>'.htmlspecialchars($e).'</div>'; ?>
      </div>
    <?php endif; ?>

    <form method="post" action="" class="space-y-4">
      <div>
        <label class="block text-sm mb-1">Username</label>
        <input name="username" required class="w-full border p-3 rounded" placeholder="Asheni" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
      </div>

      <div>
        <label class="block text-sm mb-1">Full name</label>
        <input name="name" class="w-full border p-3 rounded" placeholder="Your full name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
      </div>

      <div>
        <label class="block text-sm mb-1">Email</label>
        <input name="email" type="email" required class="w-full border p-3 rounded" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>

      <div>
        <label class="block text-sm mb-1">Password</label>
        <div class="relative">
          <input id="pw_reg" type="password" name="password" required class="w-full border p-3 rounded pr-12" placeholder="Choose a strong password">
          <button type="button" id="togglePwReg" class="absolute right-2 top-2 text-sm" style="background:transparent;border:none;color:var(--latte)">Show</button>
        </div>
        <div class="text-xs mt-1" style="color:var(--latte)">Use at least 6 characters. We'll hash your password securely.</div>
      </div>

      <div class="flex items-center gap-3">
        <button class="cl-btn px-5 py-2 rounded font-medium">Register</button>
        <a href="login.php" class="text-sm nav-link">Already have an account?</a>
      </div>
    </form>
  </div>

  <div class="hidden md:block rounded-lg overflow-hidden shadow-lg">
    <img src="https://images.pexels.com/photos/15800347/pexels-photo-15800347.jpeg" alt="Welcome" style="width:100%;height:100%;object-fit:cover;min-height:400px" />
  </div>
</div>

<script>
document.getElementById('togglePwReg')?.addEventListener('click', function(){
  var pw = document.getElementById('pw_reg');
  if (!pw) return;
  if (pw.type === 'password'){ pw.type = 'text'; this.textContent = 'Hide'; }
  else { pw.type = 'password'; this.textContent = 'Show'; }
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
