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

if(isset($data["id_user"])){
// Validate and sanitize the input   
    $id_user = intval($data["id_user"]); 

    try {  
        
        // Fetch nodes related to the user  
        $nodeModel = new NodeModel();  
        $nodes = $nodeModel->getAllNode($id_user);  

        // Respond with the nodes  
        echo json_encode([  
            "sc" => "1",       // Success  
            "dt" => $nodes     // Node data  
        ]);  
    } catch (Exception $e) {  
        // Handle unexpected errors  
        echo json_encode(["sc" => "-1", "err" => $e->getMessage()]);  
        exit;  
    }  
}
else{
    echo json_encode(["sc" => "-3"]);  
    exit;  
}
?>