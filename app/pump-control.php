<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once "../model/NodeModel.php";

$nodeModel = new NodeModel();
$user_id = $_SESSION['user_id'];
$nodes = $nodeModel->getAllNode($user_id);

$success_message = '';
$error_message = '';

// Handle pump control
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_pump') {
        $node_id = $_POST['node_id'] ?? '';
        $pump_status = $_POST['pump_status'] ?? '0';
        
        if ($node_id) {
            try {
                $result = $nodeModel->updateNodePump($node_id, $pump_status);
                if ($result > 0) {
                    $success_message = 'Pump status updated successfully!';
                    // Refresh nodes data
                    $nodes = $nodeModel->getAllNode($user_id);
                } else {
                    $error_message = 'Failed to update pump status.';
                }
            } catch (Exception $e) {
                $error_message = 'Server error occurred.';
                error_log("Pump update error: " . $e->getMessage());
            }
        }
    } elseif ($action === 'update_all_pumps') {
        $pump_status = $_POST['all_pump_status'] ?? '0';
        
        try {
            $updated = 0;
            foreach ($nodes as $node) {
                $result = $nodeModel->updateNodePump($node['id_node'], $pump_status);
                if ($result > 0) $updated++;
            }
            
            if ($updated > 0) {
                $success_message = "Updated $updated pump(s) successfully!";
                // Refresh nodes data
                $nodes = $nodeModel->getAllNode($user_id);
            } else {
                $error_message = 'No pumps were updated.';
            }
        } catch (Exception $e) {
            $error_message = 'Server error occurred.';
            error_log("Bulk pump update error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pump Control - Tilapia Farm</title>
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

        .bulk-controls {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .bulk-controls h3 {
            color: #333;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .bulk-buttons {
            display: flex;
            gap: 1rem;
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
            background: #6c757d;
            color: white;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .pumps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }

        .pump-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .pump-card:hover {
            transform: translateY(-5px);
        }

        .pump-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .pump-title {
            color: #333;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .pump-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .status-on {
            background: #d4edda;
            color: #155724;
        }

        .status-off {
            background: #f8d7da;
            color: #721c24;
        }

        .pump-info {
            margin: 1rem 0;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 0.5rem 0;
            padding: 0.25rem 0;
            border-bottom: 1px solid #eee;
        }

        .info-label {
            color: #666;
            font-weight: 500;
        }

        .info-value {
            color: #333;
            font-weight: 600;
        }

        .pump-controls {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .pump-toggle {
            position: relative;
            display: inline-block;
            width: 80px;
            height: 40px;
        }

        .pump-toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 40px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 32px;
            width: 32px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #28a745;
        }

        input:checked + .slider:before {
            transform: translateX(40px);
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

        .no-nodes {
            text-align: center;
            padding: 4rem;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .no-nodes i {
            font-size: 4rem;
            color: #ccc;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .bulk-buttons {
                justify-content: center;
            }

            .pumps-grid {
                grid-template-columns: 1fr;
            }

            .pump-controls {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <i class="fas fa-tint"></i>
                Pump Control Center
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

        <?php if (!empty($nodes)): ?>
            <div class="bulk-controls">
                <h3>
                    <i class="fas fa-list-check"></i>
                    Bulk Control
                </h3>
                <div class="bulk-buttons">
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="update_all_pumps">
                        <input type="hidden" name="all_pump_status" value="1">
                        <button type="submit" class="btn btn-success" onclick="return confirm('Turn ON all pumps?')">
                            <i class="fas fa-power-off"></i> Turn All ON
                        </button>
                    </form>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="update_all_pumps">
                        <input type="hidden" name="all_pump_status" value="0">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('Turn OFF all pumps?')">
                            <i class="fas fa-power-off"></i> Turn All OFF
                        </button>
                    </form>
                </div>
            </div>

            <div class="pumps-grid">
                <?php foreach ($nodes as $node): ?>
                    <div class="pump-card">
                        <div class="pump-header">
                            <h3 class="pump-title"><?php echo htmlspecialchars($node['name_node'] ?? 'Node ' . $node['code_node']); ?></h3>
                            <span class="pump-status <?php echo $node['pump_node'] ? 'status-on' : 'status-off'; ?>">
                                <?php echo $node['pump_node'] ? 'ON' : 'OFF'; ?>
                            </span>
                        </div>

                        <div class="pump-info">
                            <div class="info-row">
                                <span class="info-label">Node Code:</span>
                                <span class="info-value"><?php echo htmlspecialchars($node['code_node']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Location:</span>
                                <span class="info-value"><?php echo htmlspecialchars($node['location_node'] ?? 'Not set'); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Water Temperature:</span>
                                <span class="info-value"><?php echo $node['tempw_node']; ?>°C</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">pH Level:</span>
                                <span class="info-value"><?php echo $node['ph_node']; ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Last Update:</span>
                                <span class="info-value"><?php echo date('M j, H:i', strtotime($node['laston_node'])); ?></span>
                            </div>
                        </div>

                        <div class="pump-controls">
                            <form method="POST" style="flex: 1;">
                                <input type="hidden" name="action" value="update_pump">
                                <input type="hidden" name="node_id" value="<?php echo $node['id_node']; ?>">
                                
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <span style="font-weight: 500;">Pump Control:</span>
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <label class="pump-toggle">
                                            <input type="checkbox" 
                                                   name="pump_status" 
                                                   value="1"
                                                   <?php echo $node['pump_node'] ? 'checked' : ''; ?>
                                                   onchange="this.form.submit()">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-nodes">
                <i class="fas fa-tint"></i>
                <h2>No Pumps Available</h2>
                <p>No sensor nodes found. Add nodes first to control their pumps.</p>
                <a href="add-node.php" class="btn btn-primary" style="margin-top: 1rem;">
                    <i class="fas fa-plus"></i> Add Your First Node
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Auto-refresh every 30 seconds
        setInterval(() => {
            location.reload();
        }, 30000);

        // Show loading state on form submission
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const button = this.querySelector('button[type="submit"]');
                if (button) {
                    button.disabled = true;
                    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
                }
            });
        });
    </script>
</body>
</html>
