<?php
/**
 * Hole die Daten der Gruppen aus der Datenbank
 *
 * $Id: allgroups.php,v 1.1.1.1 2004/04/14 17:30:53 chris Exp $
 */


require_once("inc/config.inc.php");
require_once("inc/db_wmcdb.inc.php");

$sort_order = array(
    "name"  => "bezeichnung",
    "time"  => "erstellt DESC"
);

// Lade Gruppen Daten

$db = new DB_Usermgmt;
$db->connect();

$sqlquery1 = "SELECT kg.gid, CONCAT(k.Nachname, ', ', LEFT(k.Vorname,1), '.') AS name "
        ."FROM kunde_gruppe_rel kg "
        ."LEFT JOIN kunde k ON kg.uid = k.uid";
$sqlquery2 = "SELECT * FROM gruppe ORDER BY ".$sort_order[$sort];

$db->query( $sqlquery1 );

$a_groupuser = array();
while ( $db->next_record() ) {
    if (array_key_exists($db->f("gid"), $a_groupuser) && strlen($a_groupuser[$db->f("gid")]) > 0) {
        $a_groupuser[$db->f("gid")] .= "; ";
    } else {
        $a_groupuser[$db->f("gid")] = '';
    }
    $a_groupuser[$db->f("gid")] .= $db->f("name");
}
$db->free();

$db->query( $sqlquery2 );
$md = $db->metadata();

$a_groups = array();
$count = 0;
while ( $db->next_record() ) {
    foreach( $md as $ar) {
        $key = $ar["name"];
        $value = $db->f( $ar["name"] );
        if ($value == NULL) $value = " - ";
        $a_groups[$count][$key] = $value;
    }
    if (array_key_exists($db->f("gid"), $a_groupuser)) {
        $a_groups[$count]["users"] = $a_groupuser[$db->f("gid")];
    } else {
        $a_groups[$count]["users"] = " - ";
    }
    $count++;
}

?>