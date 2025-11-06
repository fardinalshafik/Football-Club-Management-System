<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../src/config/database.php';
require_once '../src/controllers/AuthController.php';

$authController = new \App\Controllers\AuthController($conn);

// Check if user is logged in
if (!$authController->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Only allow admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: staff.php?error=unauthorized');
    exit();
}

// Get staff ID from URL
$staffId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$staffId) {
    header('Location: staff.php?error=invalid_id');
    exit();
}

$error = '';
$success = '';
$staffData = [];

// Fetch staff data for confirmation
try {
    $stmt = $conn->prepare("SELECT * FROM staff WHERE id = ?");
    $stmt->execute([$staffId]);
    $staffData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$staffData) {
        header('Location: staff.php?error=staff_not_found');
        exit();
    }
} catch (Exception $e) {
    header('Location: staff.php?error=database_error');
    exit();
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirmed = isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'yes';
    
    if (!$confirmed) {
        $error = 'Please confirm the deletion by checking the checkbox.';
    } else {
        try {
            // Begin transaction for safe deletion
            $conn->beginTransaction();
            
            // Check if staff member has any related records (you can add more checks here)
            // For now, we'll just delete the staff record
            
            $stmt = $conn->prepare("DELETE FROM staff WHERE id = ?");
            $result = $stmt->execute([$staffId]);
            
            if ($result) {
                $conn->commit();
                header('Location: staff.php?success=' . urlencode('Staff member deleted successfully'));
                exit();
            } else {
                $conn->rollback();
                $error = 'Failed to delete staff member. Please try again.';
            }
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Staff Member - Football Club Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .danger-zone {
            border: 2px solid #dc3545;
            border-radius: 8px;
            background: #ffeaea;
        }
        .staff-info-card {
            background: #f8f9fa;
            border-left: 4px solid #17a2b8;
        }
    </style>
</head>
<body class="bg-light">
    <?php include '../src/views/partials/header.php'; ?>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-danger text-white py-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-trash-alt me-2"></i>
                            <h2 class="mb-0">Delete Staff Member</h2>
                        </div>
                        <small class="d-block mt-1">This action cannot be undone</small>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Warning!</strong> You are about to permanently delete this staff member from the system.
                        </div>

                        <!-- Staff Information Display -->
                        <div class="staff-info-card p-4 mb-4">
                            <h5 class="text-info mb-3">
                                <i class="fas fa-user-tie me-2"></i>Staff Member Details
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Name:</strong> <?php echo htmlspecialchars($staffData['name']); ?></p>
                                    <p><strong>Employee ID:</strong> <?php echo htmlspecialchars($staffData['employee_id'] ?? 'N/A'); ?></p>
                                    <p><strong>Position:</strong> <?php echo htmlspecialchars($staffData['position']); ?></p>
                                    <p><strong>Department:</strong> <?php echo htmlspecialchars(ucfirst($staffData['department'])); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($staffData['phone'] ?? 'N/A'); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($staffData['email'] ?? 'N/A'); ?></p>
                                    <p><strong>Hire Date:</strong> <?php echo $staffData['hire_date'] ? date('M d, Y', strtotime($staffData['hire_date'])) : 'N/A'; ?></p>
                                    <p><strong>Status:</strong> 
                                        <span class="badge bg-<?php echo $staffData['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $staffData['status'] ?? 'active'))); ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Deletion Consequences -->
                        <div class="danger-zone p-4 mb-4">
                            <h5 class="text-danger mb-3">
                                <i class="fas fa-exclamation-circle me-2"></i>What happens when you delete this staff member?
                            </h5>
                            <ul class="text-danger mb-0">
                                <li>All staff information will be permanently removed</li>
                                <li>This action cannot be undone</li>
                                <li>Any associated records may need to be updated manually</li>
                                <li>Historical data related to this staff member will be lost</li>
                            </ul>
                        </div>

                        <!-- Deletion Form -->
                        <form method="POST" action="staff_delete.php?id=<?php echo $staffId; ?>" id="deleteForm">
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="confirm_delete" name="confirm_delete" value="yes" required>
                                    <label class="form-check-label fw-bold text-danger" for="confirm_delete">
                                        I understand that this action is permanent and cannot be undone
                                    </label>
                                </div>
                            </div>

                            <div class="mb-4 p-3 bg-light rounded">
                                <p class="mb-2"><strong>To proceed with deletion, please type the staff member's name below:</strong></p>
                                <input type="text" class="form-control" id="confirmName" placeholder="Type '<?php echo htmlspecialchars($staffData['name']); ?>' to confirm" required>
                                <small class="text-muted">This ensures you're deleting the correct staff member</small>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <a href="staff.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-1"></i>Cancel & Go Back
                                    </a>
                                    <a href="staff_edit.php?id=<?php echo $staffId; ?>" class="btn btn-warning ms-2">
                                        <i class="fas fa-edit me-1"></i>Edit Instead
                                    </a>
                                </div>
                                <button type="submit" class="btn btn-danger px-4" id="deleteBtn" disabled>
                                    <i class="fas fa-trash-alt me-1"></i>Delete Staff Member
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../src/views/partials/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('deleteForm');
            const deleteBtn = document.getElementById('deleteBtn');
            const confirmCheckbox = document.getElementById('confirm_delete');
            const confirmNameInput = document.getElementById('confirmName');
            const expectedName = <?php echo json_encode($staffData['name']); ?>;

            function validateForm() {
                const isChecked = confirmCheckbox.checked;
                const nameMatches = confirmNameInput.value.trim() === expectedName;
                
                deleteBtn.disabled = !(isChecked && nameMatches);
                
                if (nameMatches) {
                    confirmNameInput.classList.remove('is-invalid');
                    confirmNameInput.classList.add('is-valid');
                } else if (confirmNameInput.value.trim().length > 0) {
                    confirmNameInput.classList.remove('is-valid');
                    confirmNameInput.classList.add('is-invalid');
                } else {
                    confirmNameInput.classList.remove('is-valid', 'is-invalid');
                }
            }

            confirmCheckbox.addEventListener('change', validateForm);
            confirmNameInput.addEventListener('input', validateForm);

            form.addEventListener('submit', function(e) {
                const isChecked = confirmCheckbox.checked;
                const nameMatches = confirmNameInput.value.trim() === expectedName;
                
                if (!isChecked || !nameMatches) {
                    e.preventDefault();
                    alert('Please confirm the deletion by checking the checkbox and typing the staff member\'s name correctly.');
                    return;
                }
                
                // Final confirmation
                if (!confirm(`Are you absolutely sure you want to delete ${expectedName}? This action cannot be undone.`)) {
                    e.preventDefault();
                    return;
                }
                
                deleteBtn.disabled = true;
                deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Deleting...';
            });

            // Prevent accidental form submission
            window.addEventListener('beforeunload', function(e) {
                if (confirmCheckbox.checked || confirmNameInput.value.trim().length > 0) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        });
    </script>
</body>
</html>