<?php
session_start();
require_once 'db_connect.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accountType = $_POST['account_type'] ?? '';

    if ($accountType === 'individual') {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName  = trim($_POST['last_name'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $tpin      = trim($_POST['tpin'] ?? '');
        $tpinConfirm = trim($_POST['tpin_confirm'] ?? '');

        if ($firstName && $lastName && filter_var($email, FILTER_VALIDATE_EMAIL) && $tpin && $tpin === $tpinConfirm) {
            $tpinHash = password_hash($tpin, PASSWORD_DEFAULT);

            try {
                $pdo->beginTransaction();

                // Insert into taxpayers
                $stmt = $pdo->prepare("INSERT INTO taxpayers (TPIN, TaxpayerType, RegistrationDate, Status, PrimaryEmail) 
                                       VALUES (?, 'Individual', CURDATE(), 'Active', ?)");
                $stmt->execute([$tpin, $email]);

                // Insert into individuals
                $stmt = $pdo->prepare("INSERT INTO individuals (TPIN, FirstName, LastName, email) VALUES (?, ?, ?, ?)");
                $stmt->execute([$tpin, $firstName, $lastName, $email]);
                $individualId = $pdo->lastInsertId();

                // Insert into tpin_hashes
                $stmt = $pdo->prepare("INSERT INTO tpin_hashes (IndividualID, tpin_hash) VALUES (?, ?)");
                $stmt->execute([$individualId, $tpinHash]);

                $pdo->commit();
                header("Location: user_login.php");
                exit();
            } catch (PDOException $e) {
                $pdo->rollBack();
                $errors[] = "Error: " . $e->getMessage();
            }
        } else {
            $errors[] = "Please fill all individual fields correctly.";
        }
    }

    if ($accountType === 'business') {
        $businessName = trim($_POST['business_name'] ?? '');
        $email        = trim($_POST['email'] ?? '');
        $tpin         = trim($_POST['tpin'] ?? '');
        $tpinConfirm  = trim($_POST['tpin_confirm'] ?? '');

        if ($businessName && filter_var($email, FILTER_VALIDATE_EMAIL) && $tpin && $tpin === $tpinConfirm) {
            $tpinHash = password_hash($tpin, PASSWORD_DEFAULT);

            try {
// For businesses
                  $stmt = $pdo->prepare("SELECT COUNT(*) FROM businesses WHERE TPIN = ?");
                  $stmt->execute([$tpin]);
              if ($stmt->fetchColumn() > 0) {
                $errors[] = "This TPIN is already registered as a business. Please log in instead.";
              } else {
                  $stmt = $pdo->prepare("INSERT INTO businesses (BusinessName, TPIN, Email, tpin_hash) VALUES (?, ?, ?, ?)");
                  $stmt->execute([$businessName, $tpin, $email, $tpinHash]);
              }

                header("Location: ../businesses/business_login.php");
                exit();
            } catch (PDOException $e) {
                $errors[] = "Error: " . $e->getMessage();
            }
        } else {
            $errors[] = "Please fill all business fields correctly.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register Taxpayer</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
  <h2 class="mb-4 text-center">Register</h2>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <ul>
        <?php foreach ($errors as $err): ?>
          <li><?php echo htmlspecialchars($err); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <!-- Tabs -->
  <ul class="nav nav-tabs" id="registerTabs" role="tablist">
    <li class="nav-item">
      <button class="nav-link active" id="individual-tab" data-bs-toggle="tab" data-bs-target="#individual" type="button">Individual</button>
    </li>
    <li class="nav-item">
      <button class="nav-link" id="business-tab" data-bs-toggle="tab" data-bs-target="#business" type="button">Business</button>
    </li>
  </ul>

  <div class="tab-content mt-3">
    <!-- Individual Registration -->
    <div class="tab-pane fade show active" id="individual">
      <form method="post">
        <input type="hidden" name="account_type" value="individual">
        <div class="mb-3"><label>First Name</label><input type="text" name="first_name" class="form-control" required></div>
        <div class="mb-3"><label>Last Name</label><input type="text" name="last_name" class="form-control" required></div>
        <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
        <div class="mb-3"><label>TPIN</label><input type="password" name="tpin" class="form-control" required></div>
        <div class="mb-3"><label>Confirm TPIN</label><input type="password" name="tpin_confirm" class="form-control" required></div>
        <button type="submit" class="btn btn-primary w-100">Register Individual</button>
      </form>
    </div>

    <!-- Business Registration -->
    <div class="tab-pane fade" id="business">
      <form method="post">
        <input type="hidden" name="account_type" value="business">
        <div class="mb-3"><label>Business Name</label><input type="text" name="business_name" class="form-control" required></div>
        <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
        <div class="mb-3"><label>TPIN</label><input type="password" name="tpin" class="form-control" required></div>
        <div class="mb-3"><label>Confirm TPIN</label><input type="password" name="tpin_confirm" class="form-control" required></div>
        <button type="submit" class="btn btn-success w-100">Register Business</button>
      </form>
    </div>

<div class="mt-3">
        <a href="login.php" class="btn btn-secondary">Login</a>
    </div>


  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
