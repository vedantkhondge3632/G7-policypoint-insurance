<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
require_once __DIR__ . "/../config/db.php";

$errors = [];
$insuranceType = $policyName = $description = "";
$premium = $coverage = $duration = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $insuranceType = $_POST['insurance_type'] ?? '';
    $policyName = trim($_POST['policy_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $premium = trim($_POST['premium'] ?? '');
    $coverage = trim($_POST['coverage_amount'] ?? '');
    $duration = trim($_POST['duration_years'] ?? '');

    $allowedTypes = ['life', 'health', 'auto', 'home'];
    if (!in_array($insuranceType, $allowedTypes)) {
        $errors[] = "Please select a valid insurance type.";
    }
    if (strlen($policyName) < 3) {
        $errors[] = "Policy name must be at least 3 characters.";
    }
    if (strlen($description) < 10) {
        $errors[] = "Please provide a short description (at least 10 characters).";
    }
    if (!is_numeric($premium) || $premium <= 0) {
        $errors[] = "Please enter a valid premium amount.";
    }
    if (!is_numeric($coverage) || $coverage <= 0) {
        $errors[] = "Please enter a valid coverage amount.";
    }
    if (!ctype_digit($duration) || (int)$duration <= 0) {
        $errors[] = "Please enter a valid duration in years.";
    }

    if (empty($errors)) {
        $adminId = $_SESSION['admin_id'];
        $stmt = $conn->prepare("INSERT INTO policies (admin_id, insurance_type, policy_name, description, premium, coverage_amount, duration_years)
                                 VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssddi", $adminId, $insuranceType, $policyName, $description, $premium, $coverage, $duration);
        if ($stmt->execute()) {
            $_SESSION['flash_success'] = "Policy added successfully.";
            header("Location: dashboard.php");
            exit;
        } else {
            $errors[] = "Something went wrong. Please try again.";
        }
        $stmt->close();
    }
}

$pageTitle = "Add Policy";
include __DIR__ . "/../includes/header_admin.php";
?>

<div class="container">
  <div class="form-wrap card">
    <h2 class="page-title">Add a New Policy</h2>
    <p class="page-subtitle">This will be visible to all users browsing insurance plans</p>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-error">
        <?php foreach ($errors as $err) echo htmlspecialchars($err) . "<br>"; ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label for="insurance_type">Insurance Type</label>
        <select id="insurance_type" name="insurance_type" required>
          <option value="">-- Select Type --</option>
          <option value="life" <?php echo $insuranceType === 'life' ? 'selected' : ''; ?>>Life Insurance</option>
          <option value="health" <?php echo $insuranceType === 'health' ? 'selected' : ''; ?>>Health Insurance</option>
          <option value="auto" <?php echo $insuranceType === 'auto' ? 'selected' : ''; ?>>Auto Insurance</option>
          <option value="home" <?php echo $insuranceType === 'home' ? 'selected' : ''; ?>>Home Insurance</option>
        </select>
      </div>
      <div class="form-group">
        <label for="policy_name">Policy Name</label>
        <input type="text" id="policy_name" name="policy_name" value="<?php echo htmlspecialchars($policyName); ?>" required>
      </div>
      <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($description); ?></textarea>
      </div>
      <div class="form-group">
        <label for="premium">Annual Premium (₹)</label>
        <input type="number" step="0.01" id="premium" name="premium" value="<?php echo htmlspecialchars($premium); ?>" required>
      </div>
      <div class="form-group">
        <label for="coverage_amount">Coverage Amount (₹)</label>
        <input type="number" step="0.01" id="coverage_amount" name="coverage_amount" value="<?php echo htmlspecialchars($coverage); ?>" required>
      </div>
      <div class="form-group">
        <label for="duration_years">Duration (Years)</label>
        <input type="number" id="duration_years" name="duration_years" value="<?php echo htmlspecialchars($duration); ?>" required>
      </div>
      <button type="submit" class="btn btn-block">Add Policy</button>
    </form>
  </div>
</div>

<?php include __DIR__ . "/../includes/footer.php"; ?>
