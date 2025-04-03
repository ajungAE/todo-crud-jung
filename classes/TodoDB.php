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

    /**
     * Prepare and execute the given SQL statement.
     *
     * @param string $sql The SQL statement.
     * @param array $params Parameters for the SQL statement.
     * @return PDOStatement The executed statement.
     */
    private function prepareExecuteStatement($sql, $params = [])
    {
        try {
            write_log("SQL", $sql);
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }

    public function getTodos()
    {
        $stmt = $this->prepareExecuteStatement("SELECT * FROM todo");
        return $stmt->fetchAll();
    }

    public function addTodo($title) {
        $this->prepareExecuteStatement(
            "INSERT INTO todo (title, completed) VALUES (:title, :completed)",
            ['title' => $title, 'completed' => 0]
        );
    
        $id = $this->connection->lastInsertId();
        $stmt = $this->prepareExecuteStatement("SELECT * FROM todo WHERE id = :id", ['id' => $id]);
        return $stmt->fetch();
    }


    public function setCompleted($id, $completed) {
        $this->prepareExecuteStatement(
            "UPDATE todo SET completed = :completed WHERE id = :id",
            ['id' => $id, 'completed' => $completed]
        );
    }

    public function updateTodo($id, $title, $completed) {
        $this->prepareExecuteStatement(
            "UPDATE todo SET title = :title, completed = :completed WHERE id = :id",
            ['title' => $title, 'completed' => $completed, 'id' => $id]
        );
        return ["status" => "success"];
    }

    public function deleteTodo($id) {
        $this->prepareExecuteStatement("DELETE FROM todo WHERE id = :id", ['id' => $id]);
        return ["status" => "success"];
    }
}
