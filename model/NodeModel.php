<?php  
require_once "config.php";  

class NodeModel {  
    private $conn;  

    public function __construct() {  
        $database = new Database();  
        $this->conn = $database->getConnection();  
    }  

    private function executeQuery($query, $params = []) {  
        $stmt = $this->conn->prepare($query);  
        foreach ($params as $param => $value) {  
            $stmt->bindValue($param, $value);  
        }  
        $stmt->execute();  
        return $stmt;  
    }  

    public function getAllNode($id_user) {  
        $query = "SELECT * FROM node_farm WHERE id_user = :id_user ORDER BY id_node";  
        $stmt = $this->executeQuery($query, [":id_user" => $id_user]);  
        return $stmt->fetchAll(PDO::FETCH_ASSOC);  
    }  

    public function getAllNodePump($id_user) {  
        $query = "SELECT code_node AS cn, pump_node AS pn FROM node_farm WHERE id_user = :id_user ORDER BY id_node";  
        $stmt = $this->executeQuery($query, [":id_user" => $id_user]);  
        return $stmt->fetchAll(PDO::FETCH_ASSOC);  
    }  

    public function getNodeById($id_node) {  
        $query = "SELECT * FROM node_farm WHERE id_node = :id_node";  
        $stmt = $this->executeQuery($query, [":id_node" => $id_node]);  
        return $stmt->fetch(PDO::FETCH_ASSOC);  
    }  

    public function getNodeByCode($code_node) {  
        $query = "SELECT * FROM node_farm WHERE code_node = :code_node";  
        $stmt = $this->executeQuery($query, [":code_node" => $code_node]);  
        return $stmt->fetch(PDO::FETCH_ASSOC);  
    }  

