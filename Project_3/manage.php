<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $umid = $_POST['umid'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!preg_match('/^\d{8}$/', $umid) || $password === '') {
        $_SESSION['login_errors'] = 'Invalid UMID or password.';
        header('Location: login.php');
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM students WHERE umid = ?");
    $stmt->execute([$umid]);
    $student = $stmt->fetch();
    if (!$student || !password_verify($password, $student['password_hash'])) {
        $_SESSION['login_errors'] = 'Authentication failed.';
        header('Location: login.php');
        exit;
    }
    $_SESSION['auth_umid'] = $umid;
}

if (empty($_SESSION['auth_umid'])) {
    header('Location: login.php');
    exit;
}

$umid = $_SESSION['auth_umid'];

// fetch the data of student
$stmt = $pdo->prepare("SELECT s.*, t.slot_label FROM students s JOIN timeslots t ON s.timeslot_id = t.id WHERE s.umid = ?");
$stmt->execute([$umid]);
$student = $stmt->fetch();
if (!$student) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// fetch slots and compute the slots for the seats remaining
$stmt = $pdo->query("
    SELECT t.id, t.slot_label, t.capacity, (t.capacity - IFNULL(c.count,0)) AS seats_remaining
    FROM timeslots t
    LEFT JOIN (SELECT timeslot_id, COUNT(*) AS count FROM students GROUP BY timeslot_id) c ON c.timeslot_id = t.id
    ORDER BY t.slot_date, t.start_time
");
$timeslots = $stmt->fetchAll();

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Manage Registration</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="container">
    <h2>Manage Your Registration</h2>
    <p class="small">UMID: <strong><?php echo htmlspecialchars($student['umid']); ?></strong></p>

    <form action="update.php" method="post" novalidate>
      <input type="hidden" name="umid" value="<?php echo htmlspecialchars($student['umid']); ?>">
      <div class="form-row">
        <div class="col">
          <label>First Name</label>
          <input type="text" name="first_name" value="<?php echo htmlspecialchars($student['first_name']); ?>" required>
        </div>
        <div class="col">
          <label>Last Name</label>
          <input type="text" name="last_name" value="<?php echo htmlspecialchars($student['last_name']); ?>" required>
        </div>
      </div>

      <div class="form-row">
        <div class="col">
          <label>Project Title</label>
          <input type="text" name="project_title" value="<?php echo htmlspecialchars($student['project_title']); ?>" required>
        </div>
        <div class="col">
          <label>Email</label>
          <input type="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
        </div>
      </div>

      <div class="form-row">
        <div class="col">
          <label>Phone</label>
          <input type="text" name="phone" value="<?php echo htmlspecialchars($student['phone']); ?>" required>
        </div>
        <div class="col">
          <label>Change Time Slot</label>
          <select name="timeslot" required>
            <?php foreach($timeslots as $t):
                $disabled = ($t['seats_remaining'] <= 0 && $t['id'] != $student['timeslot_id']) ? 'disabled' : '';
                $label = htmlspecialchars($t['slot_label'] . " — " . ($t['seats_remaining']>0 ? ($t['seats_remaining'].' seats remaining') : 'FULL'));
                $sel = ($t['id'] == $student['timeslot_id']) ? 'selected' : '';
            ?>
            <option value="<?php echo $t['id'];?>" <?php echo "$disabled $sel";?>>
              <?php echo $label; ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="col">
          <label>To confirm changes, enter your password:</label>
          <input type="password" name="password" required>
        </div>
        <div class="col center">
          <button type="submit">Update Registration</button>
        </div>
      </div>
    </form>

    <hr>
    <form action="delete.php" method="post" onsubmit="return confirm('Are you sure you want to cancel your registration?');">
      <input type="hidden" name="umid" value="<?php echo htmlspecialchars($student['umid']); ?>">
      <div class="form-row">
        <div class="col">
          <label>To delete (cancel) registration, confirm with password:</label>
          <input type="password" name="password" required>
        </div>
        <div class="col center">
          <button type="submit" style="background:#c53030;">Delete Registration</button>
        </div>
      </div>
    </form>

    <p class="note">After editing, the system will validate and save your changes; time slot changes are applied only if seats are available.</p>
    <p><a href="students.php">View all registered students</a> | <a href="register.php">Register a new UMID</a></p>
  </div>
</body>
</html>
