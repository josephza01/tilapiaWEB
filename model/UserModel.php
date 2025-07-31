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
            ":name_user" => $data['name_user'] ?? '', 
            ":email_user" => $data['email_user'] ?? '',  
            ":tel_user" => $data['tel_user'] ?? '',  
            ":name_farm" => $data['name_farm'] ?? '',  
            ":address_farm" => $data['address_farm'] ?? '',  
            ":zipcode_farm" => $data['zipcode_farm'] ?? '',  
            ":county_farm" => $data['county_farm'] ?? '',  
            ":latlon_farm" => $data['latlon_farm'] ?? ''  
        ])->rowCount();  
    }  

    /**
     * Update only basic user profile (name, email, phone)
     */
    public function updateUserProfile($data) {
        $query = "UPDATE user_tb SET   
            name_user = :name_user,  
            email_user = :email_user,  
            tel_user = :tel_user   
            WHERE id_user = :id_user";  
        return $this->executeQuery($query, [  
            ":id_user" => $data['id_user'],  
            ":name_user" => $data['name_user'] ?? '', 
            ":email_user" => $data['email_user'] ?? '',  
            ":tel_user" => $data['tel_user'] ?? ''
        ])->rowCount();  
    }

    /**
     * Update user alert level
     */
    public function updateUserAlertLevel($data) {
        $query = "UPDATE user_tb SET alert_level_user = :alert_level_user WHERE id_user = :id_user";
        return $this->executeQuery($query, [
            ":id_user" => $data['id_user'],
            ":alert_level_user" => $data['alert_level_user'] ?? 1
        ])->rowCount();
    }

    /**
     * Verify current password before updating
     */
    public function verifyPassword($id_user, $current_password) {
        $query = "SELECT pass_user FROM user_tb WHERE id_user = :id_user";
        $stmt = $this->executeQuery($query, [":id_user" => $id_user]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return false;
        }
        
        $stored_password = $user['pass_user'];
        
        // Try different verification methods
        // 1. Direct comparison (plain text)
        if ($stored_password === $current_password) {
            return true;
        }
        
        // 2. Trimmed comparison (in case of whitespace issues)
        if (trim($stored_password) === trim($current_password)) {
            return true;
        }
        
        // 3. Check if stored password is hashed and verify
        if (strlen($stored_password) === 60 && substr($stored_password, 0, 4) === '$2y$') {
            // Looks like bcrypt hash
            return password_verify($current_password, $stored_password);
        }
        
        // 4. Check for MD5 hash (32 characters)
        if (strlen($stored_password) === 32 && ctype_xdigit($stored_password)) {
            return md5($current_password) === $stored_password;
        }
        
        // 5. Check for SHA1 hash (40 characters)
        if (strlen($stored_password) === 40 && ctype_xdigit($stored_password)) {
            return sha1($current_password) === $stored_password;
        }
        
        return false;
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
     * Debug method to check password format
     */
    public function debugPassword($id_user) {
        $query = "SELECT pass_user FROM user_tb WHERE id_user = :id_user";
        $stmt = $this->executeQuery($query, [":id_user" => $id_user]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return ['error' => 'User not found'];
        }
        
        $password = $user['pass_user'];
        
        return [
            'password' => $password,
            'length' => strlen($password),
            'trimmed' => trim($password),
            'is_numeric' => is_numeric($password),
            'is_bcrypt' => (strlen($password) === 60 && substr($password, 0, 4) === '$2y$'),
            'is_md5' => (strlen($password) === 32 && ctype_xdigit($password)),
            'is_sha1' => (strlen($password) === 40 && ctype_xdigit($password)),
            'has_whitespace' => ($password !== trim($password))
        ];
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