<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        try {
            $stmt = $db->prepare("SELECT * FROM teams");
            $stmt->execute();
            $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                "status" => "success",
                "data" => $teams
            ]);
        } catch(PDOException $e) {
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->name)) {
            try {
                $stmt = $db->prepare("INSERT INTO teams (name, house_color) VALUES (:name, :house_color)");
                $stmt->bindParam(":name", $data->name);
                $stmt->bindParam(":house_color", $data->house_color);
                
                if($stmt->execute()) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "Team created successfully"
                    ]);
                }
            } catch(PDOException $e) {
                echo json_encode([
                    "status" => "error",
                    "message" => $e->getMessage()
                ]);
            }
        }
        break;
}
?>
