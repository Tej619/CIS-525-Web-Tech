<?php
require_once 'db.php';

// get timeslots + remaining seats
$stmt = $pdo->query("
    SELECT t.id, t.slot_label, t.slot_date, t.start_time, t.end_time, t.capacity,
           (t.capacity - IFNULL(c.count,0)) AS seats_remaining
    FROM timeslots t
    LEFT JOIN (
      SELECT timeslot_id, COUNT(*) AS count FROM students GROUP BY timeslot_id
    ) c ON c.timeslot_id = t.id
    ORDER BY t.slot_date, t.start_time
");
$timeslots = $stmt->fetchAll();

$errors = $_SESSION['reg_errors'] ?? null;
$old = $_SESSION['reg_old'] ?? [];
unset($_SESSION['reg_errors'], $_SESSION['reg_old']);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Project Demo Registration</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
  <div class="container">
    <h1>Web Technology 525 — Project Demonstration Registration</h1>

    <?php if(!empty($errors)): ?>
      <div class="error">
        Please fix the highlighted fields below.
      </div>
    <?php endif; ?>

    <form action="process_register.php" method="post" novalidate>
      <div class="form-row">
        <div class="col">
          <label for="umid">UMID (8 digits)</label>
          <input type="text" id="umid" name="umid" maxlength="8" value="<?php echo htmlspecialchars($old['umid'] ?? ''); ?>" <?php echo isset($errors['umid']) ? 'class="field-invalid"' : '';?>>
          <?php if(!empty($errors['umid'])) echo '<div class="error">'.$errors['umid'].'</div>'; ?>
        </div>
        <div class="col">
          <label for="password">Password (min 8 with upper/lower/number/symbol)</label>
          <input type="password" id="password" name="password" <?php echo isset($errors['password']) ? 'class="field-invalid"' : '';?>>
          <?php if(!empty($errors['password'])) echo '<div class="error">'.$errors['password'].'</div>'; ?>
        </div>
      </div>

      <div class="form-row">
        <div class="col">
          <label for="first_name">First Name</label>
          <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($old['first_name'] ?? ''); ?>" <?php echo isset($errors['first_name']) ? 'class="field-invalid"' : '';?>>
          <?php if(!empty($errors['first_name'])) echo '<div class="error">'.$errors['first_name'].'</div>'; ?>
        </div>
        <div class="col">
          <label for="last_name">Last Name</label>
          <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($old['last_name'] ?? ''); ?>" <?php echo isset($errors['last_name']) ? 'class="field-invalid"' : '';?>>
          <?php if(!empty($errors['last_name'])) echo '<div class="error">'.$errors['last_name'].'</div>'; ?>
        </div>
      </div>

      <div class="form-row">
        <div class="col">
          <label for="project_title">Project Title</label>
          <input type="text" id="project_title" name="project_title" value="<?php echo htmlspecialchars($old['project_title'] ?? ''); ?>" <?php echo isset($errors['project_title']) ? 'class="field-invalid"' : '';?>>
          <?php if(!empty($errors['project_title'])) echo '<div class="error">'.$errors['project_title'].'</div>'; ?>
        </div>
        <div class="col">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($old['email'] ?? ''); ?>" <?php echo isset($errors['email']) ? 'class="field-invalid"' : '';?>>
          <?php if(!empty($errors['email'])) echo '<div class="error">'.$errors['email'].'</div>'; ?>
        </div>
      </div>

      <div class="form-row">
        <div class="col">
          <label for="phone">Phone (999-999-9999)</label>
          <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($old['phone'] ?? ''); ?>" <?php echo isset($errors['phone']) ? 'class="field-invalid"' : '';?>>
          <?php if(!empty($errors['phone'])) echo '<div class="error">'.$errors['phone'].'</div>'; ?>
        </div>

        <div class="col">
          <label for="timeslot">Choose Time Slot</label>
          <select id="timeslot" name="timeslot" <?php echo isset($errors['timeslot']) ? 'class="field-invalid"' : '';?>>
            <option value="">-- select a slot --</option>
            <?php foreach($timeslots as $t): 
                $disabled = ($t['seats_remaining'] <= 0) ? 'disabled' : '';
                $label = htmlspecialchars($t['slot_label'] . " — " . ($t['seats_remaining']>0 ? ($t['seats_remaining'].' seats remaining') : 'FULL'));
                $sel = (isset($old['timeslot']) && $old['timeslot']==$t['id']) ? 'selected' : '';
            ?>
              <option value="<?php echo $t['id'];?>" <?php echo "$disabled $sel";?>><?php echo $label;?></option>
            <?php endforeach; ?>
          </select>
          <?php if(!empty($errors['timeslot'])) echo '<div class="error">'.$errors['timeslot'].'</div>'; ?>
        </div>
      </div>

      <div class="form-row">
        <div class="col">
          <button type="submit">Register</button>
        </div>
        <div class="col small center">
          Already registered? <a href="index.php">Login to manage</a><br>
          View <a href="students.php">All Registered Students</a>
        </div>
      </div>
    </form>
  </div>
</body>
</html>
