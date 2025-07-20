<?php
/**
 * Database Setup Script for Tilapia Farm Management System
 * This script will help you set up the database for the first time
 */

require_once "model/config.php";

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Setup - Tilapia Farm Management</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn:hover { background: #0056b3; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
        h1 { color: #333; text-align: center; }
        h2 { color: #667eea; border-bottom: 2px solid #667eea; padding-bottom: 5px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🐟 Tilapia Farm Database Setup</h1>";

// Check if setup is requested
if (isset($_GET['setup']) && $_GET['setup'] === 'run') {
    echo "<h2>Setting up Database...</h2>";
    
    try {
        // Test basic MySQL connection (without database)
        $testDsn = "mysql:host=localhost;charset=utf8mb4";
        $testConn = new PDO($testDsn, "root", "");
        $testConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "<div class='success'>✅ MySQL connection successful!</div>";
        
        // Create database if it doesn't exist
        $createDbQuery = "CREATE DATABASE IF NOT EXISTS `ponglert_tilapia_farm` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        $testConn->exec($createDbQuery);
        
        echo "<div class='success'>✅ Database 'ponglert_tilapia_farm' created/verified!</div>";
        
        // Now connect to the specific database
        $database = new Database();
        $conn = $database->getConnection();
        
        echo "<div class='success'>✅ Connected to ponglert_tilapia_farm database!</div>";
        
        // Read and execute SQL file
        $sqlFile = __DIR__ . '/assets/db/ponglert_tilapia_farm.sql';
        
        if (file_exists($sqlFile)) {
            $sql = file_get_contents($sqlFile);
            
            // Remove comments and split by semicolon
            $sql = preg_replace('/--.*$/m', '', $sql);
            $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
            
            $statements = array_filter(array_map('trim', explode(';', $sql)));
            
            $successCount = 0;
            $errorCount = 0;
            
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    try {
                        $conn->exec($statement);
                        $successCount++;
                    } catch (PDOException $e) {
                        // Skip errors for things like "table already exists"
                        if (strpos($e->getMessage(), 'already exists') === false) {
                            echo "<div class='error'>⚠️ SQL Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                            $errorCount++;
                        }
                    }
                }
            }
            
            echo "<div class='success'>✅ Database setup completed! Executed $successCount SQL statements.</div>";
            
            if ($errorCount > 0) {
                echo "<div class='info'>ℹ️ $errorCount statements had errors (likely tables already exist).</div>";
            }
            
            // Test if user table exists and has data
            try {
                $testQuery = $conn->query("SELECT COUNT(*) as count FROM user_tb");
                $result = $testQuery->fetch();
                echo "<div class='info'>ℹ️ User table has {$result['count']} users.</div>";
                
                if ($result['count'] == 0) {
                    echo "<div class='info'>💡 <strong>Note:</strong> No users found. You may need to create a user account first.</div>";
                }
            } catch (PDOException $e) {
                echo "<div class='error'>❌ Could not verify user table: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
            
        } else {
            echo "<div class='error'>❌ SQL file not found at: " . htmlspecialchars($sqlFile) . "</div>";
        }
        
        echo "<h2>🎉 Setup Complete!</h2>";
        echo "<div class='success'>Your database is now ready! You can now use the login system.</div>";
        echo "<p><a href='login.php' class='btn'>Go to Login Page</a></p>";
        
    } catch (PDOException $e) {
        echo "<div class='error'>❌ Database connection failed: " . htmlspecialchars($e->getMessage()) . "</div>";
        echo "<h2>🔧 Troubleshooting Steps:</h2>";
        echo "<div class='info'>
            <ol>
                <li><strong>Make sure XAMPP is running:</strong>
                    <ul>
                        <li>Open XAMPP Control Panel</li>
                        <li>Start Apache and MySQL services</li>
                        <li>Both should show green 'Running' status</li>
                    </ul>
                </li>
                <li><strong>Check MySQL configuration:</strong>
                    <ul>
                        <li>Default XAMPP MySQL username: <code>root</code></li>
                        <li>Default XAMPP MySQL password: <code>(empty)</code></li>
                    </ul>
                </li>
                <li><strong>Alternative MySQL credentials:</strong>
                    <p>If your MySQL has a different setup, edit <code>model/config.php</code>:</p>
                    <pre>private \$username = \"your_mysql_username\";
private \$password = \"your_mysql_password\";</pre>
                </li>
            </ol>
        </div>";
    }
    
} else {
    // Show setup instructions
    echo "<h2>📋 Database Setup Instructions</h2>";
    
    echo "<div class='info'>
        <p><strong>Before running setup, make sure:</strong></p>
        <ol>
            <li>XAMPP is installed and running</li>
            <li>Apache and MySQL services are started in XAMPP Control Panel</li>
            <li>MySQL is accessible on localhost:3306</li>
        </ol>
    </div>";
    
    echo "<h2>🚀 Current Configuration</h2>";
    echo "<div class='info'>
        <ul>
            <li><strong>Host:</strong> localhost</li>
            <li><strong>Database:</strong> ponglert_tilapia_farm</li>
            <li><strong>Username:</strong> root</li>
            <li><strong>Password:</strong> (empty)</li>
        </ul>
    </div>";
    
    // Test connection
    echo "<h2>🔍 Connection Test</h2>";
    try {
        $testDsn = "mysql:host=localhost;charset=utf8mb4";
        $testConn = new PDO($testDsn, "root", "");
        echo "<div class='success'>✅ MySQL connection test successful!</div>";
        echo "<p><a href='?setup=run' class='btn'>🚀 Run Database Setup</a></p>";
    } catch (PDOException $e) {
        echo "<div class='error'>❌ MySQL connection test failed: " . htmlspecialchars($e->getMessage()) . "</div>";
        
        echo "<h2>🔧 Fix Connection Issues</h2>";
        echo "<div class='info'>
            <h3>Common Solutions:</h3>
            <ol>
                <li><strong>Start XAMPP Services:</strong>
                    <ul>
                        <li>Open XAMPP Control Panel</li>
                        <li>Click 'Start' for Apache</li>
                        <li>Click 'Start' for MySQL</li>
                        <li>Wait for both to show 'Running' status</li>
                    </ul>
                </li>
                
                <li><strong>Check MySQL Port:</strong>
                    <ul>
                        <li>Default MySQL port is 3306</li>
                        <li>Make sure no other programs are using this port</li>
                    </ul>
                </li>
                
                <li><strong>Reset MySQL Password:</strong>
                    <ul>
                        <li>Open phpMyAdmin in XAMPP</li>
                        <li>Go to User accounts</li>
                        <li>Edit 'root' user</li>
                        <li>Set password to empty or update config.php</li>
                    </ul>
                </li>
                
                <li><strong>Alternative: Use Different Credentials</strong>
                    <p>If you have different MySQL credentials, edit <code>model/config.php</code>:</p>
                    <pre>private \$username = \"your_username\";
private \$password = \"your_password\";</pre>
                </li>
            </ol>
        </div>";
    }
}

echo "
        <h2>📖 Manual Setup (Alternative)</h2>
        <div class='info'>
            <p>If automatic setup doesn't work, you can manually import the database:</p>
            <ol>
                <li>Open phpMyAdmin (usually at <a href='http://localhost/phpmyadmin' target='_blank'>http://localhost/phpmyadmin</a>)</li>
                <li>Create a new database named: <code>ponglert_tilapia_farm</code></li>
                <li>Import the SQL file: <code>assets/db/ponglert_tilapia_farm.sql</code></li>
                <li>Go to <a href='login.php'>login.php</a> to test the system</li>
            </ol>
        </div>
        
        <h2>📞 Need Help?</h2>
        <div class='info'>
            <p>If you're still having issues:</p>
            <ul>
                <li>Check XAMPP error logs</li>
                <li>Verify MySQL service is running</li>
                <li>Try accessing phpMyAdmin directly</li>
                <li>Check Windows firewall settings</li>
            </ul>
        </div>
    </div>
</body>
</html>";
?>
