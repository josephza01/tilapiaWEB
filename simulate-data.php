<?php
require_once "model/config.php";

// This script simulates real sensor data updates
// Run this in the background to see live data changes

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "🌊 Starting Tilapia Farm Data Simulator...\n";
    
    // Get all nodes
    $query = "SELECT id_node, code_node, name_node FROM node_farm";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $nodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($nodes)) {
        echo "❌ No nodes found. Please add some nodes first.\n";
        exit;
    }
    
    echo "📡 Found " . count($nodes) . " nodes to simulate.\n";
    
    $iteration = 0;
    
    while (true) {
        $iteration++;
        echo "\n🔄 Simulation cycle $iteration - " . date('Y-m-d H:i:s') . "\n";
        
        foreach ($nodes as $node) {
            // Generate realistic sensor data
            $data = generateSensorData($iteration);
            
            // Update node with new data
            $updateQuery = "UPDATE node_farm SET 
                temp_node = :temp_node,
                hum_node = :hum_node,
                tempw_node = :tempw_node,
                ph_node = :ph_node,
                do_node = :do_node,
                laston_node = NOW()
                WHERE id_node = :id_node";
            
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->execute([
                ':temp_node' => $data['temp'],
                ':hum_node' => $data['humidity'],
                ':tempw_node' => $data['water_temp'],
                ':ph_node' => $data['ph'],
                ':do_node' => $data['dissolved_oxygen'],
                ':id_node' => $node['id_node']
            ]);
            
            // Insert log entry
            $logQuery = "INSERT INTO nodelog_farm (
                id_node, temp_node, hum_node, tempw_node, 
                ph_node, do_node, pump_node, alert_node, 
                laston_node
            ) VALUES (
                :id_node, :temp_node, :hum_node, :tempw_node,
                :ph_node, :do_node, 0, 0, NOW()
            )";
            
            $logStmt = $conn->prepare($logQuery);
            $logStmt->execute([
                ':id_node' => $node['id_node'],
                ':temp_node' => $data['temp'],
                ':hum_node' => $data['humidity'],
                ':tempw_node' => $data['water_temp'],
                ':ph_node' => $data['ph'],
                ':do_node' => $data['dissolved_oxygen']
            ]);
            
            echo "📊 {$node['name_node']}: T={$data['temp']}°C, WT={$data['water_temp']}°C, pH={$data['ph']}, DO={$data['dissolved_oxygen']}mg/L\n";
        }
        
        echo "✅ Updated " . count($nodes) . " nodes\n";
        
        // Wait 10 seconds before next update
        sleep(10);
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

function generateSensorData($iteration) {
    // Base values for healthy tilapia farm
    $baseTemp = 28;           // Air temperature
    $baseWaterTemp = 27;      // Water temperature 
    $basePH = 7.2;           // pH level
    $baseDO = 6.5;           // Dissolved oxygen
    $baseHumidity = 75;      // Humidity
    
    // Add realistic variations
    $timeVariation = sin($iteration * 0.1) * 2; // Simulate daily temperature cycle
    $randomVariation = (rand(-100, 100) / 100) * 1.5; // Random variation ±1.5
    
    // Occasionally simulate problems
    $problemChance = rand(1, 100);
    $hasTemperatureProblem = $problemChance <= 5; // 5% chance
    $hasPHProblem = $problemChance <= 3; // 3% chance
    $hasDOProblem = $problemChance <= 4; // 4% chance
    
    // Calculate values
    $temp = $baseTemp + $timeVariation + $randomVariation;
    $waterTemp = $baseWaterTemp + ($timeVariation * 0.7) + $randomVariation;
    $ph = $basePH + (rand(-50, 50) / 100); // ±0.5 variation
    $do = $baseDO + (rand(-100, 100) / 100); // ±1.0 variation
    $humidity = $baseHumidity + (rand(-10, 10));
    
    // Apply problems occasionally
    if ($hasTemperatureProblem) {
        $temp += rand(5, 15); // Temperature spike
        $waterTemp += rand(3, 8);
    }
    
    if ($hasPHProblem) {
        $ph += rand(0, 1) ? rand(2, 3) : rand(-2, -1); // pH spike or drop
    }
    
    if ($hasDOProblem) {
        $do = rand(2, 4); // Low oxygen
    }
    
    return [
        'temp' => round($temp, 1),
        'water_temp' => round($waterTemp, 1),
        'ph' => round($ph, 1),
        'dissolved_oxygen' => round($do, 1),
        'humidity' => round($humidity, 1)
    ];
}
?>
