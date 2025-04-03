
<?php
// BACKEND / API 
//  Empfängt die Requests vom Frontend
//  Verbindet sich mit der Datenbank über PDO
//  Führt passende SQL-Befehle aus (je nach HTTP-Methode)
//  Gibt als Antwort JSON-Daten zurück, die dein Frontend anzeigen kann

header('Content-Type: application/json');  //application/json für JSON files

require_once('./logging.php'); // Logging-Funktion einbinden
require_once('./classes/TodoDB.php'); // TodoDB-Klasse einbinden

$todoDB = new TodoDB();

// Log the request method and the TODO items
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $todo_items = $todoDB->getTodos(); // Hole alle Todos aus der DB (NEU)
        echo json_encode($todo_items);
        write_log("GET", $todo_items);
        break;
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $newTodo = $todoDB->addTodo($data['title']);
        echo json_encode($newTodo);
        write_log("POST", $newTodo);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'];
        $title = $data['title'];
        $completed = isset($data['completed']) && $data['completed'] ? 1 : 0;

        if (isset($id) && isset($title)) {
            $result = $todoDB->updateTodo($id, $title, $completed); // <-- neuer Aufruf 
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
            $result = $todoDB->deleteTodo($id); // Nutze Methode aus der Klasse
            echo json_encode($result);
            write_log("DELETE", $data);
        } else {
            http_response_code(400);
            echo json_encode(["error" => "ID fehlt"]);
        }
        break;
}
