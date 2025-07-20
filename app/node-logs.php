<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

require_once "../model/NodeModel.php";

$node_id = $_GET['id'] ?? '';
if (empty($node_id) || !is_numeric($node_id)) {
    header("Location: node-management.php");
    exit();
}

$nodeModel = new NodeModel();

// Get node information
$node = $nodeModel->getNodeById($node_id);
if (!$node) {
    header("Location: node-management.php");
    exit();
}

// Get recent logs (last 48 hours)
$logs = $nodeModel->getRecentLogs($node_id, 48);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Node Logs - <?php echo htmlspecialchars($node['name_node'] ?? $node['code_node']); ?></title>
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
        }

        .header h1 {
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: #666;
        }

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .chart-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .chart-title {
            color: #333;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logs-table {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        tr:hover {
            background: #f8f9fa;
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
            margin-bottom: 1rem;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .no-logs {
            text-align: center;
            padding: 4rem;
            color: #666;
        }

        .no-logs i {
            font-size: 4rem;
            color: #ccc;
            margin-bottom: 1rem;
        }

        .current-values {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .value-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .value-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 0.5rem;
        }

        .value-label {
            color: #666;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
            
            .current-values {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="node-management.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Nodes
        </a>

        <div class="header">
            <h1>
                <i class="fas fa-chart-line"></i>
                <?php echo htmlspecialchars($node['name_node'] ?? 'Node ' . $node['code_node']); ?> - Data Logs
            </h1>
            <p>Real-time monitoring data and historical trends</p>
        </div>

        <div class="current-values">
            <div class="value-card">
                <div class="value-number"><?php echo $node['temp_node']; ?>°C</div>
                <div class="value-label">
                    <i class="fas fa-thermometer-half"></i> Air Temperature
                </div>
            </div>
            <div class="value-card">
                <div class="value-number"><?php echo $node['tempw_node']; ?>°C</div>
                <div class="value-label">
                    <i class="fas fa-tint"></i> Water Temperature
                </div>
            </div>
            <div class="value-card">
                <div class="value-number"><?php echo $node['ph_node']; ?></div>
                <div class="value-label">
                    <i class="fas fa-flask"></i> pH Level
                </div>
            </div>
            <div class="value-card">
                <div class="value-number"><?php echo $node['do_node']; ?></div>
                <div class="value-label">
                    <i class="fas fa-wind"></i> Dissolved O₂
                </div>
            </div>
            <div class="value-card">
                <div class="value-number"><?php echo $node['hum_node']; ?>%</div>
                <div class="value-label">
                    <i class="fas fa-humidity"></i> Humidity
                </div>
            </div>
            <div class="value-card">
                <div class="value-number" style="color: <?php echo $node['pump_node'] ? '#28a745' : '#dc3545'; ?>">
                    <?php echo $node['pump_node'] ? 'ON' : 'OFF'; ?>
                </div>
                <div class="value-label">
                    <i class="fas fa-pump"></i> Water Pump
                </div>
            </div>
        </div>

        <?php if (!empty($logs)): ?>
            <div class="charts-grid">
                <div class="chart-card">
                    <h3 class="chart-title">
                        <i class="fas fa-thermometer-half"></i>
                        Temperature Trends
                    </h3>
                    <canvas id="temperatureChart"></canvas>
                </div>

                <div class="chart-card">
                    <h3 class="chart-title">
                        <i class="fas fa-flask"></i>
                        Water Quality
                    </h3>
                    <canvas id="waterQualityChart"></canvas>
                </div>
            </div>

            <div class="logs-table">
                <h3 class="chart-title" style="margin-bottom: 1rem;">
                    <i class="fas fa-table"></i>
                    Recent Data Logs
                </h3>
                <table>
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Air Temp</th>
                            <th>Water Temp</th>
                            <th>pH</th>
                            <th>DO</th>
                            <th>Humidity</th>
                            <th>Pump</th>
                            <th>Alert</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($logs, 0, 20) as $log): ?>
                            <tr>
                                <td><?php echo date('M j, H:i', strtotime($log['timeon_log'])); ?></td>
                                <td><?php echo $log['temp_nodelog']; ?>°C</td>
                                <td><?php echo $log['tempw_nodelog']; ?>°C</td>
                                <td><?php echo $log['ph_nodelog']; ?></td>
                                <td><?php echo $log['do_nodelog']; ?></td>
                                <td><?php echo $log['hum_nodelog']; ?>%</td>
                                <td style="color: <?php echo $log['pump_nodelog'] ? '#28a745' : '#dc3545'; ?>">
                                    <?php echo $log['pump_nodelog'] ? 'ON' : 'OFF'; ?>
                                </td>
                                <td style="color: <?php echo $log['alert_nodelog'] ? '#dc3545' : '#28a745'; ?>">
                                    <?php echo $log['alert_nodelog'] ? 'ALERT' : 'OK'; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="logs-table">
                <div class="no-logs">
                    <i class="fas fa-chart-line"></i>
                    <h2>No Data Available</h2>
                    <p>This node hasn't sent any data yet. Make sure the sensor is properly configured and connected.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($logs)): ?>
    <script>
        // Prepare data for charts
        const logs = <?php echo json_encode(array_reverse($logs)); ?>;
        const labels = logs.map(log => new Date(log.timeon_log).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }));

        // Temperature Chart
        const tempCtx = document.getElementById('temperatureChart').getContext('2d');
        new Chart(tempCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Air Temperature (°C)',
                    data: logs.map(log => parseFloat(log.temp_nodelog)),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Water Temperature (°C)',
                    data: logs.map(log => parseFloat(log.tempw_nodelog)),
                    borderColor: '#764ba2',
                    backgroundColor: 'rgba(118, 75, 162, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: false
                    }
                }
            }
        });

        // Water Quality Chart
        const waterCtx = document.getElementById('waterQualityChart').getContext('2d');
        new Chart(waterCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'pH Level',
                    data: logs.map(log => parseFloat(log.ph_nodelog)),
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Dissolved O₂ (mg/L)',
                    data: logs.map(log => parseFloat(log.do_nodelog)),
                    borderColor: '#17a2b8',
                    backgroundColor: 'rgba(23, 162, 184, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: false
                    }
                }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
