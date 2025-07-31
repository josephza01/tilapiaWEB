<?php
session_start();
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "data: " . json_encode(['error' => 'Not authenticated']) . "\n\n";
    exit;
}

require_once "../model/NodeModel.php";

try {
    $nodeModel = new NodeModel();
    $user_id = $_SESSION['user_id'];
    $nodes = $nodeModel->getNodesWithLatestData($user_id);
    
    // Add real-time calculations and status
    foreach ($nodes as &$node) {
        // Calculate node health score
        $health_score = 100;
        
        // Temperature checks
        if ($node['temp_node'] < 20 || $node['temp_node'] > 35) {
            $health_score -= 20;
        }
        if ($node['tempw_node'] < 24 || $node['tempw_node'] > 30) {
            $health_score -= 20;
        }
        
        // pH checks
        if ($node['ph_node'] < 6.5 || $node['ph_node'] > 8.5) {
            $health_score -= 30;
        }
        
        // DO checks
        if ($node['do_node'] < 5) {
            $health_score -= 30;
        }
        
        // Last update check
        $last_update = strtotime($node['laston_node']);
        $time_diff = time() - $last_update;
        if ($time_diff > 300) { // 5 minutes
            $health_score -= 40;
        }
        
        $node['health_score'] = max(0, $health_score);
        $node['time_since_update'] = $time_diff;
        
        // Add alert status
        $node['alerts'] = [];
        if ($node['temp_node'] < 20 || $node['temp_node'] > 35) {
            $node['alerts'][] = "Air temperature out of range";
        }
        if ($node['tempw_node'] < 24 || $node['tempw_node'] > 30) {
            $node['alerts'][] = "Water temperature out of range";
        }
        if ($node['ph_node'] < 6.5 || $node['ph_node'] > 8.5) {
            $node['alerts'][] = "pH level critical";
        }
        if ($node['do_node'] < 5) {
            $node['alerts'][] = "Low dissolved oxygen";
        }
        if ($time_diff > 300) {
            $node['alerts'][] = "No recent data";
        }
    }
    
    $data = [
        'timestamp' => time(),
        'nodes' => $nodes,
        'total_nodes' => count($nodes),
        'online_nodes' => count(array_filter($nodes, fn($n) => $n['time_since_update'] < 300)),
        'alert_count' => array_sum(array_map(fn($n) => count($n['alerts']), $nodes))
    ];
    
    echo "data: " . json_encode($data) . "\n\n";
    
} catch (Exception $e) {
    echo "data: " . json_encode(['error' => $e->getMessage()]) . "\n\n";
}

// Flush output
if (ob_get_level()) {
    ob_end_flush();
}
flush();
?>
