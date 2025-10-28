<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../user_login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT TPIN, FirstName, LastName FROM individuals WHERE IndividualID = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$tpin = $user['TPIN'];
$fullName = $user['FirstName'].' '.$user['LastName'];

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Payments</title>
  <link rel="stylesheet" href="dashboard.css">
</head>
<body>
      <?php include 'includes/user_header.php'; ?>
  <?php include 'includes/user_sidebar.php'; ?>
  <div class="main-content">
    <main class="content">
      <h2>Payment History</h2>
      <table>
        <thead>
          <tr><th>Payment ID</th><th>Date</th><th>Amount</th><th>Method</th></tr>
        </thead>
        <tbody>
          <?php
          $stmt = $pdo->prepare("SELECT PaymentID, PaymentDate, AmountPaid, PaymentMethod FROM Payments WHERE TPIN = ?");
          $stmt->execute([$tpin]);
          while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
              echo "<tr>
                      <td>".htmlspecialchars($row['PaymentID'])."</td>
                      <td>".htmlspecialchars($row['PaymentDate'])."</td>
                      <td>ZMW ".number_format($row['AmountPaid'],2)."</td>
                      <td>".htmlspecialchars($row['PaymentMethod'])."</td>
                    </tr>";
          }
          ?>
        </tbody>
      </table>
    </main>
  </div>
</body>
</html>
