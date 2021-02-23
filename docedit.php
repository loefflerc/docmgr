<?php
/**
 * Bearbeitung der Berechtigungen an Dateien und Datei loeschen
 *
 * $Id: docedit.php,v 1.1.1.1 2004/04/14 17:30:54 chris Exp $
 */


session_start();
include_once("inc/config.inc.php");

if (!isset($_SESSION['s_login']) || ($_SESSION['s_login'] <> 1) || ($_SESSION['s_userName'] != "admin")) {
    session_destroy();
    header("Location: http://".$_SERVER["SERVER_NAME"].$sec_web."index.php");
}

// Dokument bearbeiten
if (isset($_GET["dokid"])) {
    $e_dokid = $_GET["dokid"];
} else if (isset($_POST["dokid"])) {
    $e_dokid = $_POST["dokid"];
} else {
    header("Location: http://".$_SERVER["SERVER_NAME"].$sec_web."admin.php");
    exit;
}

if (isset($_GET["mode"])) {
    $mode = $_GET["mode"];
} else if (isset($_POST["mode"])) {
    $mode = $_POST["mode"];
} else {
    print "<p class='error'>Fehler: m&ouml;glicher Angriff.<br /><a href='javascript:self.close()'>schliessen</a></p>";
    session_destroy();
    exit();
}
include_once("inc/functions.inc.php");
include_once("inc/template.inc.php");
include_once("inc/db_wmcdb.inc.php");

$q = new DB_Usermgmt;
$query1 = "SELECT * FROM dokument WHERE dokid = $e_dokid";
$q->query($query1);

$tpl = new Template();

$tpl->set_file(array(
        "dedit" => "tpl/little_win.tpl.html",
        "PAGESTYLES" => $stylesheet
));
$tpl->parse("pagecss","PAGESTYLES");


$html = "";

