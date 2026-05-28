<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../../src/auth.php';

class AuthController {
    public static function register() {
        $userModel = new User();
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validateCsrfToken($_POST['csrf_token'] ?? '')) { $errors[] = 'Invalid CSRF token.'; }
            $username = trim($_POST['username'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            if ($username === '' || $email === '' || $password === '') $errors[] = 'Username, email and password are required.';
            if (empty($errors)) {
                $exists = $userModel->findByUsernameOrEmail($username) ?: $userModel->findByUsernameOrEmail($email);
                if ($exists) $errors[] = 'Username or email already taken.';
                else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                        $userModel->create($username, $name ?: null, $email, $hash);
                        // Auto-login newly registered user and migrate any guest session cart
                        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
                        $userRow = $userModel->findByUsernameOrEmail($username);
                        if ($userRow){
                            loginUser($userRow['id'], $userRow['username']);
                            // migrate session cart to DB
                            if (!empty($_SESSION['cart'])){
                                require_once __DIR__ . '/../models/Cart.php';
                                $cartModel = new Cart();
                                foreach($_SESSION['cart'] as $itemId => $qty){
                                    $cartModel->add($userRow['id'], null, (int)$itemId, (int)$qty);
                                }
                                unset($_SESSION['cart']);
                            }
                        }
                        header('Location: index.php'); exit;
                }
            }
        }
        require __DIR__ . '/../../views/auth/register.php';
    }

    public static function login() {
        $userModel = new User();
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!validateCsrfToken($_POST['csrf_token'] ?? '')) { $errors[] = 'Invalid CSRF token.'; }
            $identifier = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            if ($identifier === '' || $password === '') $errors[] = 'Username/email and password are required.';
            if (empty($errors)) {
                $user = $userModel->findByUsernameOrEmail($identifier);
                if ($user && password_verify($password, $user['password_hash'])) {
                    // Successful login: migrate session cart to user cart in DB
                    loginUser($user['id'], $user['username']);
                    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
                    // If guest session cart exists, persist to DB
                    if (!empty($_SESSION['cart'])){
                        require_once __DIR__ . '/../models/Cart.php';
                        $cartModel = new Cart();
                        foreach($_SESSION['cart'] as $itemId => $qty){
                            $cartModel->add($user['id'], null, (int)$itemId, (int)$qty);
                        }
                        unset($_SESSION['cart']);
                    }
                    header('Location: index.php'); exit;
                } else $errors[] = 'Invalid credentials.';
            }
        }
        require __DIR__ . '/../../views/auth/login.php';
    }
}
