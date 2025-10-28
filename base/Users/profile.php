<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../user_login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM individuals WHERE IndividualID = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Profile</title>
  <link rel="stylesheet" href="dashboard.css">
</head>
<body>
<?php include 'includes/user_header.php'; ?>
  <?php include 'includes/user_sidebar.php'; ?>  <div class="main-content">
    <main class="content">
      <h2>My Profile</h2>
      <table>
        <tr><th>First Name</th><td><?php echo htmlspecialchars($user['FirstName']); ?></td></tr>
        <tr><th>Last Name</th><td><?php echo htmlspecialchars($user['LastName']); ?></td></tr>
        <tr><th>Email</th><td><?php echo htmlspecialchars($user['email']); ?></td></tr>
        <tr><th>TPIN</th><td><?php echo htmlspecialchars($user['TPIN']); ?></td></tr>
      </table>
    </main>
  </div>
</body>
</html>
