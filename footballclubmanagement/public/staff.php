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

// Get all staff members
$staff = [];
$error = '';
$success = '';

// Check for success or error messages
if (isset($_GET['success'])) {
    $success = $_GET['success'];
} elseif (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'unauthorized':
            $error = 'You do not have permission to perform this action.';
            break;
        case 'invalid_id':
            $error = 'Invalid staff ID provided.';
            break;
        case 'staff_not_found':
            $error = 'Staff member not found.';
            break;
        case 'database_error':
            $error = 'Database connection error.';
            break;
        default:
            $error = 'An unknown error occurred.';
    }
}

try {
    $stmt = $conn->prepare("SELECT * FROM staff ORDER BY name ASC");
    $stmt->execute();
    $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = 'Database error: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management - Football Club</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
    <?php include '../src/views/partials/header.php'; ?>
    
    <div class="container py-5">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="text-gradient">
                        <i class="fas fa-user-tie me-2"></i>Staff Management
                    </h2>
                    <a href="staff_add.php" class="btn btn-primary">
                        <i class="fas fa-user-plus me-2"></i>Add New Staff
                    </a>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <?php if (empty($staff)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-user-tie fa-4x text-muted mb-3"></i>
                                <h4 class="text-muted">No Staff Members Found</h4>
                                <p class="text-muted">Start by adding your first staff member.</p>
                                <a href="staff_add.php" class="btn btn-primary">
                                    <i class="fas fa-user-plus me-2"></i>Add Staff Member
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Employee ID</th>
                                            <th>Name</th>
                                            <th>Position</th>
                                            <th>Department</th>
                                            <th>Phone</th>
                                            <th>Email</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($staff as $member): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($member['employee_id'] ?? 'N/A'); ?></strong>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-user-circle fa-lg text-muted me-2"></i>
                                                        <strong><?php echo htmlspecialchars($member['name']); ?></strong>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($member['position']); ?></td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <?php echo htmlspecialchars(ucfirst($member['department'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($member['phone']): ?>
                                                        <i class="fas fa-phone me-1"></i>
                                                        <?php echo htmlspecialchars($member['phone']); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($member['email']): ?>
                                                        <i class="fas fa-envelope me-1"></i>
                                                        <a href="mailto:<?php echo htmlspecialchars($member['email']); ?>" class="text-decoration-none">
                                                            <?php echo htmlspecialchars($member['email']); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">N/A</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $status = $member['status'] ?? 'active';
                                                    $badgeClass = match($status) {
                                                        'active' => 'bg-success',
                                                        'inactive' => 'bg-secondary',
                                                        'on_leave' => 'bg-warning',
                                                        'suspended' => 'bg-danger',
                                                        default => 'bg-primary'
                                                    };
                                                    ?>
                                                    <span class="badge <?php echo $badgeClass; ?>">
                                                        <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $status))); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="staff_edit.php?id=<?php echo $member['id']; ?>" 
                                                           class="btn btn-outline-primary btn-sm" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-outline-info btn-sm" 
                                                                title="View Details" 
                                                                onclick="viewStaffDetails(<?php echo htmlspecialchars(json_encode($member)); ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <?php if ($_SESSION['role'] === 'admin'): ?>
                                                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                                                    title="Delete"
                                                                    onclick="confirmDelete(<?php echo $member['id']; ?>, '<?php echo htmlspecialchars($member['name']); ?>')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-3 text-muted">
                                <small>Total Staff Members: <?php echo count($staff); ?></small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Staff Details Modal -->
    <div class="modal fade" id="staffModal" tabindex="-1" aria-labelledby="staffModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staffModalLabel">Staff Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="staffModalBody">
                    <!-- Content will be populated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="#" id="editStaffBtn" class="btn btn-primary">Edit Staff</a>
                </div>
            </div>
        </div>
    </div>

    <?php include '../src/views/partials/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        function viewStaffDetails(staff) {
            const modal = new bootstrap.Modal(document.getElementById('staffModal'));
            const modalBody = document.getElementById('staffModalBody');
            const editBtn = document.getElementById('editStaffBtn');
            
            // Format the staff details
            const hireDate = staff.hire_date ? new Date(staff.hire_date).toLocaleDateString() : 'N/A';
            const dob = staff.date_of_birth ? new Date(staff.date_of_birth).toLocaleDateString() : 'N/A';
            const contractEnd = staff.contract_end_date ? new Date(staff.contract_end_date).toLocaleDateString() : 'N/A';
            
            modalBody.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary">Personal Information</h6>
                        <p><strong>Name:</strong> ${staff.name}</p>
                        <p><strong>Employee ID:</strong> ${staff.employee_id || 'N/A'}</p>
                        <p><strong>Date of Birth:</strong> ${dob}</p>
                        <p><strong>Gender:</strong> ${staff.gender ? staff.gender.charAt(0).toUpperCase() + staff.gender.slice(1) : 'N/A'}</p>
                        <p><strong>Nationality:</strong> ${staff.nationality || 'N/A'}</p>
                        
                        <h6 class="text-primary mt-3">Contact Information</h6>
                        <p><strong>Phone:</strong> ${staff.phone || 'N/A'}</p>
                        <p><strong>Email:</strong> ${staff.email || 'N/A'}</p>
                        <p><strong>Address:</strong> ${staff.address || 'N/A'}</p>
                        <p><strong>Emergency Contact:</strong> ${staff.emergency_contact || 'N/A'}</p>
                        <p><strong>Emergency Phone:</strong> ${staff.emergency_phone || 'N/A'}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary">Professional Information</h6>
                        <p><strong>Position:</strong> ${staff.position}</p>
                        <p><strong>Department:</strong> ${staff.department.charAt(0).toUpperCase() + staff.department.slice(1)}</p>
                        <p><strong>Hire Date:</strong> ${hireDate}</p>
                        <p><strong>Experience:</strong> ${staff.experience_years || 0} years</p>
                        <p><strong>Qualification:</strong> ${staff.qualification || 'N/A'}</p>
                        <p><strong>Salary:</strong> ${staff.salary ? '$' + parseFloat(staff.salary).toLocaleString() : 'N/A'}</p>
                        
                        <h6 class="text-primary mt-3">Contract Information</h6>
                        <p><strong>Contract Type:</strong> ${staff.contract_type ? staff.contract_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A'}</p>
                        <p><strong>Contract End:</strong> ${contractEnd}</p>
                        <p><strong>Status:</strong> <span class="badge bg-${staff.status === 'active' ? 'success' : 'secondary'}">${staff.status ? staff.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'Active'}</span></p>
                    </div>
                </div>
            `;
            
            editBtn.href = `staff_edit.php?id=${staff.id}`;
            modal.show();
        }

        function confirmDelete(staffId, staffName) {
            if (confirm(`Are you sure you want to delete ${staffName}? This action cannot be undone.`)) {
                // You can implement the delete functionality here
                window.location.href = `staff_delete.php?id=${staffId}`;
            }
        }
    </script>
</body>
</html>