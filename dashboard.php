<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user data from session
$user_data = $_SESSION['user_data'] ?? [];
$user_name = $_SESSION['user_name'] ?? 'User';
$user_email = $_SESSION['user_email'] ?? '';

// You can add more data loading here using your models
// For example, getting node count, user count, etc.
try {
    require_once "model/UserModel.php";
    require_once "model/NodeModel.php";
    
    $userModel = new UserModel();
    $nodeModel = new NodeModel();
    
    // Get real statistics from database
    $totalUsers = $userModel->getTotalUsers();
    $totalNodes = $nodeModel->getTotalNodes();
    $activeNodes = $nodeModel->getActiveNodes();
    $activeFarms = 1; // You can implement this based on unique farm names
    
    // Get user's nodes for quick overview
    $userNodes = $nodeModel->getNodesWithLatestData($_SESSION['user_id']);
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $totalUsers = 0;
    $totalNodes = 0;
    $activeNodes = 0;
    $activeFarms = 0;
    $userNodes = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Tilapia Farm Management</title>
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
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            color: #667eea;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .logo i {
            margin-right: 0.5rem;
            font-size: 2rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .welcome-text {
            color: #333;
            font-weight: 500;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .logout-btn {
            background: #dc2626;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logout-btn:hover {
            background: #b91c1c;
            transform: translateY(-2px);
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .card-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-right: 1rem;
        }

        .card-title {
            color: #333;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .card-content {
            color: #666;
            line-height: 1.6;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-secondary {
            background: #f1f5f9;
            color: #334155;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .quick-actions {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .quick-actions h3 {
            color: #333;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .quick-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 1rem;
                flex-direction: column;
                gap: 1rem;
            }

            .container {
                padding: 0 1rem;
            }

            .action-buttons,
            .quick-buttons {
                flex-direction: column;
            }

            .user-info {
                flex-direction: column;
                text-align: center;
            }
        }

        .welcome-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .welcome-title {
            color: #333;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .welcome-subtitle {
            color: #666;
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <i class="fas fa-fish"></i>
            Tilapia Farm Management
        </div>
        <div class="user-info">
            <div class="user-avatar">
                <?php echo strtoupper(substr($user_name, 0, 1)); ?>
            </div>
            <div>
                <div class="welcome-text">Welcome, <?php echo htmlspecialchars($user_name); ?>!</div>
                <div style="font-size: 0.8rem; color: #666;"><?php echo htmlspecialchars($user_email); ?></div>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-section">
            <h2 class="welcome-title">
                <i class="fas fa-tachometer-alt" style="color: #667eea; margin-right: 0.5rem;"></i>
                Dashboard Overview
            </h2>
            <p class="welcome-subtitle">Monitor and manage your tilapia farm operations from this central dashboard.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $activeNodes; ?></div>
                <div class="stat-label">
                    <i class="fas fa-microchip"></i> Active Nodes
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalNodes; ?></div>
                <div class="stat-label">
                    <i class="fas fa-wifi"></i> Total Nodes
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalUsers; ?></div>
                <div class="stat-label">
                    <i class="fas fa-users"></i> Total Users
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #10b981;">Online</div>
                <div class="stat-label">
                    <i class="fas fa-heartbeat"></i> System Status
                </div>
            </div>
        </div>

        <div class="quick-actions">
            <h3>
                <i class="fas fa-bolt"></i>
                Quick Actions
            </h3>
            <div class="quick-buttons">
                <a href="app/realtime-dashboard.php" class="btn btn-primary">
                    <i class="fas fa-broadcast-tower"></i> Real-Time Data
                </a>
                <a href="app/node-management.php" class="btn btn-secondary">
                    <i class="fas fa-list"></i> View Nodes
                </a>
                <a href="app/profile-management.php" class="btn btn-secondary">
                    <i class="fas fa-user-edit"></i> Edit Profile
                </a>
                <a href="#" class="btn btn-secondary" onclick="viewAnalytics()">
                    <i class="fas fa-chart-line"></i> View Reports
                </a>
                <a href="app/alert-management.php" class="btn btn-secondary">
                    <i class="fas fa-bell"></i> Manage Alerts
                </a>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-microchip"></i>
                    </div>
                    <h3 class="card-title">Node Management</h3>
                </div>
                <div class="card-content">
                    <p>Monitor and manage your IoT sensor nodes. View real-time data from water quality sensors, temperature monitors, and feeding systems.</p>
                    <div class="action-buttons">
                        <a href="app/node-management.php" class="btn btn-primary">
                            <i class="fas fa-eye"></i> View Nodes
                        </a>
                        <a href="app/add-node.php" class="btn btn-secondary">
                            <i class="fas fa-plus"></i> Add Node
                        </a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="card-title">User Management</h3>
                </div>
                <div class="card-content">
                    <p>Manage farm users, permissions, and access controls. Add new users or update existing user information and credentials.</p>
                    <div class="action-buttons">
                        <a href="app/profile-management.php" class="btn btn-primary">
                            <i class="fas fa-eye"></i> View Profile
                        </a>
                        <a href="app/user-management.php" class="btn btn-secondary">
                            <i class="fas fa-edit"></i> Manage Users
                        </a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <h3 class="card-title">System Control</h3>
                </div>
                <div class="card-content">
                    <p>Control farm equipment including water pumps, feeding systems, and automated monitoring devices.</p>
                    <div class="action-buttons">
                        <a href="app/pump-control.php" class="btn btn-primary">
                            <i class="fas fa-tint"></i> Pump Control
                        </a>
                        <a href="app/alert-management.php" class="btn btn-secondary">
                            <i class="fas fa-bell"></i> Alert Settings
                        </a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="card-title">Analytics & Reports</h3>
                </div>
                <div class="card-content">
                    <p>View detailed analytics of your farm operations, water quality trends, feeding schedules, and fish growth patterns.</p>
                    <div class="action-buttons">
                        <a href="#" class="btn btn-primary" onclick="viewAnalytics()">
                            <i class="fas fa-chart-bar"></i> View Reports
                        </a>
                        <a href="#" class="btn btn-secondary" onclick="exportData()">
                            <i class="fas fa-download"></i> Export Data
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Show add node modal/page
        function showAddNode() {
            alert('Add node feature - you can implement this to redirect to a form or show a modal.');
        }

        // View analytics
        function viewAnalytics() {
            alert('Analytics feature - you can implement this to show charts and graphs.');
        }

        // Export data
        function exportData() {
            if (confirm('Export farm data to CSV?')) {
                alert('Data export feature - you can implement CSV/Excel export here.');
            }
        }

        // View logs
        function viewLogs() {
            alert('System logs feature - you can implement this to show activity logs.');
        }

        // Add smooth animations
        document.addEventListener('DOMContentLoaded', function() {
            // Animate stats on load
            const statNumbers = document.querySelectorAll('.stat-number');
            statNumbers.forEach(stat => {
                const finalValue = stat.textContent;
                if (!isNaN(finalValue) && finalValue !== '') {
                    let current = 0;
                    const increment = Math.ceil(finalValue / 20);
                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= finalValue) {
                            current = finalValue;
                            clearInterval(timer);
                        }
                        stat.textContent = current;
                    }, 50);
                }
            });

            // Add hover effects to cards
            const cards = document.querySelectorAll('.card, .stat-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });

        // Confirm logout
        document.querySelector('.logout-btn').addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to logout?')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
