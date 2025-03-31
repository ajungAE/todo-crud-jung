<?php
header('Content-Type: application/json');

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
        echo json_encode($todo_items);
        write_log("GET", $todo_items);
        break;
    case 'POST':
        // Get data from the input stream.
        $data = json_decode(file_get_contents('php://input'), true);
        // Create new todo item.
        $new_todo = ["id" => uniqid(), "title" => $data['title'], "completed" => false]; // (NEW)
        // Add new item to our todo item list.
        $todo_items[] = $new_todo;
        // Write todo items to JSON file.
        file_put_contents($todo_file, json_encode($todo_items));
        // Return the new item.
        echo json_encode($new_todo);
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
    case 'DELETE': // (NEW)
        $data = json_decode(file_get_contents('php://input'), true); // Receive the JSON data from the request body (includes the ID of the todo to delete)
        $todo_items = array_values(array_filter($todo_items, function ($todo) use ($data) { // Remove the todo item with the matching ID from the list
            return $todo['id'] !== $data['id'];
        }));
        file_put_contents($todo_file, json_encode($todo_items)); // Save the updated todo list back to the JSON file
        echo json_encode(['status' => 'success']); // Send a success response back to the client
        write_log("DELETE", $data); // Log the delete action for debugging or tracking
        break;
}
