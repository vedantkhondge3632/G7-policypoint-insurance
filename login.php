<?php
session_start();
require_once __DIR__ . "/../config/db.php";

$errors = [];
$email = "";

if (isset($_SESSION['flash_success'])) {
    $successMsg = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    if (strlen($password) === 0) {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, company_name, password FROM admins WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['admin_id'] = $row['id'];
                $_SESSION['admin_company'] = $row['company_name'];
                $_SESSION['admin_email'] = $email;
                header("Location: dashboard.php");
                exit;
            } else {
                $errors[] = "Incorrect password. Please try again.";
            }
        } else {
            $errors[] = "No company account found with this email. Please register first.";
        }
        $stmt->close();
    }
}

$pageTitle = "Company Login";
include __DIR__ . "/../includes/header_admin.php";
?>

<div class="container">
  <div class="form-wrap card">
    <h2 class="page-title">Company Login</h2>
    <p class="page-subtitle">Log in to manage your insurance policies</p>

    <?php if (!empty($successMsg)): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($successMsg); ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-error">
        <?php foreach ($errors as $err) echo htmlspecialchars($err) . "<br>"; ?>
      </div>
    <?php endif; ?>

    <form method="POST" id="loginForm" onsubmit="return validateLoginForm(this)">
      <div class="form-group">
        <label for="email">Company Email</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-block">Login</button>
    </form>
    <p class="form-footer-link">New company? <a href="register.php" style="color:#1c4e80;font-weight:600;">Register here</a></p>
  </div>
</div>

<script src="../assets/js/validation.js"></script>
<?php include __DIR__ . "/../includes/footer.php"; ?>
