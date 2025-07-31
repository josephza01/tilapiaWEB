<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Please log in first";
    exit;
}

require_once 'model/UserModel.php';

$userModel = new UserModel();
$user_id = $_SESSION['user_id'];

echo "<h2>Password Debug Information</h2>";

// Get debug info
$debug = $userModel->debugPassword($user_id);

echo "<pre>";
print_r($debug);
echo "</pre>";

// Test password verification with common passwords
$test_passwords = ['123456', 'password', 'admin', '123', 'test'];

echo "<h3>Test Password Verification:</h3>";
echo "<form method='post'>";
echo "Enter test password: <input type='password' name='test_password'>";
echo "<input type='submit' value='Test'>";
echo "</form>";

if (isset($_POST['test_password'])) {
    $test_password = $_POST['test_password'];
    $result = $userModel->verifyPassword($user_id, $test_password);
    echo "<p>Password '<strong>" . htmlspecialchars($test_password) . "</strong>' result: " . ($result ? "MATCH" : "NO MATCH") . "</p>";
}

echo "<h3>Your current user info:</h3>";
$user = $userModel->getUserById($user_id);
echo "<pre>";
echo "Name: " . $user['name_user'] . "\n";
echo "Email: " . $user['email_user'] . "\n";
echo "User ID: " . $user['id_user'] . "\n";
echo "</pre>";
?>
