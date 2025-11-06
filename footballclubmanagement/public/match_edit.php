<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../src/config/database.php';
require_once '../src/controllers/MatchController.php';
require_once '../src/controllers/AuthController.php';

$matchController = new \App\Controllers\MatchController($conn);
$authController = new \App\Controllers\AuthController($conn);

// Check if user is logged in
if (!$authController->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$success = $error = '';
$match = null;

if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM matches WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $match = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$match) {
        header('Location: matches.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $home_team = $_POST['home_team'] ?? '';
    $away_team = $_POST['away_team'] ?? '';
    $match_date = $_POST['match_date'] ?? '';
    $score = $_POST['score'] ?? '';

    if ($home_team && $away_team && $match_date) {
        $data = [
            'id' => $_GET['id'],
            'home_team' => $home_team,
            'away_team' => $away_team,
            'match_date' => $match_date,
            'score' => $score
        ];
        
        if ($matchController->editMatch($data)) {
            $success = 'Match updated successfully!';
            // Refresh match data
            $stmt = $conn->prepare("SELECT * FROM matches WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $match = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = 'Failed to update match.';
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Match - Football Club</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
    <?php include '../src/views/partials/header.php'; ?>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="mb-4 text-center">Edit Match</h2>
                        <?php if ($success): ?>
                            <div class="alert alert-success text-center"><?php echo $success; ?></div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger text-center"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="match_edit.php?id=<?php echo $_GET['id']; ?>">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="home_team" class="form-label">Home Team</label>
                                        <input type="text" class="form-control" id="home_team" name="home_team" 
                                               value="<?php echo htmlspecialchars($match['home_team'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="away_team" class="form-label">Away Team</label>
                                        <input type="text" class="form-control" id="away_team" name="away_team" 
                                               value="<?php echo htmlspecialchars($match['away_team'] ?? ''); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="match_date" class="form-label">Match Date & Time</label>
                                        <input type="datetime-local" class="form-control" id="match_date" name="match_date" 
                                               value="<?php echo date('Y-m-d\TH:i', strtotime($match['match_date'] ?? '')); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="score" class="form-label">Score (Optional)</label>
                                        <input type="text" class="form-control" id="score" name="score" 
                                               value="<?php echo htmlspecialchars($match['score'] ?? ''); ?>"
                                               placeholder="e.g., 2-1">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="matches.php" class="btn btn-secondary me-md-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Match</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../src/views/partials/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>