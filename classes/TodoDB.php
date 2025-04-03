<?php

/**
 * Todo list database object.
 *
 * Global variable with the object of our TodoDB class.
 */
require_once('./logging.php');
require_once('./config.php');


class TodoDB
{
    private $connection;
    private $stmt;

    /**
     * Contructructor of the TodoDB class.
     */
    public function __construct()
    {
        global $host, $db, $user, $pass;
        try {
            $this->connection = new PDO(
                "mysql:host=$host;dbname=$db;",
                $user,
                $pass
            );
            $this->connection->setAttribute(
                PDO::ATTR_DEFAULT_FETCH_MODE,
                PDO::FETCH_ASSOC
            );
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }

    public function getTodos()
    {
        $statement = $this->connection->query("SELECT * FROM todo");
        $todo_items = $statement->fetchAll();
        return $todo_items;
    }

    public function addTodo($title) {
        $stmt = $this->connection->prepare(
            "INSERT INTO todo (title, completed) VALUES (:title, :completed)"
        );
        $stmt->execute(['title' => $title, 'completed' => 0]);
    
        $id = $this->connection->lastInsertId();
        $stmt = $this->connection->prepare("SELECT * FROM todo WHERE id = ?");
        $stmt->execute([$id]);
    
        return $stmt->fetch();
    }
    

    public function setCompleted($id, $completed) {
        $statement = $this->connection->prepare(
            "UPDATE todo SET completed = :completed WHERE id = :id");
        $statement->execute(["id" => $id, "completed" => $completed]);
    }

    public function updateTodo($id, $title, $completed) {
        $stmt = $this->connection->prepare(
            "UPDATE todo SET title = ?, completed = ? WHERE id = ?"
        );
        $stmt->execute([$title, $completed, $id]);
    
        return ["status" => "success"];
    }

    public function deleteTodo($id) {
        $stmt = $this->connection->prepare("DELETE FROM todo WHERE id = ?");
        $stmt->execute([$id]);
    
        return ["status" => "success"];
    }


}
