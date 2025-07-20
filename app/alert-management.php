<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once "../model/UserModel.php";
require_once "../model/NodeModel.php";

$userModel = new UserModel();
$nodeModel = new NodeModel();
$user_id = $_SESSION['user_id'];
$user = $userModel->getUserById($user_id);
$nodes = $nodeModel->getAllNode($user_id);

$success_message = '';
$error_message = '';

// Handle alert settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alert_level = $_POST['alert_level_user'] ?? '1';
    
    try {
        $updateData = [
            'id_user' => $user_id,
            'name_user' => $user['name_user'],
            'email_user' => $user['email_user'],
            'tel_user' => $user['tel_user'],
            'name_farm' => $user['name_farm'],
            'alert_level_user' => $alert_level
        ];
        
        $result = $userModel->updateUser($updateData);
        
        if ($result > 0) {
            $success_message = 'Alert settings updated successfully!';
            $user['alert_level_user'] = $alert_level;
        } else {
            $error_message = 'No changes were made.';
        }
    } catch (Exception $e) {
        $error_message = 'Server error occurred.';
        error_log("Alert update error: " . $e->getMessage());
    }
}

// Check for current alerts
$alerts = [];
foreach ($nodes as $node) {
    $status = 'normal';
    $messages = [];
    
    // Check temperature alerts
    if ($node['temp_node'] > 35 || $node['temp_node'] < 20) {
        $messages[] = "Air temperature: {$node['temp_node']}°C (optimal: 20-35°C)";
        $status = 'warning';
    }
    
    // Check water temperature
    if ($node['tempw_node'] > 30 || $node['tempw_node'] < 24) {
        $messages[] = "Water temperature: {$node['tempw_node']}°C (optimal: 24-30°C)";
        $status = 'warning';
    }
    
    // Check pH levels
    if ($node['ph_node'] > 8.5 || $node['ph_node'] < 6.5) {
        $messages[] = "pH level: {$node['ph_node']} (optimal: 6.5-8.5)";
        $status = ($node['ph_node'] > 9 || $node['ph_node'] < 6) ? 'critical' : 'warning';
    }
    
    // Check dissolved oxygen
    if ($node['do_node'] < 5) {
        $messages[] = "Low dissolved oxygen: {$node['do_node']} mg/L (minimum: 5 mg/L)";
        $status = $node['do_node'] < 3 ? 'critical' : 'warning';
    }
    
    // Check last update time
    $lastUpdate = strtotime($node['laston_node']);
    $timeDiff = time() - $lastUpdate;
    if ($timeDiff > 3600) { // More than 1 hour
        $messages[] = "No data received for " . round($timeDiff / 3600, 1) . " hours";
        $status = $timeDiff > 86400 ? 'critical' : 'warning'; // More than 24 hours = critical
    }
    
    if (!empty($messages)) {
        $alerts[] = [
            'node' => $node,
            'status' => $status,
            'messages' => $messages
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alert Management - Tilapia Farm</title>
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
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-settings {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .alert-settings h3 {
            color: #333;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .settings-form {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .form-select {
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            background: white;
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
            background: #6c757d;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .alerts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 2rem;
        }

        .alert-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #ccc;
        }

        .alert-card.warning {
            border-left-color: #ffc107;
        }

        .alert-card.critical {
            border-left-color: #dc3545;
        }

        .alert-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .alert-title {
            color: #333;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .alert-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-critical {
            background: #f8d7da;
            color: #721c24;
        }

        .alert-messages {
            list-style: none;
            margin: 1rem 0;
        }

        .alert-messages li {
            padding: 0.5rem 0;
            color: #666;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-messages li i {
            color: #dc3545;
        }

        .alert-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .no-alerts {
            text-align: center;
            padding: 4rem;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .no-alerts i {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 1rem;
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

        .alert-summary {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }

        .summary-item {
            text-align: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .summary-number {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .summary-label {
            color: #666;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .settings-form {
                justify-content: center;
                flex-direction: column;
            }

            .alerts-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <i class="fas fa-bell"></i>
                Alert Management
            </h1>
            <a href="../dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
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
        <?php endif; ?>

        <div class="alert-settings">
            <h3>
                <i class="fas fa-cog"></i>
                Alert Settings
            </h3>
            <form method="POST" class="settings-form">
                <label for="alert_level_user" style="font-weight: 500; color: #333;">Alert Level:</label>
                <select id="alert_level_user" name="alert_level_user" class="form-select">
                    <option value="0" <?php echo ($user['alert_level_user'] ?? '1') == '0' ? 'selected' : ''; ?>>
                        No Alerts - Disable all notifications
                    </option>
                    <option value="1" <?php echo ($user['alert_level_user'] ?? '1') == '1' ? 'selected' : ''; ?>>
                        Critical Only - Only severe issues
                    </option>
                    <option value="2" <?php echo ($user['alert_level_user'] ?? '1') == '2' ? 'selected' : ''; ?>>
                        All Alerts - Warning and critical
                    </option>
                </select>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Settings
                </button>
            </form>
        </div>

        <div class="alert-summary">
            <h3 style="color: #333; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-chart-pie"></i>
                Alert Summary
            </h3>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-number" style="color: #28a745;"><?php echo count($nodes); ?></div>
                    <div class="summary-label">Total Nodes</div>
                </div>
                <div class="summary-item">
                    <div class="summary-number" style="color: #ffc107;"><?php echo count(array_filter($alerts, fn($a) => $a['status'] === 'warning')); ?></div>
                    <div class="summary-label">Warnings</div>
                </div>
                <div class="summary-item">
                    <div class="summary-number" style="color: #dc3545;"><?php echo count(array_filter($alerts, fn($a) => $a['status'] === 'critical')); ?></div>
                    <div class="summary-label">Critical</div>
                </div>
                <div class="summary-item">
                    <div class="summary-number" style="color: #28a745;"><?php echo count($nodes) - count($alerts); ?></div>
                    <div class="summary-label">Normal</div>
                </div>
            </div>
        </div>

        <?php if (empty($alerts)): ?>
            <div class="no-alerts">
                <i class="fas fa-check-circle"></i>
                <h2>All Systems Normal</h2>
                <p>No alerts detected. All your sensor nodes are operating within normal parameters.</p>
                <a href="node-management.php" class="btn btn-primary" style="margin-top: 1rem;">
                    <i class="fas fa-eye"></i> View Nodes
                </a>
            </div>
        <?php else: ?>
            <div class="alerts-grid">
                <?php foreach ($alerts as $alert): ?>
                    <div class="alert-card <?php echo $alert['status']; ?>">
                        <div class="alert-header">
                            <h3 class="alert-title">
                                <?php echo htmlspecialchars($alert['node']['name_node'] ?? 'Node ' . $alert['node']['code_node']); ?>
                            </h3>
                            <span class="alert-badge badge-<?php echo $alert['status']; ?>">
                                <?php echo strtoupper($alert['status']); ?>
                            </span>
                        </div>

                        <div style="color: #666; font-size: 0.9rem; margin-bottom: 1rem;">
                            <strong>Location:</strong> <?php echo htmlspecialchars($alert['node']['location_node'] ?? 'Not specified'); ?>
                        </div>

                        <ul class="alert-messages">
                            <?php foreach ($alert['messages'] as $message): ?>
                                <li>
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <?php echo htmlspecialchars($message); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <div class="alert-actions">
                            <a href="node-logs.php?id=<?php echo $alert['node']['id_node']; ?>" class="btn btn-primary" target="_blank">
                                <i class="fas fa-chart-line"></i> View Logs
                            </a>
                            <?php if ($alert['node']['pump_node'] == 0 && in_array($alert['status'], ['critical', 'warning'])): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="update_pump">
                                    <input type="hidden" name="node_id" value="<?php echo $alert['node']['id_node']; ?>">
                                    <input type="hidden" name="pump_status" value="1">
                                    <button type="submit" class="btn btn-secondary" onclick="return confirm('Turn on pump for this node?')">
                                        <i class="fas fa-tint"></i> Start Pump
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Auto-refresh every 60 seconds
        setInterval(() => {
            location.reload();
        }, 60000);

        // Show notification if there are critical alerts
        <?php if (count(array_filter($alerts, fn($a) => $a['status'] === 'critical')) > 0): ?>
        if ('Notification' in window) {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    new Notification('Tilapia Farm Alert', {
                        body: 'You have critical alerts that need attention!',
                        icon: 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="red"><path d="M12 2L1 21h22L12 2zm0 3l8.5 15h-17L12 5zm0 7v3h1v-3h-1zm0 4v2h1v-2h-1z"/></svg>'
                    });
                }
            });
        }
        <?php endif; ?>
    </script>
</body>
</html>
