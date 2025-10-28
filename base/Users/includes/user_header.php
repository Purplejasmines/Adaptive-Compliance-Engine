<?php
if (!isset($fullName)) {
    $fullName = 'Taxpayer';
}
if (!isset($email)) {
    $email = '';
}
?>
<header class="header">
  <div class="header-left">
    <div class="logo">
      <img src="../zra_logo.png" alt="TaxOnline" style="width: 180px; height: auto;">
    </div>
    <div class="header-icons">
      <i class="fa-solid fa-bars menu-icon" id="menu-toggle"></i>
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
