<?php
// This file displays a list of matches with options to edit or delete.

require_once '../../config/database.php';
require_once '../controllers/MatchController.php';

$matchController = new \App\Controllers\MatchController($conn);
$matches = $matchController->getAllMatches();

include '../partials/header.php';
?>

<div class="container">
    <h2>Match List</h2>
    <a href="add.php" class="btn btn-primary">Add New Match</a>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Home Team</th>
                <th>Away Team</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($matches as $match): ?>
                <tr>
                    <td><?php echo htmlspecialchars($match['id']); ?></td>
                    <td><?php echo htmlspecialchars($match['home_team']); ?></td>
                    <td><?php echo htmlspecialchars($match['away_team']); ?></td>
                    <td><?php echo htmlspecialchars($match['match_date']); ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo htmlspecialchars($match['id']); ?>" class="btn btn-warning">Edit</a>
                        <a href="../controllers/MatchController.php?action=delete&id=<?php echo htmlspecialchars($match['id']); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this match?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../partials/footer.php'; ?>