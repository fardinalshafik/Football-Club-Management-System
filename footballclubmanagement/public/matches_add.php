<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../src/config/database.php';
require_once '../src/controllers/MatchController.php';
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

$matchController = new \App\Controllers\MatchController($conn);

$success = $error = '';
$formData = [];

// Get teams for dropdowns
$teams = [];
try {
    $stmt = $conn->query("SELECT id, name FROM teams ORDER BY name");
    $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Teams table might not exist or be empty
}

// Common team names if no teams in database
$commonTeams = [
    'Arsenal', 'Chelsea', 'Liverpool', 'Manchester City', 'Manchester United', 
    'Tottenham', 'Barcelona', 'Real Madrid', 'Bayern Munich', 'Juventus',
    'Paris Saint-Germain', 'AC Milan', 'Inter Milan', 'Atletico Madrid'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Store form data for repopulation
    $formData = $_POST;
    
    $home_team = trim($_POST['home_team'] ?? '');
    $away_team = trim($_POST['away_team'] ?? '');
    $match_date = $_POST['match_date'] ?? '';
    $venue = trim($_POST['venue'] ?? '');
    $match_type = $_POST['match_type'] ?? '';
    $referee = trim($_POST['referee'] ?? '');
    $attendance = $_POST['attendance'] ?? '';
    $score = trim($_POST['score'] ?? '');
    $home_score = $_POST['home_score'] ?? '';
    $away_score = $_POST['away_score'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    // Validation
    if ($home_team && $away_team && $match_date) {
        try {
            // Auto-generate score from individual scores if provided
            if ($home_score !== '' && $away_score !== '') {
                $score = $home_score . '-' . $away_score;
            }
            
            // Determine match status
            $status = 'scheduled';
            if ($score && $score !== '') {
                $status = 'completed';
            } elseif (new DateTime($match_date) <= new DateTime()) {
                $status = 'live';
            }
            
            // Enhanced insert with all fields
            $stmt = $conn->prepare("INSERT INTO matches (home_team, away_team, match_date, venue, match_type, referee, attendance, score, home_score, away_score, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            $result = $stmt->execute([
                $home_team,
                $away_team,
                $match_date,
                $venue ?: null,
                $match_type ?: 'league',
                $referee ?: null,
                $attendance ?: null,
                $score ?: null,
                $home_score ?: null,
                $away_score ?: null,
                $status,
                $notes ?: null
            ]);
            
            if ($result) {
                $success = "Match '$home_team vs $away_team' scheduled successfully!";
                $formData = []; // Clear form data on success
            } else {
                $error = 'Failed to schedule match. Please try again.';
            }
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    } else {
        $error = 'Please fill in all required fields (Home Team, Away Team, and Match Date).';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Match - Football Club Manager</title>
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
                            <i class="fas fa-calendar-plus me-2"></i>Schedule New Match
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
                        
                        <form method="POST" action="matches_add.php" id="matchForm" novalidate>
                            <!-- Teams & Competition Information -->
                            <div class="mb-4">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-users me-2"></i>Teams & Competition
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="home_team" class="form-label">Home Team *</label>
                                        <input type="text" class="form-control" id="home_team" name="home_team" 
                                               list="teamsList" value="<?php echo htmlspecialchars($formData['home_team'] ?? ''); ?>" 
                                               placeholder="Enter home team name" required>
                                        <div class="invalid-feedback">Please provide the home team name.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="away_team" class="form-label">Away Team *</label>
                                        <input type="text" class="form-control" id="away_team" name="away_team" 
                                               list="teamsList" value="<?php echo htmlspecialchars($formData['away_team'] ?? ''); ?>" 
                                               placeholder="Enter away team name" required>
                                        <div class="invalid-feedback">Please provide the away team name.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="match_type" class="form-label">Match Type</label>
                                        <select class="form-select" id="match_type" name="match_type">
                                            <option value="league" <?php echo ($formData['match_type'] ?? 'league') === 'league' ? 'selected' : ''; ?>>League</option>
                                            <option value="cup" <?php echo ($formData['match_type'] ?? '') === 'cup' ? 'selected' : ''; ?>>Cup</option>
                                            <option value="friendly" <?php echo ($formData['match_type'] ?? '') === 'friendly' ? 'selected' : ''; ?>>Friendly</option>
                                            <option value="championship" <?php echo ($formData['match_type'] ?? '') === 'championship' ? 'selected' : ''; ?>>Championship</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="venue" class="form-label">Venue</label>
                                        <input type="text" class="form-control" id="venue" name="venue" 
                                               value="<?php echo htmlspecialchars($formData['venue'] ?? ''); ?>"
                                               placeholder="Stadium name or location">
                                    </div>
                                </div>
                                
                                <!-- Team suggestions datalist -->
                                <datalist id="teamsList">
                                    <?php if (!empty($teams)): ?>
                                        <?php foreach ($teams as $team): ?>
                                            <option value="<?php echo htmlspecialchars($team['name']); ?>">
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <?php foreach ($commonTeams as $team): ?>
                                            <option value="<?php echo $team; ?>">
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </datalist>
                            </div>

                            <!-- Date & Time Information -->
                            <div class="mb-4">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-calendar-alt me-2"></i>Date & Time
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label for="match_date" class="form-label">Match Date & Time *</label>
                                        <input type="datetime-local" class="form-control" id="match_date" name="match_date" 
                                               value="<?php echo htmlspecialchars($formData['match_date'] ?? ''); ?>" required>
                                        <div class="form-text">Select the date and time for the match</div>
                                        <div class="invalid-feedback">Please provide a valid match date and time.</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="referee" class="form-label">Referee</label>
                                        <input type="text" class="form-control" id="referee" name="referee" 
                                               value="<?php echo htmlspecialchars($formData['referee'] ?? ''); ?>"
                                               placeholder="Referee name">
                                    </div>
                                </div>
                            </div>

                            <!-- Match Results (Optional) -->
                            <div class="mb-4">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-trophy me-2"></i>Match Results <small class="text-muted">(Optional - for completed matches)</small>
                                </h5>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label for="home_score" class="form-label">Home Score</label>
                                        <input type="number" class="form-control" id="home_score" name="home_score" 
                                               value="<?php echo htmlspecialchars($formData['home_score'] ?? ''); ?>"
                                               min="0" max="20" placeholder="0">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="away_score" class="form-label">Away Score</label>
                                        <input type="number" class="form-control" id="away_score" name="away_score" 
                                               value="<?php echo htmlspecialchars($formData['away_score'] ?? ''); ?>"
                                               min="0" max="20" placeholder="0">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="score" class="form-label">Final Score</label>
                                        <input type="text" class="form-control" id="score" name="score" 
                                               value="<?php echo htmlspecialchars($formData['score'] ?? ''); ?>"
                                               placeholder="2-1" readonly>
                                        <div class="form-text">Auto-generated from scores above</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="attendance" class="form-label">Attendance</label>
                                        <input type="number" class="form-control" id="attendance" name="attendance" 
                                               value="<?php echo htmlspecialchars($formData['attendance'] ?? ''); ?>"
                                               min="0" placeholder="50000">
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="mb-4">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-info-circle me-2"></i>Additional Information
                                </h5>
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Match Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3" 
                                              placeholder="Any additional information about the match..."><?php echo htmlspecialchars($formData['notes'] ?? ''); ?></textarea>
                                    <div class="form-text">Special conditions, postponements, player highlights, etc.</div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end pt-3 border-top">
                                <a href="matches.php" class="btn btn-outline-secondary me-md-2">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-success" id="submitBtn">
                                    <i class="fas fa-calendar-plus me-2"></i>Schedule Match
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
    // Enhanced form validation and user experience for matches
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('matchForm');
        const submitBtn = document.getElementById('submitBtn');
        
        // Auto-update final score when individual scores change
        const homeScoreInput = document.getElementById('home_score');
        const awayScoreInput = document.getElementById('away_score');
        const finalScoreInput = document.getElementById('score');
        
        function updateFinalScore() {
            const homeScore = homeScoreInput.value;
            const awayScore = awayScoreInput.value;
            
            if (homeScore !== '' && awayScore !== '') {
                finalScoreInput.value = homeScore + '-' + awayScore;
            } else {
                finalScoreInput.value = '';
            }
        }
        
        homeScoreInput.addEventListener('input', updateFinalScore);
        awayScoreInput.addEventListener('input', updateFinalScore);
        
        // Prevent same team selection
        const homeTeamInput = document.getElementById('home_team');
        const awayTeamInput = document.getElementById('away_team');
        
        function validateTeams() {
            const homeTeam = homeTeamInput.value.trim().toLowerCase();
            const awayTeam = awayTeamInput.value.trim().toLowerCase();
            
            if (homeTeam && awayTeam && homeTeam === awayTeam) {
                awayTeamInput.setCustomValidity('Away team cannot be the same as home team');
                awayTeamInput.classList.add('is-invalid');
            } else {
                awayTeamInput.setCustomValidity('');
                awayTeamInput.classList.remove('is-invalid');
            }
        }
        
        homeTeamInput.addEventListener('input', validateTeams);
        awayTeamInput.addEventListener('input', validateTeams);
        
        // Set minimum date to current date
        const matchDateInput = document.getElementById('match_date');
        const now = new Date();
        const minDate = now.toISOString().slice(0, 16);
        matchDateInput.min = minDate;
        
        // Default to next match day (usually weekend)
        const nextSaturday = new Date();
        nextSaturday.setDate(now.getDate() + (6 - now.getDay()) % 7);
        nextSaturday.setHours(15, 0); // 3 PM default
        matchDateInput.value = nextSaturday.toISOString().slice(0, 16);
        
        // Custom form validation
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            let isValid = true;
            
            // Validate required fields
            const homeTeam = homeTeamInput.value.trim();
            const awayTeam = awayTeamInput.value.trim();
            const matchDate = matchDateInput.value;
            
            if (!homeTeam) {
                isValid = false;
                showFieldError('home_team', 'Home team is required');
            }
            
            if (!awayTeam) {
                isValid = false;
                showFieldError('away_team', 'Away team is required');
            }
            
            if (!matchDate) {
                isValid = false;
                showFieldError('match_date', 'Match date and time is required');
            }
            
            // Check if match date is not in the past
            if (matchDate && new Date(matchDate) < new Date()) {
                isValid = false;
                showFieldError('match_date', 'Match date cannot be in the past');
            }
            
            validateTeams();
            
            form.classList.add('was-validated');
            
            if (isValid && form.checkValidity()) {
                // Show loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Scheduling Match...';
                
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
        
        // Auto-suggest venue based on home team
        homeTeamInput.addEventListener('input', function() {
            const homeTeam = this.value.trim();
            const venueInput = document.getElementById('venue');
            
            if (homeTeam && !venueInput.value) {
                // Simple venue suggestion
                venueInput.value = homeTeam + ' Stadium';
            }
        });
        
        // Format attendance number
        const attendanceInput = document.getElementById('attendance');
        attendanceInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, ''); // Remove non-digits
            if (value) {
                // Add thousand separators
                value = parseInt(value).toLocaleString();
                this.value = value;
            }
        });
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
