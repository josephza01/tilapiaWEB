<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once "../model/NodeModel.php";
require_once "../model/UserModel.php";

$nodeModel = new NodeModel();
$userModel = new UserModel();
$user_id = $_SESSION['user_id'];
$user = $userModel->getUserById($user_id);
$nodes = $nodeModel->getNodesWithLatestData($user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real-Time Dashboard - Tilapia Farm</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            padding: 1rem;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
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

        .status-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .status-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        .status-live {
            background: #28a745;
        }

        .status-warning {
            background: #ffc107;
        }

        .status-offline {
            background: #dc3545;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.7; }
            100% { transform: scale(1); opacity: 1; }
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

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: #667eea;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .nodes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .node-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .node-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #28a745, #ffc107, #dc3545);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .node-card.updating::before {
            opacity: 1;
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .node-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .node-title {
            color: #333;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .node-status {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .health-score {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .health-bar {
            flex: 1;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }

        .health-fill {
            height: 100%;
            transition: width 0.5s ease, background-color 0.5s ease;
        }

        .health-excellent { background: #28a745; }
        .health-good { background: #ffc107; }
        .health-poor { background: #dc3545; }

        .sensor-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .sensor-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 6px;
            transition: background-color 0.3s ease;
        }

        .sensor-item.alert {
            background: #fff5f5;
            border-left: 3px solid #dc3545;
        }

        .sensor-label {
            font-size: 0.85rem;
            color: #666;
        }

        .sensor-value {
            font-weight: bold;
            color: #333;
        }

        .pump-control {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .pump-toggle {
            position: relative;
            width: 50px;
            height: 24px;
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
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
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

        .alerts {
            margin-top: 1rem;
        }

        .alert-item {
            padding: 0.5rem;
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
            color: #856404;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .chart-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .chart-title {
            color: #333;
            margin-bottom: 1rem;
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

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .last-update {
            font-size: 0.8rem;
            color: #666;
            text-align: center;
            margin-top: 1rem;
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

            .sensor-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <i class="fas fa-tachometer-alt"></i>
                Real-Time Dashboard
            </h1>
            <div>
                <div class="status-indicator">
                    <div class="status-dot status-live" id="connectionStatus"></div>
                    <span id="connectionText">Live</span>
                    <span style="margin-left: 1rem;">Last Update: <span id="lastUpdate">--:--</span></span>
                </div>
                <a href="../dashboard.php" class="btn btn-secondary" style="margin-left: 1rem;">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-microchip"></i>
                </div>
                <div class="stat-value" id="totalNodes"><?php echo count($nodes); ?></div>
                <div class="stat-label">Total Nodes</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-wifi" style="color: #28a745;"></i>
                </div>
                <div class="stat-value" id="onlineNodes">0</div>
                <div class="stat-label">Online Nodes</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle" style="color: #ffc107;"></i>
                </div>
                <div class="stat-value" id="alertCount">0</div>
                <div class="stat-label">Active Alerts</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-thermometer-half" style="color: #17a2b8;"></i>
                </div>
                <div class="stat-value" id="avgTemp">--</div>
                <div class="stat-label">Avg Temperature</div>
            </div>
        </div>

        <div class="nodes-grid" id="nodesContainer">
            <!-- Nodes will be populated by JavaScript -->
        </div>

        <div class="chart-container">
            <h3 class="chart-title">
                <i class="fas fa-chart-line"></i>
                Real-Time Temperature Trends
            </h3>
            <canvas id="temperatureChart" width="400" height="200"></canvas>
        </div>

        <div class="last-update" id="systemStatus">
            System Status: Initializing...
        </div>
    </div>

    <script>
        // Real-time data connection
        let eventSource;
        let temperatureChart;
        let chartData = {
            labels: [],
            datasets: []
        };

        // Initialize the dashboard
        function initDashboard() {
            setupEventSource();
            setupChart();
            setInterval(updateChartData, 5000); // Update chart every 5 seconds
        }

        // Setup Server-Sent Events
        function setupEventSource() {
            eventSource = new EventSource('realtime-data.php');
            
            eventSource.onmessage = function(event) {
                try {
                    const data = JSON.parse(event.data);
                    if (data.error) {
                        updateConnectionStatus('error', data.error);
                        return;
                    }
                    
                    updateDashboard(data);
                    updateConnectionStatus('connected');
                } catch (error) {
                    console.error('Error parsing data:', error);
                    updateConnectionStatus('error', 'Data parsing error');
                }
            };
            
            eventSource.onerror = function(event) {
                console.error('EventSource failed:', event);
                updateConnectionStatus('error', 'Connection lost');
                
                // Retry connection after 5 seconds
                setTimeout(() => {
                    eventSource.close();
                    setupEventSource();
                }, 5000);
            };
        }

        // Update connection status
        function updateConnectionStatus(status, message = '') {
            const statusDot = document.getElementById('connectionStatus');
            const statusText = document.getElementById('connectionText');
            
            statusDot.className = 'status-dot';
            
            switch (status) {
                case 'connected':
                    statusDot.classList.add('status-live');
                    statusText.textContent = 'Live';
                    break;
                case 'warning':
                    statusDot.classList.add('status-warning');
                    statusText.textContent = 'Warning';
                    break;
                case 'error':
                    statusDot.classList.add('status-offline');
                    statusText.textContent = message || 'Offline';
                    break;
            }
            
            document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString();
        }

        // Update dashboard with new data
        function updateDashboard(data) {
            // Update stats
            document.getElementById('totalNodes').textContent = data.total_nodes;
            document.getElementById('onlineNodes').textContent = data.online_nodes;
            document.getElementById('alertCount').textContent = data.alert_count;
            
            // Calculate average temperature
            if (data.nodes.length > 0) {
                const avgTemp = data.nodes.reduce((sum, node) => sum + parseFloat(node.temp_node), 0) / data.nodes.length;
                document.getElementById('avgTemp').textContent = avgTemp.toFixed(1) + '°C';
            }
            
            // Update nodes
            updateNodesDisplay(data.nodes);
            
            // Update system status
            document.getElementById('systemStatus').textContent = 
                `Last updated: ${new Date(data.timestamp * 1000).toLocaleString()} | ${data.total_nodes} nodes monitored`;
        }

        // Update nodes display
        function updateNodesDisplay(nodes) {
            const container = document.getElementById('nodesContainer');
            
            nodes.forEach(node => {
                let nodeCard = document.querySelector(`[data-node-id="${node.id_node}"]`);
                
                if (!nodeCard) {
                    nodeCard = createNodeCard(node);
                    container.appendChild(nodeCard);
                } else {
                    updateNodeCard(nodeCard, node);
                }
            });
        }

        // Create node card
        function createNodeCard(node) {
            const card = document.createElement('div');
            card.className = 'node-card';
            card.setAttribute('data-node-id', node.id_node);
            
            card.innerHTML = `
                <div class="node-header">
                    <h3 class="node-title">${node.name_node || 'Node ' + node.code_node}</h3>
                    <span class="node-status status-${node.status}">${node.status.charAt(0).toUpperCase() + node.status.slice(1)}</span>
                </div>
                
                <div class="health-score">
                    <span style="font-size: 0.9rem; font-weight: 500;">Health:</span>
                    <div class="health-bar">
                        <div class="health-fill" style="width: ${node.health_score}%"></div>
                    </div>
                    <span style="font-size: 0.9rem; font-weight: bold;">${node.health_score}%</span>
                </div>
                
                <div class="sensor-grid">
                    <div class="sensor-item" data-sensor="temp">
                        <span class="sensor-label">Air Temp</span>
                        <span class="sensor-value">${node.temp_node}°C</span>
                    </div>
                    <div class="sensor-item" data-sensor="tempw">
                        <span class="sensor-label">Water Temp</span>
                        <span class="sensor-value">${node.tempw_node}°C</span>
                    </div>
                    <div class="sensor-item" data-sensor="ph">
                        <span class="sensor-label">pH Level</span>
                        <span class="sensor-value">${node.ph_node}</span>
                    </div>
                    <div class="sensor-item" data-sensor="do">
                        <span class="sensor-label">Dissolved O₂</span>
                        <span class="sensor-value">${node.do_node} mg/L</span>
                    </div>
                </div>
                
                <div class="pump-control">
                    <span>Pump:</span>
                    <label class="pump-toggle">
                        <input type="checkbox" ${node.pump_node ? 'checked' : ''} 
                               onchange="togglePump(${node.id_node}, this.checked)">
                        <span class="slider"></span>
                    </label>
                    <span class="pump-status">${node.pump_node ? 'ON' : 'OFF'}</span>
                </div>
                
                <div class="alerts" id="alerts-${node.id_node}">
                    <!-- Alerts will be populated here -->
                </div>
            `;
            
            return card;
        }

        // Update existing node card
        function updateNodeCard(card, node) {
            // Add updating animation
            card.classList.add('updating');
            setTimeout(() => card.classList.remove('updating'), 1000);
            
            // Update health score
            const healthFill = card.querySelector('.health-fill');
            const healthText = card.querySelector('.health-score span:last-child');
            healthFill.style.width = node.health_score + '%';
            healthText.textContent = node.health_score + '%';
            
            // Update health color
            healthFill.className = 'health-fill';
            if (node.health_score >= 80) healthFill.classList.add('health-excellent');
            else if (node.health_score >= 60) healthFill.classList.add('health-good');
            else healthFill.classList.add('health-poor');
            
            // Update sensor values
            card.querySelector('[data-sensor="temp"] .sensor-value').textContent = node.temp_node + '°C';
            card.querySelector('[data-sensor="tempw"] .sensor-value').textContent = node.tempw_node + '°C';
            card.querySelector('[data-sensor="ph"] .sensor-value').textContent = node.ph_node;
            card.querySelector('[data-sensor="do"] .sensor-value').textContent = node.do_node + ' mg/L';
            
            // Update sensor alerts
            const sensorItems = card.querySelectorAll('.sensor-item');
            sensorItems.forEach(item => item.classList.remove('alert'));
            
            if (node.temp_node < 20 || node.temp_node > 35) {
                card.querySelector('[data-sensor="temp"]').classList.add('alert');
            }
            if (node.tempw_node < 24 || node.tempw_node > 30) {
                card.querySelector('[data-sensor="tempw"]').classList.add('alert');
            }
            if (node.ph_node < 6.5 || node.ph_node > 8.5) {
                card.querySelector('[data-sensor="ph"]').classList.add('alert');
            }
            if (node.do_node < 5) {
                card.querySelector('[data-sensor="do"]').classList.add('alert');
            }
            
            // Update pump status
            const pumpCheckbox = card.querySelector('input[type="checkbox"]');
            const pumpStatus = card.querySelector('.pump-status');
            pumpCheckbox.checked = node.pump_node;
            pumpStatus.textContent = node.pump_node ? 'ON' : 'OFF';
            
            // Update alerts
            const alertsContainer = card.querySelector(`#alerts-${node.id_node}`);
            alertsContainer.innerHTML = '';
            
            if (node.alerts && node.alerts.length > 0) {
                node.alerts.forEach(alert => {
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert-item';
                    alertDiv.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${alert}`;
                    alertsContainer.appendChild(alertDiv);
                });
            }
        }

        // Setup Chart.js
        function setupChart() {
            const ctx = document.getElementById('temperatureChart').getContext('2d');
            
            temperatureChart = new Chart(ctx, {
                type: 'line',
                data: chartData,
                options: {
                    responsive: true,
                    animation: {
                        duration: 1000
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            title: {
                                display: true,
                                text: 'Temperature (°C)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Time'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                }
            });
        }

        // Update chart data
        function updateChartData() {
            const now = new Date().toLocaleTimeString();
            
            // Keep only last 20 data points
            if (chartData.labels.length >= 20) {
                chartData.labels.shift();
                chartData.datasets.forEach(dataset => {
                    dataset.data.shift();
                });
            }
            
            chartData.labels.push(now);
            
            // Update chart
            temperatureChart.update('none');
        }

        // Toggle pump function
        async function togglePump(nodeId, isOn) {
            try {
                const formData = new FormData();
                formData.append('action', 'toggle_pump');
                formData.append('node_id', nodeId);
                formData.append('pump_status', isOn ? '1' : '0');

                const response = await fetch('node-management.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                
                if (!result.success) {
                    // Revert toggle if failed
                    const toggle = document.querySelector(`[data-node-id="${nodeId}"] input[type="checkbox"]`);
                    if (toggle) {
                        toggle.checked = !isOn;
                    }
                    alert('Failed to update pump: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error updating pump status');
            }
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', initDashboard);

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (eventSource) {
                eventSource.close();
            }
        });
    </script>
</body>
</html>
