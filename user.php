<?php
/**
 * Hole die Daten der Benutzer/Kunden aus der Datenbank
 *
 * TODO: Verwaltung der Variablen $s_auserok, damit nur bei Speichern der Daten
 *      nach MySQL die Tabelle neu geladen werden muss.
 *
 * $Id: user.php,v 1.1.1.1 2004/04/14 17:30:56 chris Exp $
 */
 
require_once("inc/config.inc.php");
require_once("inc/db_wmcdb.inc.php");
session_start();

if (!isset($_SESSION['s_login']) || ($_SESSION['s_login'] <> 1) || ($_SESSION['s_userName'] != "admin")) {
    session_destroy();
    header("Location: http://".$_SERVER["SERVER_NAME"].$sec_web."/index.php");
}

// Setzte Modus auf user
$_SESSION['s_mode'] = "user";

// Lade User Daten
if (!$_SESSION['s_auserok']) {
    // Array loeschen
    unset($_SESSION['s_user']);

    $db = new DB_Usermgmt;
    $db->connect();

    $sqlquery = " SELECT k.*, g.gid, g.bezeichnung "
            . "FROM kunde k "
            . "LEFT JOIN  kunde_gruppe_rel kg ON k.uid = kg.uid "
            . "LEFT JOIN gruppe g ON kg.gid = g.gid";

    $db->query( $sqlquery );
    $md = $db->metadata();

    $s_auser = array();
    $count = 0;
    while ( $db->next_record() ) {
        foreach ( $md as $ar ) {
            $key = $ar['name'];
            $value = $db->f($ar['name']);
            if ($value == "NULL") {
                $value = "";
            }
            $_SESSION['s_auser'][$count][$key] = $value;
        }
        $count++;
    }
    $_SESSION['s_auserok'] = true;
}

// Anzeige der Daten mit der Datei admin.php
header("Location: http://".$_SERVER["SERVER_NAME"].$sec_web."/admin.php");
?>