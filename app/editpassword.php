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

if(isset($data["id_user"]) && isset($data["pass_user"])){
    // Extract and sanitize input data  
    $id_user = intval($data["id_user"]); 
    $pass_user = trim($data["pass_user"]);  

    $userModel = new UserModel();

    $updateSuccess = $userModel->updateUserPass($id_user,$pass_user);  

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