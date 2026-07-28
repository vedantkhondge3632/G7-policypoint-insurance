<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . " - PolicyPoint" : "PolicyPoint Insurance"; ?></title>
<link rel="stylesheet" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>../assets/css/style.css">
</head>
<body>
<nav class="navbar">
  <div class="brand">Policy<span>Point</span></div>
  <div class="navbar-links">
    <?php if ($isLoggedIn): ?>
      <a href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>home.php">Home</a>
      <a href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>my_applications.php">My Applications</a>
      <span>Hi, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
      <a class="btn-outline" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>logout.php">Logout</a>
    <?php else: ?>
      <a href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>login.php">User Login</a>
      <a class="btn-outline" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>register.php">Register</a>
      <a href="../admin/login.php">Company Login</a>
    <?php endif; ?>
  </div>
</nav>
