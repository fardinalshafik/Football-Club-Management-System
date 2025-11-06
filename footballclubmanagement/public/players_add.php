<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../src/config/database.php';
require_once '../src/controllers/PlayerController.php';
require_once '../src/controllers/AuthController.php';

$authController = new \App\Controllers\AuthController($conn);

// Check if user is logged in
if (!$authController->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Only allow admin or coach
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'coach'])) {
    header('Location: dashboard.php?error=unauthorized');
    exit();
}

$playerController = new \App\Controllers\PlayerController($conn);

$success = $error = '';
$formData = [];

// Get teams for dropdown
$teams = [];
try {
    $stmt = $conn->query("SELECT id, name FROM teams ORDER BY name");
    $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Teams table might not exist or be empty
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Store form data for repopulation
    $formData = $_POST;
    
    $name = trim($_POST['name'] ?? '');
    $position = $_POST['position'] ?? '';
    $age = $_POST['age'] ?? '';
    $nationality = trim($_POST['nationality'] ?? '');
    $height = $_POST['height'] ?? '';
    $weight = $_POST['weight'] ?? '';
    $jersey_number = $_POST['jersey_number'] ?? '';
    $team_id = $_POST['team_id'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $contract_start = $_POST['contract_start'] ?? '';
    $contract_end = $_POST['contract_end'] ?? '';
    $salary = $_POST['salary'] ?? '';
    $stats = trim($_POST['stats'] ?? '');
    $contract = trim($_POST['contract'] ?? '');

    // Validation
    if ($name && $position && $age) {
        try {
            // Check if jersey number is unique within the team (if both are provided)
            if ($jersey_number && $team_id) {
                $stmt = $conn->prepare("SELECT id FROM players WHERE jersey_number = ? AND team_id = ?");
                $stmt->execute([$jersey_number, $team_id]);
                if ($stmt->fetch()) {
                    throw new Exception("Jersey number $jersey_number is already taken by another player in this team.");
                }
            }
            
            // Enhanced insert with all fields
            $stmt = $conn->prepare("INSERT INTO players (name, position, age, nationality, height, weight, jersey_number, team_id, date_of_birth, contract_start, contract_end, salary, stats, contract) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $result = $stmt->execute([
                $name,
                $position,
                $age,
                $nationality ?: null,
                $height ?: null,
                $weight ?: null,
                $jersey_number ?: null,
                $team_id ?: null,
                $date_of_birth ?: null,
                $contract_start ?: null,
                $contract_end ?: null,
                $salary ?: null,
                $stats ?: null,
                $contract ?: null
            ]);
            
            if ($result) {
                $success = "Player '$name' added successfully!";
                $formData = []; // Clear form data on success
            } else {
                $error = 'Failed to add player. Please try again.';
            }
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    } else {
        $error = 'Please fill in all required fields (Name, Position, and Age).';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Player - Football Club Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <?php include '../src/views/partials/header.php'; ?>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-gradient text-white">
                        <h2 class="mb-0 text-center">
                            <i class="fas fa-user-plus me-2"></i>Add New Player
                        </h2>
                    </div>
                    <div class="card-body p-4">
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="players_add.php" id="playerForm" novalidate>
                            <!-- Personal Information Section -->
                            <div class="mb-4">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-user me-2"></i>Personal Information
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo htmlspecialchars($formData['name'] ?? ''); ?>" 
                                               placeholder="Enter player's full name" required>
                                        <div class="invalid-feedback">Please provide a valid name.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="age" class="form-label">Age *</label>
                                        <input type="number" class="form-control" id="age" name="age" 
                                               value="<?php echo htmlspecialchars($formData['age'] ?? ''); ?>"
                                               min="16" max="50" placeholder="Age" required>
                                        <div class="invalid-feedback">Please provide a valid age (16-50).</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="nationality" class="form-label">Nationality</label>
                                        <input type="text" class="form-control" id="nationality" name="nationality" 
                                               value="<?php echo htmlspecialchars($formData['nationality'] ?? ''); ?>"
                                               placeholder="e.g., Brazilian, English, etc.">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="date_of_birth" class="form-label">Date of Birth</label>
                                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                               value="<?php echo htmlspecialchars($formData['date_of_birth'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Physical & Position Information -->
                            <div class="mb-4">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-running me-2"></i>Physical & Position Details
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="position" class="form-label">Position *</label>
                                        <select class="form-select" id="position" name="position" required>
                                            <option value="">Select Position</option>
                                            <option value="Goalkeeper" <?php echo ($formData['position'] ?? '') === 'Goalkeeper' ? 'selected' : ''; ?>>Goalkeeper</option>
                                            <option value="Defender" <?php echo ($formData['position'] ?? '') === 'Defender' ? 'selected' : ''; ?>>Defender</option>
                                            <option value="Midfielder" <?php echo ($formData['position'] ?? '') === 'Midfielder' ? 'selected' : ''; ?>>Midfielder</option>
                                            <option value="Forward" <?php echo ($formData['position'] ?? '') === 'Forward' ? 'selected' : ''; ?>>Forward</option>
                                        </select>
                                        <div class="invalid-feedback">Please select a position.</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="height" class="form-label">Height (cm)</label>
                                        <input type="number" class="form-control" id="height" name="height" 
                                               value="<?php echo htmlspecialchars($formData['height'] ?? ''); ?>"
                                               min="150" max="220" placeholder="180">
                                        <div class="form-text">In centimeters</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="weight" class="form-label">Weight (kg)</label>
                                        <input type="number" class="form-control" id="weight" name="weight" 
                                               value="<?php echo htmlspecialchars($formData['weight'] ?? ''); ?>"
                                               min="50" max="120" placeholder="75">
                                        <div class="form-text">In kilograms</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Team & Contract Information -->
                            <div class="mb-4">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-clipboard-list me-2"></i>Team & Contract Details
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="team_id" class="form-label">Team</label>
                                        <select class="form-select" id="team_id" name="team_id">
                                            <option value="">Select Team (Optional)</option>
                                            <?php foreach ($teams as $team): ?>
                                                <option value="<?php echo $team['id']; ?>" 
                                                        <?php echo ($formData['team_id'] ?? '') == $team['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($team['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="jersey_number" class="form-label">Jersey Number</label>
                                        <input type="number" class="form-control" id="jersey_number" name="jersey_number" 
                                               value="<?php echo htmlspecialchars($formData['jersey_number'] ?? ''); ?>"
                                               min="1" max="99" placeholder="e.g., 10">
                                        <div class="form-text">Must be unique within the team</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="contract_start" class="form-label">Contract Start</label>
                                        <input type="date" class="form-control" id="contract_start" name="contract_start" 
                                               value="<?php echo htmlspecialchars($formData['contract_start'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="contract_end" class="form-label">Contract End</label>
                                        <input type="date" class="form-control" id="contract_end" name="contract_end" 
                                               value="<?php echo htmlspecialchars($formData['contract_end'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label for="salary" class="form-label">Monthly Salary</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="salary" name="salary" 
                                                   value="<?php echo htmlspecialchars($formData['salary'] ?? ''); ?>"
                                                   min="0" step="0.01" placeholder="5000.00">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="mb-4">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-info-circle me-2"></i>Additional Information
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="stats" class="form-label">Player Statistics</label>
                                        <textarea class="form-control" id="stats" name="stats" rows="3" 
                                                  placeholder="Goals: 15, Assists: 8, Matches: 25"><?php echo htmlspecialchars($formData['stats'] ?? ''); ?></textarea>
                                        <div class="form-text">Current season statistics and achievements</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="contract" class="form-label">Contract Notes</label>
                                        <textarea class="form-control" id="contract" name="contract" rows="3" 
                                                  placeholder="Contract details, clauses, etc."><?php echo htmlspecialchars($formData['contract'] ?? ''); ?></textarea>
                                        <div class="form-text">Additional contract information</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end pt-3 border-top">
                                <a href="players.php" class="btn btn-outline-secondary me-md-2">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-user-plus me-2"></i>Add Player
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
    <script src="assets/js/main.js"></script>
    
    <script>
    // Enhanced form validation and user experience
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('playerForm');
        const submitBtn = document.getElementById('submitBtn');
        
        // Custom validation
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            let isValid = true;
            
            // Custom validations
            const name = document.getElementById('name').value.trim();
            const age = parseInt(document.getElementById('age').value);
            const position = document.getElementById('position').value;
            
            if (name.length < 2) {
                isValid = false;
                showFieldError('name', 'Name must be at least 2 characters long');
            }
            
            if (age < 16 || age > 50) {
                isValid = false;
                showFieldError('age', 'Age must be between 16 and 50');
            }
            
            if (!position) {
                isValid = false;
                showFieldError('position', 'Please select a position');
            }
            
            form.classList.add('was-validated');
            
            if (isValid && form.checkValidity()) {
                // Show loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Adding Player...';
                
                // Submit form
                form.submit();
            }
        });
        
        // Real-time validation feedback
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                if (this.checkValidity()) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            });
        });
        
        // Auto-calculate age from date of birth
        document.getElementById('date_of_birth').addEventListener('change', function() {
            if (this.value) {
                const birthDate = new Date(this.value);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                
                document.getElementById('age').value = age;
            }
        });
        
        // Jersey number validation
        const jerseyInput = document.getElementById('jersey_number');
        const teamSelect = document.getElementById('team_id');
        
        function validateJerseyNumber() {
            const jersey = jerseyInput.value;
            const team = teamSelect.value;
            
            if (jersey && team) {
                // You could add AJAX validation here to check if jersey number is taken
                console.log(`Validating jersey ${jersey} for team ${team}`);
            }
        }
        
        jerseyInput.addEventListener('input', validateJerseyNumber);
        teamSelect.addEventListener('change', validateJerseyNumber);
    });
    
    function showFieldError(fieldId, message) {
        const field = document.getElementById(fieldId);
        field.classList.add('is-invalid');
        
        let feedback = field.parentElement.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.textContent = message;
        }
    }
    </script>
</body>
</html>
