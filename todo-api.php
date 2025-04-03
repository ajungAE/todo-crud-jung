
<?php
// BACKEND / API 
//  Empfängt die Requests vom Frontend
//  Verbindet sich mit der Datenbank über PDO
//  Führt passende SQL-Befehle aus (je nach HTTP-Methode)
//  Gibt als Antwort JSON-Daten zurück, die dein Frontend anzeigen kann

header('Content-Type: application/json');  //application/json für JSON files

require_once('./logging.php'); // Logging-Funktion einbinden
require_once('./classes/TodoDB.php'); // TodoDB-Klasse einbinden
require_once('./config.php'); // Konfiguration einbinden


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
        $statement = $pdo->prepare( // Insert into database
            "INSERT INTO todo (title, completed) VALUES (:title, :completed)"
        );
        $statement->execute(['title' => $data['title'], 'completed' => 0]);
        $id = $pdo->lastInsertId(); // Hole die letzte ID
        $stmt = $pdo->prepare("SELECT * FROM todo WHERE id = ?"); // Lade das neue Todo aus der DB
        $stmt->execute([$id]);
        $newTodo = $stmt->fetch();
        echo json_encode($newTodo); // Rückgabe: vollständiges neues Todo
        write_log("POST", $newTodo);
        break;
    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        $title = $data['title'];
        $completed = isset($data['completed']) && $data['completed'] ? 1 : 0;

        if (isset($id) && isset($title)) {
            $stmt = $pdo->prepare("UPDATE todo SET title = ?, completed = ? WHERE id = ?");
            $stmt->execute([$title, $completed, $id]);
            echo json_encode(["status" => "success"]);
            write_log("PUT", $data);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "ID oder Titel fehlt"]);
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
