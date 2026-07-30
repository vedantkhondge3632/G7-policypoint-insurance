<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once __DIR__ . "/../config/db.php";

$allowedTypes = ['life', 'health', 'auto', 'home'];
$type = strtolower($_GET['type'] ?? '');
if (!in_array($type, $allowedTypes)) {
    $type = 'life';
}
$typeLabels = ['life' => 'Life Insurance', 'health' => 'Health Insurance', 'auto' => 'Auto Insurance', 'home' => 'Home Insurance'];

$stmt = $conn->prepare("SELECT p.id, p.policy_name, p.description, p.premium, p.coverage_amount, p.duration_years, a.company_name
                         FROM policies p JOIN admins a ON p.admin_id = a.id
                         WHERE p.insurance_type = ? ORDER BY p.premium ASC");
$stmt->bind_param("s", $type);
$stmt->execute();
$result = $stmt->get_result();
$policies = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = $typeLabels[$type];
$baseUrl = "";
include __DIR__ . "/../includes/header_user.php";
?>

<div class="container">
  <div class="section-header">
    <div>
      <h2 class="page-title"><?php echo htmlspecialchars($typeLabels[$type]); ?> Plans</h2>
      <p class="page-subtitle">Compare plans from different companies and apply for the one that suits you</p>
    </div>
    <div>
      <a href="home.php" class="btn btn-small" style="background:#6b7280;">&larr; Back to Categories</a>
    </div>
  </div>

  <div style="margin-bottom:20px;">
    <?php foreach ($typeLabels as $key => $label): ?>
      <a href="policies.php?type=<?php echo $key; ?>"
         class="btn btn-small"
         style="margin-right:8px; margin-bottom:8px; <?php echo $key === $type ? '' : 'background:#e2e6ea;color:#2b2b2b;'; ?>">
        <?php echo $label; ?>
      </a>
    <?php endforeach; ?>
  </div>

  <?php if (empty($policies)): ?>
    <div class="alert alert-info">No plans have been listed for this category yet. Please check back soon.</div>
  <?php else: ?>
    <div class="policy-grid">
      <?php foreach ($policies as $p): ?>
        <div class="policy-card">
          <div class="company"><?php echo htmlspecialchars($p['company_name']); ?></div>
          <h3><?php echo htmlspecialchars($p['policy_name']); ?></h3>
          <p class="desc"><?php echo htmlspecialchars($p['description']); ?></p>
          <div class="meta"><b>Premium:</b> ₹<?php echo number_format($p['premium'], 2); ?> / year</div>
          <div class="meta"><b>Coverage:</b> ₹<?php echo number_format($p['coverage_amount'], 2); ?></div>
          <div class="meta"><b>Duration:</b> <?php echo (int)$p['duration_years']; ?> year(s)</div>
          <a href="apply.php?policy_id=<?php echo (int)$p['id']; ?>" class="btn">Apply for Plan</a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>
