<?php
session_start();
require_once '../src/config/database.php';
require_once '../src/controllers/AuthController.php';

$authController = new \App\Controllers\AuthController($conn);

// Get role from URL parameter for targeted login
$selectedRole = $_GET['role'] ?? '';
$validRoles = ['admin', 'coach', 'player', 'member', 'staff'];

$error = '';
$success = '';

// Check for success messages
if (isset($_GET['success'])) {
    $success = $_GET['success'];
} elseif (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $success = 'You have been successfully logged out!';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        if ($authController->login($username, $password)) {
            // Redirect based on user role
            $userRole = $_SESSION['role'] ?? 'member';
            
            // If role was specified in URL, validate it matches user's actual role
            if ($selectedRole && $selectedRole !== $userRole) {
                $error = "Access denied. This login is for {$selectedRole}s only.";
                $authController->logout();
            } else {
                // Redirect to appropriate dashboard based on role
                switch ($userRole) {
                    case 'admin':
                        header('Location: dashboard.php');
                        break;
                    case 'coach':
                        header('Location: dashboard.php?view=coach');
                        break;
                    case 'player':
                        header('Location: dashboard.php?view=player');
                        break;
                    case 'staff':
                        header('Location: dashboard.php?view=staff');
                        break;
                    case 'member':
                    default:
                        header('Location: dashboard.php?view=member');
                        break;
                }
                exit();
            }
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login<?php echo $selectedRole ? ' as ' . ucfirst($selectedRole) : ''; ?> - Football Club</title>
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
<body class="bg-light d-flex flex-column justify-content-center align-items-center" style="min-height:100vh;">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-primary text-white text-center py-4">
                        <h2 class="mb-2">
                            <i class="fas fa-futbol me-2"></i>Club Login
                        </h2>
                        <?php if ($selectedRole): ?>
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
                                <i class="<?php echo $roleIcons[$selectedRole] ?? 'fas fa-user'; ?> me-1"></i>
                                <?php echo ucfirst($selectedRole); ?> Login
                            </div>
                        <?php else: ?>
                            <p class="mb-0 text-white-50">Sign in to manage your sports club</p>
                        <?php endif; ?>
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
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="login.php<?php echo $selectedRole ? '?role=' . $selectedRole : ''; ?>" id="loginForm" novalidate>
                            <div class="mb-3">
                                <label for="username" class="form-label fw-bold">
                                    <i class="fas fa-user me-1"></i>Username
                                </label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                                       required placeholder="Enter your username">
                                <div class="invalid-feedback">Please enter your username.</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label fw-bold">
                                    <i class="fas fa-lock me-1"></i>Password
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" 
                                           required placeholder="Enter your password">
                                    <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                        <i class="fas fa-eye" id="toggleIcon"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">Please enter your password.</div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                    <i class="fas fa-sign-in-alt me-2"></i>
                                    Login<?php echo $selectedRole ? ' as ' . ucfirst($selectedRole) : ''; ?>
                                </button>
                            </div>
                        </form>
                        
                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="mb-2">Don't have an account?</p>
                            <a href="register.php<?php echo $selectedRole ? '?role=' . $selectedRole : ''; ?>" class="btn btn-outline-primary">
                                <i class="fas fa-user-plus me-1"></i>Register
                            </a>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>Back to Home
                            </a>
                        </div>
                        
                        <?php if ($selectedRole): ?>
                        <div class="mt-3 p-3 bg-light rounded">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                You are logging in as a <strong><?php echo ucfirst($selectedRole); ?></strong>. 
                                Make sure you have the appropriate permissions for this role.
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const submitBtn = document.getElementById('submitBtn');
            const togglePassword = document.getElementById('togglePassword');
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            // Form validation
            form.addEventListener('submit', function(e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
                
                if (form.checkValidity()) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Signing in...';
                }
            });
            
            // Toggle password visibility
            togglePassword.addEventListener('click', function() {
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    toggleIcon.className = 'fas fa-eye-slash';
                } else {
                    passwordField.type = 'password';
                    toggleIcon.className = 'fas fa-eye';
                }
            });
        });
    </script>
</body>
</html>