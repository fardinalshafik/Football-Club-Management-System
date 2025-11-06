<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../src/config/database.php';
require_once '../src/controllers/PlayerController.php';
require_once '../src/controllers/AuthController.php';

$playerController = new \App\Controllers\PlayerController($conn);
$authController = new \App\Controllers\AuthController($conn);

// Check if user is logged in
if (!$authController->isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$success = $error = '';
$player = null;

if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM players WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $player = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$player) {
        header('Location: players.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $position = $_POST['position'] ?? '';
    $age = $_POST['age'] ?? '';
    $nationality = $_POST['nationality'] ?? '';
    $stats = $_POST['stats'] ?? '';
    $contract = $_POST['contract'] ?? '';

    if ($name && $position && $age) {
        try {
            $stmt = $conn->prepare("UPDATE players SET name = ?, position = ?, age = ?, nationality = ?, stats = ?, contract = ? WHERE id = ?");
            $result = $stmt->execute([
                $name,
                $position,
                $age,
                $nationality,
                $stats,
                $contract,
                $_GET['id']
            ]);
            
            if ($result) {
                $success = 'Player updated successfully!';
                // Refresh player data
                $stmt = $conn->prepare("SELECT * FROM players WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $player = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                $error = 'Failed to update player.';
            }
        } catch (Exception $e) {
            $error = 'Database error: ' . $e->getMessage();
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
    <title>Edit Player - Football Club</title>
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
                        <h2 class="mb-4 text-center">Edit Player</h2>
                        <?php if ($success): ?>
                            <div class="alert alert-success text-center"><?php echo $success; ?></div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger text-center"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="player_edit.php?id=<?php echo $_GET['id']; ?>">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Name *</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo htmlspecialchars($player['name'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="position" class="form-label">Position *</label>
                                        <select class="form-select" id="position" name="position" required>
                                            <option value="">Select Position</option>
                                            <option value="Goalkeeper" <?php echo ($player['position'] ?? '') === 'Goalkeeper' ? 'selected' : ''; ?>>Goalkeeper</option>
                                            <option value="Defender" <?php echo ($player['position'] ?? '') === 'Defender' ? 'selected' : ''; ?>>Defender</option>
                                            <option value="Midfielder" <?php echo ($player['position'] ?? '') === 'Midfielder' ? 'selected' : ''; ?>>Midfielder</option>
                                            <option value="Forward" <?php echo ($player['position'] ?? '') === 'Forward' ? 'selected' : ''; ?>>Forward</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="age" class="form-label">Age *</label>
                                        <input type="number" class="form-control" id="age" name="age" min="16" max="50"
                                               value="<?php echo htmlspecialchars($player['age'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nationality" class="form-label">Nationality</label>
                                        <input type="text" class="form-control" id="nationality" name="nationality"
                                               value="<?php echo htmlspecialchars($player['nationality'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="stats" class="form-label">Stats</label>
                                <textarea class="form-control" id="stats" name="stats" rows="3"><?php echo htmlspecialchars($player['stats'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="contract" class="form-label">Contract</label>
                                <input type="text" class="form-control" id="contract" name="contract"
                                       value="<?php echo htmlspecialchars($player['contract'] ?? ''); ?>"
                                       placeholder="e.g., 2023-2026">
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="players.php" class="btn btn-secondary me-md-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Player</button>
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