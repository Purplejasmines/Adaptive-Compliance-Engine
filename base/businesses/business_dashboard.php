<?php
session_start();
require_once 'db_connect.php';

// Protect page
if (!isset($_SESSION['business_id'])) {
    header('Location: business_login.php');
    exit();
}

$businessId = $_SESSION['business_id'];

// Fetch business info
$stmt = $pdo->prepare("SELECT BusinessName, TPIN, Email FROM biz_businesses WHERE BusinessID = ?");
$stmt->execute([$businessId]);
$business = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$business) {
    // Fallback if session is stale
    header('Location: business_login.php');
    exit();
}

$businessName = $business['BusinessName'];
$tpin         = $business['TPIN'];
$email        = $business['Email'];

// === Dashboard Data ===

// Pending Returns
$stmt = $pdo->prepare("SELECT COUNT(*) FROM biz_taxreturns WHERE TPIN = ? AND Status='Pending'");
$stmt->execute([$tpin]);
$pendingReturns = (int)$stmt->fetchColumn();

// Outstanding Balance (sum of unpaid assessments)
$stmt = $pdo->prepare("SELECT COALESCE(SUM(Amount),0) FROM biz_assessments WHERE TPIN = ? AND Status='Unpaid'");
$stmt->execute([$tpin]);
$outstandingBalance = (float)$stmt->fetchColumn();

// Employees (count from biz_employees)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM biz_employees WHERE BusinessTPIN = ?");
$stmt->execute([$tpin]);
$employeeCount = (int)$stmt->fetchColumn();

// Overdue Payments (count + sum from biz_payments)
$stmt = $pdo->prepare("SELECT COUNT(*), COALESCE(SUM(AmountDue),0) FROM biz_payments WHERE TPIN = ? AND Status='Overdue'");
$stmt->execute([$tpin]);
list($overdueCount, $overdueAmount) = $stmt->fetch(PDO::FETCH_NUM);
$overdueCount = (int)$overdueCount;
$overdueAmount = (float)$overdueAmount;

