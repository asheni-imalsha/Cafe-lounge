<?php
require_once __DIR__ . '/db.php';

function ensureSession(){
    if (session_status() === PHP_SESSION_ACTIVE) return;
    // Harden session cookies: secure when HTTPS, httponly, samesite=Lax
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $cookieParams = [
        'lifetime' => 0,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'] ?? '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ];
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params($cookieParams);
    } else {
        session_set_cookie_params($cookieParams['lifetime'], $cookieParams['path'] . '; samesite=' . $cookieParams['samesite'], $cookieParams['domain'], $cookieParams['secure'], $cookieParams['httponly']);
    }
    session_start();
}

ensureSession();

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function loginUser(int $userId, string $username) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
}

function logoutUser() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'] ?? '/',
            $params['domain'] ?? '',
            $params['secure'] ?? false,
            $params['httponly'] ?? false
        );
    }
    session_destroy();
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// CSRF helpers
function generateCsrfToken(){
    ensureSession();
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

function csrfInputField(){
    $t = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($t) . '" />';
}

function validateCsrfToken($token){
    ensureSession();
    if (empty($_SESSION['csrf_token']) || empty($token)) return false;
    return hash_equals($_SESSION['csrf_token'], (string)$token);
}
