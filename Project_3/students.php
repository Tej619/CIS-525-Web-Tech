<?php
require_once 'db.php';
// public page: show all registered students
$stmt = $pdo->query("
    SELECT s.umid, s.first_name, s.last_name, s.project_title, s.email, s.phone, t.slot_label
    FROM students s
    JOIN timeslots t ON s.timeslot_id = t.id
    ORDER BY t.slot_date, t.start_time, s.last_name, s.first_name
");
$rows = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Registered Students</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="container">
    <h2>Registered Students</h2>

    <table class="table">
      <thead>
        <tr>
          <th>UMID</th>
          <th>Name</th>
          <th>Project Title</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Time Slot</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="6" class="center">No registrations yet.</td></tr>
        <?php else: ?>
          <?php foreach($rows as $r): ?>
            <tr>
              <td><?php echo htmlspecialchars($r['umid']); ?></td>
              <td><?php echo htmlspecialchars($r['first_name'].' '.$r['last_name']); ?></td>
              <td><?php echo htmlspecialchars($r['project_title']); ?></td>
              <td><?php echo htmlspecialchars($r['email']); ?></td>
              <td><?php echo htmlspecialchars($r['phone']); ?></td>
              <td><?php echo htmlspecialchars($r['slot_label']); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
