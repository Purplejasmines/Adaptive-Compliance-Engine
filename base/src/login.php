<?php
session_start();
require_once 'db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $tpin = trim($_POST['tpin'] ?? '');

    if ($email && $tpin) {
        // Lookup user by email
$stmt = $pdo->prepare("
    SELECT i.IndividualID, i.FirstName, i.LastName, i.email, h.tpin_hash
    FROM individuals i
    JOIN tpin_hashes h ON i.IndividualID = h.IndividualID
    WHERE i.email = ? LIMIT 1
");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($tpin, $user['tpin_hash'])) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['IndividualID'];
    $_SESSION['user_name'] = $user['FirstName'] . ' ' . $user['LastName'];
    header('Location: ../users/dashboard.php');
    exit;
}
 else {
            $error = 'Invalid email or TPIN.';
        }
    } else {
        $error = 'Both email and TPIN are required.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Taxpayer</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container py-5">
    <h2 class="mb-4 text-center">Login as Taxpayer</h2>
    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post" class="mx-auto" style="max-width: 400px;">
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" required>
      </div>
      <div class="mb-3">
        <label for="tpin" class="form-label">Taxpayer Identification Number (TPIN)</label>
        <input type="password" class="form-control" id="tpin" name="tpin" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
  </div>

    <div class="container py-5 text-center">
    <a href="../businesses/business_login.php">
      <button type="submit" class="btn btn-primary w-10">Login as Business</button>
  </a>
    </div>



  <div class="container py-3">
    <a href="../index.html" class="btn btn-secondary w-100">Return</a>
  </div>
</body>
</html>
