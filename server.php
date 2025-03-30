<?php

// Redirect HTTP to HTTPS
if (strpos($_SERVER['HTTP_HOST'], ':8443') === false) {
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];
    // Replace port 8000 with 8443 in the host
    $host = str_replace(':8000', ':8443', $host);
    header("Location: https://$host$uri");
    exit();
}

// Handle static files first
$requestUri = $_SERVER['REQUEST_URI'];
$staticFiles = ['client.js', 'index.html'];

// If it's a request for a static file
if (in_array(basename($requestUri), $staticFiles)) {
    $file = basename($requestUri);
    if ($file === 'index.html') {
        header('Content-Type: text/html');
    } elseif ($file === 'client.js') {
        header('Content-Type: application/javascript');
    }
    readfile($file);
    exit;
}

// If it's the root URL, serve index.html
if ($requestUri === '/' || $requestUri === '/index.html') {
    header('Content-Type: text/html');
    readfile('index.html');
    exit;
}

// For API calls, set JSON content type
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' \'unsafe-eval\'; style-src \'self\' \'unsafe-inline\';');

// Handle API calls first
$action = $_GET['action'] ?? $_POST['action'] ?? '';
if (!empty($action)) {
    require_once 'vendor/autoload.php';

    // Initialize HTML Purifier
    $config = HTMLPurifier_Config::createDefault();
    $purifier = new HTMLPurifier($config);

    // Function to sanitize input using HTML Purifier
    function sanitizeInput($input) {
        global $purifier;
        if (empty($input)) {
            return '';
        } 
        // Sanitize HTML content
        $input = $purifier->purify($input);
 
        return $input;
    }

    // Database file path
    $dbFile = 'notes.db';

    // Initialize database if it doesn't exist
    if (!file_exists($dbFile)) {
        try {
            $db = new SQLite3($dbFile);
            $db->exec('CREATE TABLE notes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                content TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )');
        } catch (Exception $e) {
            error_log("Database initialization error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Database initialization failed']);
            exit;
        }
    } else {
        try {
            $db = new SQLite3($dbFile);
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed']);
            exit;
        }
    }

    try {
        switch ($action) {
            case 'add':
                $note = sanitizeInput($_POST['note'] ?? '');
                if (empty($note)) {
                    echo json_encode(['success' => false, 'message' => 'Note cannot be empty']);
                    exit;
                }
                
                try {
                    $stmt = $db->prepare('INSERT INTO notes (content) VALUES (:content)');
                    $stmt->bindValue(':content', $note, SQLITE3_TEXT);
                    $result = $stmt->execute();
                    
                    if ($result) {
                        echo json_encode(['success' => true, 'message' => 'Note added successfully']);
                    } else {
                        throw new Exception('Failed to add note');
                    }
                } catch (Exception $e) {
                    error_log("Database error: " . $e->getMessage());
                    echo json_encode(['success' => false, 'message' => 'Failed to add note']);
                }
                break;

            case 'get':
                $stmt = $db->prepare('SELECT * FROM notes ORDER BY created_at DESC');
                $result = $stmt->execute();
                if ($result === false) {
                    throw new Exception('Failed to fetch notes');
                }
                $notes = [];
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                    $notes[] = $row;
                }
                echo json_encode($notes);
                break;

            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid action']);
                break;
        }
    } catch (Exception $e) {
        error_log("Database operation error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'An error occurred while processing your request']);
    }

    $db->close();
    exit;
}
?>
