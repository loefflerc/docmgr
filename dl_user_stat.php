<?php
/**
 *  Hole die Daten der Benutzer/Kunden aus der Datenbank
 *
 * $Id: dl_user_stat.php,v 1.1.1.1 2004/04/14 17:30:54 chris Exp $
 */

session_start();
$html = '';

include_once("inc/config.inc.php");
if (!isset($_SESSION['s_login']) || ($_SESSION['s_login'] <> 1) || ($_SESSION['s_userName'] != "admin")) {
    session_destroy();
    header("Location: http://".$_SERVER["SERVER_NAME"].$sec_web."index.php");
}

// Gruppe bearbeiten
if (isset($_GET["id"])) {
    $dokid = $_GET["id"];
} else {
    header("Location: http://".$_SERVER["SERVER_NAME"].$sec_web."admin.php");
    exit;
}

include_once("inc/functions.inc.php");
include_once("inc/template.inc.php");
include_once("inc/db_wmcdb.inc.php");

$q = new DB_Usermgmt;
$query1 = " SELECT dokname, dokzeit, bemerkung "
        ."FROM statistics WHERE dokname = '$dokid'";
$q->query($query1);
if ($q->next_record()) {
    $title = "Statistik f&uuml;r ".$q->f("dokname") ;

    $tpl = new Template();

    $tpl->set_file(array(
        "stats" => "tpl/little_win.tpl.html",
        "PAGESTYLES" => $stylesheet,
        "form" => "tpl/statistik2.tpl.html"
    ));

    $tpl->parse("pagecss","PAGESTYLES");
    $tpl->parse("content","form");

    $tpl->set_var(array(
        "dokname" => $q->f("dokname"),
        "bemerkung" => $q->f("bemerkung"),
        "erstellt" => formatSQLDate($q->f("dokzeit"))
    ));

    $tpl->set_block("form","docstat","docstats");
    $query = " SELECT s.zeit, s.version, CONCAT(k.Nachname, ',&nbsp;',k.Vorname) as name, host, browser FROM statistics s "
            ." LEFT JOIN kunde k ON s.uid = k.uid WHERE dokname = '$dokid' ORDER BY s.zeit DESC";
    $q->query($query);
    while ($q->next_record()) {
        $tpl->set_var(array(
            "zeit" => formatSQLDate($q->f("zeit")),
            "version" => ( $q->f("version") != "" ) ? htmlentities($q->f("version")) : " - " ,
            "name" => $q->f("name"),
            "host" => $q->f("host"),
            "browser" => str_replace( ' ', '&nbsp;', $q->f("browser") )
        ));
        $tpl->parse("docstats","docstat",true);
    }
}

$tpl->set_var(array(
    "title" => $title,
    "body"  => $html
));
$tpl->parse("result","stats");
$tpl->p("result");
?>