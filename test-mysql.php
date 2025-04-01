<?php
$mysqli = new mysqli("localhost", "alexjung", "links234", "test_db");
 
if ($mysqli->connect_error) {
    die("Verbindung fehlgeschlagen: " . $mysqli->connect_error);
}
 
echo "Erfolgreich verbunden!";
?>