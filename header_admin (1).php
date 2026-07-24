<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$isAdminLoggedIn = isset($_SESSION['admin_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . " - PolicyPoint" : "PolicyPoint Insurance"; ?></title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<nav class="navbar">
  <div class="brand">Policy<span>Point</span> <small style="font-size:0.7rem; color:#c9d9ea;">Company Portal</small></div>
  <div class="navbar-links">
    <?php if ($isAdminLoggedIn): ?>
      <a href="dashboard.php">Dashboard</a>
      <a href="add_policy.php">Add Policy</a>
      <a href="applications.php">Applications</a>
      <span>Hi, <?php echo htmlspecialchars($_SESSION['admin_company']); ?></span>
      <a class="btn-outline" href="logout.php">Logout</a>
    <?php else: ?>
      <a href="login.php">Company Login</a>
      <a class="btn-outline" href="register.php">Register Company</a>
      <a href="../user/login.php">User Login</a>
    <?php endif; ?>
  </div>
</nav>
