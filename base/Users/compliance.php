<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT TPIN, FirstName, LastName FROM individuals WHERE IndividualID = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$tpin = $user['TPIN'];
$fullName = $user['FirstName'].' '.$user['LastName'];

$currentPage = basename($_SERVER['PHP_SELF']);

// Example compliance calculation
$totalReturns = $pdo->prepare("SELECT COUNT(*) FROM TaxReturns WHERE TPIN = ?");
$totalReturns->execute([$tpin]);
$totalReturns = $totalReturns->fetchColumn();

$filedReturns = $pdo->prepare("SELECT COUNT(*) FROM TaxReturns WHERE TPIN = ? AND Status='Filed'");
$filedReturns->execute([$tpin]);
$filedReturns = $filedReturns->fetchColumn();

$complianceRate = $totalReturns > 0 ? round(($filedReturns/$totalReturns)*100,2) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Compliance Status</title>
  <link rel="stylesheet" href="dashboard.css">
</head>
<body>
<?php include 'includes/user_header.php'; ?>
  <?php include 'includes/user_sidebar.php'; ?>  <div class="main-content">
    <main class="content">
      <h2>Compliance Overview</h2>
      <p>Total Returns: <?php echo $totalReturns; ?></p>
      <p>Filed Returns: <?php echo $filedReturns; ?></p>
      <p>Compliance Rate: <?php echo $complianceRate; ?>%</p>
    </main>
  </div>
</body>
</html>
