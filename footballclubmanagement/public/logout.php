<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../src/controllers/AuthController.php';
require_once '../src/config/database.php';

$authController = new \App\Controllers\AuthController($conn);
$authController->logout();

// Redirect to index page with logout success message
header('Location: index.php?logout=success');
exit();
?>