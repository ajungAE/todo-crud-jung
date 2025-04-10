<?php

/**
 * Todo list database object.
 */
require_once('./logging.php');
require_once('./config.php');

class TodoDB
{
    private $connection;

    /**
     * Constructor of the TodoDB class.
     */
    public function __construct()
    {
        global $host, $db, $user, $pass;

        try {
            $this->connection = new PDO(
                "mysql:host=$host;dbname=$db;charset=utf8mb4",
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Aktiviere Exceptions!
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            throw new Exception("Verbindung zur Datenbank fehlgeschlagen: " . $e->getMessage());
        }
    }

    /**
     * Prepare and execute the given SQL statement.
     *
     * @param string $sql The SQL statement.
     * @param array $params Parameters for the SQL statement.
     * @return PDOStatement
     * @throws Exception
     */
    private function prepareExecuteStatement($sql, $params = [])
    {
        try {
            write_log("SQL", $sql);
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("SQL-Fehler: " . $e->getMessage());
        }
    }

    public function getTodos()
    {
        $stmt = $this->prepareExecuteStatement("SELECT * FROM todo ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function addTodo($title)
    {
        $this->prepareExecuteStatement(
            "INSERT INTO todo (title, completed) VALUES (:title, :completed)",
            ['title' => $title, 'completed' => 0]
        );

        $id = $this->connection->lastInsertId();

        $stmt = $this->prepareExecuteStatement(
            "SELECT * FROM todo WHERE id = :id",
            ['id' => $id]
        );

        return $stmt->fetch();
    }

    public function setCompleted($id, $completed)
    {
        $this->prepareExecuteStatement(
            "UPDATE todo SET completed = :completed WHERE id = :id",
            ['id' => $id, 'completed' => $completed]
        );
        return true;
    }

    public function updateTodo($id, $title, $completed)
    {
        $this->prepareExecuteStatement(
            "UPDATE todo SET title = :title, completed = :completed WHERE id = :id",
            ['title' => $title, 'completed' => $completed, 'id' => $id]
        );

        $stmt = $this->prepareExecuteStatement("SELECT * FROM todo WHERE id = :id", ['id' => $id]);
        return $stmt->fetch();
    }

    public function deleteTodo($id)
    {
        $this->prepareExecuteStatement("DELETE FROM todo WHERE id = :id", ['id' => $id]);
        return true;
    }
}
