<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once __DIR__ . "/../config/db.php";

// Count how many policies exist per type, just to show something useful
$counts = ['life' => 0, 'health' => 0, 'auto' => 0, 'home' => 0];
$res = $conn->query("SELECT insurance_type, COUNT(*) as cnt FROM policies GROUP BY insurance_type");
while ($row = $res->fetch_assoc()) {
    $counts[$row['insurance_type']] = $row['cnt'];
}

$pageTitle = "Home";
$baseUrl = "";
include __DIR__ . "/../includes/header_user.php";
?>

<div class="container">
  <h2 class="page-title">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h2>
  <p class="page-subtitle">Select an insurance category to view and compare available plans</p>

  <div class="type-grid">
    <a href="policies.php?type=life" class="type-card">
      <div class="icon">🛡️</div>
      <h3>Life Insurance</h3>
      <p><?php echo $counts['life']; ?> plans available</p>
    </a>
    <a href="policies.php?type=health" class="type-card">
      <div class="icon">🏥</div>
      <h3>Health Insurance</h3>
      <p><?php echo $counts['health']; ?> plans available</p>
    </a>
    <a href="policies.php?type=auto" class="type-card">
      <div class="icon">🚗</div>
      <h3>Auto Insurance</h3>
      <p><?php echo $counts['auto']; ?> plans available</p>
    </a>
    <a href="policies.php?type=home" class="type-card">
      <div class="icon">🏠</div>
      <h3>Home Insurance</h3>
      <p><?php echo $counts['home']; ?> plans available</p>
    </a>
  </div>
</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>
