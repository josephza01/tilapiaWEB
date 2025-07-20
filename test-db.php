<?php
/**
 * Simple Database Connection Test
 * Use this to quickly test if your database connection is working
 */

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
        h1 { color: #333; text-align: center; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔍 Database Connection Test</h1>";

try {
    // Test 1: Basic MySQL connection
    echo "<h3>Test 1: Basic MySQL Connection</h3>";
    $testDsn = "mysql:host=localhost;charset=utf8mb4";
    $testConn = new PDO($testDsn, "root", "");
    $testConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<div class='success'>✅ Basic MySQL connection successful!</div>";
    
    // Test 2: Database exists
    echo "<h3>Test 2: Database Existence</h3>";
    $dbCheckQuery = $testConn->query("SHOW DATABASES LIKE 'ponglert_tilapia_farm'");
    $dbExists = $dbCheckQuery->rowCount() > 0;
    
    if ($dbExists) {
        echo "<div class='success'>✅ Database 'ponglert_tilapia_farm' exists!</div>";
    } else {
        echo "<div class='error'>❌ Database 'ponglert_tilapia_farm' does not exist!</div>";
        echo "<div class='info'>💡 Run setup.php to create the database automatically.</div>";
    }
    
    // Test 3: Connect to specific database
    if ($dbExists) {
        echo "<h3>Test 3: Connect to Tilapia Farm Database</h3>";
        require_once "model/config.php";
        $database = new Database();
        $conn = $database->getConnection();
        echo "<div class='success'>✅ Connected to ponglert_tilapia_farm database!</div>";
        
        // Test 4: Check tables
        echo "<h3>Test 4: Database Tables</h3>";
        $tablesQuery = $conn->query("SHOW TABLES");
        $tables = $tablesQuery->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($tables) > 0) {
            echo "<div class='success'>✅ Found " . count($tables) . " tables:</div>";
            echo "<div class='info'><ul>";
            foreach ($tables as $table) {
                echo "<li>" . htmlspecialchars($table) . "</li>";
            }
            echo "</ul></div>";
            
            // Test 5: Check user table specifically
            if (in_array('user_tb', $tables)) {
                echo "<h3>Test 5: User Table Test</h3>";
                $userCountQuery = $conn->query("SELECT COUNT(*) as count FROM user_tb");
                $userCount = $userCountQuery->fetch()['count'];
                echo "<div class='success'>✅ User table has $userCount users</div>";
                
                if ($userCount > 0) {
                    echo "<div class='info'>🎉 <strong>Everything looks good! Your login system should work now.</strong></div>";
                    echo "<p><a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
                } else {
                    echo "<div class='info'>💡 No users in database. You may need to add users manually or check if the SQL import was complete.</div>";
                }
            } else {
                echo "<div class='error'>❌ 'user_tb' table not found!</div>";
            }
        } else {
            echo "<div class='error'>❌ No tables found in database!</div>";
            echo "<div class='info'>💡 Run setup.php to import the database structure.</div>";
        }
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>❌ Connection failed: " . htmlspecialchars($e->getMessage()) . "</div>";
    
    echo "<h3>🔧 Troubleshooting Steps:</h3>";
    echo "<div class='info'>
        <ol>
            <li><strong>Check XAMPP Status:</strong>
                <ul>
                    <li>Open XAMPP Control Panel</li>
                    <li>Make sure Apache and MySQL are both running (green status)</li>
                </ul>
            </li>
            <li><strong>Verify MySQL Settings:</strong>
                <ul>
                    <li>Default XAMPP MySQL username: <code>root</code></li>
                    <li>Default XAMPP MySQL password: <code>(empty)</code></li>
                    <li>Port: <code>3306</code></li>
                </ul>
            </li>
            <li><strong>Try phpMyAdmin:</strong>
                <ul>
                    <li>Go to <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a></li>
                    <li>If it works, your MySQL is running correctly</li>
                </ul>
            </li>
            <li><strong>Run Database Setup:</strong>
                <ul>
                    <li>Go to <a href='setup.php'>setup.php</a> to automatically set up the database</li>
                </ul>
            </li>
        </ol>
    </div>";
}

echo "
        <h3>📞 Quick Actions</h3>
        <div style='text-align: center; margin-top: 20px;'>
            <a href='setup.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>Database Setup</a>
            <a href='login.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>Login Page</a>
            <a href='http://localhost/phpmyadmin' target='_blank' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>phpMyAdmin</a>
        </div>
    </div>
</body>
</html>";
?>
