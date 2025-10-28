<?php
declare(strict_types=1);

// Security-related response headers
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer-when-downgrade');

session_start();
require_once 'db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $validationErrors = [];

    // Basic validation
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $validationErrors[] = 'Please enter a valid email address.';
    }
    if ($password === '' || strlen($password) < 8) {
        $validationErrors[] = 'Please enter your password (min 8 characters).';
    }

    if (empty($validationErrors)) {
        // Query user by email
        $stmt = $pdo->prepare("SELECT id, full_name, email, password_hash FROM tax_admins WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Validate password hash and login
        if (!$user) {
            $error = 'No account found for that email.';
        } elseif (!isset($user['password_hash']) || !is_string($user['password_hash'])) {
            $error = 'Password hash is missing or invalid.';
        } elseif (!password_verify($password, $user['password_hash'])) {
            $error = 'Incorrect password.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['full_name'] ?? 'Admin';
            header('Location: dashboard.php');
            exit;
        }
    } else {
        $error = implode(' ', $validationErrors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Tax Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container py-5">
    <h2 class="mb-4 text-center">Login Tax Admin</h2>
    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post" class="mx-auto" style="max-width: 400px;">
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" autocomplete="current-password" minlength="8" class="form-control" id="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
