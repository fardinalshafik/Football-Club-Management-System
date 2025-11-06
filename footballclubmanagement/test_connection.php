<?php
// Test database connection script
echo "<h2>Testing Database Connection</h2>";

try {
    // Include the database configuration
    require_once 'src/config/database.php';
    
    echo "<p style='color: green;'>✓ Database connection successful!</p>";
    
    // Test if tables exist
    $tables = ['users', 'teams', 'players', 'matches', 'staff'];
    
    echo "<h3>Checking Tables:</h3>";
    foreach ($tables as $table) {
        try {
            $stmt = $conn->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<p style='color: green;'>✓ Table '$table' exists with $count records</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Table '$table' not found: " . $e->getMessage() . "</p>";
        }
    }
    
    // Test specific queries that controllers use
    echo "<h3>Testing Controller Queries:</h3>";
    
    // Test players query
    try {
        $stmt = $conn->query("SELECT * FROM players");
        $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<p style='color: green;'>✓ Players query successful - Found " . count($players) . " players</p>";
        
        // Show first player if exists
        if (count($players) > 0) {
            $player = $players[0];
            echo "<p>Sample player: {$player['name']} - {$player['position']} - Age {$player['age']}</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>✗ Players query failed: " . $e->getMessage() . "</p>";
    }
    
    // Test matches query
    try {
        $stmt = $conn->query("SELECT * FROM matches");
        $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<p style='color: green;'>✓ Matches query successful - Found " . count($matches) . " matches</p>";
        
        // Show first match if exists
        if (count($matches) > 0) {
            $match = $matches[0];
            echo "<p>Sample match: {$match['home_team']} vs {$match['away_team']} - {$match['match_date']}</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>✗ Matches query failed: " . $e->getMessage() . "</p>";
    }
    
    // Test users query
    try {
        $stmt = $conn->query("SELECT * FROM users");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<p style='color: green;'>✓ Users query successful - Found " . count($users) . " users</p>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>✗ Users query failed: " . $e->getMessage() . "</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<p>Please check:</p>";
    echo "<ul>";
    echo "<li>XAMPP MySQL service is running</li>";
    echo "<li>Database 'football_club_management' exists</li>";
    echo "<li>Database credentials in src/config/database.php are correct</li>";
    echo "</ul>";
}

echo "<h3>Instructions to Fix:</h3>";
echo "<ol>";
echo "<li>Start XAMPP and ensure MySQL service is running</li>";
echo "<li>Open phpMyAdmin (http://localhost/phpmyadmin)</li>";
echo "<li>Create a new database called 'football_club_management' or run the SQL file</li>";
echo "<li>Import the SQL schema from sql/football_club_schema.sql</li>";
echo "<li>Refresh this page to test again</li>";
echo "</ol>";
?>