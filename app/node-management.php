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
$nodes = $nodeModel->getNodesWithLatestData($user_id);

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'toggle_pump':
            $node_id = $_POST['node_id'] ?? '';
            $pump_status = $_POST['pump_status'] ?? '0';
            
            if ($node_id) {
                $result = $nodeModel->updateNodePump($node_id, $pump_status);
                echo json_encode(['success' => $result > 0, 'message' => $result > 0 ? 'Pump updated successfully' : 'Failed to update pump']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid node ID']);
            }
            exit;
            
        case 'delete_node':
            $node_id = $_POST['node_id'] ?? '';
            
            if ($node_id) {
                $result = $nodeModel->deleteNode($node_id);
                echo json_encode(['success' => $result > 0, 'message' => $result > 0 ? 'Node deleted successfully' : 'Failed to delete node']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid node ID']);
            }
            exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Node Management - Tilapia Farm</title>
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

        .nodes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }

        .node-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .node-card:hover {
            transform: translateY(-5px);
        }

        .node-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .node-title {
            color: #333;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .node-status {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-online {
            background: #d4edda;
            color: #155724;
        }

        .status-warning {
            background: #fff3cd;
            color: #856404;
        }

        .status-offline {
            background: #f8d7da;
            color: #721c24;
        }

        .node-info {
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

        .node-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }

        .pump-control {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 1rem 0;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .pump-toggle {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
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
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
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
            transform: translateX(26px);
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

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .nodes-grid {
                grid-template-columns: 1fr;
            }

            .node-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <i class="fas fa-microchip"></i>
                Node Management
            </h1>
            <div>
                <a href="../dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <button class="btn btn-primary" onclick="showAddNodeModal()">
                    <i class="fas fa-plus"></i> Add New Node
                </button>
            </div>
        </div>

        <div id="alertContainer"></div>

        <?php if (empty($nodes)): ?>
            <div class="no-nodes">
                <i class="fas fa-microchip"></i>
                <h2>No Nodes Found</h2>
                <p>You haven't added any sensor nodes yet. Click "Add New Node" to get started.</p>
                <button class="btn btn-primary" onclick="showAddNodeModal()" style="margin-top: 1rem;">
                    <i class="fas fa-plus"></i> Add Your First Node
                </button>
            </div>
        <?php else: ?>
            <div class="nodes-grid">
                <?php foreach ($nodes as $node): ?>
                    <div class="node-card" data-node-id="<?php echo $node['id_node']; ?>">
                        <div class="node-header">
                            <h3 class="node-title"><?php echo htmlspecialchars($node['name_node'] ?? 'Node ' . $node['code_node']); ?></h3>
                            <span class="node-status status-<?php echo $node['status']; ?>">
                                <?php echo ucfirst($node['status']); ?>
                            </span>
                        </div>

                        <div class="node-info">
                            <div class="info-row">
                                <span class="info-label">Code:</span>
                                <span class="info-value"><?php echo htmlspecialchars($node['code_node']); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Location:</span>
                                <span class="info-value"><?php echo htmlspecialchars($node['location_node'] ?? 'Not set'); ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Temperature:</span>
                                <span class="info-value"><?php echo $node['temp_node']; ?>°C</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Water Temp:</span>
                                <span class="info-value"><?php echo $node['tempw_node']; ?>°C</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">pH Level:</span>
                                <span class="info-value"><?php echo $node['ph_node']; ?></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Dissolved O₂:</span>
                                <span class="info-value"><?php echo $node['do_node']; ?> mg/L</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Last Update:</span>
                                <span class="info-value"><?php echo date('M j, Y H:i', strtotime($node['laston_node'])); ?></span>
                            </div>
                        </div>

                        <div class="pump-control">
                            <span style="font-weight: 500;">Water Pump:</span>
                            <label class="pump-toggle">
                                <input type="checkbox" <?php echo $node['pump_node'] ? 'checked' : ''; ?> 
                                       onchange="togglePump(<?php echo $node['id_node']; ?>, this.checked)">
                                <span class="slider"></span>
                            </label>
                            <span class="pump-status"><?php echo $node['pump_node'] ? 'ON' : 'OFF'; ?></span>
                        </div>

                        <div class="node-actions">
                            <button class="btn btn-primary" onclick="viewNodeLogs(<?php echo $node['id_node']; ?>)">
                                <i class="fas fa-chart-line"></i> View Logs
                            </button>
                            <button class="btn btn-secondary" onclick="editNode(<?php echo $node['id_node']; ?>)">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-danger" onclick="deleteNode(<?php echo $node['id_node']; ?>)">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Show alert function
        function showAlert(message, type = 'success') {
            const alertContainer = document.getElementById('alertContainer');
            const alert = document.createElement('div');
            alert.className = `alert alert-${type}`;
            alert.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${message}`;
            
            alertContainer.appendChild(alert);
            
            setTimeout(() => {
                alert.remove();
            }, 5000);
        }

        // Toggle pump function
        async function togglePump(nodeId, isOn) {
            try {
                const formData = new FormData();
                formData.append('action', 'toggle_pump');
                formData.append('node_id', nodeId);
                formData.append('pump_status', isOn ? '1' : '0');

                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                
                if (result.success) {
                    showAlert(result.message, 'success');
                    // Update pump status text
                    const pumpStatus = document.querySelector(`[data-node-id="${nodeId}"] .pump-status`);
                    if (pumpStatus) {
                        pumpStatus.textContent = isOn ? 'ON' : 'OFF';
                    }
                } else {
                    showAlert(result.message, 'danger');
                    // Revert toggle if failed
                    const toggle = document.querySelector(`[data-node-id="${nodeId}"] input[type="checkbox"]`);
                    if (toggle) {
                        toggle.checked = !isOn;
                    }
                }
            } catch (error) {
                showAlert('Error updating pump status', 'danger');
                console.error('Error:', error);
            }
        }

        // Delete node function
        async function deleteNode(nodeId) {
            if (!confirm('Are you sure you want to delete this node? This action cannot be undone.')) {
                return;
            }

            try {
                const formData = new FormData();
                formData.append('action', 'delete_node');
                formData.append('node_id', nodeId);

                const response = await fetch('', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                
                if (result.success) {
                    showAlert(result.message, 'success');
                    // Remove the node card from DOM
                    const nodeCard = document.querySelector(`[data-node-id="${nodeId}"]`);
                    if (nodeCard) {
                        nodeCard.style.transform = 'scale(0)';
                        setTimeout(() => {
                            nodeCard.remove();
                            // Check if no nodes left
                            if (document.querySelectorAll('.node-card').length === 0) {
                                location.reload();
                            }
                        }, 300);
                    }
                } else {
                    showAlert(result.message, 'danger');
                }
            } catch (error) {
                showAlert('Error deleting node', 'danger');
                console.error('Error:', error);
            }
        }

        // View node logs function
        function viewNodeLogs(nodeId) {
            window.open(`node-logs.php?id=${nodeId}`, '_blank');
        }

        // Edit node function
        function editNode(nodeId) {
            alert('Edit node feature - redirect to edit form');
            // You can implement this to show a modal or redirect to edit page
        }

        // Show add node modal
        function showAddNodeModal() {
            window.location.href = 'add-node.php';
        }

        // Auto-refresh every 30 seconds
        setInterval(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>
