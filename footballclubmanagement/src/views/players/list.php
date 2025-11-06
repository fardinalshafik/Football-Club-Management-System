<?php
require_once '../../config/database.php';
require_once '../../controllers/PlayerController.php';

$playerController = new \App\Controllers\PlayerController($conn);
$players = $playerController->getAllPlayers();

include '../partials/header.php';
?>

<div class="container">
    <h1>Player List</h1>
    <a href="add.php" class="btn btn-primary">Add New Player</a>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Position</th>
                <th>Age</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($players as $player): ?>
                <tr>
                    <td><?php echo htmlspecialchars($player['id']); ?></td>
                    <td><?php echo htmlspecialchars($player['name']); ?></td>
                    <td><?php echo htmlspecialchars($player['position']); ?></td>
                    <td><?php echo htmlspecialchars($player['age']); ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo htmlspecialchars($player['id']); ?>" class="btn btn-warning">Edit</a>
                        <a href="../../controllers/PlayerController.php?action=delete&id=<?php echo htmlspecialchars($player['id']); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this player?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../partials/footer.php'; ?>