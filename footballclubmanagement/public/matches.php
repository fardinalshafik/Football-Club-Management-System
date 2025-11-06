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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $matchController->deleteMatch($_POST['id']);
        header('Location: matches.php');
        exit();
    }
}

$matches = $matchController->getAllMatches();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Match Management - Football Club</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
    <?php include '../src/views/partials/header.php'; ?>

    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="display-5 fw-bold mb-3">Match Management</h1>
                <a href="matches_add.php" class="btn btn-primary">Add New Match</a>
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
                                        <th>Home Team</th>
                                        <th>Away Team</th>
                                        <th>Date</th>
                                        <th>Score</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($matches)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No matches found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($matches as $match): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($match['id']); ?></td>
                                                <td><?php echo htmlspecialchars($match['home_team']); ?></td>
                                                <td><?php echo htmlspecialchars($match['away_team']); ?></td>
                                                <td><?php echo htmlspecialchars($match['match_date']); ?></td>
                                                <td><?php echo htmlspecialchars($match['score'] ?? 'Not played'); ?></td>
                                                <td>
                                                    <a href="match_edit.php?id=<?php echo $match['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                    <form action="matches.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this match?');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $match['id']; ?>">
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
</body>
</html>