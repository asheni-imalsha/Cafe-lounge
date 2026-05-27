<?php
require_once __DIR__ . '/../src/auth.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$order = $_SESSION['last_order'] ?? null;
$html = $_SESSION['last_order_html'] ?? null;
$pdfPath = $_SESSION['last_order_pdf'] ?? null;

if (!$order){
    $_SESSION['flash'] = ['type'=>'error','msg'=>'No receipt available.'];
    header('Location: cart.php'); 
    exit;
}
require_once __DIR__ . '/../views/partials/header.php';
?>

<div class="receipt-wrap" style="min-height:calc(100vh - 220px);display:flex;align-items:center;justify-content:center;padding:40px 20px;background:#f9f6f0">
  <style>
    .receipt-card {
      background: white;
      border-radius: 24px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.08);
      max-width: 720px;
      width: 100%;
      overflow: hidden;
    }
    .receipt-header {
      background: linear-gradient(135deg, #2c1810 0%, #3d2317 100%);
      color: white;
      padding: 35px 30px;
      text-align: center;
    }
    .receipt-header h1 { margin: 0 0 8px; font-size: 36px; letter-spacing: 2px; }
    .receipt-header .subtitle { opacity: 0.9; font-size: 14px; }
    .receipt-body { padding: 30px; }
    .order-summary { background: #faf7f2; border-radius: 16px; padding: 20px; margin-bottom: 25px; border-left: 4px solid #c8a87c; }
    .order-summary h3 { margin: 0 0 15px; color: #2c1810; font-size: 18px; }
    .summary-grid { display: flex; justify-content: space-between; flex-wrap: wrap; gap: 15px; }
    .summary-item { flex: 1; }
    .summary-item label { display:block; font-size:12px; color:#888; margin-bottom:4px; text-transform:uppercase; letter-spacing:0.5px; }
    .summary-item .value { font-size:16px; color:#2c1810; font-weight:500; }
    .booking-card { background: #fff8f0; border:1px solid #f0e4d5; border-radius:16px; padding:20px; margin-bottom:25px; }
    .booking-card h3 { margin:0 0 15px; color:#2c1810; font-size:18px; }
    .booking-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:15px; }
    .booking-item label { display:block; font-size:11px; color:#999; margin-bottom:4px; text-transform:uppercase; }
    .booking-item .value { font-size:15px; color:#2c1810; font-weight:500; }
    .items-section h3 { margin:0 0 15px; color:#2c1810; font-size:18px; }
    .receipt-table { width:100%; border-collapse:collapse; margin-bottom:25px; }
    .receipt-table th { background:#faf7f2; padding:12px; text-align:left; font-weight:600; color:#2c1810; border-bottom:2px solid #e0d4c8; }
    .receipt-table td { padding:12px; border-bottom:1px solid #eee; }
    .receipt-table tr:last-child td { border-bottom:none }
    .total-section { background:#faf7f2; border-radius:16px; padding:20px; display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; }
    .total-label{ font-size:18px; font-weight:600; color:#2c1810; }
    .total-amount{ font-size:28px; font-weight:700; color:#2c1810; }
    .action-buttons{ display:flex; gap:12px; justify-content:center; margin-top:10px; }
    .btn-pdf{ background:#c8a87c; color:white; border:none; padding:12px 28px; border-radius:40px; font-size:14px; font-weight:600; cursor:pointer; text-decoration:none; display:inline-block }
    .btn-pdf:hover{ background:#2c1810; transform:translateY(-2px) }
    .btn-home{ background:#f0e4d5; color:#2c1810; padding:12px 28px; border-radius:40px; font-size:14px; font-weight:600; text-decoration:none }
    .btn-home:hover{ background:#e0d4c8 }
    .footer-note{ text-align:center; padding-top:25px; margin-top:10px; border-top:1px solid #e0d4c8; color:#888; font-size:12px }
    .thankyou-text{ color:#c8a87c; font-size:14px; margin-top:10px }
  </style>
  
  <div class="receipt-card">
    <div class="receipt-header">
      <h1>Cafe Lounge</h1>
      <div class="subtitle">Your Receipt</div>
    </div>
    
    <div class="receipt-body">
      <!-- Order Summary -->
      <div class="order-summary">
        <h3>Order Summary</h3>
        <div class="summary-grid">
          <div class="summary-item">
            <label>Order Number</label>
            <div class="value">#<?= htmlspecialchars($order['order_ref'] ?? 'ORD' . date('Ymd')) ?></div>
          </div>
          <div class="summary-item">
            <label>Date & Time</label>
            <div class="value"><?= htmlspecialchars(date('F j, Y \a\t g:i A', strtotime($order['created_at']))) ?></div>
          </div>
        </div>
      </div>

      <!-- Booking Details -->
      <div class="booking-card">
        <h3>Reservation Details</h3>
        <div class="booking-grid">
          <div class="booking-item">
            <label>Space Name</label>
            <div class="value"><?= htmlspecialchars($order['booking']['space_name'] ?? ($order['booking']['space_type'] ?? 'Standard Space')) ?></div>
          </div>
          <div class="booking-item">
            <label>Date</label>
            <div class="value"><?= htmlspecialchars(date('l, F j, Y', strtotime($order['booking']['booking_date'] ?? 'today'))) ?></div>
          </div>
          <div class="booking-item">
            <label>Time</label>
            <div class="value"><?= htmlspecialchars(date('g:i A', strtotime($order['booking']['start_time'] ?? '00:00'))) ?> - <?= htmlspecialchars(date('g:i A', strtotime($order['booking']['end_time'] ?? '00:00'))) ?></div>
          </div>
          <div class="booking-item">
            <label>Guest Name</label>
            <div class="value"><?= htmlspecialchars($order['booking']['username'] ?? ($_SESSION['username'] ?? 'Valued Customer')) ?></div>
          </div>
        </div>
      </div>

      <!-- Order Items -->
      <?php if (!empty($order['items'])): ?>
      <div class="items-section">
        <h3>Order Items</h3>
        <table class="receipt-table">
          <thead>
            <tr>
              <th>Item</th>
              <th style="text-align:center">Qty</th>
              <th style="text-align:right">Price</th>
              <th style="text-align:right">Total</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach($order['items'] as $it): ?>
            <tr>
              <td><?= htmlspecialchars($it['name'] ?? ($it['item_name'] ?? 'Menu Item')) ?></td>
              <td style="text-align:center"><?= (int)($it['quantity'] ?? 1) ?></td>
              <td style="text-align:right">LKR <?= number_format($it['price'] ?? 0, 0) ?></td>
              <td style="text-align:right">LKR <?= number_format((($it['price'] ?? 0) * ($it['quantity'] ?? 1)), 0) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>

      <!-- Total -->
      <div class="total-section">
        <span class="total-label">Grand Total</span>
        <span class="total-amount">LKR <?= number_format($order['total'], 0) ?></span>
      </div>

      <!-- Action Buttons -->
      <div class="action-buttons">
        <?php if ($pdfPath && file_exists($pdfPath)): 
          $url = str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'])) . '/receipts/' . basename($pdfPath);
        ?>
          <a href="<?= htmlspecialchars($url) ?>" class="btn-pdf" target="_blank">Download PDF</a>
        <?php else: ?>
          <form method="POST" action="receipt_download.php" style="display:inline">
            <button type="submit" class="btn-pdf">Download PDF</button>
          </form>
        <?php endif; ?>
        <a href="index.php" class="btn-home">Back to Home</a>
      </div>

      <!-- Footer -->
      <div class="footer-note">
        <p>Cafe Lounge • 123 Coffee Street, Colombo</p>
        <p>📞 +94 11 234 5678 | 📧 hello@cafelounge.lk</p>
        <div class="thankyou-text">Thank you for choosing us! We hope to serve you again soon.</div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../views/partials/footer.php';

