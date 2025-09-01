<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once "../model/NodeModel.php";
require_once "../model/UserModel.php";

try {
    $nodeModel = new NodeModel();
    $userModel = new UserModel();
    
    // Get user data
    $user_name = $_SESSION['user_name'] ?? 'User';
    $user_email = $_SESSION['user_email'] ?? '';
    $user_id = $_SESSION['user_id'];
    
    // Get user's nodes
    $userNodes = $nodeModel->getAllNode($user_id);
    
    // Get selected node or default to first node
    $selectedNodeId = $_GET['node_id'] ?? ($userNodes[0]['id_node'] ?? null);
    $selectedNode = null;
    
    if ($selectedNodeId) {
        $selectedNode = $nodeModel->getNodeById($selectedNodeId);
    }
    
    // Get recent logs for selected node (last 50 entries)
    $recentLogs = [];
    if ($selectedNodeId) {
        // Use the existing method or create a direct query
        $recentLogs = $nodeModel->getRecentLogs($selectedNodeId, 50);
    }
    
} catch (Exception $e) {
    error_log("Data logs error: " . $e->getMessage());
    $userNodes = [];
    $recentLogs = [];
    $selectedNode = null;
}

// Function to get status color based on value ranges
function getStatusClass($value, $type) {
    switch ($type) {
        case 'temperature':
            if ($value >= 26 && $value <= 30) return 'status-good';
            if ($value >= 24 && $value <= 32) return 'status-warning';
            return 'status-critical';
        case 'ph':
            if ($value >= 6.5 && $value <= 8.5) return 'status-good';
            if ($value >= 6.0 && $value <= 9.0) return 'status-warning';
            return 'status-critical';
        case 'do':
            if ($value >= 5.0 && $value <= 15.0) return 'status-good';
            if ($value >= 3.0 && $value <= 20.0) return 'status-warning';
            return 'status-critical';
        case 'humidity':
            if ($value >= 60 && $value <= 80) return 'status-good';
            if ($value >= 40 && $value <= 90) return 'status-warning';
            return 'status-critical';
        default:
            return 'status-good';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recent Data Logs - Tilapia Farm Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .back-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 10px 15px;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .header h1 {
            color: #333;
            font-size: 2rem;
        }

        .user-info {
            color: #666;
            font-size: 0.9rem;
        }

        .controls {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .node-selector {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .node-selector label {
            font-weight: 600;
            color: #333;
        }

        .node-selector select {
            padding: 10px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            background: white;
            min-width: 200px;
        }

        .refresh-btn {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .refresh-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .node-info {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .node-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .detail-item {
            text-align: center;
            padding: 15px;
            background: #f8fafc;
            border-radius: 10px;
        }

        .detail-label {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .detail-value {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
        }

        .logs-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .logs-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logs-table {
            width: 100%;
            border-collapse: collapse;
        }

        .logs-table th,
        .logs-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .logs-table th {
            background: #f8fafc;
            font-weight: 600;
            color: #333;
            position: sticky;
            top: 0;
        }

        .logs-table tr:hover {
            background: #f8fafc;
        }

        .status-good {
            color: #10b981;
            font-weight: 600;
        }

        .status-warning {
            color: #f59e0b;
            font-weight: 600;
        }

        .status-critical {
            color: #ef4444;
            font-weight: 600;
        }

        .no-data {
            text-align: center;
            padding: 50px;
            color: #666;
        }

        .no-data i {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #cbd5e1;
        }

        .table-container {
            max-height: 600px;
            overflow-y: auto;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .header {
                padding: 15px;
            }

            .header h1 {
                font-size: 1.5rem;
            }

            .node-selector {
                flex-direction: column;
                align-items: stretch;
            }

            .node-selector select {
                min-width: auto;
                width: 100%;
            }

            .logs-table {
                font-size: 0.9rem;
            }

            .logs-table th,
            .logs-table td {
                padding: 8px 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-left">
                <a href="../dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <div>
                    <h1><i class="fas fa-chart-line"></i> Recent Data Logs</h1>
                    <div class="user-info">Welcome, <?php echo htmlspecialchars($user_name); ?></div>
                </div>
            </div>
        </div>

        <div class="controls">
            <div class="node-selector">
                <label for="nodeSelect"><i class="fas fa-microchip"></i> Select Node:</label>
                <select id="nodeSelect" onchange="changeNode()">
                    <option value="">-- Select a Node --</option>
                    <?php foreach ($userNodes as $node): ?>
                        <option value="<?php echo $node['id_node']; ?>" 
                                <?php echo ($selectedNodeId == $node['id_node']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($node['code_node'] . ' - ' . $node['name_node']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button class="refresh-btn" onclick="refreshData()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>

        <?php if ($selectedNode): ?>
        <div class="node-info">
            <h3><i class="fas fa-info-circle"></i> Node Information</h3>
            <div class="node-details">
                <div class="detail-item">
                    <div class="detail-label">Node Code</div>
                    <div class="detail-value"><?php echo htmlspecialchars($selectedNode['code_node']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Node Name</div>
                    <div class="detail-value"><?php echo htmlspecialchars($selectedNode['name_node']); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Last Seen</div>
                    <div class="detail-value"><?php echo date('M j, Y H:i', strtotime($selectedNode['laston_node'])); ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="logs-container">
            <div class="logs-header">
                <h3><i class="fas fa-database"></i> Recent Data Logs</h3>
                <span><?php echo count($recentLogs); ?> records found</span>
            </div>

            <?php if (!empty($recentLogs)): ?>
            <div class="table-container">
                <table class="logs-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-clock"></i> Timestamp</th>
                            <th><i class="fas fa-thermometer-half"></i> Temperature (°C)</th>
                            <th><i class="fas fa-tint"></i> Humidity (%)</th>
                            <th><i class="fas fa-flask"></i> pH Level</th>
                            <th><i class="fas fa-wind"></i> Dissolved O2 (mg/L)</th>
                            <th><i class="fas fa-thermometer-quarter"></i> Water Temp (°C)</th>
                            <th><i class="fas fa-cog"></i> Pump Status</th>
                            <th><i class="fas fa-exclamation-triangle"></i> Alert</th>
                            <th><i class="fas fa-signal"></i> RSSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentLogs as $log): ?>
                        <tr>
                            <td><?php echo date('M j, Y H:i:s', strtotime($log['timeon_log'])); ?></td>
                            <td class="<?php echo getStatusClass($log['temp_nodelog'], 'temperature'); ?>">
                                <?php echo number_format($log['temp_nodelog'], 1); ?>°C
                            </td>
                            <td class="<?php echo getStatusClass($log['hum_nodelog'], 'humidity'); ?>">
                                <?php echo number_format($log['hum_nodelog'], 1); ?>%
                            </td>
                            <td class="<?php echo getStatusClass($log['ph_nodelog'], 'ph'); ?>">
                                <?php echo number_format($log['ph_nodelog'], 1); ?>
                            </td>
                            <td class="<?php echo getStatusClass($log['do_nodelog'], 'do'); ?>">
                                <?php echo number_format($log['do_nodelog'], 1); ?> mg/L
                            </td>
                            <td class="<?php echo getStatusClass($log['tempw_nodelog'], 'temperature'); ?>">
                                <?php echo number_format($log['tempw_nodelog'], 1); ?>°C
                            </td>
                            <td>
                                <?php if ($log['pump_nodelog'] == 1): ?>
                                    <span style="color: #10b981;"><i class="fas fa-check-circle"></i> ON</span>
                                <?php else: ?>
                                    <span style="color: #6b7280;"><i class="fas fa-times-circle"></i> OFF</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($log['alert_nodelog'] == 1): ?>
                                    <span style="color: #ef4444;"><i class="fas fa-exclamation-triangle"></i> Alert</span>
                                <?php else: ?>
                                    <span style="color: #10b981;"><i class="fas fa-check"></i> Normal</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="color: <?php echo $log['rssi_log'] > -70 ? '#10b981' : ($log['rssi_log'] > -85 ? '#f59e0b' : '#ef4444'); ?>">
                                    <?php echo $log['rssi_log']; ?> dBm
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="no-data">
                <i class="fas fa-database"></i>
                <h3>No Data Available</h3>
                <p>
                    <?php if (empty($userNodes)): ?>
                        No nodes found for your account. Please add nodes first.
                    <?php elseif (!$selectedNodeId): ?>
                        Please select a node to view its data logs.
                    <?php else: ?>
                        No data logs found for the selected node.
                    <?php endif; ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function changeNode() {
            const select = document.getElementById('nodeSelect');
            const nodeId = select.value;
            if (nodeId) {
                window.location.href = `data-logs.php?node_id=${nodeId}`;
            } else {
                window.location.href = 'data-logs.php';
            }
        }

        function refreshData() {
            window.location.reload();
        }

        // Auto-refresh every 30 seconds if a node is selected
        <?php if ($selectedNodeId): ?>
        setInterval(function() {
            // Only refresh if the page is visible
            if (!document.hidden) {
                refreshData();
            }
        }, 30000);
        <?php endif; ?>
    </script>
</body>
</html>
