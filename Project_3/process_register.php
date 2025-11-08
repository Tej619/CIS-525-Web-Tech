<?php
require_once 'db.php';

function bad($msg) {
    $_SESSION['reg_errors'] = $msg['errors'] ?? ['general' => 'Validation failed'];
    $_SESSION['reg_old'] = $msg['old'] ?? [];
    header('Location: register.php');
    exit;
}

$umid = $_POST['umid'] ?? '';
$password = $_POST['password'] ?? '';
$first = trim($_POST['first_name'] ?? '');
$last = trim($_POST['last_name'] ?? '');
$title = trim($_POST['project_title'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$timeslot = $_POST['timeslot'] ?? '';

$errors = [];
$old = compact('umid','first','last','title','email','phone','timeslot');

// validations with regex
if (!preg_match('/^\d{8}$/', $umid)) $errors['umid'] = 'UMID must be exactly 8 digits.';
if (strlen($password) < 8
    || !preg_match('/[A-Z]/', $password)
    || !preg_match('/[a-z]/', $password)
    || !preg_match('/[0-9]/', $password)
    || !preg_match('/[\W_]/', $password)
) $errors['password'] = 'Password must be >=8 and include upper, lower, number, and special char.';
if ($first === '') $errors['first_name'] = 'First name required.';
if ($last === '') $errors['last_name'] = 'Last name required.';
if ($title === '') $errors['project_title'] = 'Project title required.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email.';
if (!preg_match('/^\d{3}-\d{3}-\d{4}$/', $phone)) $errors['phone'] = 'Phone must be 999-999-9999.';
if (!ctype_digit((string)$timeslot) ) $errors['timeslot'] = 'Select a time slot.';

if (!empty($errors)) {
    bad(['errors'=>$errors,'old'=>$old]);
}

// checking for the duplicate UMID
$stmt = $pdo->prepare("SELECT umid FROM students WHERE umid = ?");
$stmt->execute([$umid]);
if ($stmt->fetch()) {
    $errors['umid'] = 'UMID already registered. Please <a href="login.php">login</a> to manage your registration.';
    bad(['errors'=>$errors,'old'=>$old]);
}

try {
    $pdo->beginTransaction();

    // to book the timeslot
    $stmt = $pdo->prepare("SELECT capacity FROM timeslots WHERE id = ? FOR UPDATE");
    $stmt->execute([$timeslot]);
    $row = $stmt->fetch();
    if (!$row) {
        $pdo->rollBack();
        $errors['timeslot'] = 'Selected timeslot not found.';
        bad(['errors'=>$errors,'old'=>$old]);
    }

    // to count the current registrations
    $stmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM students WHERE timeslot_id = ?");
    $stmt->execute([$timeslot]);
    $cnt = (int)$stmt->fetchColumn();

    if ($cnt >= (int)$row['capacity']) {
        $pdo->rollBack();
        $errors['timeslot'] = 'Selected timeslot is FULL. Please select another.';
        bad(['errors'=>$errors,'old'=>$old]);
    }

    // insert the data
    $passHash = password_hash($password, PASSWORD_DEFAULT);
    $insert = $pdo->prepare("INSERT INTO students (umid, password_hash, first_name, last_name, project_title, email, phone, timeslot_id)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $insert->execute([$umid, $passHash, $first, $last, $title, $email, $phone, $timeslot]);

    $pdo->commit();

    $_SESSION['success_msg'] = "Registration successful. You can login to manage your registration.";
    header('Location: register.php');
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $errors['general'] = 'An internal error occurred. Please try again.';
    bad(['errors'=>$errors,'old'=>$old]);
}
