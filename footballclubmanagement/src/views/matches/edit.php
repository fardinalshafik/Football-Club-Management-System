<?php
// edit.php - Form for editing an existing match

require_once '../../config/database.php';
require_once '../../controllers/MatchController.php';

$matchController = new \App\Controllers\MatchController($conn);
$matchId = $_GET['id'] ?? null;


if ($matchId) {
    // Fetch match data
    $stmt = $conn->prepare("SELECT * FROM matches WHERE id = ?");
    $stmt->execute([$matchId]);
    $match = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$match) {
        header('Location: list.php');
        exit;
    }
    // Handle update
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $stmt = $conn->prepare("UPDATE matches SET home_team = ?, away_team = ?, match_date = ?, score = ? WHERE id = ?");
        $stmt->execute([
            $_POST['home_team'],
            $_POST['away_team'],
            $_POST['match_date'],
            $_POST['score'],
            $matchId
        ]);
        header('Location: list.php');
        exit;
    }
} else {
    header('Location: list.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Match</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body>
    <?php include '../partials/header.php'; ?>
    <div class="container">
        <h2>Edit Match</h2>
        <form action="" method="POST">
            <div class="form-group">
                <label for="home_team">Home Team</label>
                <input type="text" name="home_team" id="home_team" value="<?php echo htmlspecialchars($match['home_team']); ?>" required>
            </div>
            <div class="form-group">
                <label for="away_team">Away Team</label>
                <input type="text" name="away_team" id="away_team" value="<?php echo htmlspecialchars($match['away_team']); ?>" required>
            </div>
            <div class="form-group">
                <label for="match_date">Date</label>
                <input type="date" name="match_date" id="match_date" value="<?php echo htmlspecialchars($match['match_date']); ?>" required>
            </div>
            <div class="form-group">
                <label for="time">Time</label>
                <input type="time" name="time" id="time" value="<?php echo htmlspecialchars($match['time']); ?>" required>
            </div>
            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" name="location" id="location" value="<?php echo htmlspecialchars($match['location']); ?>" required>
            </div>
            <button type="submit">Update Match</button>
        </form>
    </div>
    <?php include '../partials/footer.php'; ?>
</body>
</html>