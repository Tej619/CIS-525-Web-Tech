<?php
require_once 'db.php';

$umid = $_POST['umid'] ?? '';
$password = $_POST['password'] ?? '';
$first = trim($_POST['first_name'] ?? '');
$last = trim($_POST['last_name'] ?? '');
$title = trim($_POST['project_title'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$timeslot = $_POST['timeslot'] ?? '';

$errors = [];

if (!preg_match('/^\d{8}$/', $umid)) $errors[] = 'Invalid UMID.';
if ($first==='') $errors[] = 'First name required.';
if ($last==='') $errors[] = 'Last name required.';
if ($title==='') $errors[] = 'Project title required.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email.';
if (!preg_match('/^\d{3}-\d{3}-\d{4}$/', $phone)) $errors[] = 'Phone must be 999-999-9999.';
if ($password === '') $errors[] = 'Password required for confirmation.';
if (!ctype_digit((string)$timeslot)) $errors[] = 'Invalid timeslot selected.';

if (!empty($errors)) {
    $_SESSION['manage_errors'] = implode('<br>', $errors);
    header('Location: manage.php');
    exit;
}

// fetch user & verify password
$stmt = $pdo->prepare("SELECT password_hash, timeslot_id FROM students WHERE umid = ?");
$stmt->execute([$umid]);
$user = $stmt->fetch();
if (!$user || !password_verify($password, $user['password_hash'])) {
    $_SESSION['manage_errors'] = 'Authentication failed.';
    header('Location: manage.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // if timeslot changed, check capacity with FOR UPDATE on timeslot row
    if ($timeslot != $user['timeslot_id']) {
        // lock new timeslot row
        $stmt = $pdo->prepare("SELECT capacity FROM timeslots WHERE id = ? FOR UPDATE");
        $stmt->execute([$timeslot]);
        $slot = $stmt->fetch();
        if (!$slot) {
            $pdo->rollBack();
            $_SESSION['manage_errors'] = 'Selected timeslot does not exist.';
            header('Location: manage.php');
            exit;
        }
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE timeslot_id = ?");
        $stmt->execute([$timeslot]);
        $cnt = (int)$stmt->fetchColumn();
        if ($cnt >= (int)$slot['capacity']) {
            $pdo->rollBack();
            $_SESSION['manage_errors'] = 'Selected timeslot is full.';
            header('Location: manage.php');
            exit;
        }
    }

    // update record
    $update = $pdo->prepare("UPDATE students SET first_name=?, last_name=?, project_title=?, email=?, phone=?, timeslot_id=? WHERE umid = ?");
    $update->execute([$first,$last,$title,$email,$phone,$timeslot,$umid]);

    $pdo->commit();

    $_SESSION['success_msg'] = 'Registration updated successfully.';
    header('Location: manage.php');
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $_SESSION['manage_errors'] = 'An internal error occurred.';
    header('Location: manage.php');
    exit;
}
