<?php
// This file contains the form for editing an existing player's information.

require_once '../../config/database.php';
require_once '../../controllers/PlayerController.php';

$playerController = new \App\Controllers\PlayerController($conn);

$playerId = $_GET['id'] ?? null;
$player = null;
if ($playerId) {
    // Fetch player data
    $stmt = $conn->prepare("SELECT * FROM players WHERE id = ?");
    $stmt->execute([$playerId]);
    $player = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$player) {
        header('Location: /public/players.php');
        exit;
    }
    // Handle update
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $stmt = $conn->prepare("UPDATE players SET name = ?, position = ?, age = ?, team = ? WHERE id = ?");
        $stmt->execute([
            $_POST['name'],
            $_POST['position'],
            $_POST['age'],
            $_POST['team'],
            $playerId
        ]);
        header('Location: /public/players.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Player</title>
    <link rel="stylesheet" href="/public/assets/css/style.css">
</head>
<body>
    <?php include '../partials/header.php'; ?>
    <div class="container">
        <h2>Edit Player</h2>
        <form action="" method="POST">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($player['name']); ?>" required>

            <label for="position">Position:</label>
            <input type="text" id="position" name="position" value="<?php echo htmlspecialchars($player['position']); ?>" required>

            <label for="age">Age:</label>
            <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($player['age']); ?>" required>

            <label for="team">Team:</label>
            <input type="text" id="team" name="team" value="<?php echo htmlspecialchars($player['team']); ?>" required>

            <button type="submit">Update Player</button>
        </form>
    </div>
    <?php include '../partials/footer.php'; ?>
</body>
</html>