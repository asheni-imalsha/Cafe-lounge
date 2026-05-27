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

<div class="max-w-3xl mx-auto mt-8 mb-8 px-4">
  <h1 class="text-3xl font-bold mb-8" style="color:var(--espresso)">Account Settings</h1>
  
  <?php if (!empty($errors)): ?>
    <div class="mb-6 p-4 rounded-lg" style="background:#fee;border-left:4px solid #c33;color:#833">
      <?php foreach ($errors as $error): ?>
        <div class="mb-2"><?= htmlspecialchars($error) ?></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  
  <?php if (!empty($success)): ?>
    <div class="mb-6 p-4 rounded-lg" style="background:#efe;border-left:4px solid #3c3;color:#383">
      <?= htmlspecialchars($success) ?>
    </div>
  <?php endif; ?>
  
  <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
    <!-- Sidebar Menu -->
    <div>
      <div class="glass-card p-4 reveal">
        <nav class="space-y-2">
          <a href="#profile" class="block px-4 py-2 rounded-lg transition-smooth tab-link active" data-tab="profile" style="background:var(--sand);color:var(--espresso);font-weight:600">Profile Information</a>
          <a href="#username" class="block px-4 py-2 rounded-lg transition-smooth tab-link" data-tab="username" style="color:var(--espresso)">Change Username</a>
          <a href="#password" class="block px-4 py-2 rounded-lg transition-smooth tab-link" data-tab="password" style="color:var(--espresso)">Change Password</a>
        </nav>
      </div>
    </div>
    
    <!-- Main Content -->
    <div class="md:col-span-2">
      <!-- Profile Information Tab -->
      <div id="profile" class="tab-content reveal">
        <div class="glass-card p-8">
          <h2 class="text-2xl font-semibold mb-6" style="color:var(--espresso)">Profile Information</h2>
          
          <div class="mb-8 p-6 rounded-lg" style="background:var(--sand);opacity:0.5">
            <div class="mb-4">
              <span class="font-semibold text-sm" style="color:var(--latte)">Member Since</span>
              <p class="text-lg" style="color:var(--espresso)"><?= date('F d, Y', strtotime($user['created_at'] ?? now())) ?></p>
            </div>
          </div>
          
          <form method="POST" class="space-y-5">
            <input type="hidden" name="action" value="update_profile">
            
            <label class="block">
              <span class="font-semibold mb-3 block text-sm" style="color:var(--espresso)">Full Name</span>
              <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required class="w-full border-2 p-3 rounded-lg focus:outline-none transition-colors" style="border-color:var(--sand)" placeholder="Your full name">
            </label>
            
            <label class="block">
              <span class="font-semibold mb-3 block text-sm" style="color:var(--espresso)">Email Address</span>
              <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required class="w-full border-2 p-3 rounded-lg focus:outline-none transition-colors" style="border-color:var(--sand)" placeholder="your@email.com">
            </label>
            
            <div class="pt-4">
              <button type="submit" class="cl-btn px-6 py-3 rounded-lg font-semibold transition-smooth">Save Profile</button>
            </div>
          </form>
        </div>
      </div>
      
      <!-- Change Username Tab -->
      <div id="username" class="tab-content hidden reveal">
        <div class="glass-card p-8">
          <h2 class="text-2xl font-semibold mb-6" style="color:var(--espresso)">Change Username</h2>
          
          <div class="mb-6 p-4 rounded-lg" style="background:var(--sand);opacity:0.3">
            <p class="text-sm text-gray-700">Current username: <strong><?= htmlspecialchars($user['username']) ?></strong></p>
          </div>
          
          <form method="POST" class="space-y-5">
            <input type="hidden" name="action" value="update_username">
            
            <label class="block">
              <span class="font-semibold mb-3 block text-sm" style="color:var(--espresso)">New Username</span>
              <input type="text" name="new_username" required class="w-full border-2 p-3 rounded-lg focus:outline-none transition-colors" style="border-color:var(--sand)" placeholder="Enter new username" pattern="[a-zA-Z0-9_-]{3,}" minlength="3">
              <p class="text-xs text-gray-600 mt-2">Only letters, numbers, underscores and hyphens. Minimum 3 characters.</p>
            </label>
            
            <div class="pt-4">
              <button type="submit" class="cl-btn px-6 py-3 rounded-lg font-semibold transition-smooth">Update Username</button>
            </div>
          </form>
        </div>
      </div>
      
      <!-- Change Password Tab -->
      <div id="password" class="tab-content hidden reveal">
        <div class="glass-card p-8">
          <h2 class="text-2xl font-semibold mb-6" style="color:var(--espresso)">Change Password</h2>
          
          <form method="POST" class="space-y-5">
            <input type="hidden" name="action" value="update_password">
            
            <label class="block">
              <span class="font-semibold mb-3 block text-sm" style="color:var(--espresso)">Current Password</span>
              <input type="password" name="current_password" required class="w-full border-2 p-3 rounded-lg focus:outline-none transition-colors" style="border-color:var(--sand)" placeholder="Enter your current password">
            </label>
            
            <label class="block">
              <span class="font-semibold mb-3 block text-sm" style="color:var(--espresso)">New Password</span>
              <input type="password" name="new_password" required class="w-full border-2 p-3 rounded-lg focus:outline-none transition-colors" style="border-color:var(--sand)" placeholder="Enter new password" minlength="6">
              <p class="text-xs text-gray-600 mt-2">Minimum 6 characters.</p>
            </label>
            
            <label class="block">
              <span class="font-semibold mb-3 block text-sm" style="color:var(--espresso)">Confirm New Password</span>
              <input type="password" name="confirm_password" required class="w-full border-2 p-3 rounded-lg focus:outline-none transition-colors" style="border-color:var(--sand)" placeholder="Confirm new password" minlength="6">
            </label>
            
            <div class="pt-4">
              <button type="submit" class="cl-btn px-6 py-3 rounded-lg font-semibold transition-smooth">Update Password</button>
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
      });
      
      // Hide all tabs
      document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
      });
      
      // Activate clicked tab
      this.style.background = 'var(--sand)';
      this.style.fontWeight = '600';
      document.getElementById(tabName).classList.remove('hidden');
    });
  });
</script>

<?php require_once __DIR__ . '/../views/partials/footer.php'; ?>