    public function insertNodeLog($data) {  
        $query = "INSERT INTO nodelog_farm (id_node, timeon_log, temp_nodelog, hum_nodelog, do_nodelog,   
        ph_nodelog, tempw_nodelog, pump_nodelog, alert_nodelog, rssi_log)   
        VALUES (:id_node, :timeon_log, :temp_nodelog, :hum_nodelog, :do_nodelog, :ph_nodelog,   
        :tempw_nodelog, :pump_nodelog, :alert_nodelog, :rssi_log)";  
        return $this->executeQuery($query, [  
            ":id_node" => $data['id_node'],  
            ":timeon_log" => $data['timeon_log'],  
            ":temp_nodelog" => $data['temp_nodelog'],  
            ":hum_nodelog" => $data['hum_nodelog'],  
            ":do_nodelog" => $data['do_nodelog'],  
            ":ph_nodelog" => $data['ph_nodelog'],  
            ":tempw_nodelog" => $data['tempw_nodelog'],  
            ":pump_nodelog" => $data['pump_nodelog'],  
            ":alert_nodelog" => $data['alert_nodelog'],  
            ":rssi_log" => $data['rssi_log']  
        ])->rowCount();  
    }  

    public function getAllNodelog($id_node) {  
        $query = "SELECT * FROM nodelog_farm WHERE id_node = :id_node ORDER BY id_nodelog";  
        $stmt = $this->executeQuery($query, [":id_node" => $id_node]);  
        return $stmt->fetchAll(PDO::FETCH_ASSOC);  
    }  

    public function updateNode($data) {  
        $query = "UPDATE node_farm SET temp_node = :temp_node, hum_node = :hum_node,   
        do_node = :do_node, ph_node = :ph_node, tempw_node = :tempw_node, pump_node = :pump_node,   
        alert_node = :alert_node, laston_node = :laston_node WHERE id_node = :id_node";  
        return $this->executeQuery($query, [  
            ":id_node" => $data['id_node'],  
            ":temp_node" => $data['temp_node'],  
            ":hum_node" => $data['hum_node'],  
            ":do_node" => $data['do_node'],  
            ":ph_node" => $data['ph_node'],  
            ":tempw_node" => $data['tempw_node'],  
            ":pump_node" => $data['pump_node'],  
            ":alert_node" => $data['alert_node'],  
            ":laston_node" => $data['laston_node']  
        ])->rowCount();  
    }  

    public function updateNodePump($id_node, $pump_node) {  
        $query = "UPDATE node_farm SET pump_node = :pump_node   
            WHERE id_node = :id_node";  
        return $this->executeQuery($query, [  
            ":id_node" => $id_node,  
            ":pump_node" => $pump_node  
        ])->rowCount();  
    }

    /**
     * Get total number of nodes in the system
     */
    public function getTotalNodes() {
        $query = "SELECT COUNT(*) as total FROM node_farm";
        $stmt = $this->executeQuery($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    /**
     * Get total number of active nodes (recently updated)
     */
    public function getActiveNodes() {
        $query = "SELECT COUNT(*) as total FROM node_farm WHERE laston_node >= DATE_SUB(NOW(), INTERVAL 1 HOUR)";
        $stmt = $this->executeQuery($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    /**
     * Get nodes with their latest data for dashboard
     */
    public function getNodesWithLatestData($id_user) {
        $query = "SELECT n.*, 
                         CASE 
                            WHEN n.laston_node >= DATE_SUB(NOW(), INTERVAL 1 HOUR) THEN 'online'
                            WHEN n.laston_node >= DATE_SUB(NOW(), INTERVAL 24 HOUR) THEN 'warning'
                            ELSE 'offline'
                         END as status
                  FROM node_farm n 
                  WHERE n.id_user = :id_user 
                  ORDER BY n.laston_node DESC";
        $stmt = $this->executeQuery($query, [":id_user" => $id_user]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get recent node logs for analytics
     */
    public function getRecentLogs($id_node, $limit = 24) {
        $query = "SELECT * FROM nodelog_farm 
                  WHERE id_node = :id_node 
                  ORDER BY timeon_log DESC 
                  LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(":id_node", $id_node);
        $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add new node
     */
    public function addNode($data) {
        $query = "INSERT INTO node_farm (id_user, code_node, name_node, location_node, temp_node, hum_node, 
                  do_node, ph_node, tempw_node, pump_node, alert_node, laston_node) 
                  VALUES (:id_user, :code_node, :name_node, :location_node, 0, 0, 0, 0, 0, 0, 0, NOW())";
        
        return $this->executeQuery($query, [
            ":id_user" => $data['id_user'],
            ":code_node" => $data['code_node'],
            ":name_node" => $data['name_node'],
            ":location_node" => $data['location_node']
        ])->rowCount();
    }

    /**
     * Delete node
     */
    public function deleteNode($id_node) {
        // First delete all logs for this node
        $this->executeQuery("DELETE FROM nodelog_farm WHERE id_node = :id_node", [":id_node" => $id_node]);
        
        // Then delete the node
        $query = "DELETE FROM node_farm WHERE id_node = :id_node";
        return $this->executeQuery($query, [":id_node" => $id_node])->rowCount();
    }

    /**
     * Get latest sensor data from all active nodes
     */
    public function getLatestSensorData($id_user = null) {
        $whereClause = $id_user ? "WHERE id_user = :id_user" : "";
        $query = "SELECT AVG(temp_node) as temperature,
                         AVG(ph_node) as ph,
                         AVG(do_node) as dissolved_oxygen,
                         AVG(hum_node) as turbidity,
                         MAX(laston_node) as last_update,
                         COUNT(*) as node_count
                  FROM node_farm 
                  {$whereClause}
                  AND laston_node >= DATE_SUB(NOW(), INTERVAL 2 HOUR)";
        
        $params = $id_user ? [":id_user" => $id_user] : [];
        $stmt = $this->executeQuery($query, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get system health score based on sensor readings
     */
    public function getSystemHealth($id_user = null) {
        $data = $this->getLatestSensorData($id_user);
        
        if (!$data || $data['node_count'] == 0) {
            return ['score' => 0, 'status' => 'No Data', 'message' => 'No active nodes found'];
        }

        $score = 100;
        $issues = [];

        // Temperature check (26-30°C is optimal)
        if ($data['temperature'] < 24 || $data['temperature'] > 32) {
            $score -= 25;
            $issues[] = 'Temperature out of range';
        } elseif ($data['temperature'] < 26 || $data['temperature'] > 30) {
            $score -= 10;
            $issues[] = 'Temperature suboptimal';
        }

        // pH check (6.5-8.5 is optimal)
        if ($data['ph'] < 6.0 || $data['ph'] > 9.0) {
            $score -= 30;
            $issues[] = 'pH critically out of range';
        } elseif ($data['ph'] < 6.5 || $data['ph'] > 8.5) {
            $score -= 15;
            $issues[] = 'pH suboptimal';
        }

        // Dissolved Oxygen check (5-15 mg/L is optimal)
        if ($data['dissolved_oxygen'] < 3 || $data['dissolved_oxygen'] > 20) {
            $score -= 35;
            $issues[] = 'Dissolved oxygen critically low/high';
        } elseif ($data['dissolved_oxygen'] < 5 || $data['dissolved_oxygen'] > 15) {
            $score -= 15;
            $issues[] = 'Dissolved oxygen suboptimal';
        }

        $score = max(0, $score);

        if ($score >= 90) {
            $status = 'Excellent';
        } elseif ($score >= 75) {
            $status = 'Good';
        } elseif ($score >= 50) {
            $status = 'Fair';
        } elseif ($score >= 25) {
            $status = 'Poor';
        } else {
            $status = 'Critical';
        }

        return [
            'score' => $score,
            'status' => $status,
            'issues' => $issues,
            'data' => $data
        ];
    }
}  
?>