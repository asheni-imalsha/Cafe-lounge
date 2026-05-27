<?php
require_once __DIR__ . '/../src/auth.php';
logoutUser();
header('Location: index.php');
exit;
