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

// (NEW) This will be deleted, when the database is working
// $todo_file = 'todo.json';
// if (file_exists($todo_file)) {
//     $todo_file = 'todo.json';
//     if (file_exists($todo_file)) {
//         $todo_items = json_decode(
//             file_get_contents($todo_file),
//             true
//         );
//     } else {
//         $todos_items = [];
//     }
// }

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
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        $completed = $data['completed'];

        if (isset($id) && isset($completed)) {
            $stmt = $pdo->prepare("UPDATE todo SET completed = ? WHERE id = ?");
            $stmt->execute([$completed, $id]);
            echo json_encode(["status" => "success"]);
            write_log("PUT", $data);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "ID oder Status fehlt"]);
        }

        break;
    case 'DELETE':
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
