<?php
/**
 * Datenbankverbindung und Abfrage mit PDO
 *
 * Dieses Skript stellt eine Verbindung zur MySQL-Datenbank "test_db"
 * mithilfe von PDO (PHP Data Objects) her.
 *
 * Funktionen dieses Skripts:
 * - Aufbau einer sicheren PDO-Verbindung mit Fehlerbehandlung
 * - Abfrage aller Einträge aus der Tabelle "todo"
 * - Ausgabe der einzelnen Felder (uid, title, completed)
 * - Ausgabe des kompletten Datensatzes per var_dump (Debug-Zwecke)
 */

 
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
    var_dump($pdo);
} catch (\PDOException $e) {
    error_log("PDOException: " . $e->getMessage() . " in "
              . $e->getFile() . " on line " . $e->getLine());
}

$statement = $pdo->query("SELECT * FROM todo");
$todo_items = $statement->fetchAll();
foreach ($todo_items as $todo) {
    echo $todo['uid'] . "<br>";
    echo $todo['title'] . "<br>";
    echo $todo['completed'] . "<br>";
}

var_dump($todo_items);

?>