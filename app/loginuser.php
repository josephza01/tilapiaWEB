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

if(isset($data["email_user"]) && isset($data["pass_user"])){
    // Extract and sanitize input data  
    $email_user = trim($data["email_user"]);  
    $pass_user = trim($data["pass_user"] );
    

    try {  
        $userModel = new UserModel();  
        $user = $userModel->LoginUser($email_user,$pass_user);  

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