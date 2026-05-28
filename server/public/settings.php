<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../app/models/User.php';

requireLogin();

$userId = getCurrentUserId();
$userModel = new User();
$user = $userModel->findById($userId);

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // CSRF protection
  if (!validateCsrfToken($_POST['csrf_token'] ?? '')){
    $errors[] = 'Invalid CSRF token.';
  }
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_username') {
        $newUsername = trim($_POST['new_username'] ?? '');
        
        if (empty($newUsername)) {
            $errors[] = 'Username cannot be empty.';
        } elseif ($newUsername === $user['username']) {
            $errors[] = 'New username is the same as current username.';
        } elseif ($userModel->usernameExists($newUsername, $userId)) {
            $errors[] = 'Username already taken.';
        } elseif (strlen($newUsername) < 3) {
            $errors[] = 'Username must be at least 3 characters long.';
        } elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $newUsername)) {
            $errors[] = 'Username can only contain letters, numbers, underscores, and hyphens.';
        } else {
            if ($userModel->updateUsername($userId, $newUsername)) {
                $_SESSION['username'] = $newUsername;
                $user['username'] = $newUsername;
                $success = 'Username updated successfully!';
            } else {
                $errors[] = 'Failed to update username. Please try again.';
            }
        }
    } 
    elseif ($action === 'update_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($currentPassword)) {
            $errors[] = 'Please enter your current password.';
        } elseif (!$userModel->verifyPassword($userId, $currentPassword)) {
            $errors[] = 'Current password is incorrect.';
        } elseif (empty($newPassword)) {
            $errors[] = 'New password cannot be empty.';
        } elseif (strlen($newPassword) < 6) {
            $errors[] = 'New password must be at least 6 characters long.';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'New passwords do not match.';
        } elseif ($currentPassword === $newPassword) {
            $errors[] = 'New password must be different from current password.';
        } else {
            $newPasswordHash = password_hash($newPassword, PASSWORD_BCRYPT);
            if ($userModel->updatePassword($userId, $newPasswordHash)) {
                $success = 'Password updated successfully!';
            } else {
                $errors[] = 'Failed to update password. Please try again.';
            }
        }
    }
    elseif ($action === 'update_profile') {
        $newName = trim($_POST['name'] ?? '');
        $newEmail = trim($_POST['email'] ?? '');
        
        if (empty($newName)) {
            $errors[] = 'Name cannot be empty.';
        } elseif (empty($newEmail)) {
            $errors[] = 'Email cannot be empty.';
        } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        } elseif ($newEmail !== $user['email'] && $userModel->emailExists($newEmail, $userId)) {
            $errors[] = 'Email already registered.';
        } else {
            if ($userModel->updateProfile($userId, $newName, $newEmail)) {
                $user['name'] = $newName;
                $user['email'] = $newEmail;
                $success = 'Profile updated successfully!';
            } else {
                $errors[] = 'Failed to update profile. Please try again.';
            }
        }
    }
}

require_once __DIR__ . '/../views/partials/header.php';
?>

