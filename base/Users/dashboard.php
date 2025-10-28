<?php
session_start();
require_once 'db_connect.php';

// Protect page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT FirstName, LastName, TPIN FROM individuals WHERE IndividualID = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$fullName = $user['FirstName'] . ' ' . $user['LastName'];
$tpin = $user['TPIN'];

// Example queries
$totalPayments = $pdo->prepare("SELECT SUM(AmountPaid) FROM Payments WHERE TPIN = ?");
$totalPayments->execute([$tpin]);
$totalPayments = $totalPayments->fetchColumn() ?: 0;

$outstandingReturns = $pdo->prepare("SELECT COUNT(*) FROM TaxReturns WHERE TPIN = ? AND Status='Pending'");
$outstandingReturns->execute([$tpin]);
$outstandingReturns = $outstandingReturns->fetchColumn();

#$pendingLiabilities = $pdo->prepare("SELECT SUM(Amount) FROM Assessments WHERE TPIN = ? AND Status='Unpaid'");
#$pendingLiabilities->execute([$tpin]);
#$pendingLiabilities = $pendingLiabilities->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Dashboard</title>
  <link rel="stylesheet" href="dashboard.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
<div class="dashboard-container">
  <header class="header">
    <div class="header-left">
      <div class="logo">
        <img src="../img/zra_img.png" alt="TaxOnline" style="width:210px;height:auto;">
      </div>
      <div class="header-icons">
        <i class="fa-solid fa-bars menu-icon" id="menu-toggle"></i>
        <i class="fa-solid fa-magnifying-glass"></i>
        <i class="fa-solid fa-circle-question"></i>
        <span class="zra-authority">Zambia Revenue Authority</span>
      </div>
    </div>
    <div class="header-right">
      <div class="notification-badge">
        <i class="fa-solid fa-bell"></i>
        <span class="badge">1</span>
      </div>
      <div class="user-profile">
        <span><?php echo htmlspecialchars($fullName); ?></span>
        <i class="fa-solid fa-user-circle"></i>
        <i class="fa-solid fa-angle-down"></i>
      </div>
    </div>
  </header>

  <div class="main-wrapper">
    <nav class="sidebar">
      <div class="sidebar-header">TAXONLINE II PORTAL</div>
      <ul class="nav-menu">
        <li class="nav-item"><i class="fa-regular fa-address-card"></i><a href="dashboard.php" style="text-decoration: none; color: #111;">Dashboard</a></li>
        <li class="nav-item"><i class="fa-solid fa-file-invoice"></i><a href="filings.php" style="text-decoration: none; color: #111;">Returns</a></li>
        <li class="nav-item"><i class="fa-solid fa-credit-card"></i><a href="payments.php" style="text-decoration: none; color: #111;">Payments</a></li>
        <li class="nav-item"><i class="fa-solid fa-clipboard-check"></i><a href="compliance.php" style="text-decoration: none; color: #111;">Compliance</a></li>
        <li class="nav-item"><i class="fa-solid fa-user"></i><a href="profile.php" style="text-decoration: none; color: #111;">Profile</a></li>
        <li class="nav-item"><i class="fa-solid fa-sign-out-alt"></i><a href="logout.php" style="text-decoration: none; color: #111;">Logout</a></li>

      </ul>
    </nav>

    <main class="main-content">
      <div class="welcome-section">
        <h1>Welcome <?php echo htmlspecialchars($fullName); ?></h1>
        <button class="profile-button">Taxpayer Profile / TPIN Certificate</button>
      </div>

                <div class="dashboard-grid">
                    <div class="card card-accumulative">
                        <h2 class="card-title">Accumulative tax</h2>
                        <p class="card-content-text">No tax payments information available.</p>
                        <button class="btn btn-primary">View Payments</button>
                    </div>

                    <div class="card card-due-dates">
                        <h2 class="card-title">Due Dates</h2>
                        <div class="status-indicator success-large"></div>
                        <p class="card-content-text">No due dates.</p>
                        <button class="btn btn-primary">View</button>
                    </div>
                     
                    <div class="card card-notifications">
                        <h2 class="card-title">Notifications</h2>
                        <h3>No Notices</h3>
                        <p class="card-content-text">No notices are currently available for viewing.</p>
                    </div>

                    <div class="card card-outstanding">
                        <h2 class="card-title">Outstanding Returns</h2>
                        <div class="status-indicator success-small"></div>
                        <p class="card-content-text">No outstanding returns</p>
                        <button class="btn btn-success">File A Return</button>
                    </div>
                    
                    <div class="card card-pending">
                        <h2 class="card-title">Pending Liabilities</h2>
                        <div class="status-indicator success-small"></div>
                        <p class="card-content-text">No pending Liabilities</p>
                        <button class="btn btn-success">Make A Payment</button>
                    </div>

                    <div class="card card-demand">
                        <h2 class="card-title">Active Demand Letters</h2>
                        <div class="status-indicator success-small"></div>
                        <p class="card-content-text">You have no pending demand notices.</p>
                        <button class="btn btn-primary">View</button>
                    </div>
                </div>

                <div class="section-card" style="margin-top: 20px;">
                            <div class="section-title">Quick Actions</div>
                            <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 15px;">
                                <button class="btn btn-primary" onclick="window.location.href='#business_forms'">
                                    <i class="fas fa-download"></i> Download Tax Forms
                                </button>
                                <button class="btn btn-primary" onclick="window.location.href='taxcalculate.html'">
                                    <i class="fas fa-question-circle"></i> Tax Calculator
                                </button>
                            </div>
                        </div>


    </main>
  </div>
</div>
</body>
</html>
