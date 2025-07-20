<?php  
require_once "config.php";  

class UserModel {  
    private $conn;  

    public function __construct() {  
        $database = new Database();  
        $this->conn = $database->getConnection();  
    }  

    /**  
     * Centralized method to execute a query with parameters.  
     */  
    private function executeQuery($query, $params = []) {  
        $stmt = $this->conn->prepare($query);  
        foreach ($params as $key => $value) {  
            $stmt->bindValue($key, $value);  
        }  
        $stmt->execute();  
        return $stmt;  
    }  

    /**  
     * Get user by ID.  
     */  
    public function getUserById($id_user) {  
        $query = "SELECT * FROM user_tb WHERE id_user = :id_user";  
        $stmt = $this->executeQuery($query, [":id_user" => $id_user]);  
        return $stmt->fetch(PDO::FETCH_ASSOC);  
    }  

    /**  
     * Get user by token.  
     */  
    public function getUserByCode($Token_user) {  
        $query = "SELECT * FROM user_tb WHERE Token_user = :Token_user";  
        $stmt = $this->executeQuery($query, [":Token_user" => $Token_user]);  
        return $stmt->fetch(PDO::FETCH_ASSOC);  
    }  

    /**  
     * Update user details.  
     */  
    public function updateUser($data) {  
        $query = "UPDATE user_tb SET   
            name_user = :name_user,  
            email_user = :email_user,  
            tel_user = :tel_user,   
            name_farm = :name_farm,   
            address_farm = :address_farm,   
            zipcode_farm = :zipcode_farm,   
            county_farm = :county_farm,   
            latlon_farm = :latlon_farm   
            WHERE id_user = :id_user";  
        return $this->executeQuery($query, [  
            ":id_user" => $data['id_user'],  
            ":name_user" => $data['name_user'], 
            ":email_user" => $data['email_user'],  
            ":tel_user" => $data['tel_user'],  
            ":name_farm" => $data['name_farm'],  
            ":address_farm" => $data['address_farm'],  
            ":zipcode_farm" => $data['zipcode_farm'],  
            ":county_farm" => $data['county_farm'],  
            ":latlon_farm" => $data['latlon_farm']  
        ])->rowCount();  
    }  

    /**  
     * Update user alert settings.  
     */  
    public function updateUserAlert($id_user, $alert_auto, $time_alert, $pump_auto) {  
        $query = "UPDATE user_tb SET   
            alert_auto = :alert_auto, 
            pump_auto = :pump_auto, 
            time_alert = :time_alert   
            WHERE id_user = :id_user";  
        return $this->executeQuery($query, [  
            ":id_user" => $id_user,  
            ":alert_auto" => $alert_auto,  
            ":pump_auto" => $pump_auto,
            ":time_alert" => $time_alert  
        ])->rowCount();  
    }  

    /**  
     * Update user password.  
     */  
    public function updateUserPass($id_user, $pass_user) {  
        $query = "UPDATE user_tb SET pass_user = :pass_user   
            WHERE id_user = :id_user";  
        return $this->executeQuery($query, [  
            ":id_user" => $id_user,  
            ":pass_user" => $pass_user  
        ])->rowCount();  
    }

    public function LoginUser($email_user, $pass_user) {  
        $query = "SELECT * FROM user_tb WHERE email_user = :email_user and pass_user = :pass_user";  
        $stmt = $this->executeQuery($query, [  
            ":email_user" => $email_user,  
            ":pass_user" => $pass_user  
        ]);  
        return $stmt->fetch(PDO::FETCH_ASSOC);  
    }

    /**
     * Get total number of users in the system
     */
    public function getTotalUsers() {
        $query = "SELECT COUNT(*) as total FROM user_tb";
        $stmt = $this->executeQuery($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    /**
     * Get all users for management
     */
    public function getAllUsers() {
        $query = "SELECT id_user, name_user, email_user, tel_user, name_farm, alert_level_user, lastlogin_user FROM user_tb ORDER BY name_user";
        $stmt = $this->executeQuery($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if email already exists (for registration)
     */
    public function emailExists($email_user, $exclude_id = null) {
        $query = "SELECT COUNT(*) as count FROM user_tb WHERE email_user = :email_user";
        $params = [":email_user" => $email_user];
        
        if ($exclude_id) {
            $query .= " AND id_user != :id_user";
            $params[":id_user"] = $exclude_id;
        }
        
        $stmt = $this->executeQuery($query, $params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    /**
     * Create new user
     */
    public function createUser($data) {
        $query = "INSERT INTO user_tb (name_user, email_user, pass_user, tel_user, name_farm, alert_level_user, lastlogin_user) 
                  VALUES (:name_user, :email_user, :pass_user, :tel_user, :name_farm, :alert_level_user, :lastlogin_user)";
        
        return $this->executeQuery($query, [
            ":name_user" => $data['name_user'],
            ":email_user" => $data['email_user'],
            ":pass_user" => $data['pass_user'],
            ":tel_user" => $data['tel_user'],
            ":name_farm" => $data['name_farm'],
            ":alert_level_user" => $data['alert_level_user'] ?? 1,
            ":lastlogin_user" => date('Y-m-d H:i:s')
        ])->rowCount();
    }

    /**
     * Update last login time
     */
    public function updateLastLogin($id_user) {
        $query = "UPDATE user_tb SET lastlogin_user = :lastlogin_user WHERE id_user = :id_user";
        return $this->executeQuery($query, [
            ":id_user" => $id_user,
            ":lastlogin_user" => date('Y-m-d H:i:s')
        ])->rowCount();
    }
}  
?>