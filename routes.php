<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define base path
define('BASE_PATH', __DIR__);

// Include necessary files
require_once BASE_PATH . '/config/database.php';

// Logger function
function logError($message) {
    error_log(date('Y-m-d H:i:s') . " - " . $message . "\n", 3, BASE_PATH . '/error.log');
}

// Router class to handle routing
class Router {
    private $routes = [];
    private $db;

    public function __construct() {
        try {
            $database = new Database();
            $this->db = $database->getConnection();
            logError("Database connection established");
        } catch (Exception $e) {
            logError("Database connection error: " . $e->getMessage());
        }
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
        logError("Handling request: $method $path");
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $path) {
                if ($route['auth'] && !$this->isAuthenticated()) {
                    logError("Authentication required for $path");
                    header('Location: /Quadribol/login.html');
                    exit;
                }
                try {
                    return $route['handler']($this->db);
                } catch (Exception $e) {
                    logError("Error in route handler: " . $e->getMessage());
                    return json_encode(['status' => 'error', 'message' => $e->getMessage()]);
                }
            }
        }
        logError("Route not found: $path");
        return $this->notFound();
    }

    private function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }

    private function notFound() {
        header("HTTP/1.0 404 Not Found");
        return json_encode(['status' => 'error', 'message' => 'Route not found']);
    }
}

// Create router instance
$router = new Router();

// Auth routes
$router->add('POST', '/auth/register', function($db) {
    logError("Processing registration request");
    
    $data = json_decode(file_get_contents("php://input"));
    logError("Registration data received: " . json_encode($data));
    
    if (empty($data->username) || empty($data->email) || empty($data->password)) {
        logError("Missing required fields in registration");
        return json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    }

    $hashedPassword = password_hash($data->password, PASSWORD_DEFAULT);
    
    try {
        $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
        $stmt->bindParam(":username", $data->username);
        $stmt->bindParam(":email", $data->email);
        $stmt->bindParam(":password", $hashedPassword);
        
        if ($stmt->execute()) {
            logError("User registered successfully: " . $data->email);
            return json_encode(['status' => 'success', 'message' => 'User registered successfully']);
        }
    } catch(PDOException $e) {
        logError("Registration error: " . $e->getMessage());
        // Check for duplicate entry
        if ($e->getCode() == 23000) {
            return json_encode(['status' => 'error', 'message' => 'Email or username already exists']);
        }
        return json_encode(['status' => 'error', 'message' => 'Registration failed']);
    }
});

$router->add('POST', '/auth/login', function($db) {
    logError("Processing login request");
    
    $data = json_decode(file_get_contents("php://input"));
    logError("Login data received: " . json_encode($data));
    
    if (empty($data->email) || empty($data->password)) {
        logError("Missing required fields in login");
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
                
                logError("Login successful: " . $data->email);
                return json_encode(['status' => 'success', 'message' => 'Login successful']);
            }
        }
        
        logError("Invalid login credentials: " . $data->email);
        return json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
    } catch(PDOException $e) {
        logError("Login error: " . $e->getMessage());
        return json_encode(['status' => 'error', 'message' => 'Login failed']);
    }
});

$router->add('POST', '/auth/logout', function($db) {
    session_destroy();
    logError("User logged out");
    return json_encode(['status' => 'success', 'message' => 'Logged out successfully']);
});

// Protected routes (require authentication)
$router->add('GET', '/teams', function($db) {
    logError("Processing teams request");
    
    try {
        $stmt = $db->prepare("SELECT * FROM teams");
        $stmt->execute();
        $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        logError("Teams data retrieved successfully");
        return json_encode(['status' => 'success', 'data' => $teams]);
    } catch(PDOException $e) {
        logError("Teams data retrieval error: " . $e->getMessage());
        return json_encode(['status' => 'error', 'message' => 'Failed to retrieve teams data']);
    }
}, true);

$router->add('GET', '/players', function($db) {
    logError("Processing players request");
    
    try {
        $stmt = $db->prepare("SELECT p.*, t.name as team_name FROM players p LEFT JOIN teams t ON p.team_id = t.id");
        $stmt->execute();
        $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        logError("Players data retrieved successfully");
        return json_encode(['status' => 'success', 'data' => $players]);
    } catch(PDOException $e) {
        logError("Players data retrieval error: " . $e->getMessage());
        return json_encode(['status' => 'error', 'message' => 'Failed to retrieve players data']);
    }
}, true);

$router->add('GET', '/matches', function($db) {
    logError("Processing matches request");
    
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
        
        logError("Matches data retrieved successfully");
        return json_encode(['status' => 'success', 'data' => $matches]);
    } catch(PDOException $e) {
        logError("Matches data retrieval error: " . $e->getMessage());
        return json_encode(['status' => 'error', 'message' => 'Failed to retrieve matches data']);
    }
}, true);

// Handle the request
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/Quadribol', '', $path);

logError("Incoming request: $method $path");

header('Content-Type: application/json');
echo $router->handle($method, $path);
?>
