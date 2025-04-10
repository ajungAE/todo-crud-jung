<?php
// Entwicklungsmodus: Fehler anzeigen (im Livebetrieb deaktivieren!)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Header für JSON setzen
header('Content-Type: application/json');

// Globale Fehlerbehandlung → gibt JSON zurück statt leere Seite
set_exception_handler(function ($e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
    exit;
});

// Externe Dateien laden
require_once('./logging.php');
require_once('./classes/TodoDB.php');

$todoDB = new TodoDB();

// HTTP-Methoden behandeln
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        $todos = $todoDB->getTodos();
        echo json_encode($todos);
        write_log("GET", $todos);
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['title']) || trim($data['title']) === '') {
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "message" => "Titel darf nicht leer sein."
            ]);
            exit;
        }

        $newTodo = $todoDB->addTodo($data['title']);
        echo json_encode([
            "status" => "success",
            "data" => $newTodo
        ]);
        write_log("POST", $newTodo);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "message" => "ID fehlt"
            ]);
            exit;
        }

        // Nur "completed" toggeln
        if (isset($data['completed']) && !isset($data['title'])) {
            $todoDB->setCompleted($id, (int)$data['completed']);
            echo json_encode(["status" => "success"]);
            write_log("PUT", $data);
            break;
        }

        // Titel (und optional completed) aktualisieren
        if (isset($data['title'])) {
            $title = trim($data['title']);
            $completed = (int)($data['completed'] ?? 0);

            if ($title === '') {
                http_response_code(400);
                echo json_encode([
                    "status" => "error",
                    "message" => "Titel darf nicht leer sein."
                ]);
                exit;
            }

            $result = $todoDB->updateTodo($id, $title, $completed);
            echo json_encode(["status" => "success", "data" => $result]);
            write_log("PUT", $data);
            break;
        }

        http_response_code(400);
        echo json_encode([
            "status" => "error",
            "message" => "Ungültige Daten für PUT-Request"
        ]);
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "message" => "ID fehlt"
            ]);
            exit;
        }

        $result = $todoDB->deleteTodo($id);
        echo json_encode(["status" => "success", "data" => $result]);
        write_log("DELETE", $data);
        break;

    default:
        http_response_code(405);
        echo json_encode([
            "status" => "error",
            "message" => "HTTP-Methode nicht erlaubt"
        ]);
        break;
}
