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

if(isset($data["id_user"]) && isset($data["alert_auto"]) && isset($data["time_alert"]) &&
isset($data["pump_auto"])){
    // Extract and sanitize input data  
    $id_user = intval($data["id_user"]); 
    $alert_auto = intval($data["alert_auto"]);  
    $time_alert = intval($data["time_alert"]);  
    $pump_auto = intval($data["pump_auto"]); 

    $userModel = new UserModel();

    $updateSuccess = $userModel->updateUserAlert($id_user,$alert_auto,$time_alert,$pump_auto);  

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