if ($mode == "del") {

    if ($q->next_record() || !$_GET["confirm"]) {

        $title = "Datei l&ouml;schen - best&auml;tigen";

        $html .= "<p>M&ouml;chten Sie die Datei ".$q->f("bezeichnung"). " l&ouml;schen?<br />";
        $html .= "Falls Berechtigungen f�r diese Datei existieren, werden diese auch gel&ouml;scht.<br />";
        $html .= "<a href='$self?dokid=$e_dokid&mode=del&confirm=1'>Ja</a>&nbsp;&nbsp;<a href='$self?dokid=$e_dokid&mode=del&cancel=1'>Nein</a></p>";
    } else {
        $title = "Fehler";
        $html .= "<p>Ein Fehler ist aufgetreten: Die Datei existiert nicht.<br /><a href='#' onclick='self.close();'>schliessen</a></p>";
    }

    if ( isset($_GET["confirm"])) {

        $title = "Datei wird gel&ouml;scht";
        $name = $q->f("bezeichnung");
        $pfad = $q->f("pfad");

        $query3 = " DELETE FROM dokument WHERE dokid = $e_dokid ";
        $query4 = " DELETE FROM kunde_dok_rel WHERE dokid = $e_dokid ";
        $query5 = " DELETE FROM gruppe_dok_rel WHERE dokid = $e_dokid ";
        $q->query($query3);
        $q->query($query4);
        $q->query($query5);
        $filename = $d_root.$pfad;
        if (unlink($filename)) {
            $html .= "<p>L&ouml;schen von $filename war erfolgreich.<br />";
        } else {
            $html .= "<p>L&ouml;schen von $filename ist fehlgeschlagen.<br />";
        }

        $html .= "Datei $name aus Datenbank gel&ouml;scht...<br /><a href='#' onclick='opener.location.reload();self.close();'>schliessen</a></p>";
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
    $title = "Berechtigungen bearbeiten";

    // update kunde_dok_rel
    if (isset($_POST["f_kperms"]) && strchr($_POST["submit_add_knd"], ">")) {
        $query = "DELETE FROM kunde_dok_rel WHERE dokid = $e_dokid AND uid = ".$_POST["f_kperms"];
//            $debug = $query;
        $q->query($query);
    } else if (isset($_POST["f_klist"]) && strchr($_POST["submit_add_knd"], "<")) {
        $query = "INSERT INTO kunde_dok_rel (dokid, uid) VALUES ($e_dokid, ".$_POST["f_klist"].")";
//            $debug = $query;
        $q->query($query);
    }

    // update gruppe_dok_rel
    if (isset($_POST["f_gperms"]) && strchr($_POST["submit_add_grp"], ">")) {
        $query = "DELETE FROM gruppe_dok_rel WHERE dokid = $e_dokid AND gid = ".$_POST["f_gperms"];
//            $debug = $query;
        $q->query($query);
    } else if (isset($_POST["f_glist"]) && strchr($_POST["submit_add_grp"], "<")) {
        $query = "INSERT INTO gruppe_dok_rel (dokid, gid) VALUES ($e_dokid, ".$_POST["f_glist"].")";
//            $debug = $query;
        $q->query($query);
    }

    // Dokumenteigenschaften �ndern
    if (isset($_POST["submit"]) && $_POST["submit"] == "Speichern" && $_POST["f_dokname"] != "" ) {
        $vers = str_replace("'", "", $_POST['f_version']);
        $query = "UPDATE dokument SET dokname = '".$_POST["f_dokname"]."', version = '$vers',
            bemerkung = '". $_POST["f_bemerkung"]."', ist_neu = ".$_POST['f_neu']
                ." WHERE dokid = $e_dokid";
        $q->query($query);
//            $debug = "Dateieigenschaften wurden gespeichert";
    }

    // read kunde_dok_rel - Berechtigunen
    $query = "SELECT  kd.uid AS id, CONCAT(k.Nachname, ', ', k.Vorname) AS name FROM kunde_dok_rel kd LEFT JOIN kunde k ON kd.uid = k.uid WHERE dokid = $e_dokid";
    $q->query($query);
    $html_pknd = "";
    $ar_kd = array();
    while($q->next_record()) {
        $html_pknd .= "<option value='".$q->f("id")."'>".$q->f("name")."</option>\n";
        $ar_kd[] = $q->f("id");
    }

    // read gruppe_dok_rel - Berechtigunen
    $query = "SELECT  gd.gid AS id, g.bezeichnung FROM gruppe_dok_rel gd LEFT JOIN gruppe g ON gd.gid = g.gid WHERE dokid = $e_dokid";
    $q->query($query);
    $html_pgrp = "";
    $ar_gr = array();
    while($q->next_record()) {
        $html_pgrp .= "<option value='".$q->f("id")."'>".$q->f("bezeichnung")."</option>\n";
        $ar_gr[] = $q->f("id");
    }

    // read kunde
    $query = "SELECT uid, CONCAT(Nachname, ', ', Vorname) AS name FROM kunde";
    $q->query($query);
    $html_kunde = "";
    while($q->next_record()) {
        if ($q->f("name")== "") continue;
        if (in_array( $q->f("uid"), $ar_kd )) continue;
        $html_kunde .= "<option value='".$q->f("uid")."'>".$q->f("name")."</option>\n";
    }

    // read gruppe
    $query = "SELECT gid, bezeichnung AS name FROM gruppe";
    $q->query($query);
    $html_gruppe = "";
    while($q->next_record()) {
        if ($q->f("name")== "") continue;
        if (in_array($q->f("gid"), $ar_gr )) continue;
        $html_gruppe .= "<option value='".$q->f("gid")."'>".$q->f("name")."</option>\n";
    }

    // Formular f�r Bearbeitung
    $tpl->set_file(array("form" => "tpl/editdocForm.tpl.html"));
    $tpl->parse("content","form");

    // Dokumentdaten lesen
    $query = "SELECT * FROM dokument WHERE dokid = $e_dokid";
    $q->query($query);
    $q->next_record();
    /*
        ob_start();
        phpinfo();
        $out = ob_get_contents();
        ob_end_clean();
    */
//        $debug .= $out;

    $tpl->set_var(array(
            "action" => $self,
            "id"    => $e_dokid,
            "file"  => $q->f("pfad"),
            "dokname" => $q->f("dokname"),
            "version" => htmlentities( $q->f("version") ),
            "bemerkung" => htmlentities( $q->f("bemerkung") ),
            "neu_ja"    => ($q->f("ist_neu") ? ' checked="checked"' : ''),
            "neu_nein"  => ($q->f("ist_neu") ? '' : ' checked="checked"'),
            "kperms" => $html_pknd,
            "gperms" => $html_pgrp,
            "klist" => $html_kunde,
            "glist" => $html_gruppe  //,
//                        "debug" => $debug
    ));
} // end mode edit


$tpl->set_var(array(
        "title" => $title,
        "body"  => $html
));
$tpl->parse("result","dedit");
$tpl->p("result");
?>