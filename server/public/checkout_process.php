<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../app/models/CafeItem.php';
require_once __DIR__ . '/../app/models/Cart.php';
require_once __DIR__ . '/../app/models/Booking.php';
// Composer autoloader (for dompdf)
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
}

header('Content-Type: application/json');
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$user = getCurrentUserId();
if (!$user) {
    http_response_code(401);
    echo json_encode(['error' => 'unauthenticated']);
    exit;
}

$selected = $_POST['selected'] ?? [];
$booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : null;
if (!is_array($selected) || count($selected) === 0) {
    echo json_encode(['error' => 'no items selected']);
    exit;
}

$ci = new CafeItem();
$cart = new Cart();
$bookingModel = new Booking();

$order = [
    'user_id' => $user,
    'booking' => $bookingModel->find($booking_id),
    'items' => [],
    'total' => 0,
    'created_at' => date('Y-m-d H:i:s')
];
// add a simple order reference
$order['order_ref'] = 'ORD' . date('YmdHis');

foreach ($selected as $s) {
    if (strpos($s, 'cart:') === 0) {
        $cid = (int) substr($s, 5);
        $rows = $cart->itemsForUser($user);
        foreach ($rows as $r) {
            if ((int)$r['cart_id'] === $cid) {
                $order['items'][] = $r;
                $order['total'] += $r['price'] * $r['quantity'];
                $cart->remove($cid);
            }
        }
    } elseif (strpos($s, 'item:') === 0) {
        $iid = (int) substr($s, 5);
        $row = $ci->find($iid);
        $q = isset($_SESSION['cart'][$iid]) ? $_SESSION['cart'][$iid] : 1;
        if ($row) {
            $r = ['cart_id' => null, 'item_id' => $row['id'], 'name' => $row['name'], 'price' => $row['price'], 'quantity' => $q];
            $order['items'][] = $r;
            $order['total'] += $r['price'] * $r['quantity'];
            unset($_SESSION['cart'][$iid]);
        }
    }
}

// (Receipt HTML will be generated after finalizing items to ensure accurate data for PDF)

// ensure receipts dir
$receiptsDir = __DIR__ . '/receipts';
if (!is_dir($receiptsDir)) {
    mkdir($receiptsDir, 0755, true);
}

// save to session for receipt page
$_SESSION['last_order'] = $order;

// store raw selected values for debugging (helps diagnose missing items)
$_SESSION['last_order_selected_raw'] = $selected;

// Ensure purchased items are removed from cart (DB or session)
foreach ($order['items'] as $it) {
    if (!empty($it['cart_id'])) {
        // remove DB cart row (safe to call again)
        try { $cart->remove((int)$it['cart_id']); } catch(Exception $e) {}
    }
    if (!empty($it['item_id'])) {
        // remove from guest session cart if present
        if (isset($_SESSION['cart'][$it['item_id']])) unset($_SESSION['cart'][$it['item_id']]);
    }
}
// persist session cart (no-op but ensures changes)
$_SESSION['cart'] = $_SESSION['cart'] ?? [];

