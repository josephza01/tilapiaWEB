<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once "../model/UserModel.php";

$userModel = new UserModel();
$user_id = $_SESSION['user_id'];
$user = $userModel->getUserById($user_id);

$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name_user = trim($_POST['name_user'] ?? '');
    $email_user = trim($_POST['email_user'] ?? '');
    $tel_user = trim($_POST['tel_user'] ?? '');
    $name_farm = trim($_POST['name_farm'] ?? '');
    $alert_level_user = $_POST['alert_level_user'] ?? '1';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    
    if (empty($name_user) || empty($email_user)) {
        $error_message = 'Name and email are required.';
    } elseif (!filter_var($email_user, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } elseif ($userModel->emailExists($email_user, $user_id)) {
        $error_message = 'Email address is already in use.';
    } else {
        try {
            // Prepare update data
            $updateData = [
                'id_user' => $user_id,
                'name_user' => $name_user,
                'email_user' => $email_user,
                'tel_user' => $tel_user,
                'name_farm' => $name_farm,
                'alert_level_user' => $alert_level_user
            ];
            
            // Handle password change separately
            if (!empty($new_password)) {
                if (empty($current_password)) {
                    $error_message = 'Current password is required to change password.';
                } elseif (!$userModel->verifyPassword($user_id, $current_password)) {
                    $error_message = 'Current password is incorrect.';
                } elseif ($new_password !== $confirm_password) {
                    $error_message = 'New passwords do not match.';
                } elseif (strlen($new_password) < 6) {
                    $error_message = 'New password must be at least 6 characters long.';
                } else {
                    // Update password separately
                    $passwordResult = $userModel->updateUserPass($user_id, $new_password);
                    if ($passwordResult > 0) {
                        $success_message .= 'Password updated successfully! ';
                    }
                }
            }
            
            if (empty($error_message)) {
                // Update profile (without password)
                $result = $userModel->updateUserProfile($updateData);
                
                // Update alert level separately
                $alertResult = $userModel->updateUserAlertLevel([
                    'id_user' => $user_id,
                    'alert_level_user' => $alert_level_user
                ]);
                
                if ($result >= 0 || $alertResult >= 0) { // >= 0 because even 0 rows affected can be valid (no changes)
                    $success_message = 'Profile updated successfully!' . $success_message;
                    
                    // Update session data
                    $_SESSION['user_name'] = $name_user;
                    $_SESSION['user_email'] = $email_user;
                    
                    // Refresh user data
                    $user = $userModel->getUserById($user_id);
                } else {
                    if (empty($success_message)) {
                        $error_message = 'No changes were made or update failed.';
                    }
                }
            }
        } catch (Exception $e) {
            $error_message = 'Server error occurred. Please try again later.';
            error_log("Profile update error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Management - Tilapia Farm</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .header h1 {
            color: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: #666;
        }

        .profile-form {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .section-title {
            color: #333;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #667eea;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-input, .form-select {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            text-align: center;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .password-section {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1rem;
        }

        .form-help {
            font-size: 0.8rem;
            color: #666;
            margin-top: 0.25rem;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <i class="fas fa-user-cog"></i>
                Profile Management
            </h1>
            <p>Update your personal information and account settings</p>
        </div>

        <div class="profile-form">
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="profileForm">
                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-user"></i>
                        Personal Information
                    </h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name_user" class="form-label">Full Name *</label>
                            <input 
                                type="text" 
                                id="name_user" 
                                name="name_user"
                                class="form-input" 
                                value="<?php echo htmlspecialchars($user['name_user'] ?? ''); ?>"
                                required
                                maxlength="100"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="email_user" class="form-label">Email Address *</label>
                            <input 
                                type="email" 
                                id="email_user" 
                                name="email_user"
                                class="form-input" 
                                value="<?php echo htmlspecialchars($user['email_user'] ?? ''); ?>"
                                required
                            >
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="tel_user" class="form-label">Phone Number</label>
                            <input 
                                type="tel" 
                                id="tel_user" 
                                name="tel_user"
                                class="form-input" 
                                value="<?php echo htmlspecialchars($user['tel_user'] ?? ''); ?>"
                                maxlength="20"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="name_farm" class="form-label">Farm Name</label>
                            <input 
                                type="text" 
                                id="name_farm" 
                                name="name_farm"
                                class="form-input" 
                                value="<?php echo htmlspecialchars($user['name_farm'] ?? ''); ?>"
                                maxlength="100"
                            >
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="alert_level_user" class="form-label">Alert Level</label>
                        <select id="alert_level_user" name="alert_level_user" class="form-select">
                            <option value="0" <?php echo ($user['alert_level_user'] ?? '1') == '0' ? 'selected' : ''; ?>>No Alerts</option>
                            <option value="1" <?php echo ($user['alert_level_user'] ?? '1') == '1' ? 'selected' : ''; ?>>Critical Only</option>
                            <option value="2" <?php echo ($user['alert_level_user'] ?? '1') == '2' ? 'selected' : ''; ?>>All Alerts</option>
                        </select>
                        <div class="form-help">Choose how many alerts you want to receive</div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">
                        <i class="fas fa-lock"></i>
                        Change Password
                    </h3>
                    
                    <div class="password-section">
                        <div class="form-group">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input 
                                type="password" 
                                id="current_password" 
                                name="current_password"
                                class="form-input" 
                                placeholder="Enter current password to change"
                            >
                            <div class="form-help">Required only if you want to change your password</div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="new_password" class="form-label">New Password</label>
                                <input 
                                    type="password" 
                                    id="new_password" 
                                    name="new_password"
                                    class="form-input" 
                                    placeholder="Enter new password"
                                    minlength="6"
                                >
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input 
                                    type="password" 
                                    id="confirm_password" 
                                    name="confirm_password"
                                    class="form-input" 
                                    placeholder="Confirm new password"
                                    minlength="6"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="../dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Form validation
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const currentPassword = document.getElementById('current_password').value;
            
            // If trying to change password
            if (newPassword || confirmPassword) {
                if (!currentPassword) {
                    e.preventDefault();
                    alert('Please enter your current password to change password.');
                    return;
                }
                
                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    alert('New passwords do not match.');
                    return;
                }
                
                if (newPassword.length < 6) {
                    e.preventDefault();
                    alert('New password must be at least 6 characters long.');
                    return;
                }
            }
        });

        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword && confirmPassword && newPassword !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });

        // Show/hide password section based on current password input
        document.getElementById('current_password').addEventListener('input', function() {
            const passwordSection = document.querySelector('.password-section');
            if (this.value) {
                passwordSection.style.background = '#e3f2fd';
                passwordSection.style.border = '2px solid #667eea';
            } else {
                passwordSection.style.background = '#f8f9fa';
                passwordSection.style.border = 'none';
            }
        });
    </script>
</body>
</html>
