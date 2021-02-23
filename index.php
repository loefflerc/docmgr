<?php

/**
 *  $Id: index.php,v 1.1.1.1 2004/04/14 17:30:55 chris Exp $
 */

set_include_path(realpath(dirname(__FILE__)) . PATH_SEPARATOR . get_include_path());

include_once("inc/config.inc.php");
include_once("inc/template.inc.php");

$out = '';  // debug variable

if (isset($_POST['submit'])) {

    // Hier _alle_ Variablen &uuml;berpr&uuml;fen!!!
    $valid = true;

    if (!isset($f_user) && (isset($_GET['f_user']) || isset($_POST['f_user']))) {
        if (isset($_GET['f_user'])) {
            $f_user = $_GET['f_user'];
        } else {
            $f_user = $_POST['f_user'];
        }
    } else if (!isset($f_user)) {
        $f_user = '';
    }

    if (!isset($f_pass) && (isset($_GET['f_pass']) || isset($_POST['f_pass']))) {
        if (isset($_GET['f_pass'])) {
            $f_pass = $_GET['f_pass'];
        } else {
            $f_pass = $_POST['f_pass'];
        }
    } else if (!isset($f_pass)) {
        $f_pass = '';
    }

    if ( strlen($f_user) > 15 || strlen($f_user) == 0 ) {
        $valid = false;
    }
    if (preg_match("/\'/",$f_user) ||  preg_match("/\'/",$f_pass)) {
        $valid = false;
    }
    if ( strlen($f_pass) > 12 || strlen($f_pass) == 0 ) {
        $valid = false;
    }

    if (!$valid) {
        unset($submit);
        header("Location: http://".$_SERVER["SERVER_NAME"].$sec_web."/index.php");
        exit;
    }
    // Falls nicht vorhanden, generiert eine Session(datei)
    // auf dem Server.
    // Falls bereits vorhanden, liest Sessiondaten wieder ein und
    // (re-)initialisiert die gespeicherten Variablen.

    session_start();

    include_once("inc/db_wmcdb.inc.php");
    $db = new DB_Usermgmt;

    $db->connect();
    $salt = substr( $f_pass, 0, 2 );
    $sqlquery = " SELECT uid,username,nachname,anrede FROM kunde WHERE username LIKE '".$f_user."'"
            ." AND passwort LIKE '".crypt( $f_pass, $salt ) ."'";

    $db->query( $sqlquery );

    if ($db->nf() == 1) {
        $db->next_record();
        if (!isset($_SESSION['s_login'])) $_SESSION['s_login'] = 1;
        if (!isset($_SESSION['s_userName'])) $_SESSION['s_userName'] = $f_user;
        if (!isset($_SESSION['s_uid'])) $_SESSION['s_uid'] = $db->f("uid");
        if (!isset($_SESSION['s_name'])) $_SESSION['s_name'] = $db->f("anrede")." ".$db->f("nachname");
        $db->free();

        //Code fuer Admin-Gruppe einfuegen
        if ($_SESSION['s_userName'] != "admin") { 
            $weiterleitung = "seite2.php";
        } else {
            $weiterleitung = "admin.php";
        }
    } else {
        session_destroy();
        $weiterleitung = "index.php";
    }

    // Weiterleitung
    header ("Location: http://".$_SERVER["SERVER_NAME"].$sec_web.$weiterleitung);
    exit();
} else {
    @session_start();

    if ( isset($_SESSION['s_login']) && $_SESSION['s_login'] == 1 ) {
        header("Location: http://".$_SERVER["SERVER_NAME"].$sec_web."seite2.php");
    } else {
        @session_start();
        session_destroy();

        // generate debug info
        /*        ob_start();
        print "Login: $f_user<br />Pass: $f_pass<br />";
        var_dump($link);
        $out = ob_get_contents();
        ob_end_clean();
         // end debug
        */
        $tpl = new Template();
        $tpl->set_file(array("index" => "tpl/vorlage.tpl.html",
                "PAGESTYLES" => $stylesheet,
                "MAIN" => "tpl/loginForm.tpl.html",
                "focusscript" => "tpl/focus.js.html"));

        $tpl->parse("login", "MAIN", true);
        $tpl->parse("pagecss", "PAGESTYLES", true);

        $tpl->set_var(array("ACTIONURL" => $_SERVER["PHP_SELF"],
                "TITLEADD" => "- Login",
                "MENU" => "&nbsp;",
                "PERSONNAME" => "Herzlich Willkommen",
                "debug" => $out,
                "COMPLOGO" => $complogo,
                "COMPNAME" => $compname,
                "home"  => "".$_SERVER['SERVER_NAME']."/"
        ));

        $tpl->parse("result",array("focusscript","login","pagecss","index"));
        $tpl->p("result");

    }	// end if isset($_SESSION['s_login'])
}
?>