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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Tax Filings</title>
  <link rel="stylesheet" href="dashboard.css">
</head>
<body>
<?php include 'includes/user_header.php'; ?>
  <?php include 'includes/user_sidebar.php'; ?>
  <div class="main-content">
    <main class="content">
      <h2>My Tax Filings</h2>
      <table>
        <thead>
          <tr><th>Return ID</th><th>Year</th><th>Status</th><th>Date Filed</th></tr>
        </thead>
        <tbody>
          <?php
          $stmt = $pdo->prepare("SELECT ReturnID, Status, FilingDate FROM TaxReturns WHERE TPIN = ?");
          $stmt->execute([$tpin]);
          while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
              echo "<tr>
                      <td>".htmlspecialchars($row['ReturnID'])."</td>
                      <td>".htmlspecialchars($row['TaxYear'])."</td>
                      <td>".htmlspecialchars($row['Status'])."</td>
                      <td>".htmlspecialchars($row['FilingDate'])."</td>
                    </tr>";
          }
          ?>
        </tbody>
      </table>
    </main>
  </div>
</body>
</html>
