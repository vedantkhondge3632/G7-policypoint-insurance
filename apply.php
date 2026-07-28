<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/mailer.php";

$policyId = (int)($_GET['policy_id'] ?? $_POST['policy_id'] ?? 0);

// Fetch the policy to display + confirm it exists
$stmt = $conn->prepare("SELECT p.id, p.policy_name, p.insurance_type, p.premium, a.company_name
                         FROM policies p JOIN admins a ON p.admin_id = a.id WHERE p.id = ?");
$stmt->bind_param("i", $policyId);
$stmt->execute();
$policy = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$policy) {
    header("Location: home.php");
    exit;
}

$errors = [];
$fullName = $_SESSION['user_name'];
$email = $_SESSION['user_email'];
$phone = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (strlen($fullName) < 2) {
        $errors[] = "Please enter your full name.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        $errors[] = "Phone number must be exactly 10 digits.";
    }

    if (empty($errors)) {
        // Generate a 6 digit OTP
        $otp = strval(random_int(100000, 999999));
        $userId = $_SESSION['user_id'];
        $stmt = $conn->prepare("INSERT INTO applications (user_id, policy_id, full_name, email, phone, otp, otp_verified, status)
                                 VALUES (?, ?, ?, ?, ?, ?, 0, 'Pending OTP')");
        $stmt->bind_param("iissss", $userId, $policyId, $fullName, $email, $phone, $otp);

        if ($stmt->execute()) {
            $applicationId = $stmt->insert_id;
            $stmt->close();

            // Send OTP via real-time email
            send_mail($email, "Your PolicyPoint OTP Code",
                "<h2>Verify your application</h2>
                 <p>Your One-Time Password (OTP) is:</p>
                 <h1 style='letter-spacing:6px;'>" . $otp . "</h1>
                 <p>Enter this code on the website to confirm your application for <b>" . htmlspecialchars($policy['policy_name']) . "</b>.</p>
                 <p>This OTP is valid for this session only.</p>");

            $_SESSION['pending_application_id'] = $applicationId;
            header("Location: verify_otp.php?application_id=" . $applicationId);
            exit;
        } else {
            $errors[] = "Something went wrong while submitting your application. Please try again.";
        }
    }
}

$pageTitle = "Apply for Plan";
$baseUrl = "";
include __DIR__ . "/../includes/header_user.php";
?>

<div class="container">
  <div class="form-wrap card">
    <h2 class="page-title">Apply for Plan</h2>
    <p class="page-subtitle">
      <?php echo htmlspecialchars($policy['policy_name']); ?>
      (<?php echo htmlspecialchars($policy['company_name']); ?>) &middot;
      ₹<?php echo number_format($policy['premium'], 2); ?> / year
    </p>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-error">
        <?php foreach ($errors as $err) echo htmlspecialchars($err) . "<br>"; ?>
      </div>
    <?php endif; ?>

    <form method="POST" id="applyForm" onsubmit="return validateApplyForm(this)">
      <input type="hidden" name="policy_id" value="<?php echo (int)$policy['id']; ?>">
      <div class="form-group">
        <label for="full_name">Full Name</label>
        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($fullName); ?>" required>
      </div>
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
      </div>
      <div class="form-group">
        <label for="phone">Phone Number</label>
        <input type="text" id="phone" name="phone" maxlength="10" value="<?php echo htmlspecialchars($phone); ?>" placeholder="10 digit mobile number" required>
      </div>
      <button type="submit" class="btn btn-block">Send OTP &amp; Apply</button>
    </form>
  </div>
</div>

<script src="../assets/js/validation.js"></script>
<?php include __DIR__ . "/../includes/footer.php"; ?>