// ensure each item has up-to-date name/price (fetch from CafeItem if needed) and recompute total
$order['total'] = 0;
foreach ($order['items'] as &$it) {
    $it['quantity'] = isset($it['quantity']) ? (int)$it['quantity'] : 1;
    if (empty($it['name']) || empty($it['price'])){
        $iid = $it['item_id'] ?? null;
        if ($iid){
            $fresh = $ci->find($iid);
            if ($fresh){
                $it['name'] = $fresh['name'];
                $it['price'] = $fresh['price'];
                $it['image'] = $fresh['image'] ?? '';
            }
        }
    }
    $order['total'] += ($it['price'] ?? 0) * $it['quantity'];
}
unset($it);
// update session copy with computed total and items
$_SESSION['last_order'] = $order;
// generate a nicer, user-friendly receipt HTML for display and PDF (uses the site's visual style)
$html = '<!doctype html><html><head><meta charset="utf-8"><title>Cafe Lounge - Your Receipt</title>';
$html .= '<style>
    body{font-family:"Segoe UI",Arial,Helvetica,sans-serif;margin:0;padding:0;background:#f9f6f0;color:#333}
    .receipt-container{max-width:700px;margin:30px auto;background:white;border-radius:20px;box-shadow:0 10px 40px rgba(0,0,0,0.1);overflow:hidden}
    .receipt-header{background:#2c1810;color:#fff;padding:30px;text-align:center}
    .receipt-header h1{margin:0 0 8px;font-size:32px;letter-spacing:2px}
    .receipt-header p{margin:0;opacity:0.8}
    .receipt-body{padding:30px}
    .order-info{background:#faf7f2;border-radius:12px;padding:20px;margin-bottom:25px;border-left:4px solid #c8a87c}
    .order-info h3{margin:0 0 12px;color:#2c1810;font-size:18px}
    .order-details{display:flex;justify-content:space-between;flex-wrap:wrap;gap:15px}
    .order-details div{flex:1}
    .order-details strong{display:block;font-size:12px;color:#888;margin-bottom:4px}
    .order-details span{font-size:16px;color:#2c1810}
    .booking-box{background:#fff8f0;border:1px solid #f0e4d5;border-radius:12px;padding:20px;margin-bottom:25px}
    .booking-box h3{margin:0 0 15px;color:#2c1810;font-size:18px}
    .booking-details{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px}
    .booking-details strong{display:block;font-size:12px;color:#888;margin-bottom:4px}
    .booking-details span{font-size:15px;color:#2c1810}
    .items-table{width:100%;border-collapse:collapse;margin-bottom:25px}
    .items-table th{background:#faf7f2;padding:12px;text-align:left;font-weight:600;color:#2c1810;border-bottom:2px solid #e0d4c8}
    .items-table td{padding:12px;border-bottom:1px solid #eee}
    .items-table tr:last-child td{border-bottom:none}
    .total-row{background:#faf7f2;border-radius:12px;padding:15px 20px;display:flex;justify-content:space-between;align-items:center;margin-bottom:25px}
    .total-label{font-size:18px;font-weight:600;color:#2c1810}
    .total-amount{font-size:24px;font-weight:700;color:#2c1810}
    .footer{text-align:center;padding:25px 30px;background:#faf7f2;color:#888;font-size:13px;border-top:1px solid #e0d4c8}
    .footer p{margin:5px 0}
    .thankyou{font-size:16px;color:#c8a87c;margin-top:10px}
    .receipt-id{font-size:11px;color:#aaa;margin-top:10px}
</style>';
$html .= '</head><body>';
$html .= '<div class="receipt-container">';
$html .= '<div class="receipt-header">';
$html .= '<h1>Cafe Lounge</h1>';
$html .= '<p>Thank you for your order!</p>';
$html .= '</div>';
$html .= '<div class="receipt-body">';

// Order Reference and Time
$html .= '<div class="order-info">';
$html .= '<h3>Order Summary</h3>';
$html .= '<div class="order-details">';
$html .= '<div><strong>Order Number</strong><span>#' . htmlspecialchars($order['order_ref'] ?? 'ORD' . date('Ymd')) . '</span></div>';
$html .= '<div><strong>Date & Time</strong><span>' . htmlspecialchars(date('F j, Y \a\t g:i A', strtotime($order['created_at']))) . '</span></div>';
$html .= '</div>';
$html .= '</div>';

// Booking Details - User Friendly
$b = $order['booking'] ?: [];
$html .= '<div class="booking-box">';
$html .= '<h3>Reservation Details</h3>';
$html .= '<div class="booking-details">';
$html .= '<div><strong>Space Name</strong><span>' . htmlspecialchars($b['space_name'] ?? ($b['space_type'] ?? 'Standard Space')) . '</span></div>';
$html .= '<div><strong>Date</strong><span>' . htmlspecialchars(date('l, F j, Y', strtotime($b['booking_date'] ?? 'today'))) . '</span></div>';
$html .= '<div><strong>Time</strong><span>' . htmlspecialchars(date('g:i A', strtotime($b['start_time'] ?? '00:00'))) . ' - ' . htmlspecialchars(date('g:i A', strtotime($b['end_time'] ?? '00:00'))) . '</span></div>';
$html .= '<div><strong>Guest Name</strong><span>' . htmlspecialchars($b['username'] ?? ($_SESSION['username'] ?? 'Valued Customer')) . '</span></div>';
$html .= '</div>';
$html .= '</div>';

// Items Table
if (!empty($order['items'])) {
    $html .= '<h3 style="margin:0 0 15px;color:#2c1810"> Order Items</h3>';
    $html .= '<table class="items-table">';
    $html .= '<thead><tr><th>Item</th><th style="text-align:center">Quantity</th><th style="text-align:right">Price</th><th style="text-align:right">Total</th></tr></thead>';
    $html .= '<tbody>';
    foreach ($order['items'] as $it) {
        $name = htmlspecialchars($it['name'] ?? ($it['item_name'] ?? 'Menu Item'));
        $qty = (int)($it['quantity'] ?? 1);
        $unit = number_format($it['price'] ?? 0, 0);
        $line = number_format((($it['price'] ?? 0) * $qty), 0);
        $html .= "<tr>";
        $html .= "<td>{$name}</td>";
        $html .= "<td style=\"text-align:center\">{$qty}</td>";
        $html .= "<td style=\"text-align:right\">LKR {$unit}</td>";
        $html .= "<td style=\"text-align:right\">LKR {$line}</td>";
        $html .= "</tr>";
    }
    $html .= '</tbody></table>';
}

// Total
$html .= '<div class="total-row">';
$html .= '<span class="total-label">Grand Total</span>';
$html .= '<span class="total-amount">LKR ' . number_format($order['total'], 0) . '</span>';
$html .= '</div>';

$html .= '</div>'; // close receipt-body
$html .= '<div class="footer">';
$html .= '<p>Cafe Lounge • 123 Coffee Street, Colombo</p>';
$html .= '<p> +94 11 234 5678 |  hello@cafelounge.lk</p>';
$html .= '<p class="thankyou">Thank you for choosing us! We hope to serve you again soon.</p>';
$html .= '<div class="receipt-id">Receipt #' . htmlspecialchars($order['order_ref'] ?? 'N/A') . '</div>';
$html .= '</div>';
$html .= '</div></body></html>';

$_SESSION['last_order_html'] = $html;

$response = ['success' => true, 'receipt_page' => 'receipt.php'];

// try to generate PDF if Dompdf is available
if (class_exists('Dompdf\\Dompdf') || class_exists('Dompdf')) {
    try {
        if (!class_exists('Dompdf\\Dompdf')) {
            class_alias('Dompdf', 'Dompdf\\Dompdf');
        }
        $ts = time();
        $pdfFile = $receiptsDir . '/receipt_' . $ts . '_user_' . $user . '.pdf';
        $dompdf = new Dompdf\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        file_put_contents($pdfFile, $dompdf->output());
        $_SESSION['last_order_pdf'] = $pdfFile;
        $response['pdf_url'] = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])) . '/receipts/' . basename($pdfFile);
    } catch (Exception $e) {
        // ignore PDF errors, fallback to HTML receipt
    }
}

echo json_encode($response);
exit;