<?php  
header("Content-Type: application/json");  
require_once "../model/UserModel.php";  

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

if(isset($data["Token_user"])){
    // Extract and sanitize input data  
    $Token_user = trim($data["Token_user"]);  
    
    try {  
        $userModel = new UserModel();  
        $user = $userModel->getUserByCode($Token_user);  

        if (!$user) {  
            echo json_encode(["sc" => "0"]); // User not found  
            exit;  
        }  

        // Respond with the nodes  
        echo json_encode([  
            "sc" => "1",       // Success  
            "dt" => $user     // Node data  
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