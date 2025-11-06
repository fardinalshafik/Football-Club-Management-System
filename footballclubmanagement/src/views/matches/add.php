<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Match</title>
    <link rel="stylesheet" href="../../public/assets/css/style.css">
</head>
<body>
    <?php include '../partials/header.php'; ?>

    <div class="container">
        <h2>Add New Match</h2>
        <form action="../../src/controllers/MatchController.php?action=add" method="POST">
            <div class="form-group">
                <label for="home_team">Home Team:</label>
                <input type="text" id="home_team" name="home_team" required>
            </div>
            <div class="form-group">
                <label for="away_team">Away Team:</label>
                <input type="text" id="away_team" name="away_team" required>
            </div>
            <div class="form-group">
                <label for="match_date">Match Date:</label>
                <input type="date" id="match_date" name="match_date" required>
            </div>
            <div class="form-group">
                <label for="venue">Venue:</label>
                <input type="text" id="venue" name="venue" required>
            </div>
            <button type="submit">Add Match</button>
        </form>
    </div>

    <?php include '../partials/footer.php'; ?>
</body>
</html>