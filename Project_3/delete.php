<?php
require_once 'db.php';

$umid = $_POST['umid'] ?? '';
$password = $_POST['password'] ?? '';

if (!preg_match('/^\d{8}$/', $umid) || $password === '') {
    $_SESSION['manage_errors'] = 'UMID and password required.';
    header('Location: manage.php');
    exit;
}

$stmt = $pdo->prepare("SELECT password_hash FROM students WHERE umid = ?");
$stmt->execute([$umid]);
$user = $stmt->fetch();
if (!$user || !password_verify($password, $user['password_hash'])) {
    $_SESSION['manage_errors'] = 'Authentication failed.';
    header('Location: manage.php');
    exit;
}

try {
    $del = $pdo->prepare("DELETE FROM students WHERE umid = ?");
    $del->execute([$umid]);
    unset($_SESSION['auth_umid']);
    $_SESSION['success_msg'] = 'Your registration has been deleted.';
    header('Location: register.php');
    exit;
} catch (Exception $e) {
    $_SESSION['manage_errors'] = 'An internal error occurred.';
    header('Location: manage.php');
    exit;
}
