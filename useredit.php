<?php
/**
 * Hole die Daten der Benutzer/Kunden aus der Datenbank
 *
 * TODO: Verwaltung der Variablen $s_auserok, damit nur bei Speichern der Daten
 * nach MySQL die Tabelle neu geladen werden muss.
 *
 * $Id: useredit.php,v 1.1.1.1 2004/04/14 17:30:56 chris Exp $
 */

session_start();

include_once("inc/config.inc.php");
if (!isset($_SESSION["s_login"]) || ($_SESSION['s_login'] <> 1) || ($_SESSION['s_userName'] != "admin")) {
    session_destroy();
    header("Location: http://".$_SERVER["SERVER_NAME"].$sec_web."index.php");
}

// Benutzer bearbeiten
if (isset($_GET["id"]) && isset($_GET["mode"])) {
    $e_uid = $_GET["id"];
    $mode = $_GET["mode"];
} else if (isset($_POST["f_uid"]) && isset($_POST["f_mode"])) {
    $e_uid = $_POST["f_uid"];
    $mode = $_POST["f_mode"];
} else {
    header("Location: http://".$_SERVER["SERVER_NAME"].$sec_web."admin.php");
    exit;
}

include_once("inc/functions.inc.php");
include_once("inc/template.inc.php");
include_once("inc/db_wmcdb.inc.php");

$q = new DB_Usermgmt;

$tpl = new Template();

$tpl->set_file(array(
        "uedit" => "tpl/little_win.tpl.html",
        "PAGESTYLES" => $stylesheet
));
$tpl->parse("pagecss","PAGESTYLES", true);

$html = "";

if ($mode == "edit") {

    // Update der Benutzerdaten
    if (isset($e_uid) && isset($_POST["submit"]) && $_POST["submit"] == "Speichern") {
        $title = "Daten werden gespeichert";

        $bemerk = addslashes(trim($_POST["f_bemerkung"]));
        if (strlen($bemerk) > 255) {
            $bemerk = substr($bemerk,0,255);
        }

        // Tabelle kunde
        $query = "UPDATE kunde SET"
                ." Nachname = '".$_POST["f_nachname"]."',"
                ." Vorname = '".$_POST["f_vorname"]."',"
                ." anrede = ".$_POST["f_anrede"].","
                ." email = '".$_POST["f_email"]."',"
                ." bemerkung = '".$bemerk."',"
                ." username = '".$_POST["f_username"]."'";
        if (!empty($_POST["f_passwort"]) && $_POST["f_passwort"] == $_POST["f_passwort2"]) {
            $salt = substr($_POST["f_passwort"], 0, 2);
            $query .= ", passwort = '".crypt( $_POST["f_passwort"], $salt )."'";
        }
        $query .= " WHERE uid = $e_uid";
        $html .= "Kundendaten wurden aktualisiert...<br>";
        // Update kunde
        $q->query($query);

        // Gruppenzugehoerigkeit abspeichern
        $query = "DELETE FROM kunde_gruppe_rel WHERE uid = $e_uid";
        $q->query($query);
        if (is_array($_POST["f_selgroups"])) {
            while (list($key,$group) = each($_POST["f_selgroups"])) {
                $query = "INSERT INTO kunde_gruppe_rel (gid, uid) VALUES ($group, $e_uid)";
                $html .= "Verkn&uuml;pfungen wurden aktualisiert...<br>";
                $q->query($query);
            }
        }
    } else {

        $title = "Kundendaten bearbeiten";
    }

    $tpl->set_file(array("form" => "tpl/edituserForm.tpl.html"));
    $tpl->parse("content","form");

    if (!isset($kg_rel)) $kg_rel = array();
    $query1 = "SELECT gid FROM kunde_gruppe_rel WHERE uid = $e_uid";
    $q->query($query1);
    while ($q->next_record()) {
        array_push( $kg_rel, $q->f("gid") );
    }

    $query2 = "SELECT gid, bezeichnung FROM gruppe";
    $q->query($query2);
    $sel_groups = "";
    while($q->next_record()) {
        $sel_groups .= "<option value='".$q->f("gid")."'";
        $sel_groups .= (in_array($q->f("gid"), $kg_rel) ? " selected" : "");
        $sel_groups .=">".$q->f("bezeichnung")."</option>\n";
    }

    $query3 = "SELECT * FROM kunde WHERE uid = $e_uid";
    $q->query($query3);

    $q->next_record();
    $select_anrede = "sel_anrede".$q->f("anrede");

    $tpl->set_var(array(
            "formaction" => $self,
            "mode"  => "edit",
            "uid"   => $e_uid,
            "nachname" => $q->f("Nachname"),
            "vorname" => $q->f("Vorname"),
            "email" => $q->f("email"),
            "bemerkung" => $q->f("bemerkung"),
            "username" => $q->f("username"),
            "username" => $q->f("username"),
            $select_anrede => " selected",
            "select_groups" => $sel_groups
    ));

} // end mode del

if ($mode == "del") {
    $title = "Benutzer l&ouml;schen";

    $query = "SELECT CONCAT(Vorname, ' ', Nachname) AS name FROM kunde WHERE uid = $e_uid";
    $q->query($query);
    if ($q->nf() == 1) {
        $q->next_record();
        $luser = $q->f("name");
        $html .= "<p>M&ouml;chten Sie <b>$luser</b> l&ouml;schen?<br>";
        $html .= "<a href='$self?id=$e_uid&mode=del&confirm=1'>Ja</a>&nbsp;&nbsp;<a href='$self?id=$e_uid&mode=del&cancel=1'>Nein</a></p>";
    } else {
        $title = "Fehler";
        $html .= "<p>Ein Fehler ist aufgetreten: Der Benutzer existiert nicht.<br><a href='#' onclick='self.close();'>schliessen</a></p>";
    }

    if (isset($_GET["confirm"])) {

        $title = "Benutzer wird gel&ouml;scht";

        $query3 = "DELETE FROM kunde WHERE uid = $e_uid";
        $query4 = "DELETE FROM kunde_gruppe_rel WHERE uid = $e_uid";
        $q->query($query3);
        $q->query($query4);

        $html = "<p>Benutzer $luser gel&ouml;scht...<br><a href='#' onclick='opener.location.reload();self.close();'>schliessen</a></p>";
    }

    if (isset($_GET["cancel"])) {
        $title = "Fenster schliessen";
        $html .= '<script language="JavaScript" type="text/javascript">
            <!--
            self.close();
            //-->
            </script>';
    }
} // end mode del


$tpl->set_var(array(
        "title" => $title,
        "body"  => $html
));
$tpl->parse("result",array("pagecss","content","uedit"));
$tpl->p("result");
?>
