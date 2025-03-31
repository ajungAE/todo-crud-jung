<?php
$mysqli = new mysqli("localhost", "alexjung", "Kostia17", "test_db");
 
if ($mysqli->connect_error) {
    die("Verbindung fehlgeschlagen: " . $mysqli->connect_error);
}
 
echo "Erfolgreich verbunden!";
?>