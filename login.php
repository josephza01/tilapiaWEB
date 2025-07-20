<?php
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once "model/UserModel.php";
    
    $email_user = trim($_POST['email_user'] ?? '');
    $pass_user = trim($_POST['pass_user'] ?? '');
    
    if (empty($email_user) || empty($pass_user)) {
        $error_message = 'Please fill in all fields.';
    } elseif (!filter_var($email_user, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        try {
            $userModel = new UserModel();
            $user = $userModel->LoginUser($email_user, $pass_user);
            
            if ($user) {
                // Login successful
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['user_name'] = $user['name_user'];
                $_SESSION['user_email'] = $user['email_user'];
                $_SESSION['user_data'] = $user;
                
                $success_message = 'Login successful! Redirecting...';
                
                // Redirect after a short delay
                header("refresh:2;url=dashboard.php");
            } else {
                $error_message = 'Invalid email or password. Please try again.';
            }
        } catch (Exception $e) {
            $error_message = 'Server error occurred. Please try again later.';
            error_log("Login error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1.0">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title>Login - Tilapia Farm Management</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Animated background elements */
        .background-animation {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .fish-icon {
            position: absolute;
            color: rgba(255, 255, 255, 0.1);
            font-size: 2rem;
            animation: float 6s ease-in-out infinite;
        }

        .fish-icon:nth-child(1) { top: 20%; left: 10%; animation-delay: 0s; }
        .fish-icon:nth-child(2) { top: 60%; left: 80%; animation-delay: 2s; }
        .fish-icon:nth-child(3) { top: 80%; left: 20%; animation-delay: 4s; }
        .fish-icon:nth-child(4) { top: 30%; left: 70%; animation-delay: 1s; }
        .fish-icon:nth-child(5) { top: 50%; left: 5%; animation-delay: 3s; }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            width: 400px;
            max-width: 90vw;
            position: relative;
            z-index: 1;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .login-title {
            color: #333;
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            color: #666;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-input {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 2.2rem;
            color: #999;
            font-size: 1.1rem;
        }

        .password-toggle {
            position: absolute;
            right: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            cursor: pointer;
            font-size: 1.1rem;
            transition: color 0.3s ease, background 0.3s ease;
            padding: 0.5rem;
            border-radius: 50%;
            min-width: 44px;
            min-height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        .password-toggle:hover {
            color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }

        .password-toggle:active {
            background: rgba(102, 126, 234, 0.2);
        }

        .login-button {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
            min-height: 50px;
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.3);
        }

        .login-button:active {
            transform: translateY(0);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
        }

        .login-button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
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

        .forgot-password {
            text-align: center;
            margin-top: 1.5rem;
        }

        .forgot-password a {
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .forgot-password a:hover {
            color: #764ba2;
        }

        .footer {
            text-align: center;
            margin-top: 2rem;
            color: #999;
            font-size: 0.8rem;
        }

        .loading {
            opacity: 0.7;
            pointer-events: none;
        }

        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 0.5rem;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive design for tablets */
        @media (max-width: 1024px) {
            body {
                padding: 1rem;
            }
            
            .login-container {
                width: 90%;
                max-width: 500px;
            }
        }

        /* Responsive design for mobile phones */
        @media (max-width: 768px) {
            body {
                padding: 0.5rem;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                overflow-x: hidden;
            }
            
            .login-container {
                width: 95%;
                max-width: none;
                padding: 2rem 1.5rem;
                margin: 1rem auto;
                border-radius: 15px;
            }
            
            .login-title {
                font-size: 1.6rem;
            }
            
            .login-subtitle {
                font-size: 0.85rem;
            }
            
            .logo {
                width: 70px;
                height: 70px;
                font-size: 1.8rem;
            }
            
            .form-input {
                padding: 0.9rem 0.9rem 0.9rem 2.8rem;
                font-size: 0.95rem;
            }
            
            .input-icon {
                left: 0.9rem;
                top: 2.1rem;
                font-size: 1rem;
            }
            
            .password-toggle {
                right: 0.4rem;
                top: 50%;
                transform: translateY(-50%);
                font-size: 1rem;
                min-width: 40px;
                min-height: 40px;
            }
            
            .login-button {
                padding: 0.9rem;
                font-size: 1rem;
            }
        }

        /* Responsive design for small phones */
        @media (max-width: 480px) {
            body {
                padding: 0.25rem;
            }
            
            .login-container {
                width: 98%;
                padding: 1.5rem 1rem;
                margin: 0.5rem auto;
                border-radius: 12px;
            }
            
            .login-title {
                font-size: 1.4rem;
            }
            
            .login-subtitle {
                font-size: 0.8rem;
            }
            
            .logo {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
                margin-bottom: 0.8rem;
            }
            
            .form-group {
                margin-bottom: 1.2rem;
            }
            
            .form-input {
                padding: 0.8rem 0.8rem 0.8rem 2.5rem;
                font-size: 0.9rem;
                border-radius: 10px;
            }
            
            .input-icon {
                left: 0.8rem;
                top: 2rem;
                font-size: 0.9rem;
            }
            
            .password-toggle {
                right: 0.3rem;
                top: 50%;
                transform: translateY(-50%);
                font-size: 0.9rem;
                min-width: 36px;
                min-height: 36px;
            }
            
            .login-button {
                padding: 0.8rem;
                font-size: 0.95rem;
                border-radius: 10px;
            }
            
            .forgot-password {
                margin-top: 1rem;
            }
            
            .forgot-password a {
                font-size: 0.85rem;
            }
            
            .footer {
                margin-top: 1.5rem;
            }
            
            .footer p {
                font-size: 0.75rem;
            }
            
            .alert {
                padding: 0.8rem;
                font-size: 0.85rem;
                margin-bottom: 0.8rem;
            }
        }

        /* Responsive design for very small screens */
        @media (max-width: 360px) {
            .login-container {
                width: 100%;
                margin: 0;
                border-radius: 0;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }
            
            .login-title {
                font-size: 1.3rem;
            }
            
            .form-input {
                padding: 0.75rem 0.75rem 0.75rem 2.3rem;
                font-size: 0.85rem;
            }
            
            .input-icon {
                left: 0.75rem;
                top: 1.95rem;
                font-size: 0.85rem;
            }
            
            .password-toggle {
                right: 0.25rem;
                top: 50%;
                transform: translateY(-50%);
                font-size: 0.85rem;
                min-width: 32px;
                min-height: 32px;
            }
        }

        /* Landscape orientation for mobile devices */
        @media (max-width: 768px) and (orientation: landscape) {
            body {
                padding: 0.5rem;
            }
            
            .login-container {
                width: 80%;
                max-width: 600px;
                padding: 1.5rem;
                margin: 1rem auto;
            }
            
            .login-header {
                margin-bottom: 1.5rem;
            }
            
            .logo {
                width: 60px;
                height: 60px;
                margin-bottom: 0.8rem;
            }
            
            .form-group {
                margin-bottom: 1rem;
            }
        }

        /* High DPI displays */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .login-container {
                box-shadow: 0 25px 45px rgba(0, 0, 0, 0.15);
            }
            
            .logo {
                box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
            }
        }

        /* Success animation */
        .success-redirect {
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="background-animation">
        <i class="fas fa-fish fish-icon"></i>
        <i class="fas fa-fish fish-icon"></i>
        <i class="fas fa-fish fish-icon"></i>
        <i class="fas fa-fish fish-icon"></i>
        <i class="fas fa-fish fish-icon"></i>
    </div>

    <div class="login-container" id="loginContainer">
        <div class="login-header">
            <div class="logo">
                <i class="fas fa-fish"></i>
            </div>
            <h1 class="login-title">Tilapia Farm</h1>
            <p class="login-subtitle">Management System</p>
        </div>

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
            <script>
                document.getElementById('loginContainer').classList.add('success-redirect');
            </script>
        <?php endif; ?>

        <form method="POST" id="loginForm" <?php echo !empty($success_message) ? 'style="display:none;"' : ''; ?>>
            <div class="form-group">
                <label for="email_user" class="form-label">Email Address</label>
                <div class="input-wrapper">
                   
                    <input 
                        type="email" 
                        id="email_user" 
                        name="email_user"
                        class="form-input" 
                        placeholder="Enter your email address"
                        value="<?php echo htmlspecialchars($_POST['email_user'] ?? ''); ?>"
                        required
                        autocomplete="email"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="pass_user" class="form-label">Password</label>
                <div class="input-wrapper">
                
                    <input 
                        type="password" 
                        id="pass_user" 
                        name="pass_user"
                        class="form-input" 
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    >
                    <i class="fas fa-eye password-toggle" id="passwordToggle"></i>
                </div>
            </div>

            <button type="submit" class="login-button" id="loginButton">
                <span id="buttonText">Sign In</span>
            </button>
        </form>

        <div class="forgot-password">
            <a href="#" onclick="showForgotPassword()">Forgot your password?</a>
        </div>

        <div class="footer">
            <p>&copy; 2025 Tilapia Farm Management System</p>
        </div>
    </div>

    <script>
        // Password visibility toggle
        document.getElementById('passwordToggle').addEventListener('click', function() {
            const passwordInput = document.getElementById('pass_user');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle icon
            if (type === 'text') {
                this.classList.remove('fa-eye');
                this.classList.add('fa-eye-slash');
            } else {
                this.classList.remove('fa-eye-slash');
                this.classList.add('fa-eye');
            }
        });

        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const button = document.getElementById('loginButton');
            const buttonText = document.getElementById('buttonText');
            
            // Add loading state
            button.disabled = true;
            button.classList.add('loading');
            buttonText.innerHTML = '<span class="spinner"></span>Signing In...';
        });

        // Auto-focus on email input
        window.addEventListener('load', function() {
            const emailInput = document.getElementById('email_user');
            if (emailInput && !emailInput.value) {
                emailInput.focus();
            }
        });

        // Forgot password function
        function showForgotPassword() {
            alert('Please contact your administrator to reset your password.');
        }

        // Add smooth animations to form inputs
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });

        // Enter key support
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    document.getElementById('loginForm').submit();
                }
            });
        });

        <?php if (!empty($success_message)): ?>
        // Auto-hide success message after redirect
        setTimeout(function() {
            window.location.href = 'dashboard.php';
        }, 2000);
        <?php endif; ?>
    </script>
</body>
</html>
