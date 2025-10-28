<?php
if (!isset($currentPage)) {
    $currentPage = basename($_SERVER['PHP_SELF']);
}
?>
<nav class="sidebar">
  <div class="sidebar-header">TAXONLINE II PORTAL</div>
  <ul class="nav-menu">
    <li class="nav-item <?php if($currentPage=='user_dashboard.php') echo 'active'; ?>">
      <i class="fa fa-home"></i>
      <a href="dashboard.php" style="text-decoration: none; color: #111;">Dashboard</a>
    </li>
    <li class="nav-item <?php if($currentPage=='filings.php') echo 'active'; ?>">
      <i class="fa-solid fa-file-invoice"></i>
      <a href="filings.php" style="text-decoration: none; color: #111;">Returns</a>
    </li>
    <li class="nav-item <?php if($currentPage=='payments.php') echo 'active'; ?>">
      <i class="fa-solid fa-credit-card"></i>
      <a href="payments.php" style="text-decoration: none; color: #111;">Payments</a>
    </li>
    <li class="nav-item <?php if($currentPage=='compliance.php') echo 'active'; ?>">
      <i class="fa-solid fa-clipboard-check"></i>
      <a href="compliance.php" style="text-decoration: none; color: #111;">Compliance</a>
    </li>
    <li class="nav-item <?php if($currentPage=='profile.php') echo 'active'; ?>">
      <i class="fa-solid fa-user"></i>
      <a href="profile.php" style="text-decoration: none; color: #111;">Profile</a>
    </li>
    <li class="nav-item">
      <i class="fa-solid fa-sign-out-alt"></i>
      <a href="logout.php" style="text-decoration: none; color: #111;">Logout</a>
    </li>
  </ul>
</nav>
