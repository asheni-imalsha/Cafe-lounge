<?php
// Run this script from CLI: php create_user_asheni.php
// or visit via browser at /server/scripts/create_user_asheni.php (ensure readable by webserver)

require_once __DIR__ . '/../app/models/Database.php';

try {
    $pdo = Database::get();

    $username = 'Asheni';
    $name = 'Asheni';
    $email = 'ash@gmail.com';
    $password = 'ash123';
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (username, name, email, password_hash) VALUES (:u, :n, :e, :p)");
    $stmt->execute([
        ':u' => $username,
        ':n' => $name,
        ':e' => $email,
        ':p' => $password_hash,
    ]);

    echo "User created successfully. ID: " . $pdo->lastInsertId() . PHP_EOL;
} catch (Exception $e) {
    echo "Error creating user: " . $e->getMessage() . PHP_EOL;
}
