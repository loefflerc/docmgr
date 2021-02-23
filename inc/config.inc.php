<?php
/**
 * Einstellungen
 *
 * $Id: config.inc.php,v 1.1.1.1 2004/04/14 17:31:01 chris Exp $
 */

// Datenbankeinstellungen
define('DB_HOST', 'localhost');
define('DB_NAME', 'docmgr');
define('DB_USER', 'root');
define('DB_PASS', 'markoff');

// minimale Laenge des Passworts
$minpasswd_length = 4;
$maxpasswd_length = 12;

// Site Data
$mailWebmaster = 'webadmin@eq-system.de';

// Logo fuer Titelzeile
$complogo = '/images/eq-system.png';
$compname = 'EQ-System GmbH';

// Stylesheet Datei
$stylesheet = 'inc/styles.css';
$bgcolor = '';

// gibt den Speicherplatz an, der fuer die Downloadseite vereinbart wurde.
// z.B. die Groesse des nutzbaren Plattenspeichers des Hosting-Angebots
// in Megabyte
$disk_total = 300;

// geschuetzter Bereich
// Verzeichnis ausgehend von DocumentRoot
$sec_web = DIRECTORY_SEPARATOR . "support" . DIRECTORY_SEPARATOR;
// Verzeichnis im Dateisystem
$sec_root = $_SERVER["DOCUMENT_ROOT"] . $sec_web;

// Pfad zu den Dateien
// d_root ist ein Verzeichnis oberhalb (ausserhalb) des DocumentRoot
$pos = strrpos($_SERVER["DOCUMENT_ROOT"], DIRECTORY_SEPARATOR);
$d_root = substr($_SERVER["DOCUMENT_ROOT"],0,$pos) . DIRECTORY_SEPARATOR
    . "test_docmgr" . DIRECTORY_SEPARATOR;
$self = $_SERVER["PHP_SELF"];

?>