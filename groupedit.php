<?php
/**
 *  Hole die Daten der Benutzer/Kunden aus der Datenbank
 *
 * $Id: groupedit.php,v 1.1.1.1 2004/04/14 17:30:55 chris Exp $
 *
 *  TODO: Verwaltung der Variablen $s_auserok, damit nur bei Speichern der Daten
 *       nach MySQL die Tabelle neu geladen werden muss.
 */

include_once("inc/config.inc.php");
session_start();

if (!isset($_SESSION['s_login']) || ($_SESSION['s_login'] <> 1) || ($_SESSION['s_userName'] != "admin")) {
    session_destroy();
    header("Location: http://" . $_SERVER["SERVER_NAME"] . $sec_web . "/index.php");
}

// Gruppe bearbeiten
if (isset($_GET["gid"])) {
    $e_gid = $_GET["gid"];
} else if (isset($_POST["gid"])) {
    $e_gid = $_POST["gid"];
} else {
    header("Location: http://" . $_SERVER["SERVER_NAME"] . $sec_web . "/admin.php");
    exit;
}

if (isset($_GET["mode"])) {
    $mode = $_GET["mode"];
} else if (isset($_POST["mode"])) {
    $mode = $_POST["mode"];
} else {
    print "<p class='error'>Fehler: m&ouml;glicher Angriff.<br><a href='javascript:self.close()'>schliessen</a></p>";
    session_destroy();
    exit();
}
include_once("inc/functions.inc.php");
include_once("inc/template.inc.php");
include_once("inc/db_wmcdb.inc.php");

$q = new DB_Usermgmt;
$query1 = "SELECT * FROM gruppe WHERE gid = $e_gid";
$q->query($query1);

$tpl = new Template();

$tpl->set_file(array(
        "gedit" => "tpl/little_win.tpl.html",
        "PAGESTYLES" => $stylesheet
));
$tpl->parse("pagecss","PAGESTYLES");

$html = "";

if ($mode == "del") {

    if ($q->next_record() || !$_GET["confirm"]) {

        $title = "Gruppe l&ouml;schen - best&auml;tigen";

        $html .= "<p>M&ouml;chten Sie die Gruppe " . $q->f("bezeichnung") . " l&ouml;schen?<br>";
        $query2 = "SELECT * FROM kunde_gruppe_rel WHERE gid = $e_gid";
        $q->query($query2);
        if ($q->num_rows() > 0) $html .= "Dieser Gruppe sind Kunden zugeordnet.<br>";
        $html .= "<a href='$self?gid=$e_gid&mode=del&confirm=1'>Ja</a>&nbsp;&nbsp;<a
            href='$self?gid=$e_gid&mode=del&cancel=1'>Nein</a>";
    } else {
        $title = "Fehler";
        $html .= "<p>Ein Fehler ist aufgetreten: Die Gruppe existiert nicht.<br><a
            href='#' onclick='self.close();'>schliessen</a></p>";
    }

    if (isset($_GET["confirm"])) {

        $title = "Gruppe wird gel&ouml;scht";

        $query3 = " DELETE FROM gruppe WHERE gid = $e_gid ";
        $query4 = " DELETE FROM kunde_gruppe_rel WHERE gid = $e_gid ";
        $query5 = " DELETE FROM gruppe_dok_rel WHERE gid = $e_gid ";
        $q->query( $query3 );
        $q->query( $query4 );
        $q->query( $query5 );

        $html = "<p>Gruppe " . $q->f("bezeichnung") . " gel&ouml;scht...<br><a
            href='#' onclick='opener.location.reload();self.close();'>schliessen</a></p>";
    }

    if (isset($_GET["cancel"])) {
        $title = "Fenster schliessen";
        $html .= '<script language="JavaScript" type="text/javascript"><!--
self.close();
//-->
</script>';
    }
} // end mode del

if ($mode == "edit") {
    $title = 'Gruppen bearbeiten';

    // update kunde_gruppe_rel
    if (isset($_POST["f_members"]) && strchr($_POST["submit_add"], ">")) {
        $query = "DELETE FROM kunde_gruppe_rel WHERE gid = $e_gid AND uid = ".$_POST["f_members"];
//            $debug = $query;
        $q->query($query);
    } else if (isset($_POST["f_users"]) && strchr($_POST["submit_add"], "<")) {
        $query = "INSERT INTO kunde_gruppe_rel (gid, uid) VALUES ($e_gid, ".$_POST["f_users"].")";
//            $debug = $query;
        $q->query($query);
    }

    if (isset($_POST["submit"]) && $_POST["submit"] == "Speichern" && $_POST["f_bemerkung"] != "" ) {
        $query = "UPDATE gruppe SET bemerkung = '". $_POST["f_bemerkung"]."' WHERE gid = $e_gid";
        $q->query($query);
//            $debug = "Bemerkungen wurden gespeichert";
    }



    // read kunde_gruppe_rel
    $query = "SELECT  kg.uid AS id, CONCAT(k.Nachname, ', ', k.Vorname) AS name"
        . " FROM kunde_gruppe_rel kg"
        . " LEFT JOIN kunde k ON kg.uid = k.uid WHERE gid = $e_gid";
    $q->query($query);
    $html_grp = "";
    $ar_lst = array();
    while ($q->next_record()) {
        $html_grp .= "<option value='" . $q->f("id") . "'>" . $q->f("name") . "</option>\n";
        $ar_lst[] = $q->f("id");
    }

    $query = "SELECT uid, CONCAT(Nachname, ', ', Vorname) AS name FROM kunde";
    $q->query($query);
    $html_kunde = "";
    while ($q->next_record()) {
        if ($q->f("name") == "") continue;
        if ( in_array( $q->f("uid"), $ar_lst ) ) continue;
        $html_kunde .= "<option value='" . $q->f("uid") . "'>" . $q->f("name") . "</option>\n";
    }


    $tpl->set_file(array("form" => "tpl/editgroupForm.tpl.html"));
    $tpl->parse("content","form");

    $query = "SELECT * FROM gruppe WHERE gid = $e_gid";
    $q->query($query);
    $q->next_record();
    /*
        ob_start();
        phpinfo();
        $out = ob_get_contents();
        ob_end_clean();

        $debug .= "<br><br>".$out;
    */
    $tpl->set_var(array(
            "action" => $self,
            "id"    => $e_gid,
            "bezeichnung" => $q->f("bezeichnung"),
            "bemerkung" => $q->f("bemerkung"),
            "members" => $html_grp,
            "users" => $html_kunde //,
//                        "debug" => $debug
    ));
} // end mode edit


$tpl->set_var(array(
        "title" => $title,
        "body"  => $html
));
$tpl->parse("result","gedit");
$tpl->p("result");
?>S