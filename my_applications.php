<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once __DIR__ . "/../config/db.php";

$stmt = $conn->prepare("SELECT a.id, a.status, a.otp_verified, a.created_at, p.policy_name, p.insurance_type, comp.company_name
                         FROM applications a
                         JOIN policies p ON a.policy_id = p.id
                         JOIN admins comp ON p.admin_id = comp.id
                         WHERE a.user_id = ?
                         ORDER BY a.created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = "My Applications";
$baseUrl = "";
include __DIR__ . "/../includes/header_user.php";
?>

<div class="container">
  <h2 class="page-title">My Applications</h2>
  <p class="page-subtitle">Track the status of the insurance plans you've applied for</p>

  <?php if (empty($applications)): ?>
    <div class="alert alert-info">You haven't applied for any plans yet. <a href="home.php">Browse plans</a>.</div>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Policy</th>
          <th>Company</th>
          <th>Type</th>
          <th>Applied On</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($applications as $a): ?>
          <tr>
            <td><?php echo htmlspecialchars($a['policy_name']); ?></td>
            <td><?php echo htmlspecialchars($a['company_name']); ?></td>
            <td><?php echo ucfirst($a['insurance_type']); ?></td>
            <td><?php echo date("d M Y", strtotime($a['created_at'])); ?></td>
            <td>
              <?php if ($a['otp_verified']): ?>
                <span class="badge badge-verified"><?php echo htmlspecialchars($a['status']); ?></span>
              <?php else: ?>
                <span class="badge badge-pending">Awaiting OTP Verification</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>
