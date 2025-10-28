<?php
session_start();
require_once 'db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $tpin  = trim($_POST['tpin'] ?? '');

    if ($email && $tpin) {
        // Lookup business by email
        $stmt = $pdo->prepare("SELECT BusinessID, BusinessName, Email, tpin_hash 
                               FROM biz_businesses 
                               WHERE Email = ? LIMIT 1");
        $stmt->execute([$email]);
        $business = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($business && password_verify($tpin, $business['tpin_hash'])) {
            session_regenerate_id(true);
            $_SESSION['business_id']   = $business['BusinessID'];
            $_SESSION['business_name'] = $business['BusinessName'];
            $_SESSION['business_email'] = $business['Email'];

            // Redirect to business dashboard (to be built)
            header('Location: business_dashboard.php');
            exit;
        } else {
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
  <title>Business Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
  <h2 class="mb-4 text-center">Login as Business</h2>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <form method="post" class="mx-auto" style="max-width: 400px;">
    <div class="mb-3">
      <label for="email" class="form-label">Business Email</label>
      <input type="email" class="form-control" id="email" name="email" required>
    </div>
    <div class="mb-3">
      <label for="tpin" class="form-label">Business TPIN</label>
      <input type="password" class="form-control" id="tpin" name="tpin" required>
    </div>
    <button type="submit" class="btn btn-success w-100">Login</button>
  </form>
</div>

<div class="container py-5 text-center">
  <a href="register.php" class="btn btn-secondary w-10">Register a Business</a>
</div>


<div class="container py-5 text-center">
    <a href="../index.html">
      <button type="submit" class="btn btn-primary w-10">Return</button>
  </a>
    </div>


</body>
</html>
