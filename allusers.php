<?php
/**
 * Hole die Daten der Benutzer/Kunden aus der Datenbank
 *
 * $Id: allusers.php,v 1.1.1.1 2004/04/14 17:30:54 chris Exp $
 */

include_once("inc/config.inc.php");
require_once("inc/db_wmcdb.inc.php");

$sort_order = array(
    "name"  =>  "Nachname",
    "vorname" => "Vorname",
    "time"  =>  "erstellt DESC",
    "bemerk" => "bemerkung"
);

// Lade User Daten
$db = new DB_Usermgmt;
$db->connect();

$sqlquery = " SELECT uid, username, passwort, Nachname, Vorname, anrede, email, bemerkung, erstellt "
        . " FROM kunde ORDER BY ".$sort_order[$sort];
$sqlqueryid = "SELECT kg.uid, g.bezeichnung "
        ."FROM  kunde_gruppe_rel kg "
        ."LEFT JOIN gruppe g ON kg.gid = g.gid";

$db->query( $sqlqueryid );

$a_usergroup = array();
while ( $db->next_record() ) {
    if (array_key_exists($db->f(0), $a_usergroup) && strlen($a_usergroup[$db->f(0)]) > 0) {
        $a_usergroup[$db->f(0)] .= ", ";
    } else {
        $a_usergroup[$db->f(0)] = '';
    }
    $a_usergroup[$db->f(0)] .= $db->f(1);
}
$db->free();

$db->query( $sqlquery );
$md = $db->metadata();

$a_user = array();
$count = 0;
while ( $db->next_record() ) {
    foreach( $md as $ar) {
        $key = $ar["name"];
        $value = $db->f( $ar["name"] );
        if ($value == NULL) {
            $value = " - ";
        }
        $a_user[$count][$key] = $value;
    }
    if (array_key_exists($db->f("uid"), $a_usergroup)) {
        $a_user[$count]["groups"] = $a_usergroup[$db->f("uid")];
    } else {
        $a_user[$count]["groups"] = " - ";
    }
    $count++;
}

?>