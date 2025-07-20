<?php  
header("Content-Type: application/json");  
require_once "../model/NodeModel.php";  

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

if(isset($data["id_node"]) && isset($data["pump_node"])){
    // Extract and sanitize input data  
    $id_node = intval($data["id_node"]); 
    $pump_node = intval($data["pump_node"]);  

    $nodeModel = new NodeModel();

    $updateSuccess = $nodeModel->updateNodePump($id_node,$pump_node);  

    if ($updateSuccess) {  
        echo json_encode(["sc" =>  "1"]);  
    } else {  
        echo json_encode(["sc" => "-1"]);  
    }  
}
else{
    echo json_encode(["sc" => "-3"]);  
    exit;  
}
?>