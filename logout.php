<?php
/**
 * Logout
 *
 * $Id: logout.php,v 1.1.1.1 2004/04/14 17:30:55 chris Exp $
 */

include_once("inc/config.inc.php");

session_start();

// Wird in diesem Script kein session_unregister() oder
// session_destroy() ausgefuehrt, bleiben die Daten erhalten!

if (isset($_GET["p"]) && $_GET["p"] == "home") {
    $weiterleitung = "http://" . $_SERVER["SERVER_NAME"] . "/home.html";
} else {
    session_destroy();
    $weiterleitung = "http://" . $_SERVER["SERVER_NAME"] . $sec_web . "index.php";
}

header("Location: " . $weiterleitung);
exit();
?>