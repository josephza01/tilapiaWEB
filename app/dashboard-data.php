<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    require_once "../model/NodeModel.php";
    
    $nodeModel = new NodeModel();
    $userId = $_SESSION['user_id'];
    
    // Get latest sensor data
    $latestData = $nodeModel->getLatestSensorData($userId);
    $systemHealth = $nodeModel->getSystemHealth($userId);
    
    // Get node statistics
    $totalNodes = $nodeModel->getTotalNodes();
    $activeNodes = $nodeModel->getActiveNodes();
    
    $response = [
        'success' => true,
        'timestamp' => time(),
        'data' => [
            'temperature' => $latestData['temperature'] ? floatval($latestData['temperature']) : null,
            'ph' => $latestData['ph'] ? floatval($latestData['ph']) : null,
            'dissolved_oxygen' => $latestData['dissolved_oxygen'] ? floatval($latestData['dissolved_oxygen']) : null,
            'turbidity' => $latestData['turbidity'] ? floatval($latestData['turbidity']) : null,
            'last_update' => $latestData['last_update'],
            'node_count' => intval($latestData['node_count']),
            'system_health' => $systemHealth,
            'stats' => [
                'total_nodes' => $totalNodes,
                'active_nodes' => $activeNodes
            ]
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error',
        'message' => $e->getMessage()
    ]);
}
?>
