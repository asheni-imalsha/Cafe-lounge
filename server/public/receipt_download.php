<?php
require_once __DIR__ . '/../src/auth.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$order = $_SESSION['last_order'] ?? null;
$html = $_SESSION['last_order_html'] ?? null;
// Composer autoload for dompdf
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
}
if (!$order) { echo 'No order'; exit; }
// Try Dompdf
if (!class_exists('Dompdf\\Dompdf') && class_exists('Dompdf')){
    class_alias('Dompdf','Dompdf\\Dompdf');
}
if (class_exists('Dompdf\\Dompdf')){
    try{
        $receiptsDir = __DIR__ . '/receipts'; if (!is_dir($receiptsDir)) @mkdir($receiptsDir,0755,true);
        $ts = time(); $baseName = "receipt_{$ts}_user_{$order['user_id']}"; $pdfFile = $receiptsDir.'/'. $baseName .'.pdf';
        $dompdf = new Dompdf\\Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4','portrait');
        $dompdf->render();
        file_put_contents($pdfFile, $dompdf->output());
        // stream to browser
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="receipt.pdf"');
        echo $dompdf->output();
        exit;
    } catch(Exception $e){ /* fallthrough */ }
}
// Fallback: send HTML as download
header('Content-Type: text/html');
header('Content-Disposition: attachment; filename="receipt.html"');
echo $html;
exit;
