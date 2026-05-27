<?php require_once __DIR__ . '/../partials/header.php'; ?>
<?php if (empty($booking)){ echo '<div class="max-w-4xl mx-auto mt-8">Booking not found.</div>'; require_once __DIR__ . '/../partials/footer.php'; exit; } ?>

<div class="max-w-3xl mx-auto mt-8">
  <h1 class="text-2xl font-bold mb-4" style="color:var(--espresso)">Edit Booking</h1>

  <?php if (!empty($errors)): ?><div class="mb-4 text-red-600"><?php foreach($errors as $e) echo '<div>'.htmlspecialchars($e).'</div>'; ?></div><?php endif; ?>

  <form method="post" action="" class="space-y-4 bg-white p-6 rounded-lg shadow-sm">
    <label class="block">Space name
      <input name="space_name" required class="w-full border p-3 rounded" value="<?= htmlspecialchars($booking['space_name']) ?>">
    </label>
    <label class="block">Space type
      <input name="space_type" class="w-full border p-3 rounded" value="<?= htmlspecialchars($booking['space_type']) ?>">
    </label>
    <label class="block">Booking date
      <input type="datetime-local" name="booking_date" required class="w-full border p-3 rounded" value="<?= date('Y-m-d\TH:i', strtotime($booking['booking_date'])) ?>">
    </label>
    <div class="grid grid-cols-2 gap-3">
      <label class="block">Start time
        <input type="time" name="start_time" required class="w-full border p-3 rounded" value="<?= isset($booking['start_time']) ? date('H:i', strtotime($booking['start_time'])) : '' ?>">
      </label>
      <label class="block">End time
        <input type="time" name="end_time" required class="w-full border p-3 rounded" value="<?= isset($booking['end_time']) ? date('H:i', strtotime($booking['end_time'])) : '' ?>">
      </label>
    </div>
    <div>
      <button class="cl-btn px-4 py-2 rounded">Save</button>
      <a href="bookings.php" class="px-3 py-2 border rounded ml-2">Cancel</a>
    </div>
  </form>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
