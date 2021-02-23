<?php
/**
 * Falls nicht vorhanden, generiert eine Session(datei)
 * auf dem Server.
 * Falls bereits vorhanden, liest Sessiondaten wieder ein und
 * (re-)initialisiert die gespeicherten Variablen.
 *
 * $Id: seite2.php,v 1.1.1.1 2004/04/14 17:30:55 chris Exp $
 */

session_start();

require_once("inc/config.inc.php");
require_once("inc/db_wmcdb.inc.php");
require_once("inc/functions.inc.php");
include_once("inc/template.inc.php");

if ( !isset($_SESSION['s_login']) ) {
    header("Location: http://".$_SERVER["SERVER_NAME"].$sec_web."index.php");
} else if ( isset($_SESSION['s_userName']) && $_SESSION['s_userName'] == "admin" ) {
    header("Location: http://".$_SERVER["SERVER_NAME"].$sec_web."admin.php");
}


// Wird in diesem Script kein session_unregister() oder
// session_destroy() ausgefuehrt, bleiben die Daten erhalten!

if (!isset($_SESSION['s_docs'])) {
    $_SESSION['s_docs'] = '';

    $db = new DB_Usermgmt;
    $db->connect();

    // Rechte an Dateien holen
    $sqlquery = "SELECT r.dokid FROM kunde_dok_rel AS r "
        . " JOIN  dokument AS d "
        . " WHERE r.dokid = d.dokid AND r.uid=" . $_SESSION['s_uid'];
    // MySQL Version vor 3.23.17 unterstuetzen keine join_condition (ON)

    //$rs = mysql_query($sqlquery) or die ("Ungueltige Abfrage:<br>  $sqlquery<br>".mysql_error());
    $db->query( $sqlquery );

    // indices in array
    $arDokID = array();
    while ( $db->next_record() ) {
        $arDokID[] = $db->f("dokid");
    }
    $db->free();

    // evtuelle Gruppenzugehoerigkeit ermitteln
    if (isset($_SESSION['s_uid'])) {
        $sqlquery = sprintf("SELECT kg.uid, kg.gid, g.bezeichnung "
                ."FROM kunde_gruppe_rel kg "
                ."JOIN gruppe g "
                ."WHERE kg.gid = g.gid "
                ."AND uid=%s", $_SESSION['s_uid']);
        $db->query( $sqlquery );
        if ( $db->nf() > 0) {
            $_SESSION['s_gid'] = array();
            $gnum = 0;
            while ( $db->next_record() ) {
                $_SESSION['s_gid'][$gnum] = array (
                        "gid"                =>        $db->f("gid"),
                        "bezeichnung"        =>        $db->f("bezeichnung")
                );
                $gnum++;
            }        // end while
            $db->free();

            // Dateien der Gruppenberechtigung holen
            $sqlquery = "SELECT r.dokid "
                    . " FROM gruppe_dok_rel r "
                    . " LEFT JOIN dokument d "
                    . "   ON r.dokid = d.dokid ";

            $more = 0;
            foreach ($_SESSION['s_gid'] as $gruppe) {
                if ($more < 1 ) {
                    $sqlquery .= " WHERE r.gid='".$gruppe["gid"] ."'";
                } else {
                    $sqlquery .= " OR r.gid='".$gruppe["gid"] . "'";
                }
                $more++;
            }

            $db->query( $sqlquery );
            while ( $db->next_record() ) {
                $val = $db->f("dokid");
                if (!in_array( $val, $arDokID ))
                    $arDokID[] = $val;
            }
            $db->free();

        } // end if (mysql_num_rows($rs) > 0)
    } // end if (isset($s_uid))

    // documente sortiert holen

    // IN (...) bauen
    $strIN = "(";
    foreach( $arDokID as $val ) {
        $strIN .= $val . ",";
    }
    $strIN = substr( $strIN, 0, strlen( $strIN ) - 1 );
    $strIN .= ")";

    $sqlquery = "SELECT dokid, dokname, pfad, bemerkung, version, ist_neu FROM dokument "
            ." WHERE dokid IN ". $strIN
            ." ORDER BY dokname, bemerkung ";

    $db->query( $sqlquery );

    $_SESSION['s_docs'] = array();
    while ( $db->next_record() ) {
        $_SESSION['s_docs'][$db->f("dokid")] = array(
                "name"         => $db->f("dokname"),
                "url"          => $db->f("pfad"),
                "bemerkung"    => $db->f("bemerkung"),
                "version"      => $db->f("version"),
                "ist_neu"       => $db->f("ist_neu")
        );
    }

} // end if (session is registered("s_docs"))

$tpl = new Template();
$tpl->set_file( array (
        "seite"         => "tpl/vorlage.tpl.html",
        "PAGESTYLES"    => $stylesheet,
        "MENU"          => "tpl/menu_user.tpl.html",
        "MAIN"          => "tpl/dok.tpl.html"
));
$tpl->parse("umenu", "MENU", true);
$tpl->parse("pagecss", "PAGESTYLES", true);


// Gibt den Inhalt der wiederhergestellten Variablen aus.
$group = '';
if (isset($_SESSION['s_gid'])) {
    foreach ($_SESSION['s_gid'] as $gruppe) {
        $group .= sprintf ("GruppenID: %s<br>",$gruppe["gid"]);
        $group .= sprintf ("Gruppenname: %s<br>",$gruppe["bezeichnung"]);
    }
}


// Variable definieren
$tpl->set_block("MAIN","dok","doks");
if (isset($_SESSION['s_docs']) && is_array($_SESSION['s_docs']) ) {

    $img_neu = '<div class="docimgneu"><img align="middle" src="img/neu.png" title="NEU" /></div>';

    foreach ($_SESSION['s_docs'] as $num => $doc) {

        $groesse = sprintf("Gr&ouml;sse: %d kByte", filesize($d_root.$doc["url"])/1024 );
        $endung = getSuffix($d_root.$doc["url"]);
        $icon = getFileIcon($endung);
        if ($doc["version"] != "") {
            $version_html = '<span class="version">Version: '
                    .$doc["version"].'</span><br>';
        } else {
            $version_html = "";
        }
        if ($doc["bemerkung"] != "") {
            $bemerkung_html = '<span style="margin-left: 21px; margin-top: 0px;"><u>Bemerkung:</u> '
                    . $doc["bemerkung"] .'</span>';
        } else {
            $bemerkung_html = "";
        }

        $tpl->set_var( array(
                "icon" => $icon,
                "suffix" => $endung,
                "download_dok_url" => "http://".$_SERVER['SERVER_NAME'].$sec_web."sendpdf.php?id=".$num,
                "name_dok" => $doc["name"],
                "bemerkung" => $bemerkung_html,
                "version"   => $version_html,
                "groesse_dok" => $groesse,
                "dl_neu" => ($doc['ist_neu'] ? $img_neu : '')
                ) );
        $tpl->parse("doks","dok",true);
    }

}

$tpl->set_var(array(
        "TITLEADD" => "Ihre Dateien",
        "PERSONNAME" => "Willkommen, ".$_SESSION['s_name'],
        "userid" => $_SESSION['s_uid'],
        "username" => $_SESSION['s_userName'],
        "group" => $group,
        "SECWEB" => $sec_web,
        "COMPLOGO" => $complogo,
        "COMPNAME" => $compname
));
$tpl->parse("result",array("MAIN","umenu","seite"));
$tpl->p("result");
?>