// Recent Tax Returns (last 5)
$recentReturnsStmt = $pdo->prepare("SELECT TaxType, TaxYear, FilingDate, Status, DueDate
                                    FROM biz_taxreturns
                                    WHERE TPIN = ?
                                    ORDER BY COALESCE(FilingDate, DueDate) DESC
                                    LIMIT 5");
$recentReturnsStmt->execute([$tpin]);
$recentReturns = $recentReturnsStmt->fetchAll(PDO::FETCH_ASSOC);

// Recent Activity from notices (last 5)
$recentActivityStmt = $pdo->prepare("SELECT NoticeType, Message, CreatedAt 
                                     FROM biz_notices 
                                     WHERE TPIN = ? 
                                     ORDER BY CreatedAt DESC 
                                     LIMIT 5");
$recentActivityStmt->execute([$tpin]);
$recentActivities = $recentActivityStmt->fetchAll(PDO::FETCH_ASSOC);

function statusBadgeClass($status) {
    $s = strtolower($status ?? '');
    if ($s === 'pending') return 'status-pending';
    if ($s === 'filed' || $s === 'completed') return 'status-completed';
    if ($s === 'overdue') return 'status-overdue';
    // default
    return 'status-pending';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Business Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <style>
        :root {
            --zra-blue: #0056b3;
            --zra-light-blue: #e6f2ff;
            --zra-green: #28a745;
            --zra-red: #dc3545;
            --zra-orange: #fd7e14;
            --zra-gray: #6c757d;
            --zra-light-gray: #f8f9fa;
            --zra-dark: #343a40;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: var(--zra-dark);
            line-height: 1.6;
        }
        
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 200px;
            max-height: 200vh;
            background: #0c6fa1;
            color: white;
            transition: all 0.3s;
        }
        
        .sidebar-header {
            padding: 20px;
            background: #0c6fa1;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .sidebar-header img {
            height: 40px;
        }
        
        .sidebar-menu {
            padding: 15px 0;
        }
        
        .menu-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .menu-item:hover, .menu-item.active {
            background: rgba(255, 255, 255, 0.1);
            border-left: 4px solid white;
        }
        
        .menu-item i {
            width: 20px;
            text-align: center;
        }
        
        /* Main Content Styles */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .top-nav {
            background: #0c6fa1;
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .search-box {
            display: flex;
            align-items: center;
            background: var(--zra-light-gray);
            border-radius: 4px;
            padding: 8px 15px;
            width: 300px;
        }
        
        .search-box input {
            border: none;
            background: transparent;
            margin-left: 10px;
            width: 100%;
            outline: none;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            color: var(--zra-light-gray);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--zra-blue);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .content-area {
            padding: 25px;
            flex: 1;
        }
        
        .page-title {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-title h1 {
            font-size: 24px;
            color: var(--zra-blue);
        }
        
        /* Dashboard Widgets */
        .widgets-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .widget {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-top: 4px solid var(--zra-blue);
        }
        
        .widget.orange {
            border-top-color: var(--zra-orange);
        }
        
        .widget.green {
            border-top-color: var(--zra-green);
        }
        
        .widget.red {
            border-top-color: var(--zra-red);
        }
        
        .widget-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .widget-title {
            font-size: 16px;
            color: var(--zra-gray);
        }
        
        .widget-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: var(--zra-light-blue);
            color: var(--zra-blue);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .widget-value {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .widget-change {
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .widget-change.positive {
            color: var(--zra-green);
        }
        
        .widget-change.negative {
            color: var(--zra-red);
        }
        
        /* Tax Sections */
        .tax-sections {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
        }
        
        .section-card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .section-title {
            font-size: 18px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            color: var(--zra-blue);
        }
        
        /* Tables */
        .tax-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .tax-table th {
            text-align: left;
            padding: 12px 15px;
            background: var(--zra-light-gray);
            color: var(--zra-gray);
            font-weight: 600;
        }
        
        .tax-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-completed {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-overdue {
            background: #f8d7da;
            color: #721c24;
        }
        
        .btn {
            padding: 8px 15px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: var(--zra-blue);
            color: white;
        }
        
        .btn-primary:hover {
            background: #004494;
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid var(--zra-blue);
            color: var(--zra-blue);
        }
        
        .btn-outline:hover {
            background: var(--zra-light-blue);
        }
        
        /* Recent Activity */
        .activity-list {
            list-style: none;
        }
        
        .activity-item {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--zra-light-blue);
            color: var(--zra-blue);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .activity-details {
            flex: 1;
        }
        
        .activity-title {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .activity-time {
            font-size: 12px;
            color: var(--zra-gray);
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .tax-sections {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                width: 70px;
            }
            
            .sidebar .menu-text {
                display: none;
            }
            
            .sidebar-header h2 {
                display: none;
            }
            
            .sidebar-header {
                justify-content: center;
            }
        }
        
        @media (max-width: 768px) {
            .widgets-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>ZRA Portal</h2>
            </div>
            <div class="sidebar-menu">
                <div class="menu-item active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="menu-text">Dashboard</span>
                </div>
                <div class="menu-item">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <a href="#business_returns" style="text-decoration:none;color:#fff;"><span class="menu-text">Tax Returns</span></a>
                </div>
                <div class="menu-item">
                    <i class="fas fa-money-check-alt"></i>
                    <a href="#business_payments" style="text-decoration:none;color:#fff;"><span class="menu-text">Payments</span></a>
                </div>
                <div class="menu-item">
                    <i class="fas fa-users"></i>
                    <a href="#business_employees" style="text-decoration:none;color:#fff;"><span class="menu-text">Employee PAYE</span></a>
                </div>
                <div class="menu-item">
                    <i class="fas fa-chart-bar"></i>
                    <a href="#business_reports" style="text-decoration:none;color:#fff;"><span class="menu-text">Reports</span></a>
                </div>
                <div class="menu-item" style="margin-top: 70vh;">
                    <i class="fas fa-bell"></i>
                    <a href="#business_notifications" style="text-decoration:none;color:#fff;"><span class="menu-text">Notifications</span></a>
                </div>
                <div class="menu-item">
                    <i class="fas fa-cog"></i>
                    <a href="#business_settings" style="text-decoration:none;color:#fff;"><span class="menu-text">Settings</span></a>
                </div>
                <div class="menu-item">
                    <i class="fas fa-angle-left"></i>
                    <a href="../index.html" style="text-decoration: none; color: var(--zra-light-gray);"><span class="menu-text">Logout</span></a>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation -->
            <div class="top-nav">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search...">
                </div>
                <div class="user-info">
                    <div class="notifications">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="user-avatar"><?php echo strtoupper(substr($businessName,0,2)); ?></div>
                    <div class="user-details">
                        <div class="user-name"><?php echo htmlspecialchars($businessName); ?></div>
                        <div class="user-role">Administrator</div>
                    </div>
                </div>
            </div>
            
            <!-- Content Area -->
            <div class="content-area">
                <div class="page-title">
                    <h1>Company Tax Dashboard</h1>
                    <div class="actions">
                        <button class="btn btn-outline">Help & Support</button>
                    </div>
                </div>
                
                <!-- Dashboard Widgets -->
                <div class="widgets-container">
                    <div class="widget">
                        <div class="widget-header">
                            <div class="widget-title">Pending Returns</div>
                            <div class="widget-icon"><i class="fas fa-file-alt"></i></div>
                        </div>
                        <div class="widget-value"><?php echo $pendingReturns; ?></div>
                        <div class="widget-change positive">
                            <i class="fas fa-arrow-down"></i>
                            <span>Compared to last month</span>
                        </div>
                    </div>
                    
                    <div class="widget orange">
                        <div class="widget-header">
                            <div class="widget-title">Outstanding Balance</div>
                            <div class="widget-icon"><i class="fas fa-money-bill-wave"></i></div>
                        </div>
                        <div class="widget-value">ZMW <?php echo number_format($outstandingBalance,2); ?></div>
                        <div class="widget-change negative">
                            <i class="fas fa-arrow-up"></i>
                            <span>Due soon</span>
                        </div>
                    </div>
                    
                    <div class="widget green">
                        <div class="widget-header">
                            <div class="widget-title">Employees</div>
                            <div class="widget-icon"><i class="fas fa-users"></i></div>
                        </div>
                        <div class="widget-value"><?php echo $employeeCount; ?></div>
                        <div class="widget-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>New hires this month</span>
                        </div>
                    </div>
                    
                    <div class="widget red">
                        <div class="widget-header">
                            <div class="widget-title">Overdue Payments</div>
                            <div class="widget-icon"><i class="fas fa-exclamation-circle"></i></div>
                        </div>
                        <div class="widget-value"><?php echo $overdueCount; ?></div>
                        <div class="widget-change negative">
                            <i class="fas fa-arrow-up"></i>
                            <span>ZMW <?php echo number_format($overdueAmount,2); ?> overdue</span>
                        </div>
                    </div>
                </div>

                <!-- Tax Sections -->
                <div class="tax-sections">
                    <!-- Left Column -->
                    <div class="left-column">
                        <div class="section-card">
                            <div class="section-title">Recent Tax Returns</div>
                            <table class="tax-table">
                                <thead>
                                    <tr>
                                        <th>Tax Type</th>
                                        <th>Period</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
<?php if (!empty($recentReturns)): ?>
    <?php foreach ($recentReturns as $r): ?>
        <?php
            $taxType = $r['TaxType'] ?? 'N/A';
            // Build a period string: if FilingDate exists, show Month + Year, else just TaxYear
            $period = '';
            if (!empty($r['FilingDate'])) {
                $period = date('F Y', strtotime($r['FilingDate']));
            } elseif (!empty($r['DueDate'])) {
                $period = date('F Y', strtotime($r['DueDate']));
            } elseif (!empty($r['TaxYear'])) {
                $period = 'Year ' . $r['TaxYear'];
            } else {
                $period = 'N/A';
            }

            $dueDate = !empty($r['DueDate']) ? date('M d, Y', strtotime($r['DueDate'])) : '—';
            $status  = $r['Status'] ?? 'Pending';
            $badge   = statusBadgeClass($status);
            $isPending = strtolower($status) === 'pending';
        ?>
        <tr>
            <td><?php echo htmlspecialchars($taxType); ?></td>
            <td><?php echo htmlspecialchars($period); ?></td>
            <td><?php echo htmlspecialchars($dueDate); ?></td>
            <td><span class="status-badge <?php echo $badge; ?>"><?php echo htmlspecialchars($status); ?></span></td>
            <td>
                <?php if ($isPending): ?>
                    <button class="btn btn-primary">File</button>
                <?php else: ?>
                    <button class="btn btn-outline">View</button>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="5">No recent returns found.</td>
    </tr>
<?php endif; ?>
</tbody>

                            </table>
                        </div>
                    </div>
                    
                    <!-- Right Column -->
                    <div class="right-column">
                        <div class="section-card">
                            <div class="section-title">Recent Activity</div>
                            <ul class="activity-list">
                                <?php if (!empty($recentActivities)): ?>
                                    <?php foreach ($recentActivities as $a): ?>
                                        <li class="activity-item">
                                            <div class="activity-icon">
                                                <i class="fas fa-bell"></i>
                                            </div>
                                            <div class="activity-details">
                                                <div class="activity-title"><?php echo htmlspecialchars($a['NoticeType'] ?? 'Activity'); ?></div>
                                                <div class="activity-time"><?php echo htmlspecialchars($a['CreatedAt']); ?></div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li class="activity-item">
                                        <div class="activity-icon"><i class="fas fa-info-circle"></i></div>
                                        <div class="activity-details">
                                            <div class="activity-title">No recent activity</div>
                                            <div class="activity-time">—</div>
                                        </div>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        
                        <div class="section-card" style="margin-top: 20px;">
                            <div class="section-title">Quick Actions</div>
                            <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 15px;">
                                <button class="btn btn-primary" onclick="window.location.href='#business_employees_add'">
                                    <i class="fas fa-plus-circle"></i> Register New Employee
                                </button>
                                <button class="btn btn-outline" onclick="window.location.href='#business_forms'">
                                    <i class="fas fa-download"></i> Download Tax Forms
                                </button>
                                <button class="btn btn-outline" onclick="window.location.href='taxcalculate.html'">
                                    <i class="fas fa-question-circle"></i> Tax Calculator
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- /content-area -->
        </div> <!-- /main-content -->
    </div> <!-- /dashboard-container -->

    <script>
        // Simple interactive elements for demonstration
        document.addEventListener('DOMContentLoaded', function() {
            // Menu item selection
            const menuItems = document.querySelectorAll('.menu-item');
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    menuItems.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                });
            });
            
            // Simulate loading data example (kept minimal)
            setTimeout(() => {
                const firstWidgetValue = document.querySelector('.widget .widget-value');
                if (firstWidgetValue) firstWidgetValue.textContent = '<?php echo $pendingReturns; ?>';
            }, 500);
        });
    </script>
</body>
</html>
