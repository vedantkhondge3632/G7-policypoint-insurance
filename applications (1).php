<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
require_once __DIR__ . "/../config/db.php";

$adminId = $_SESSION['admin_id'];

$stmt = $conn->prepare("SELECT a.full_name, a.email, a.phone, a.status, a.otp_verified, a.created_at, p.policy_name, p.insurance_type
                         FROM applications a
                         JOIN policies p ON a.policy_id = p.id
                         WHERE p.admin_id = ?
                         ORDER BY a.created_at DESC");
$stmt->bind_param("i", $adminId);
$stmt->execute();
$applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = "Applications";
include __DIR__ . "/../includes/header_admin.php";
?>

<div class="container">
  <h2 class="page-title">Applications Received</h2>
  <p class="page-subtitle">Users who applied for your policies</p>

  <?php if (empty($applications)): ?>
    <div class="alert alert-info">No applications received yet.</div>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Applicant</th>
          <th>Contact</th>
          <th>Policy</th>
          <th>Type</th>
          <th>Applied On</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($applications as $a): ?>
          <tr>
            <td><?php echo htmlspecialchars($a['full_name']); ?></td>
            <td><?php echo htmlspecialchars($a['email']); ?><br><?php echo htmlspecialchars($a['phone']); ?></td>
            <td><?php echo htmlspecialchars($a['policy_name']); ?></td>
            <td><?php echo ucfirst($a['insurance_type']); ?></td>
            <td><?php echo date("d M Y", strtotime($a['created_at'])); ?></td>
            <td>
              <?php if ($a['otp_verified']): ?>
                <span class="badge badge-verified">Confirmed</span>
              <?php else: ?>
                <span class="badge badge-pending">Awaiting OTP</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>
