<?php
/**
 * $Id: alldocs.php,v 1.1.1.1 2004/04/14 17:30:54 chris Exp $
 *
 * Hole die Daten der Dateien aus der Datenbank
 *
 */

require_once("inc/config.inc.php");
require_once("inc/db_wmcdb.inc.php");

$sort_order = array(
    "name"  => "dokname",
    "time"  => "erstellt DESC"
);

// Lade Dokument Daten
$db = new DB_Usermgmt;
$db->connect();

$sql = "SELECT * FROM dokument ORDER BY ".$sort_order[$sort];

$db->query($sql);
$md = $db->metadata();

$a_docs = array();
$count = 0;
while ( $db->next_record() ) {
    foreach( $md as $ar) {
        $key = $ar["name"];
        $value = $db->f( $ar["name"] );
        if ($value == NULL) $value = "- ";
        $a_docs[$count][$key] = $value;
    }
    $count++;
}

// Rechte der Kunden (Benutzer)
$sql = "SELECT kd.dokid, CONCAT(k.Nachname, ', ', LEFT(k.Vorname,1),'.') AS name FROM kunde_dok_rel kd"
        ." LEFT JOIN kunde k ON kd.uid = k.uid";
$db->query( $sql );
$ak_rights = array();
while ( $db->next_record() ) {
//        if (in_array($row[1],$ak_rights)) continue;
    $ak_rights[]= array(
            "dokid" => $db->f("dokid"),
            "kd" => $db->f("name")
    );
}

// Rechte der Gruppen
$sql = "SELECT gd.dokid, g.bezeichnung FROM gruppe_dok_rel gd"
        ." LEFT JOIN gruppe g ON gd.gid = g.gid";
$db->query( $sql );
$ag_rights = array();
while ( $db->next_record() ) {
    // if (in_array($row[1],$ag_rights)) continue;
    $ag_rights[] = array(
            "dokid" => $db->f("dokid"),
            "gr" => $db->f("bezeichnung")
    );
}



?>