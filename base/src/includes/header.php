<?php
if (!isset($adminName)) {
    $adminName = 'Admin User';
}
?>
<header>
  <div class="header-content">
    <div class="header-text">
      <h1>Admin Dashboard</h1>
      <p class="subtitle">Zero-Trust Revenue Administration System</p>
    </div>
    <div class="header-right">
      <div class="clock" id="current-time"></div>
      <div class="profile-badge">Officer: <?php echo htmlspecialchars($adminName); ?></div>
      <a href="logout.php" class="btn btn-danger btn-sm ms-3 logout-btn">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </div>
  </div>
</header>
