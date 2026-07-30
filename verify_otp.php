<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/mailer.php";

$applicationId = (int)($_GET['application_id'] ?? $_POST['application_id'] ?? 0);

$stmt = $conn->prepare("SELECT a.id, a.otp, a.otp_verified, a.full_name, a.email, a.status,
                                p.policy_name, comp.company_name
                         FROM applications a
                         JOIN policies p ON a.policy_id = p.id
                         JOIN admins comp ON p.admin_id = comp.id
                         WHERE a.id = ? AND a.user_id = ?");
$stmt->bind_param("ii", $applicationId, $_SESSION['user_id']);
$stmt->execute();
$application = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$application) {
    header("Location: home.php");
    exit;
}

$errors = [];
$verified = ((int)$application['otp_verified'] === 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$verified) {
    $enteredOtp = trim($_POST['otp'] ?? '');

    if ($enteredOtp === $application['otp']) {
        $stmt = $conn->prepare("UPDATE applications SET otp_verified = 1, status = 'Confirmed - Agent will contact you' WHERE id = ?");
        $stmt->bind_param("i", $applicationId);
        $stmt->execute();
        $stmt->close();
        $verified = true;

        // Real-time confirmation email
        send_mail($application['email'], "Application Confirmed - PolicyPoint",
            "<h2>You're all set, " . htmlspecialchars($application['full_name']) . "!</h2>
             <p>Your application for <b>" . htmlspecialchars($application['policy_name']) . "</b>
             (" . htmlspecialchars($application['company_name']) . ") has been confirmed.</p>
             <p>Our agent will reach you soon to complete the process.</p>");
    } else {
        $errors[] = "Incorrect OTP. Please check your email and try again.";
    }
}

$pageTitle = "Verify OTP";
$baseUrl = "";
include __DIR__ . "/../includes/header_user.php";
?>

<div class="container">
  <div class="form-wrap card">
    <?php if ($verified): ?>
      <div class="alert alert-success">
        <b>Application confirmed!</b><br>
        Our agent will reach you soon regarding <b><?php echo htmlspecialchars($application['policy_name']); ?></b>.
      </div>
      <a href="home.php" class="btn btn-block">Back to Home</a>
    <?php else: ?>
      <h2 class="page-title">Enter OTP</h2>
      <p class="page-subtitle">
        We've sent a 6-digit OTP to <b><?php echo htmlspecialchars($application['email']); ?></b>.
        Enter it below to confirm your application.
      </p>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
          <?php foreach ($errors as $err) echo htmlspecialchars($err) . "<br>"; ?>
        </div>
      <?php endif; ?>

      <form method="POST" class="otp-box">
        <input type="hidden" name="application_id" value="<?php echo (int)$applicationId; ?>">
        <div class="form-group">
          <label for="otp">6-digit OTP</label>
          <input type="text" id="otp" name="otp" maxlength="6" required autofocus>
        </div>
        <button type="submit" class="btn btn-block">Verify OTP</button>
      </form>
      <p class="form-footer-link">
        Didn't get the code? On localhost, check <code>mail_log.txt</code> in the project root for the OTP.
      </p>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>
