<?php

// Zugangsdaten fuer die Datenbank
// Diese sollten der Sicherheit halber
// in ein Verzeichnis außerhalb des
// Document-Root ausgelagert werden.

$host     =    "localhost";
$user     =    "user";
$pass     =    "demo_password";

$datab    =    "demo_db";
$table    =    "counter";

// Verbindung zum MySQL-Server aufbauen
$db = @mysql_connect($host,$user,$pass);

if ($db) {
     if (@mysql_select_db($datab,$db)) {
          // Eintrag fuer die per GET uebergebene URL um 1 erhoehen.
          $query = "UPDATE $table SET count = count + 1 WHERE url = '$url'";
          $result = @mysql_query($query);
     }
}

// Auf uebergebene URL weiterleiten
Header("Location: ".$url);

?>