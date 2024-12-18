<?php
session_start();

// Define base path
define('BASE_PATH', __DIR__);

// Include necessary files
require_once BASE_PATH . '/config/database.php';

// Router class to handle routing
class Router {
    private $routes = [];
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function add($method, $path, $handler, $auth = false) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'auth' => $auth
        ];
    }

    public function handle($method, $path) {
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $path) {
                if ($route['auth'] && !$this->isAuthenticated()) {
                    header('Location: /Quadribol/login.php');
                    exit;
                }
                return $route['handler']($this->db);
            }
        }
        return $this->notFound();
    }

    private function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }

    private function notFound() {
        header("HTTP/1.0 404 Not Found");
        echo json_encode(['status' => 'error', 'message' => 'Route not found']);
    }
}

// Create router instance
$router = new Router();

// Auth routes
$router->add('POST', '/auth/register', function($db) {
    $data = json_decode(file_get_contents("php://input"));
    
    if (empty($data->username) || empty($data->email) || empty($data->password)) {
        return json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    }

    $hashedPassword = password_hash($data->password, PASSWORD_DEFAULT);
    
    try {
        $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
        $stmt->bindParam(":username", $data->username);
        $stmt->bindParam(":email", $data->email);
        $stmt->bindParam(":password", $hashedPassword);
        
        if ($stmt->execute()) {
            return json_encode(['status' => 'success', 'message' => 'User registered successfully']);
        }
    } catch(PDOException $e) {
        return json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
});

$router->add('POST', '/auth/login', function($db) {
    $data = json_decode(file_get_contents("php://input"));
    
    if (empty($data->email) || empty($data->password)) {
        return json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    }
    
    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(":email", $data->email);
        $stmt->execute();
        
        if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (password_verify($data->password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                return json_encode(['status' => 'success', 'message' => 'Login successful']);
            }
        }
        
        return json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
    } catch(PDOException $e) {
        return json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
});

$router->add('POST', '/auth/logout', function($db) {
    session_destroy();
    return json_encode(['status' => 'success', 'message' => 'Logged out successfully']);
});

// Protected routes (require authentication)
$router->add('GET', '/teams', function($db) {
    try {
        $stmt = $db->prepare("SELECT * FROM teams");
        $stmt->execute();
        $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return json_encode(['status' => 'success', 'data' => $teams]);
    } catch(PDOException $e) {
        return json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}, true);

$router->add('GET', '/players', function($db) {
    try {
        $stmt = $db->prepare("SELECT p.*, t.name as team_name FROM players p LEFT JOIN teams t ON p.team_id = t.id");
        $stmt->execute();
        $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return json_encode(['status' => 'success', 'data' => $players]);
    } catch(PDOException $e) {
        return json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}, true);

$router->add('GET', '/matches', function($db) {
    try {
        $stmt = $db->prepare("
            SELECT m.*, 
                   t1.name as team1_name, 
                   t2.name as team2_name,
                   w.name as winner_name
            FROM matches m
            LEFT JOIN teams t1 ON m.team1_id = t1.id
            LEFT JOIN teams t2 ON m.team2_id = t2.id
            LEFT JOIN teams w ON m.winner_id = w.id
        ");
        $stmt->execute();
        $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return json_encode(['status' => 'success', 'data' => $matches]);
    } catch(PDOException $e) {
        return json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}, true);

// Handle the request
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/Quadribol', '', $path);

header('Content-Type: application/json');
echo $router->handle($method, $path);
?>
