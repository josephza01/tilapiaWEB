<?php  
header("Content-Type: application/json");  
require_once "../model/NodeModel.php";  

// Optimized function to determine alert level  
function determineAlertLevel($do, $ph, $tempw) { 
    
    if ($do === -1 && $ph === -1 && $tempw === -1) return 4;  
    if ($do === -1 && $ph === -1) {  
        if($tempw < 15 || $tempw > 40){
            return 3;
        }
        if(($tempw >= 15 && $tempw <= 22) || ($tempw >= 33 && $tempw <= 40)){
            return 2;
        }
        if($tempw >= 23 && $tempw <= 32){
            return 1;
        }  
    }  
    if ($do === -1 && $tempw === -1){
        if($ph < 6 || $ph > 8.5){
            return 3;
        }
        if(($ph >= 6 && $ph <= 6.4) || ($ph >= 8.1 && $ph <= 8.5)){
            return 2;
        }
        if($ph >= 6.5 && $ph <= 8){
            return 1;
        }
    }
    if ($ph === -1 && $tempw === -1){
        if($do < 2.1){
            return 3;
        }
        if(($do >= 2.1 && $do <= 3.7)){
            return 2;
        }
        if($do >= 3.8){
            return 1;
        }
        
    }
    if ($do === -1) {  
        if(($ph < 6 || $ph > 8.5) || ($tempw < 15 || $tempw > 40)){
            return 3;
        }
        if(($ph >= 6 && $ph <= 6.4) || ($ph >= 8.1 && $ph <= 8.5) || ($tempw >= 15 && $tempw <= 22) || ($tempw >= 33 && $tempw <= 40)){
            return 2;
        }
        if(($ph >= 6.5 && $ph <= 8) && ($tempw >= 23 && $tempw <= 32)){
            return 1;
        }
        
    }  
    if ($ph === -1) {  
        if($do < 2.1 || ($tempw < 15 || $tempw > 40))
        {
            return 3;
        } 
        if(($do >= 2.1 && $do <= 3.7) || ($tempw >= 15 && $tempw <= 22) || ($tempw >= 33 && $tempw <= 40)){
            return 2;
        } 
        if($do >= 3.8 && ($tempw >= 23 && $tempw <= 32)){
            return 1;
        }
        
          
    }  
    if ($tempw === -1) {  
       if($do < 2.1 || ($ph < 6 || $ph > 8.5)){
            return 3;
       }
       if(($do >= 2.1 && $do <= 3.7) || (($ph >= 6 && $ph <= 6.4) || ($ph >= 8.1 && $ph <= 8.5))){
            return 2;
       }
       if($do >= 3.8 && ($ph >= 6.5 && $ph <= 8)){
            return 1;
       }
    }  

    if($do < 2.1 || ($ph < 6 || $ph > 8.5) || ($tempw < 15 || $tempw > 40)){
        return 3;
    }
    if(($do >= 2.1 && $do <= 3.7) || (($ph >= 6 && $ph <= 6.4) || ($ph >= 8.1 && $ph <= 8.5)) || 
    ($tempw >= 15 && $tempw <= 22) || ($tempw >= 33 && $tempw <= 40)){
        return 2;
    }
    if($do >= 3.8 && ($ph >= 6.5 && $ph <= 8) && ($tempw >= 23 && $tempw <= 32)){
        return 1;
    }

} 

// Ensure the request method is POST  
if ($_SERVER["REQUEST_METHOD"] !== "POST") {  
    echo json_encode(["sc" => "-3"]);  
    exit;  
}  

// Parse JSON input  
$data = json_decode(file_get_contents("php://input"), true);  
if (!$data) {  
    echo json_encode(["sc" => "-3"]);  
    exit;  
}  

if(isset($data["cn"]) && isset($data["do"]) && isset($data["dot"]) &&
isset($data["ph"]) && isset($data["ta"]) && isset($data["hm"]) && isset($data["rsi"])){
    // Extract and sanitize input data  
    $code_node = trim($data["cn"]);  
    $do_node = floatval($data["do"]);  
    $tempw_node = floatval($data["dot"]);  
    $ph_node = floatval($data["ph"]);  
    $temp_node = floatval($data["ta"]);  
    $hum_node = floatval($data["hm"]);  
    $rssi_log = intval($data["rsi"]);  

    date_default_timezone_set("Asia/Bangkok");  

    $nodeModel = new NodeModel();  
    $node = $nodeModel->getNodeByCode($code_node);  

    // Check if the node exists  
    if (!$node) {  
        echo json_encode(["sc" => "-2"]);  
        exit;  
    }  

    $id_node = $node['id_node'];  
    $pump_node = $node['pump_node'];  
    $laston_node = date("Y-m-d H:i:s");   

    // Calculate alert level  
    $alert_node = determineAlertLevel($do_node, $ph_node, $tempw_node);  

    // Update node data and log the result  
    $updateSuccess = $nodeModel->updateNode([  
        'id_node' => $id_node,  
        'temp_node' => $temp_node,  
        'hum_node' => $hum_node,  
        'do_node' => $do_node,  
        'ph_node' => $ph_node,  
        'tempw_node' => $tempw_node,  
        'pump_node' => $pump_node,  
        'alert_node' => $alert_node,  
        'laston_node' => $laston_node  
    ]);  

    if ($updateSuccess) {  
        $logResult = $nodeModel->insertNodeLog([  
            'id_node' => $id_node,  
            'timeon_log' => $laston_node,  
            'temp_nodelog' => $temp_node,  
            'hum_nodelog' => $hum_node,  
            'do_nodelog' => $do_node,  
            'ph_nodelog' => $ph_node,  
            'tempw_nodelog' => $tempw_node,  
            'pump_nodelog' => $pump_node,  
            'alert_nodelog' => $alert_node,  
            'rssi_log' => $rssi_log  
        ]);  

        echo json_encode(["sc" => $logResult ? "1" : "0"]);  
    } else {  
        echo json_encode(["sc" => "-1"]);  
    }  
}
else{
    echo json_encode(["sc" => "-3"]);  
    exit;  
}
?>