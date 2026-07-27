<?php
session_start();
require_once __DIR__ . "/../config/db.php";

$errors = [];
$companyName = $email = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $companyName = trim($_POST['company_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (strlen($companyName) < 2) {
        $errors[] = "Please enter your company name.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM admins WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "An account with this email already exists. Please login instead.";
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO admins (company_name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $companyName, $email, $hashedPassword);
        if ($stmt->execute()) {
            $_SESSION['flash_success'] = "Company registration successful! Please log in.";
            header("Location: login.php");
            exit;
        } else {
            $errors[] = "Something went wrong. Please try again.";
        }
        $stmt->close();
    }
}

$pageTitle = "Company Registration";
include __DIR__ . "/../includes/header_admin.php";
?>

<div class="container">
  <div class="form-wrap card">
    <h2 class="page-title">Register your Company</h2>
    <p class="page-subtitle">List your insurance policies for users to compare and apply</p>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-error">
        <?php foreach ($errors as $err) echo htmlspecialchars($err) . "<br>"; ?>
      </div>
    <?php endif; ?>

    <form method="POST" id="registerForm" onsubmit="return validateRegisterForm(this)">
      <div class="form-group">
        <label for="company_name">Company Name</label>
        <input type="text" id="company_name" name="company_name" value="<?php echo htmlspecialchars($companyName); ?>" required>
      </div>
      <div class="form-group">
        <label for="email">Company Email</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </div>
      <div class="form-group">
        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
      </div>
      <button type="submit" class="btn btn-block">Register Company</button>
    </form>
    <p class="form-footer-link">Already registered? <a href="login.php" style="color:#1c4e80;font-weight:600;">Login here</a></p>
  </div>
</div>

<script src="../assets/js/validation.js"></script>
<?php include __DIR__ . "/../includes/footer.php"; ?>
