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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_player'])) {
        $playerController->delete($_POST['delete_player']);
        header('Location: players.php');
        exit();
    }
}

$players = $playerController->getAllPlayers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Management - Football Club</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
    <?php include '../src/views/partials/header.php'; ?>

    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="display-5 fw-bold mb-3">Player Management</h1>
                <a href="players_add.php" class="btn btn-primary">Add New Player</a>
            </div>
        </div>
        
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Position</th>
                                        <th>Age</th>
                                        <th>Nationality</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($players)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No players found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($players as $player): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($player['id']); ?></td>
                                                <td><?php echo htmlspecialchars($player['name']); ?></td>
                                                <td><?php echo htmlspecialchars($player['position']); ?></td>
                                                <td><?php echo htmlspecialchars($player['age']); ?></td>
                                                <td><?php echo htmlspecialchars($player['nationality'] ?? 'N/A'); ?></td>
                                                <td>
                                                    <a href="player_edit.php?id=<?php echo $player['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                    <form action="players.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this player?');">
                                                        <input type="hidden" name="delete_player" value="<?php echo $player['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../src/views/partials/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>