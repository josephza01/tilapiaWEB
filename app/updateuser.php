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

if(isset($data["id_user"]) && isset($data["name_user"]) && isset($data["email_user"]) && 
isset($data["tel_user"]) && isset($data["name_farm"])){
    // Extract and sanitize input data  
    $id_user = intval($data["id_user"]);  
    $name_user = trim($data["name_user"]);  
    $email_user = trim($data["email_user"]);  
    $tel_user = trim($data["tel_user"]);  
    $name_farm = trim($data["name_farm"]);  
    $address_farm = trim($data["address_farm"]);  
    $zipcode_farm = trim($data["zipcode_farm"]); 
    $county_farm = trim($data["county_farm"]);  
    $latlon_farm = trim($data["latlon_farm"]);  


    $userModel = new UserModel();

    $updateSuccess = $userModel->updateUser([  
        'id_user' => $id_user,  
        'name_user' => $name_user,  
        'email_user' => $email_user,  
        'tel_user' => $tel_user,  
        'name_farm' => $name_farm,  
        'address_farm' => $address_farm,  
        'zipcode_farm' => $zipcode_farm,  
        'county_farm' => $county_farm,  
        'latlon_farm' => $latlon_farm  
    ]);  

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