<div class="max-w-6xl mx-auto mt-8 mb-12 px-4 md:px-6">
  <div class="mb-8 pb-3 border-b" style="border-bottom-color:#e8e0d8">
    <h1 class="text-3xl font-bold tracking-tight" style="color:var(--espresso)">Account Settings</h1>
  </div>
  
  <?php if (!empty($errors)): ?>
    <div class="mb-6 p-4 rounded-lg border-l-4" style="background:#fef2f0;border-left-color:#c0392b;">
      <?php foreach ($errors as $error): ?>
        <div class="mb-1 text-sm" style="color:#c0392b"><?= htmlspecialchars($error) ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  
  <?php if (!empty($success)): ?>
    <div class="mb-6 p-4 rounded-lg border-l-4" style="background:#e8f5e9;border-left-color:#2e7d32;">
      <div class="text-sm" style="color:#2e7d32"><?= htmlspecialchars($success) ?></div>
    </div>
  <?php endif; ?>
  
  <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
    <!-- Sidebar Menu -->
    <div class="md:col-span-1">
      <div class="bg-white rounded-xl shadow-sm border overflow-hidden" style="border-color:#e8e0d8">
        <nav class="flex flex-col">
          <a href="#profile" class="tab-link flex items-center gap-3 px-5 py-3 transition-all border-b" style="border-bottom-color:#f0ebe5;color:var(--espresso);text-decoration:none" data-tab="profile">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--latte)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            <span>Profile Information</span>
          </a>
          <a href="#username" class="tab-link flex items-center gap-3 px-5 py-3 transition-all border-b" style="border-bottom-color:#f0ebe5;color:var(--espresso);text-decoration:none" data-tab="username">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--latte)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>Change Username</span>
          </a>
          <a href="#password" class="tab-link flex items-center gap-3 px-5 py-3 transition-all" style="color:var(--espresso);text-decoration:none" data-tab="password">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--latte)"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            <span>Change Password</span>
          </a>
        </nav>
      </div>
    </div>
    
    <!-- Main Content -->
    <div class="md:col-span-3">
      <!-- Profile Information Tab -->
      <div id="profile" class="tab-content">
        <div class="bg-white rounded-xl shadow-sm border p-6 md:p-8" style="border-color:#e8e0d8">
          <h2 class="text-2xl font-bold mb-6" style="color:var(--espresso)">Profile Information</h2>
          
          <div class="mb-6 p-5 rounded-lg" style="background:#faf8f5">
            <div class="flex items-center justify-between flex-wrap gap-3">
              <div>
                <span class="text-xs uppercase tracking-wide font-semibold" style="color:var(--latte)">Member Since</span>
                <p class="text-base font-medium mt-1" style="color:var(--espresso)"><?= date('F d, Y', strtotime($user['created_at'] ?? date('Y-m-d'))) ?></p>
              </div>
              <div>
                <span class="text-xs uppercase tracking-wide font-semibold" style="color:var(--latte)">Username</span>
                <p class="text-base font-medium mt-1" style="color:var(--espresso)"><?= htmlspecialchars($user['username']) ?></p>
              </div>
            </div>
          </div>
          
          <form method="POST" class="space-y-5">
            <?php echo csrfInputField(); ?>
            <input type="hidden" name="action" value="update_profile">
            
            <div>
              <label class="block text-sm font-semibold mb-2" style="color:var(--espresso)">Full Name</label>
              <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 transition-all" style="border-color:#d4c5b5" placeholder="Your full name">
            </div>
            
            <div>
              <label class="block text-sm font-semibold mb-2" style="color:var(--espresso)">Email Address</label>
              <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 transition-all" style="border-color:#d4c5b5" placeholder="your@email.com">
            </div>
            
            <div class="pt-3">
              <button type="submit" class="cl-btn px-6 py-2.5 rounded-lg font-medium transition-all hover:shadow-md" style="background:var(--espresso);color:white;border:none;cursor:pointer">Save Profile</button>
            </div>
          </form>
        </div>
      </div>
      
      <!-- Change Username Tab -->
      <div id="username" class="tab-content hidden">
        <div class="bg-white rounded-xl shadow-sm border p-6 md:p-8" style="border-color:#e8e0d8">
          <h2 class="text-2xl font-bold mb-6" style="color:var(--espresso)">Change Username</h2>
          
          <div class="mb-6 p-4 rounded-lg" style="background:#faf8f5">
            <p class="text-sm" style="color:#5a4a3a">Current username: <strong style="color:var(--espresso)"><?= htmlspecialchars($user['username']) ?></strong></p>
          </div>
          
          <form method="POST" class="space-y-5">
            <?php echo csrfInputField(); ?>
            <input type="hidden" name="action" value="update_username">
            
            <div>
              <label class="block text-sm font-semibold mb-2" style="color:var(--espresso)">New Username</label>
              <input type="text" name="new_username" required class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 transition-all" style="border-color:#d4c5b5" placeholder="Enter new username" pattern="[a-zA-Z0-9_-]{3,}" minlength="3">
              <p class="text-xs mt-2" style="color:#8b735f">Only letters, numbers, underscores and hyphens. Minimum 3 characters.</p>
            </div>
            
            <div class="pt-3">
              <button type="submit" class="cl-btn px-6 py-2.5 rounded-lg font-medium transition-all hover:shadow-md" style="background:var(--espresso);color:white;border:none;cursor:pointer">Update Username</button>
            </div>
          </form>
        </div>
      </div>
      
      <!-- Change Password Tab -->
      <div id="password" class="tab-content hidden">
        <div class="bg-white rounded-xl shadow-sm border p-6 md:p-8" style="border-color:#e8e0d8">
          <h2 class="text-2xl font-bold mb-6" style="color:var(--espresso)">Change Password</h2>
          
          <form method="POST" class="space-y-5">
            <?php echo csrfInputField(); ?>
            <input type="hidden" name="action" value="update_password">
            
            <div>
              <label class="block text-sm font-semibold mb-2" style="color:var(--espresso)">Current Password</label>
              <input type="password" name="current_password" required class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 transition-all" style="border-color:#d4c5b5" placeholder="Enter your current password">
            </div>
            
            <div>
              <label class="block text-sm font-semibold mb-2" style="color:var(--espresso)">New Password</label>
              <input type="password" name="new_password" required class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 transition-all" style="border-color:#d4c5b5" placeholder="Enter new password" minlength="6">
              <p class="text-xs mt-2" style="color:#8b735f">Minimum 6 characters.</p>
            </div>
            
            <div>
              <label class="block text-sm font-semibold mb-2" style="color:var(--espresso)">Confirm New Password</label>
              <input type="password" name="confirm_password" required class="w-full border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 transition-all" style="border-color:#d4c5b5" placeholder="Confirm new password" minlength="6">
            </div>
            
            <div class="pt-3">
              <button type="submit" class="cl-btn px-6 py-2.5 rounded-lg font-medium transition-all hover:shadow-md" style="background:var(--espresso);color:white;border:none;cursor:pointer">Update Password</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  // Tab switching
  document.querySelectorAll('.tab-link').forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      const tabName = this.dataset.tab;
      
      // Remove active state from all links
      document.querySelectorAll('.tab-link').forEach(l => {
        l.style.background = 'transparent';
        l.style.fontWeight = 'normal';
        l.classList.remove('active');
      });
      
      // Hide all tabs
      document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
      });
      
      // Activate clicked tab
      this.style.background = 'var(--sand)';
      this.style.fontWeight = '600';
      this.classList.add('active');
      document.getElementById(tabName).classList.remove('hidden');
    });
  });
  
  // Set active tab based on URL hash
  if (window.location.hash) {
    const hash = window.location.hash.substring(1);
    const targetLink = document.querySelector(`.tab-link[data-tab="${hash}"]`);
    if (targetLink) {
      targetLink.click();
    }
  }
</script>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>