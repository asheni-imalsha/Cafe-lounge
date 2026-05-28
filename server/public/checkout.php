<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../app/models/CafeItem.php';
require_once __DIR__ . '/../app/models/Cart.php';
require_once __DIR__ . '/../app/models/Booking.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$user = getCurrentUserId();
if (!$user){
    // require login to checkout
    header('Location: login.php'); exit;
}
$ci = new CafeItem();
$cart = new Cart();
$bookingModel = new Booking();
$bookings = $bookingModel->allForUser($user);
$selected = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
  // CSRF validation
  if (!validateCsrfToken($_POST['csrf_token'] ?? '')){
    $_SESSION['flash'] = ['type'=>'error','msg'=>'Invalid CSRF token.'];
    header('Location: cart.php'); exit;
  }
    if (isset($_POST['selected'])){
        $selectedRaw = $_POST['selected'];
        if (!is_array($selectedRaw)) $selectedRaw = [$selectedRaw];
        foreach($selectedRaw as $s){
            if (strpos($s,'cart:')===0){
                $cid = (int)substr($s,5);
                // fetch cart row for user
                $rows = $cart->itemsForUser($user);
                foreach($rows as $r) if ($r['cart_id']===$cid) $selected[] = $r;
            } else if (strpos($s,'item:')===0){
                $iid = (int)substr($s,5);
                $row = $ci->find($iid);
                if ($row) $selected[] = ['cart_id'=>null,'item_id'=>$row['id'],'name'=>$row['name'],'price'=>$row['price'],'image'=>$row['image'],'description'=>$row['description'],'quantity'=> ($_SESSION['cart'][$iid] ?? 1)];
            }
        }
    } elseif (isset($_POST['selected_all'])){
        // load all items from user's cart
        $selected = $cart->itemsForUser($user);
    }

    // Confirm checkout
    if (isset($_POST['action']) && $_POST['action']==='confirm'){
        $booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : null;
        $booking = $bookingModel->find($booking_id);
        // In a real app we'd persist an order. Here we will prepare a receipt and remove cart rows.
        $order = [
            'user_id'=>$user,
            'booking'=>$booking,
            'items'=>[],
            'total'=>0,
            'created_at'=>date('Y-m-d H:i:s')
        ];
        foreach($_POST['selected'] as $s){
            if (strpos($s,'cart:')===0){
                $cid = (int)substr($s,5);
                // find row
                $rows = $cart->itemsForUser($user);
                foreach($rows as $r) if ($r['cart_id']===$cid) { $order['items'][]=$r; $order['total'] += $r['price']*$r['quantity']; $cart->remove($cid); }
            } else if (strpos($s,'item:')===0){
                $iid = (int)substr($s,5);
                $row = $ci->find($iid);
                $q = isset($_SESSION['cart'][$iid]) ? $_SESSION['cart'][$iid] : 1;
                if ($row){ $r = ['cart_id'=>null,'item_id'=>$row['id'],'name'=>$row['name'],'price'=>$row['price'],'quantity'=>$q]; $order['items'][]=$r; $order['total'] += $r['price']*$r['quantity']; unset($_SESSION['cart'][$iid]); }
            }
        }
        // show success and receipt link
        ?>
        <!doctype html>
        <html>
        <head>
          <meta charset="utf-8">
          <title>Checkout Success</title>
          <link rel="stylesheet" href="/client/tailwind.min.css">
        </head>
        <body style="font-family:system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial; padding:30px">
          <div style="max-width:800px;margin:0 auto;text-align:center">
            <h1 style="color:var(--espresso)">Checkout Successful</h1>
            <p>Your order has been placed and linked to booking: <strong><?= htmlspecialchars($booking['id'] ?? 'N/A') ?></strong></p>
            <p><button id="download" style="padding:10px 20px;border-radius:8px;background:#7b3f00;color:#fff;border:none">Download Receipt (Print/PDF)</button></p>
            <p><a href="cart.php">Back to cart</a> | <a href="index.php">Home</a></p>
          </div>
          <script>
            document.getElementById('download').addEventListener('click', function(){
              const w = window.open('', '_blank');
              const html = `
                <html><head><meta charset="utf-8"><title>Receipt</title>
                <style>body{font-family:Arial,Helvetica,sans-serif;padding:20px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ddd;padding:8px}</style>
                </head><body>
                <h2>Receipt</h2>
                <p>Order Time: <?= htmlspecialchars($order['created_at']) ?></p>
                <h3>Booking Details</h3>
                <pre><?= htmlspecialchars(json_encode($order['booking']?:[], JSON_PRETTY_PRINT)) ?></pre>
                <h3>Items</h3>
                <table><thead><tr><th>Name</th><th>Qty</th><th>Unit</th><th>Line Total</th></tr></thead><tbody>
                <?php foreach($order['items'] as $it): ?>
                  <tr><td><?= htmlspecialchars($it['name']) ?></td><td><?= (int)$it['quantity'] ?></td><td><?= number_format($it['price'],0) ?></td><td><?= number_format($it['price']*$it['quantity'],0) ?></td></tr>
                <?php endforeach; ?>
                </tbody></table>
                <h3>Total: LKR <?= number_format($order['total'],0) ?></h3>
                <hr>
                <p>User ID: <?= (int)$order['user_id'] ?></p>
                </body></html>`;
              w.document.write(html); w.document.close();
              w.print();
            });
          </script>
        </body>
        </html>
        <?php
        exit;
    }
}
// Render selection/confirmation page
?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Checkout</title>
  <link rel="stylesheet" href="/client/tailwind.min.css">
  <style>.box{max-width:900px;margin:30px auto;padding:20px;background:#fff;border-radius:8px}</style>
</head>
<body>
  <div class="box">
    <h2 style="color:var(--espresso)">Checkout</h2>
    <?php if (empty($selected)): ?>
      <p>No items selected. <a href="cart.php">Back to cart</a></p>
    <?php else: ?>
      <h3>Selected Items</h3>
      <ul>
        <?php foreach($selected as $it): ?>
          <li><?= htmlspecialchars($it['name']) ?> — Qty: <?= (int)$it['quantity'] ?> — LKR <?= number_format($it['price'],0) ?> — Line: LKR <?= number_format($it['price']*$it['quantity'],0) ?></li>
        <?php endforeach; ?>
      </ul>
      <h3>Select Booking</h3>
      <form method="POST" id="confirmForm">
        <?php echo csrfInputField(); ?>
        <?php if (isset($_POST['selected'])): foreach($_POST['selected'] as $s): ?>
          <input type="hidden" name="selected[]" value="<?= htmlspecialchars($s) ?>" />
        <?php endforeach; elseif (isset($_POST['selected_all'])): 
            // include all current cart rows for user as selected[]
            $all = $cart->itemsForUser($user);
            foreach($all as $r): ?>
              <input type="hidden" name="selected[]" value="cart:<?= (int)$r['cart_id'] ?>" />
        <?php endforeach; endif; ?>
        <?php if (isset($_POST['selected_all'])): ?>
          <input type="hidden" name="selected_all" value="1" />
        <?php endif; ?>
        <div style="margin:10px 0">
          <select name="booking_id" id="bookingSelect">
            <option value="">-- Select booking --</option>
            <?php foreach($bookings as $b): ?>
              <option value="<?= (int)$b['id'] ?>">Booking #<?= (int)$b['id'] ?> — <?= htmlspecialchars($b['booking_date']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div id="bookingDetails" style="border:1px solid #eee;padding:10px;border-radius:6px;display:none"></div>
        <div style="margin-top:16px">
          <button type="submit" name="action" value="confirm" style="padding:10px 16px;border-radius:8px;background:#7b3f00;color:#fff;border:none">Confirm Checkout</button>
          <a href="cart.php" style="margin-left:12px">Cancel</a>
        </div>
      </form>
    <?php endif; ?>
  </div>
<script>
  const bookings = <?= json_encode($bookings) ?>;
  const select = document.getElementById('bookingSelect');
  const details = document.getElementById('bookingDetails');
  if (select){
    select.addEventListener('change', ()=>{
      const id = select.value;
      if (!id){ details.style.display='none'; details.innerHTML=''; return; }
      const b = bookings.find(x=>String(x.id)===String(id));
      if (!b) return;
      details.style.display='block';
      details.innerHTML = `<strong>Booking #${b.id}</strong><br>Space: ${b.space_name || b.space_type || '-'}<br>Date: ${b.booking_date}<br>Start: ${b.start_time || '-'} End: ${b.end_time || '-'}<br>User: ${b.username}`;
    });
  }
</script>
</body>
</html>
