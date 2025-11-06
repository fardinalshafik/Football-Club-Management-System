<?php
require_once '../src/config/database.php';

// Create test users for each role
$testUsers = [
    [
        'username' => 'admin',
        'password' => password_hash('admin123', PASSWORD_DEFAULT),
        'email' => 'admin@club.com',
        'role' => 'admin'
    ],
    [
        'username' => 'coach',
        'password' => password_hash('coach123', PASSWORD_DEFAULT),
        'email' => 'coach@club.com',
        'role' => 'coach'
    ],
    [
        'username' => 'player',
        'password' => password_hash('player123', PASSWORD_DEFAULT),
        'email' => 'player@club.com',
        'role' => 'player'
    ],
    [
        'username' => 'member',
        'password' => password_hash('member123', PASSWORD_DEFAULT),
        'email' => 'member@club.com',
        'role' => 'member'
    ]
];

try {
    echo "<h2>Creating Test Users</h2>";
    
    foreach ($testUsers as $user) {
        // Check if user already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$user['username']]);
        
        if ($stmt->fetch()) {
            echo "<p>✅ User '{$user['username']}' already exists</p>";
        } else {
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
            $result = $stmt->execute([$user['username'], $user['password'], $user['email'], $user['role']]);
            
            if ($result) {
                echo "<p>✅ Created user: {$user['username']} (role: {$user['role']}) - Password: {$user['username']}123</p>";
            } else {
                echo "<p>❌ Failed to create user: {$user['username']}</p>";
            }
        }
    }
    
    echo "<h3>Test Login Credentials:</h3>";
    echo "<ul>";
    echo "<li><strong>Admin:</strong> username: admin, password: admin123</li>";
    echo "<li><strong>Coach:</strong> username: coach, password: coach123</li>";
    echo "<li><strong>Player:</strong> username: player, password: player123</li>";
    echo "<li><strong>Member:</strong> username: member, password: member123</li>";
    echo "</ul>";
    
    echo "<h3>Navigation Test:</h3>";
    echo "<p><a href='index.php'>Go to Home Page</a></p>";
    echo "<p><a href='login.php'>Go to Login Page</a></p>";
    echo "<p><a href='dashboard.php'>Go to Dashboard (requires login)</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>