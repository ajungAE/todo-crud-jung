<?php
/**
 * Verbindungstest zur MySQL-Datenbank
 *
 * Dieses Skript stellt eine Verbindung zur MySQL-Datenbank "test_db" her
 * und überprüft, ob die Verbindung erfolgreich war.
 *
 * Es verwendet die objektorientierte MySQLi-Erweiterung.
 * Bei erfolgreicher Verbindung wird eine entsprechende Erfolgsmeldung ausgegeben,
 * andernfalls wird eine Fehlermeldung angezeigt und das Skript beendet.
 *
 */
$mysqli = new mysqli("localhost", "alexjung", "links234", "test_db");
 
if ($mysqli->connect_error) {
    die("Verbindung fehlgeschlagen: " . $mysqli->connect_error);
}
 
echo "Erfolgreich verbunden!";
?>