
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../src/config/database.php';
require_once '../src/controllers/AuthController.php';
require_once '../src/controllers/PlayerController.php';
require_once '../src/controllers/MatchController.php';

// Initialize controllers with error handling
try {
    $authController = new \App\Controllers\AuthController($conn);
    $playerController = new \App\Controllers\PlayerController($conn);
    $matchController = new \App\Controllers\MatchController($conn);

    if (!$authController->isLoggedIn()) {
        header('Location: login.php');
        exit();
    }

    // Get user role and view parameter
    $userRole = $_SESSION['role'] ?? 'member';
    $view = $_GET['view'] ?? $userRole; // Use role as default view
    
    // Ensure user can only access their own role view or admin can access all
    if ($userRole !== 'admin' && $view !== $userRole) {
        $view = $userRole;
    }

    $players = $playerController->getAllPlayers();
    $matches = $matchController->getAllMatches();
    
    // Function to calculate wins for our club
    function calculateWins($matches) {
        $wins = 0;
        $clubNames = ['Our Club']; // Add more club names as needed
        
        foreach ($matches as $match) {
            if (!empty($match['score']) && strpos($match['score'], '-') !== false) {
                $scores = explode('-', $match['score']);
                if (count($scores) == 2) {
                    $homeScore = intval($scores[0]);
                    $awayScore = intval($scores[1]);
                    
                    // Check if any of our club names won
                    foreach ($clubNames as $clubName) {
                        if (($match['home_team'] === $clubName && $homeScore > $awayScore) || 
                            ($match['away_team'] === $clubName && $awayScore > $homeScore)) {
                            $wins++;
                            break; // Avoid double counting
                        }
                    }
                }
            }
        }
        return $wins;
    }
} catch (Exception $e) {
    // If there's a database connection error, show a helpful message
    $error_message = "Database connection failed. Please ensure XAMPP MySQL is running and the database is properly set up.";
    $players = [];
    $matches = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Football Club Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
    <?php include '../src/views/partials/header.php'; ?>

    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-12 text-center">
                <?php 
                $dashboardTitles = [
                    'admin' => 'Admin Dashboard',
                    'coach' => 'Coach Dashboard', 
                    'player' => 'Player Dashboard',
                    'staff' => 'Staff Dashboard',
                    'member' => 'Member Dashboard'
                ];
                $dashboardIcons = [
                    'admin' => 'fas fa-crown',
                    'coach' => 'fas fa-chalkboard-teacher',
                    'player' => 'fas fa-running', 
                    'staff' => 'fas fa-user-tie',
                    'member' => 'fas fa-users'
                ];
                ?>
                <h1 class="display-5 fw-bold mb-3">
                    <i class="<?php echo $dashboardIcons[$view] ?? 'fas fa-tachometer-alt'; ?> me-2"></i>
                    <?php echo $dashboardTitles[$view] ?? 'Dashboard'; ?>
                </h1>
                <p class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-warning" role="alert">
                        <strong>Database Connection Issue:</strong> <?php echo $error_message; ?>
                        <br><small>Please check the <a href="../test_connection.php">database connection test</a> for more details.</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($view === 'admin'): ?>
        <!-- Admin Statistics -->
        <div class="row mb-4 justify-content-center">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="dashboard-card">
                    <i class="fas fa-users fa-2x mb-3"></i>
                    <h5 class="card-title">Total Players</h5>
                    <p class="display-6 mb-0 fw-bold"><?php echo count($players); ?></p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="dashboard-card">
                    <i class="fas fa-calendar-alt fa-2x mb-3"></i>
                    <h5 class="card-title">Total Matches</h5>
                    <p class="display-6 mb-0 fw-bold"><?php echo count($matches); ?></p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="dashboard-card">
                    <i class="fas fa-trophy fa-2x mb-3"></i>
                    <h5 class="card-title">Wins</h5>
                    <p class="display-6 mb-0 fw-bold">
                        <?php echo calculateWins($matches); ?>
                    </p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="dashboard-card">
                    <i class="fas fa-user-tie fa-2x mb-3"></i>
                    <h5 class="card-title">Staff</h5>
                    <p class="display-6 mb-0 fw-bold">
                        <?php 
                        try {
                            $stmt = $conn->prepare("SELECT COUNT(*) FROM staff");
                            $stmt->execute();
                            echo $stmt->fetchColumn();
                        } catch (Exception $e) {
                            echo "N/A";
                        }
                        ?>
                    </p>
                </div>
            </div>
        </div>
        
        <?php elseif ($view === 'coach'): ?>
        <!-- Coach Statistics -->
        <div class="row mb-4 justify-content-center">
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="dashboard-card">
                    <i class="fas fa-users fa-2x mb-3"></i>
                    <h5 class="card-title">My Players</h5>
                    <p class="display-6 mb-0 fw-bold"><?php echo count($players); ?></p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="dashboard-card">
                    <i class="fas fa-calendar-check fa-2x mb-3"></i>
                    <h5 class="card-title">Upcoming Matches</h5>
                    <p class="display-6 mb-0 fw-bold">
                        <?php 
                        $upcomingMatches = 0;
                        foreach ($matches as $match) {
                            if (strtotime($match['match_date']) > time()) {
                                $upcomingMatches++;
                            }
                        }
                        echo $upcomingMatches;
                        ?>
                    </p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="dashboard-card">
                    <i class="fas fa-trophy fa-2x mb-3"></i>
                    <h5 class="card-title">Season Wins</h5>
                    <p class="display-6 mb-0 fw-bold">
                        <?php echo calculateWins($matches); ?>
                    </p>
                </div>
            </div>
        </div>
        
        <?php elseif ($view === 'player'): ?>
        <!-- Player Statistics -->
        <div class="row mb-4 justify-content-center">
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="dashboard-card">
                    <i class="fas fa-calendar-alt fa-2x mb-3"></i>
                    <h5 class="card-title">Total Matches</h5>
                    <p class="display-6 mb-0 fw-bold"><?php echo count($matches); ?></p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="dashboard-card">
                    <i class="fas fa-calendar-check fa-2x mb-3"></i>
                    <h5 class="card-title">Next Match</h5>
                    <p class="display-6 mb-0 fw-bold">
                        <?php 
                        $nextMatch = null;
                        foreach ($matches as $match) {
                            if (strtotime($match['match_date']) > time()) {
                                if (!$nextMatch || strtotime($match['match_date']) < strtotime($nextMatch['match_date'])) {
                                    $nextMatch = $match;
                                }
                            }
                        }
                        echo $nextMatch ? date('M j', strtotime($nextMatch['match_date'])) : 'TBD';
                        ?>
                    </p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="dashboard-card">
                    <i class="fas fa-users fa-2x mb-3"></i>
                    <h5 class="card-title">Team Members</h5>
                    <p class="display-6 mb-0 fw-bold"><?php echo count($players); ?></p>
                </div>
            </div>
        </div>
        
        <?php else: ?>
        <!-- Staff/Member Statistics -->
        <div class="row mb-4 justify-content-center">
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="dashboard-card">
                    <i class="fas fa-calendar-alt fa-2x mb-3"></i>
                    <h5 class="card-title">Matches</h5>
                    <p class="display-6 mb-0 fw-bold"><?php echo count($matches); ?></p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="dashboard-card">
                    <i class="fas fa-users fa-2x mb-3"></i>
                    <h5 class="card-title">Players</h5>
                    <p class="display-6 mb-0 fw-bold"><?php echo count($players); ?></p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="dashboard-card">
                    <i class="fas fa-trophy fa-2x mb-3"></i>
                    <h5 class="card-title">Wins</h5>
                    <p class="display-6 mb-0 fw-bold">
                        <?php echo calculateWins($matches); ?>
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="management-card">
                    <h4 class="mb-4 text-center text-gradient">Quick Actions</h4>
                    <div class="row g-3">
                        <?php if ($view === 'admin'): ?>
                            <!-- Admin Actions -->
                            <div class="col-md-6">
                                <a href="players_add.php" class="btn btn-primary w-100">
                                    <i class="fas fa-user-plus me-2"></i>Add Player
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="players.php" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-users me-2"></i>Manage Players
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="matches_add.php" class="btn btn-success w-100">
                                    <i class="fas fa-calendar-plus me-2"></i>Add Match
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="matches.php" class="btn btn-outline-success w-100">
                                    <i class="fas fa-calendar-alt me-2"></i>Manage Matches
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="staff_add.php" class="btn btn-warning w-100">
                                    <i class="fas fa-user-tie me-2"></i>Add Staff
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="staff.php" class="btn btn-outline-warning w-100">
                                    <i class="fas fa-users-cog me-2"></i>Manage Staff
                                </a>
                            </div>
                        
                        <?php elseif ($view === 'coach'): ?>
                            <!-- Coach Actions -->
                            <div class="col-md-6">
                                <a href="players.php" class="btn btn-primary w-100">
                                    <i class="fas fa-users me-2"></i>View Players
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="matches.php" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-calendar-alt me-2"></i>View Matches
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="players_add.php" class="btn btn-success w-100">
                                    <i class="fas fa-user-plus me-2"></i>Add Player
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="matches_add.php" class="btn btn-outline-success w-100">
                                    <i class="fas fa-calendar-plus me-2"></i>Schedule Match
                                </a>
                            </div>
                        
                        <?php elseif ($view === 'player'): ?>
                            <!-- Player Actions -->
                            <div class="col-md-6">
                                <a href="matches.php" class="btn btn-primary w-100">
                                    <i class="fas fa-calendar-alt me-2"></i>View Matches
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="players.php" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-users me-2"></i>Team Roster
                                </a>
                            </div>
                            <div class="col-md-12">
                                <a href="#" class="btn btn-success w-100" onclick="alert('Profile management feature coming soon!')">
                                    <i class="fas fa-user-edit me-2"></i>Update My Profile
                                </a>
                            </div>
                        
                        <?php elseif ($view === 'staff'): ?>
                            <!-- Staff Actions -->
                            <div class="col-md-6">
                                <a href="players.php" class="btn btn-primary w-100">
                                    <i class="fas fa-users me-2"></i>View Players
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="matches.php" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-calendar-alt me-2"></i>View Matches
                                </a>
                            </div>
                            <div class="col-md-12">
                                <a href="staff.php" class="btn btn-success w-100">
                                    <i class="fas fa-user-tie me-2"></i>Staff Directory
                                </a>
                            </div>
                        
                        <?php else: ?>
                            <!-- Member Actions -->
                            <div class="col-md-6">
                                <a href="players.php" class="btn btn-primary w-100">
                                    <i class="fas fa-users me-2"></i>View Players
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="matches.php" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-calendar-alt me-2"></i>View Matches
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../src/views/partials/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>