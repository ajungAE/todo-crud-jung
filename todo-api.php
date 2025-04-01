<?php
header('Content-Type: application/json');  //application/json für JSON files

$host = 'localhost';
$db = 'test_db';
$user = 'alexjung';
$pass = 'links234';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    //var_dump($pdo);
} catch (\PDOException $e) {
    error_log("PDOException: " . $e->getMessage() . " in "
        . $e->getFile() . " on line " . $e->getLine());
}

// LOG function in PHP
function write_log($action, $data)
{
    $log = fopen('log.txt', 'a');
    $timestamp = date('Y-m-d H:i:s');
    fwrite($log, "$timestamp - $action: " . json_encode($data) . "\n");
    fclose($log);
}

// Read content of the file and decode JSON data to an array.
$todo_file = 'todo.json';
if (file_exists($todo_file)) {
    $todo_file = 'todo.json';
    if (file_exists($todo_file)) {
        $todo_items = json_decode(
            file_get_contents($todo_file),
            true
        );
    } else {
        $todos_items = [];
    }
}

// Log the request method and the TODO items
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $statement = $pdo->query("SELECT * FROM todo"); // (NEW) 
        $todo_items = $statement->fetchAll(); // (NEW) 
        echo json_encode($todo_items);
        write_log("GET", $todo_items);
        break;
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);

        // Insert into database
        $statement = $pdo->prepare(
            "INSERT INTO todo (title, completed) VALUES (:title, :completed)"
        );
        $statement->execute(['title' => $data['title'], 'completed' => 0]);

        // Hole die letzte ID
        $id = $pdo->lastInsertId();

        // Lade das neue Todo aus der DB
        $stmt = $pdo->prepare("SELECT * FROM todo WHERE id = ?");
        $stmt->execute([$id]);
        $newTodo = $stmt->fetch();

        // Rückgabe: vollständiges neues Todo
        echo json_encode($newTodo);
        write_log("POST", $newTodo);
        break;
    case 'PUT':
        // Get the data sent from the client
        $data = json_decode(file_get_contents('php://input'), true);

        // Update the matching todo item
        foreach ($todo_items as &$todo) {
            if ($todo['id'] === $data['id']) {
                // Update the "completed" status
                $todo['completed'] = $data['completed'];
                break;
            }
        }
        // Save updated list
        file_put_contents($todo_file, json_encode($todo_items));

        // Return the updated item
        echo json_encode($data);

        // Log the update
        write_log("PUT", $data);
        break;
    case 'DELETE': // (NEW) Adjusting to MySQL <---------------------------------------------
        $data = json_decode(file_get_contents('php://input'), true); 
        $id = $data['id'];

        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM todo WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(["status" => "success"]);
            write_log("DELETE", $data);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "ID fehlt"]);
        }

        break;
}
