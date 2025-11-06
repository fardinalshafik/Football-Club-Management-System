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

// Only allow admin and senior staff
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'coach'])) {
    header('Location: dashboard.php?error=unauthorized');
    exit();
}

// Get staff ID from URL
$staffId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$staffId) {
    header('Location: staff.php?error=invalid_id');
    exit();
}

// Initialize variables
$success = $error = '';
$validationErrors = [];
$staffData = [];

// Fetch current staff data
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $name = trim($_POST['name'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $hire_date = $_POST['hire_date'] ?? '';
    $salary = trim($_POST['salary'] ?? '');
    $employee_id = trim($_POST['employee_id'] ?? '');
    $qualification = trim($_POST['qualification'] ?? '');
    $experience_years = intval($_POST['experience_years'] ?? 0);
    $emergency_contact = trim($_POST['emergency_contact'] ?? '');
    $emergency_phone = trim($_POST['emergency_phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $gender = trim($_POST['gender'] ?? '');
    $nationality = trim($_POST['nationality'] ?? '');
    $contract_type = trim($_POST['contract_type'] ?? '');
    $contract_end_date = $_POST['contract_end_date'] ?? '';
    $status = trim($_POST['status'] ?? 'active');

    // Validation
    if (empty($name)) {
        $validationErrors['name'] = 'Full name is required';
    } elseif (strlen($name) < 2) {
        $validationErrors['name'] = 'Name must be at least 2 characters';
    }

    if (empty($position)) {
        $validationErrors['position'] = 'Position is required';
    }

    if (empty($department)) {
        $validationErrors['department'] = 'Department is required';
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $validationErrors['email'] = 'Please enter a valid email address';
    }

    if (!empty($phone) && !preg_match('/^[\+]?[0-9\s\-\(\)]{10,}$/', $phone)) {
        $validationErrors['phone'] = 'Please enter a valid phone number';
    }

    if (!empty($salary) && (!is_numeric($salary) || $salary < 0)) {
        $validationErrors['salary'] = 'Please enter a valid salary amount';
    }

    if (!empty($employee_id) && $employee_id !== $staffData['employee_id']) {
        // Check if employee ID already exists (only if it's being changed)
        $stmt = $conn->prepare("SELECT id FROM staff WHERE employee_id = ? AND id != ?");
        $stmt->execute([$employee_id, $staffId]);
        if ($stmt->fetch()) {
            $validationErrors['employee_id'] = 'Employee ID already exists';
        }
    }

    if (!empty($hire_date)) {
        $hireDateTime = DateTime::createFromFormat('Y-m-d', $hire_date);
        if (!$hireDateTime || $hireDateTime > new DateTime()) {
            $validationErrors['hire_date'] = 'Hire date cannot be in the future';
        }
    }

    if (!empty($date_of_birth)) {
        $birthDate = DateTime::createFromFormat('Y-m-d', $date_of_birth);
        if (!$birthDate) {
            $validationErrors['date_of_birth'] = 'Please enter a valid date of birth';
        } else {
            $age = (new DateTime())->diff($birthDate)->y;
            if ($age < 16 || $age > 70) {
                $validationErrors['date_of_birth'] = 'Age must be between 16 and 70 years';
            }
        }
    }

    if (empty($validationErrors)) {
        try {
            $stmt = $conn->prepare("UPDATE staff SET name = ?, position = ?, department = ?, phone = ?, email = ?, hire_date = ?, salary = ?, employee_id = ?, qualification = ?, experience_years = ?, emergency_contact = ?, emergency_phone = ?, address = ?, date_of_birth = ?, gender = ?, nationality = ?, contract_type = ?, contract_end_date = ?, status = ? WHERE id = ?");
            
            $result = $stmt->execute([
                $name,
                $position,
                $department,
                $phone ?: null,
                $email ?: null,
                $hire_date ?: null,
                $salary ?: null,
                $employee_id ?: $staffData['employee_id'],
                $qualification ?: null,
                $experience_years ?: null,
                $emergency_contact ?: null,
                $emergency_phone ?: null,
                $address ?: null,
                $date_of_birth ?: null,
                $gender ?: null,
                $nationality ?: null,
                $contract_type ?: null,
                $contract_end_date ?: null,
                $status,
                $staffId
            ]);
            
            if ($result) {
                $success = 'Staff member updated successfully!';
                // Refresh staff data
                $stmt = $conn->prepare("SELECT * FROM staff WHERE id = ?");
                $stmt->execute([$staffId]);
                $staffData = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = 'Failed to update staff member. Please try again.';
            }
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    } else {
        $error = 'Please correct the validation errors below.';
    }
}

// Get departments for dynamic suggestions
try {
    $stmt = $conn->prepare("SELECT DISTINCT department FROM staff ORDER BY department");
    $stmt->execute();
    $existing_departments = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $existing_departments = [];
}

// Get positions for dynamic suggestions
try {
    $stmt = $conn->prepare("SELECT DISTINCT position FROM staff ORDER BY position");
    $stmt->execute();
    $existing_positions = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $existing_positions = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Staff Member - Football Club Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .form-section {
            background: var(--bs-gray-50);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-left: 4px solid var(--primary-color);
        }
        .form-section-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        .form-section-title i {
            margin-right: 0.5rem;
            width: 20px;
        }
        .is-invalid {
            border-color: #dc3545;
        }
        .invalid-feedback {
            display: block;
        }
    </style>
</head>
<body class="bg-light">
    <?php include '../src/views/partials/header.php'; ?>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-warning text-dark py-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-user-edit me-2"></i>
                            <h2 class="mb-0">Edit Staff Member</h2>
                        </div>
                        <small class="d-block mt-1">Employee ID: <?php echo htmlspecialchars($staffData['employee_id'] ?? 'N/A'); ?></small>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo htmlspecialchars($success); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="staff_edit.php?id=<?php echo $staffId; ?>" id="staffForm" novalidate>
                            <!-- Personal Information Section -->
                            <div class="form-section">
                                <h5 class="form-section-title">
                                    <i class="fas fa-user"></i>
                                    Personal Information
                                </h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="name" class="form-label fw-bold">
                                                Full Name <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control <?php echo isset($validationErrors['name']) ? 'is-invalid' : ''; ?>" 
                                                   id="name" name="name" 
                                                   value="<?php echo htmlspecialchars($_POST['name'] ?? $staffData['name'] ?? ''); ?>" 
                                                   required
                                                   placeholder="Enter full name">
                                            <?php if (isset($validationErrors['name'])): ?>
                                                <div class="invalid-feedback"><?php echo $validationErrors['name']; ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="employee_id" class="form-label fw-bold">
                                                Employee ID
                                            </label>
                                            <input type="text" 
                                                   class="form-control <?php echo isset($validationErrors['employee_id']) ? 'is-invalid' : ''; ?>" 
                                                   id="employee_id" name="employee_id"
                                                   value="<?php echo htmlspecialchars($_POST['employee_id'] ?? $staffData['employee_id'] ?? ''); ?>"
                                                   placeholder="e.g., COA0001">
                                            <?php if (isset($validationErrors['employee_id'])): ?>
                                                <div class="invalid-feedback"><?php echo $validationErrors['employee_id']; ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="date_of_birth" class="form-label fw-bold">Date of Birth</label>
                                            <input type="date" 
                                                   class="form-control <?php echo isset($validationErrors['date_of_birth']) ? 'is-invalid' : ''; ?>" 
                                                   id="date_of_birth" name="date_of_birth"
                                                   value="<?php echo htmlspecialchars($_POST['date_of_birth'] ?? $staffData['date_of_birth'] ?? ''); ?>">
                                            <?php if (isset($validationErrors['date_of_birth'])): ?>
                                                <div class="invalid-feedback"><?php echo $validationErrors['date_of_birth']; ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="gender" class="form-label fw-bold">Gender</label>
                                            <select class="form-select" id="gender" name="gender">
                                                <option value="">Select Gender</option>
                                                <option value="male" <?php echo ($_POST['gender'] ?? $staffData['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                                                <option value="female" <?php echo ($_POST['gender'] ?? $staffData['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                                                <option value="other" <?php echo ($_POST['gender'] ?? $staffData['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="nationality" class="form-label fw-bold">Nationality</label>
                                            <input type="text" class="form-control" id="nationality" name="nationality"
                                                   value="<?php echo htmlspecialchars($_POST['nationality'] ?? $staffData['nationality'] ?? ''); ?>"
                                                   placeholder="e.g., American, British">
                                            <datalist id="nationalityList">
                                                <option value="American">
                                                <option value="British">
                                                <option value="Canadian">
                                                <option value="Brazilian">
                                                <option value="Spanish">
                                                <option value="French">
                                                <option value="German">
                                                <option value="Italian">
                                                <option value="Portuguese">
                                                <option value="Argentine">
                                            </datalist>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Professional Information Section -->
                            <div class="form-section">
                                <h5 class="form-section-title">
                                    <i class="fas fa-briefcase"></i>
                                    Professional Information
                                </h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="position" class="form-label fw-bold">
                                                Position <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" 
                                                   class="form-control <?php echo isset($validationErrors['position']) ? 'is-invalid' : ''; ?>" 
                                                   id="position" name="position" 
                                                   value="<?php echo htmlspecialchars($_POST['position'] ?? $staffData['position'] ?? ''); ?>" 
                                                   required
                                                   list="positionList"
                                                   placeholder="e.g., Head Coach, Assistant Coach">
                                            <datalist id="positionList">
                                                <?php foreach ($existing_positions as $pos): ?>
                                                    <option value="<?php echo htmlspecialchars($pos); ?>">
                                                <?php endforeach; ?>
                                                <option value="Head Coach">
                                                <option value="Assistant Coach">
                                                <option value="Goalkeeper Coach">
                                                <option value="Fitness Coach">
                                                <option value="Team Doctor">
                                                <option value="Physiotherapist">
                                                <option value="Sports Psychologist">
                                                <option value="Team Manager">
                                                <option value="Technical Director">
                                                <option value="Scout">
                                                <option value="Analyst">
                                                <option value="Equipment Manager">
                                                <option value="Media Manager">
                                                <option value="Administrative Assistant">
                                            </datalist>
                                            <?php if (isset($validationErrors['position'])): ?>
                                                <div class="invalid-feedback"><?php echo $validationErrors['position']; ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="department" class="form-label fw-bold">
                                                Department <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select <?php echo isset($validationErrors['department']) ? 'is-invalid' : ''; ?>" 
                                                    id="department" name="department" required>
                                                <option value="">Select Department</option>
                                                <option value="coaching" <?php echo ($_POST['department'] ?? $staffData['department'] ?? '') === 'coaching' ? 'selected' : ''; ?>>Coaching Staff</option>
                                                <option value="medical" <?php echo ($_POST['department'] ?? $staffData['department'] ?? '') === 'medical' ? 'selected' : ''; ?>>Medical & Sports Science</option>
                                                <option value="administrative" <?php echo ($_POST['department'] ?? $staffData['department'] ?? '') === 'administrative' ? 'selected' : ''; ?>>Administration</option>
                                                <option value="technical" <?php echo ($_POST['department'] ?? $staffData['department'] ?? '') === 'technical' ? 'selected' : ''; ?>>Technical & Analytics</option>
                                                <option value="operations" <?php echo ($_POST['department'] ?? $staffData['department'] ?? '') === 'operations' ? 'selected' : ''; ?>>Operations</option>
                                                <option value="media" <?php echo ($_POST['department'] ?? $staffData['department'] ?? '') === 'media' ? 'selected' : ''; ?>>Media & Communications</option>
                                                <option value="youth" <?php echo ($_POST['department'] ?? $staffData['department'] ?? '') === 'youth' ? 'selected' : ''; ?>>Youth Development</option>
                                            </select>
                                            <?php if (isset($validationErrors['department'])): ?>
                                                <div class="invalid-feedback"><?php echo $validationErrors['department']; ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="hire_date" class="form-label fw-bold">Hire Date</label>
                                            <input type="date" 
                                                   class="form-control <?php echo isset($validationErrors['hire_date']) ? 'is-invalid' : ''; ?>" 
                                                   id="hire_date" name="hire_date"
                                                   value="<?php echo htmlspecialchars($_POST['hire_date'] ?? $staffData['hire_date'] ?? ''); ?>">
                                            <?php if (isset($validationErrors['hire_date'])): ?>
                                                <div class="invalid-feedback"><?php echo $validationErrors['hire_date']; ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="experience_years" class="form-label fw-bold">Years of Experience</label>
                                            <input type="number" class="form-control" id="experience_years" name="experience_years"
                                                   value="<?php echo htmlspecialchars($_POST['experience_years'] ?? $staffData['experience_years'] ?? ''); ?>"
                                                   min="0" max="50" placeholder="0">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="salary" class="form-label fw-bold">
                                                Annual Salary ($)
                                                <small class="text-muted">(Optional)</small>
                                            </label>
                                            <input type="number" 
                                                   class="form-control <?php echo isset($validationErrors['salary']) ? 'is-invalid' : ''; ?>" 
                                                   id="salary" name="salary"
                                                   value="<?php echo htmlspecialchars($_POST['salary'] ?? $staffData['salary'] ?? ''); ?>"
                                                   min="0" step="1000" placeholder="50000">
                                            <?php if (isset($validationErrors['salary'])): ?>
                                                <div class="invalid-feedback"><?php echo $validationErrors['salary']; ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="qualification" class="form-label fw-bold">Qualifications & Certifications</label>
                                    <textarea class="form-control" id="qualification" name="qualification" rows="2"
                                              placeholder="e.g., UEFA Pro License, Sports Science Degree, Medical Degree"><?php echo htmlspecialchars($_POST['qualification'] ?? $staffData['qualification'] ?? ''); ?></textarea>
                                </div>
                            </div>

                            <!-- Contract Information Section -->
                            <div class="form-section">
                                <h5 class="form-section-title">
                                    <i class="fas fa-file-contract"></i>
                                    Contract Information
                                </h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="contract_type" class="form-label fw-bold">Contract Type</label>
                                            <select class="form-select" id="contract_type" name="contract_type">
                                                <option value="">Select Contract Type</option>
                                                <option value="permanent" <?php echo ($_POST['contract_type'] ?? $staffData['contract_type'] ?? '') === 'permanent' ? 'selected' : ''; ?>>Permanent</option>
                                                <option value="temporary" <?php echo ($_POST['contract_type'] ?? $staffData['contract_type'] ?? '') === 'temporary' ? 'selected' : ''; ?>>Temporary</option>
                                                <option value="part_time" <?php echo ($_POST['contract_type'] ?? $staffData['contract_type'] ?? '') === 'part_time' ? 'selected' : ''; ?>>Part-time</option>
                                                <option value="consultant" <?php echo ($_POST['contract_type'] ?? $staffData['contract_type'] ?? '') === 'consultant' ? 'selected' : ''; ?>>Consultant</option>
                                                <option value="internship" <?php echo ($_POST['contract_type'] ?? $staffData['contract_type'] ?? '') === 'internship' ? 'selected' : ''; ?>>Internship</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="contract_end_date" class="form-label fw-bold">Contract End Date</label>
                                            <input type="date" class="form-control" id="contract_end_date" name="contract_end_date"
                                                   value="<?php echo htmlspecialchars($_POST['contract_end_date'] ?? $staffData['contract_end_date'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="status" class="form-label fw-bold">Employment Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="active" <?php echo ($_POST['status'] ?? $staffData['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                                                <option value="inactive" <?php echo ($_POST['status'] ?? $staffData['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                <option value="on_leave" <?php echo ($_POST['status'] ?? $staffData['status'] ?? '') === 'on_leave' ? 'selected' : ''; ?>>On Leave</option>
                                                <option value="suspended" <?php echo ($_POST['status'] ?? $staffData['status'] ?? '') === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information Section -->
                            <div class="form-section">
                                <h5 class="form-section-title">
                                    <i class="fas fa-address-card"></i>
                                    Contact Information
                                </h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="phone" class="form-label fw-bold">Phone Number</label>
                                            <input type="tel" 
                                                   class="form-control <?php echo isset($validationErrors['phone']) ? 'is-invalid' : ''; ?>" 
                                                   id="phone" name="phone"
                                                   value="<?php echo htmlspecialchars($_POST['phone'] ?? $staffData['phone'] ?? ''); ?>"
                                                   placeholder="+1 (555) 123-4567">
                                            <?php if (isset($validationErrors['phone'])): ?>
                                                <div class="invalid-feedback"><?php echo $validationErrors['phone']; ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label fw-bold">Email Address</label>
                                            <input type="email" 
                                                   class="form-control <?php echo isset($validationErrors['email']) ? 'is-invalid' : ''; ?>" 
                                                   id="email" name="email"
                                                   value="<?php echo htmlspecialchars($_POST['email'] ?? $staffData['email'] ?? ''); ?>"
                                                   placeholder="john.doe@club.com">
                                            <?php if (isset($validationErrors['email'])): ?>
                                                <div class="invalid-feedback"><?php echo $validationErrors['email']; ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label fw-bold">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="2"
                                              placeholder="Street address, City, State, ZIP Code"><?php echo htmlspecialchars($_POST['address'] ?? $staffData['address'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="emergency_contact" class="form-label fw-bold">Emergency Contact Name</label>
                                            <input type="text" class="form-control" id="emergency_contact" name="emergency_contact"
                                                   value="<?php echo htmlspecialchars($_POST['emergency_contact'] ?? $staffData['emergency_contact'] ?? ''); ?>"
                                                   placeholder="Emergency contact person">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="emergency_phone" class="form-label fw-bold">Emergency Contact Phone</label>
                                            <input type="tel" class="form-control" id="emergency_phone" name="emergency_phone"
                                                   value="<?php echo htmlspecialchars($_POST['emergency_phone'] ?? $staffData['emergency_phone'] ?? ''); ?>"
                                                   placeholder="+1 (555) 987-6543">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <div class="text-muted">
                                    <small><span class="text-danger">*</span> Required fields</small>
                                </div>
                                <div>
                                    <a href="staff.php" class="btn btn-secondary me-3">
                                        <i class="fas fa-arrow-left me-1"></i>Back to Staff List
                                    </a>
                                    <button type="submit" class="btn btn-warning px-4" id="submitBtn">
                                        <i class="fas fa-save me-1"></i>Update Staff Member
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../src/views/partials/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('staffForm');
            const submitBtn = document.getElementById('submitBtn');
            
            // Form validation
            form.addEventListener('submit', function(e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
                
                if (form.checkValidity()) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Updating...';
                }
            });

            // Real-time validation feedback (same as add form)
            const requiredFields = ['name', 'position', 'department'];
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.addEventListener('blur', function() {
                        validateField(this);
                    });
                    field.addEventListener('input', function() {
                        if (this.classList.contains('is-invalid')) {
                            validateField(this);
                        }
                    });
                }
            });

            // Email validation
            const emailField = document.getElementById('email');
            if (emailField) {
                emailField.addEventListener('blur', function() {
                    if (this.value && !isValidEmail(this.value)) {
                        this.classList.add('is-invalid');
                        showFieldError(this, 'Please enter a valid email address');
                    } else {
                        this.classList.remove('is-invalid');
                        hideFieldError(this);
                    }
                });
            }

            // Phone validation
            const phoneField = document.getElementById('phone');
            if (phoneField) {
                phoneField.addEventListener('blur', function() {
                    if (this.value && !isValidPhone(this.value)) {
                        this.classList.add('is-invalid');
                        showFieldError(this, 'Please enter a valid phone number');
                    } else {
                        this.classList.remove('is-invalid');
                        hideFieldError(this);
                    }
                });
            }

            // Contract end date validation
            const hireDateField = document.getElementById('hire_date');
            const contractEndField = document.getElementById('contract_end_date');
            if (hireDateField && contractEndField) {
                contractEndField.addEventListener('blur', function() {
                    if (this.value && hireDateField.value) {
                        if (new Date(this.value) <= new Date(hireDateField.value)) {
                            this.classList.add('is-invalid');
                            showFieldError(this, 'Contract end date must be after hire date');
                        } else {
                            this.classList.remove('is-invalid');
                            hideFieldError(this);
                        }
                    }
                });
            }

            // Age calculation from date of birth
            const dobField = document.getElementById('date_of_birth');
            if (dobField) {
                dobField.addEventListener('change', function() {
                    if (this.value) {
                        const birthDate = new Date(this.value);
                        const today = new Date();
                        const age = Math.floor((today - birthDate) / (365.25 * 24 * 60 * 60 * 1000));
                        
                        if (age < 16 || age > 70) {
                            this.classList.add('is-invalid');
                            showFieldError(this, `Age (${age}) must be between 16 and 70 years`);
                        } else {
                            this.classList.remove('is-invalid');
                            hideFieldError(this);
                        }
                    }
                });
            }

            function validateField(field) {
                if (field.hasAttribute('required') && !field.value.trim()) {
                    field.classList.add('is-invalid');
                    showFieldError(field, 'This field is required');
                    return false;
                } else {
                    field.classList.remove('is-invalid');
                    hideFieldError(field);
                    return true;
                }
            }

            function showFieldError(field, message) {
                let feedback = field.parentNode.querySelector('.invalid-feedback');
                if (!feedback) {
                    feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback';
                    field.parentNode.appendChild(feedback);
                }
                feedback.textContent = message;
            }

            function hideFieldError(field) {
                const feedback = field.parentNode.querySelector('.invalid-feedback');
                if (feedback && !feedback.textContent.includes('php')) {
                    feedback.remove();
                }
            }

            function isValidEmail(email) {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            }

            function isValidPhone(phone) {
                return /^[\+]?[0-9\s\-\(\)]{10,}$/.test(phone);
            }
        });
    </script>
</body>
</html>