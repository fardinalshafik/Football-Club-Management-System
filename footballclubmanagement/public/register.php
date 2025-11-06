<?php
session_start();
require_once '../src/config/database.php';
require_once '../src/controllers/AuthController.php';

$authController = new \App\Controllers\AuthController($conn);

// Get role from URL parameter
$selectedRole = $_GET['role'] ?? 'member'; // Default to member if no role specified
$validRoles = ['admin', 'coach', 'player', 'member', 'staff'];

// Validate role
if (!in_array($selectedRole, $validRoles)) {
    $selectedRole = 'member';
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? $selectedRole;
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($username) || empty($password) || empty($email) || empty($role)) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!in_array($role, $validRoles)) {
        $error = 'Invalid role selected.';
    } else {
        try {
            // Check for duplicate username
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = 'Username is already taken. Please choose another.';
            } else {
                // Check for duplicate email
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = 'Email is already registered.';
                } else {
                    // Register user with role
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
                    $result = $stmt->execute([$username, $hashedPassword, $email, $role]);
                    
                    if ($result) {
                        $success = 'Registration successful! You can now log in.';
                        // Redirect to login page after 2 seconds
                        header("refresh:2;url=login.php?role=$role&success=" . urlencode($success));
                    } else {
                        $error = 'Registration failed. Please try again.';
                    }
                }
            }
        } catch (Exception $e) {
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
    <title>Register as <?php echo ucfirst($selectedRole); ?> - Football Club</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .role-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .role-admin { background: linear-gradient(135deg, #dc3545, #c82333); color: white; }
        .role-coach { background: linear-gradient(135deg, #ffc107, #e0a800); color: #212529; }
        .role-player { background: linear-gradient(135deg, #0d6efd, #084298); color: white; }
        .role-member { background: linear-gradient(135deg, #198754, #146c43); color: white; }
        .role-staff { background: linear-gradient(135deg, #6f42c1, #5a32a3); color: white; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white text-center py-4">
                        <h2 class="mb-2">
                            <i class="fas fa-user-plus me-2"></i>Register
                        </h2>
                        <div class="role-badge role-<?php echo $selectedRole; ?>">
                            <?php 
                            $roleIcons = [
                                'admin' => 'fas fa-crown',
                                'coach' => 'fas fa-chalkboard-teacher', 
                                'player' => 'fas fa-running',
                                'member' => 'fas fa-users',
                                'staff' => 'fas fa-user-tie'
                            ];
                            ?>
                            <i class="<?php echo $roleIcons[$selectedRole]; ?> me-1"></i>
                            <?php echo ucfirst($selectedRole); ?>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo htmlspecialchars($success); ?>
                                <div class="mt-2">
                                    <small class="d-block">Redirecting to login page...</small>
                                    <div class="progress mt-2" style="height: 3px;">
                                        <div class="progress-bar progress-bar-animated" style="width: 100%; animation-duration: 2s;"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="register.php?role=<?php echo $selectedRole; ?>" id="registerForm" novalidate>
                            <input type="hidden" name="role" value="<?php echo $selectedRole; ?>">
                            
                            <div class="mb-3">
                                <label for="username" class="form-label fw-bold">
                                    <i class="fas fa-user me-1"></i>Username
                                </label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                                       required minlength="3" maxlength="50"
                                       placeholder="Choose a unique username">
                                <div class="invalid-feedback">Please choose a username (3-50 characters).</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label fw-bold">
                                    <i class="fas fa-envelope me-1"></i>Email Address
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                       required placeholder="your.email@example.com">
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label fw-bold">
                                    <i class="fas fa-lock me-1"></i>Password
                                </label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       required minlength="6" placeholder="Minimum 6 characters">
                                <div class="invalid-feedback">Password must be at least 6 characters long.</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label fw-bold">
                                    <i class="fas fa-lock me-1"></i>Confirm Password
                                </label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       required placeholder="Re-enter your password">
                                <div class="invalid-feedback">Passwords must match.</div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                    <i class="fas fa-user-plus me-2"></i>Register as <?php echo ucfirst($selectedRole); ?>
                                </button>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="mb-2">Already have an account?</p>
                            <a href="login.php?role=<?php echo $selectedRole; ?>" class="btn btn-outline-primary">
                                <i class="fas fa-sign-in-alt me-1"></i>Login
                            </a>
                        </div>
                        
                        <div class="text-center mt-3">
                            <p class="mb-2">Want to register as a different role?</p>
                            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>Back to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            const submitBtn = document.getElementById('submitBtn');
            const passwordField = document.getElementById('password');
            const confirmPasswordField = document.getElementById('confirm_password');
            
            // Form validation
            form.addEventListener('submit', function(e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
                
                if (form.checkValidity()) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Registering...';
                }
            });
            
            // Password confirmation validation
            function validatePasswordMatch() {
                if (confirmPasswordField.value && passwordField.value !== confirmPasswordField.value) {
                    confirmPasswordField.setCustomValidity('Passwords do not match');
                } else {
                    confirmPasswordField.setCustomValidity('');
                }
            }
            
            passwordField.addEventListener('input', validatePasswordMatch);
            confirmPasswordField.addEventListener('input', validatePasswordMatch);
        });
    </script>
</body>
</html>