<?php
require_once 'config/database.php';

echo "<h1>Car Rental System - Database Setup</h1>";
echo "<p>This script will set up the database tables required for the Car Rental System.</p>";

if (isset($_GET['setup']) && $_GET['setup'] === 'now') {
    echo "<h2>Setting up database...</h2>";
    
    // Read the SQL file
    $sqlFile = file_get_contents('database_setup.sql');
    
    if (!$sqlFile) {
        echo "<p style='color: red;'>❌ Error: Could not read database_setup.sql file</p>";
        exit();
    }
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sqlFile)));
    $executed = 0;
    $errors = [];
    
    try {
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                try {
                    $pdo->exec($statement);
                    $executed++;
                } catch (Exception $e) {
                    $errors[] = $statement . "\n  Error: " . $e->getMessage();
                }
            }
        }
        
        echo "<p style='color: green;'>✓ Database setup completed successfully!</p>";
        echo "<p><strong>Executed $executed statements</strong></p>";
        
        if (count($errors) > 0) {
            echo "<h3 style='color: orange;'>Warnings/Errors (usually harmless if tables already exist):</h3>";
            echo "<ul>";
            foreach ($errors as $error) {
                echo "<li><small>" . htmlspecialchars($error) . "</small></li>";
            }
            echo "</ul>";
        }
        
        // Verify tables
        echo "<h3>Database Tables Created:</h3>";
        $result = $pdo->query("SHOW TABLES");
        $tables = $result->fetchAll(PDO::FETCH_COLUMN);
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>" . htmlspecialchars($table) . "</li>";
        }
        echo "</ul>";
        
        echo "<h3>Sample Login Credentials:</h3>";
        echo "<p><strong>Admin:</strong> username: <code>admin</code>, password: <code>admin123</code></p>";
        echo "<p><strong>Customer:</strong> username: <code>customer1</code>, password: <code>admin123</code> (use this to test bookings)</p>";
        
        echo "<p><a href='index.php' style='padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; display: inline-block;'>Go to Home Page →</a></p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<h2>Database Status Check:</h2>";
    
    try {
        // Check existing tables
        $result = $pdo->query("SHOW TABLES");
        $tables = $result->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($tables)) {
            echo "<p style='color: orange;'>No tables found in database.</p>";
            echo "<p style='color: orange;'>⚠️ <strong>IMPORTANT:</strong> The database needs to be set up before you can use the system.</p>";
            echo "<p><a href='?setup=now' style='padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;'>Click here to set up database automatically →</a></p>";
        } else {
            echo "<h3>✓ Found " . count($tables) . " tables:</h3>";
            echo "<ul>";
            foreach ($tables as $table) {
                echo "<li>" . htmlspecialchars($table) . "</li>";
            }
            echo "</ul>";
            
            // Check if rentals table has data
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM rentals");
                $row = $stmt->fetch();
                echo "<p><strong>Rentals in database:</strong> " . $row['count'] . "</p>";
            } catch (Exception $e) {
                echo "<p style='color: orange;'>Could not check rentals table: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
            
            // Check if cars table has data
            try {
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM car");
                $row = $stmt->fetch();
                echo "<p><strong>Cars in database:</strong> " . $row['count'] . "</p>";
            } catch (Exception $e) {
                echo "<p style='color: orange;'>Could not check cars table: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
            
            echo "<h3>Verify Rentals Table Structure:</h3>";
            try {
                $result = $pdo->query("DESCRIBE rentals");
                echo "<table border='1' cellpadding='5' style='margin-top: 10px;'>";
                echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
                while ($col = $result->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
                    echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
                    echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
                    echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
                    echo "<td>" . htmlspecialchars($col['Default']) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } catch (Exception $e) {
                echo "<p style='color: red;'>Error checking rentals table: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
            
            echo "<p style='margin-top: 20px;'><a href='?setup=now' style='padding: 10px 20px; background: #2196F3; color: white; text-decoration: none; border-radius: 5px; display: inline-block;'>Re-run Database Setup →</a></p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Error connecting to database: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

echo "<hr>";
echo "<p><a href='index.php'>← Back to Home</a></p>";
?>
