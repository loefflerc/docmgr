<?php
/**
 * $Id: admin.php,v 1.1.1.1 2004/04/14 17:30:53 chris Exp $
 *
 * Falls nicht vorhanden, generiert eine Session(datei) auf dem Server.
 * Falls bereits vorhanden, liest Sessiondaten wieder ein und (re-)initialisiert
 * die gespeicherten Variablen.
 */


session_start();
include_once("inc/config.inc.php");

if (!isset($_SESSION['s_login']) || $_SESSION['s_userName'] != "admin") {
    session_destroy();
    header("Location: http://" . $_SERVER["SERVER_NAME"] . $sec_web . "index.php");
}

if (!isset($_SESSION['s_auserok'])) {
    $_SESSION['s_auserok'] = false;
}

if (!isset($_GET["mode"])) {
    $mode = "menu";
} else {
    $mode = $_GET["mode"];
}

if (!isset($_GET["sort"])) {
    $sort = "name";
} else {
    $sort = $_GET["sort"];
    $sort = str_replace("'", "", $sort);
    $sort = stripslashes($sort);
    $sort = str_replace('"', "", $sort);
}

include_once("inc/functions.inc.php");
include_once("inc/template.inc.php");
include_once("inc/db_wmcdb.inc.php");

$tpl = new Template();

$tpl->set_file(array(
        "admin" => "tpl/vorlage.tpl.html",
        "PAGESTYLES" => $stylesheet,
        "MENU"  => "tpl/menu_admin.tpl.html"));
$tpl->parse("amenu","MENU");
$tpl->parse("pagecss", "PAGESTYLES", true);

ob_start();
//
// Start of Output /////////////////////////////////////////////////
//

// Ausgabe Menu
if ($mode == "menu") {
    $db = new DB_Usermgmt;

    $tpl->set_file(array("MAIN" => "tpl/statistik.tpl.html"));

    $sql = "SELECT dokname, dokzeit, bemerkung, COUNT(*) as count"
        . " FROM statistics"
        . " GROUP BY dokname"
        . " ORDER BY dokname";
    $db->query($sql);
    $ar_stat = array();
    while ($db->next_record()) {
        $ar_stat[$db->f("dokname")] = array(
                "count" => $db->f("count"),
                "dokname" => $db->f("dokname"),
                "erstellt" => $db->f("dokzeit"),
                "bemerkung" => $db->f("bemerkung") );

    }

    $tpl->set_block("MAIN","docstat","docstats");
    foreach ($ar_stat as $key => $stat ) {
        if ($stat["bemerkung"] == "") $stat["bemerkung"] = " - ";
        $tpl->set_var(array(
                "dokname" => $stat["dokname"],
                "erstellt"  => formatSQLDate($stat["erstellt"]),
                "bemerkung" => $stat["bemerkung"],
                "dl_count"  => $stat["count"],
                "home"	=> "http://".$_SERVER["SERVER_NAME"].$sec_web,
                "dokid" => $key
        ));
        $tpl->parse("docstats","docstat",true);
    }


} // end menu

// Ausgabe User
if ($mode == "user") {

    include_once("allusers.php");

    $tpl->set_file(array(
        "MAIN" => "tpl/admin_user.tpl.html"));

    // Variable users definieren
    $tpl->set_block("MAIN","user","users");

    if (is_array($a_user)) {
        foreach ($a_user as $user) {
            $tpl->set_var(array(
                    "name" => $user["Nachname"],
                    "vorname" => $user["Vorname"],
                    "email" => $user["email"],
                    "bemerkung" => $user["bemerkung"],
                    "groups" => $user["groups"],
                    "timestamp" => formatSQLDate($user["erstellt"]),
                    "editurl" => "useredit.php?id=".$user["uid"]."&mode=edit"
            ));
            if ($user["username"] == "admin") {
                // Admin darf man nicht loeschen koennen
                $tpl->set_var(array(
                        "delurl" => $self,
                        "deltext" => "",
                        "space" => "&nbsp;"
                ));
            } else {
                $tpl->set_var(array(
                        "delurl" => "useredit.php?id=".$user["uid"]."&mode=del",
                        "deltext" => "l&ouml;schen"
                ));
            }

            $tpl->parse("users","user",true);
        }
    }

}  // end user

// Ausgabe Dateien
if ($mode == "doc") {

    include_once("alldocs.php");

    $tpl->set_file(array(
            "MAIN" => "tpl/admin_docs.tpl.html"));

    // Variable docs definieren
    $tpl->set_block("MAIN", "doc","docs");

    if (is_array($a_docs)) {
        foreach($a_docs as $doc) {
            $kr_html = "";
            $gr_html = "";
            $bemkurz = htmlentities($doc["bemerkung"]);
            // Bemerkung kuerzen auf z.B. 60 Zeichen
            /*	        if (strlen($bemkurz) > 60) {
	            $bemkurz = substr($bemkurz,0,60);
	            $lastws = strrpos($bemkurz, " ");
	            if ($lastws) $bemkurz = substr($bemkurz,0,$lastws);
	            $bemkurz .= " ...";
            }
            */
            if ( $doc["ist_neu"] == 1 ) {
                $neu = "<span style='color: #FF0000; font-weight: bold'>NEU</span> - ";
            } else {
                $neu = "";
            }

            if ($doc["dokname"] == "DO") {
                $dn = '<a href="#" title="Metadaten eingeben" onClick="javascript:nw=window.open(\'upload2.php\',\'Upload\',\'height=500,width=500,location=no,scrollbars=yes,menubars=no,toolbars=no,resizable=yes\');return false;">Bearbeitung erforderlich</a>';
            } else {
                $dn = $doc["dokname"];
            }
            foreach ( $ak_rights as  $arKR ) {
                if ( $doc["dokid"] == $arKR["dokid"] ) {
                    if (strlen($kr_html) > 0) $kr_html .= "; ";
                    $kr_html .= $arKR["kd"];
                }
            }
            foreach ( $ag_rights as $arGR ) {
                if ( $doc["dokid"] == $arGR["dokid"]  ) {
                    if (strlen($gr_html) > 0) $gr_html .= "; ";
                    $gr_html .= $arGR["gr"];
                }
            }

            $tpl->set_var(array(
                    "docname" => $neu . $dn,
                    "docpfad" => $doc["pfad"],
                    "version" => htmlentities($doc["version"]),
                    "bemerkung" => $bemkurz,
                    "berechtigung" => $kr_html . "<br>" . $gr_html,
                    "timestamp" => formatSQLDate($doc["erstellt"]),
                    "permurl" => "docedit.php?dokid=".$doc["dokid"]."&mode=edit",
                    "replurl" => "docreplace.php?dokid=".$doc["dokid"],
                    "delurl" => "docedit.php?dokid=".$doc["dokid"]."&mode=del"
            ));
            $tpl->parse("docs","doc", true);
        }
    }
} // end doks

if ($mode == "group") {

    include_once("allgroups.php");

    $tpl->set_file(array(
            "MAIN" => "tpl/admin_groups.tpl.html"));

    // Variable docs definieren
    $tpl->set_block("MAIN", "group","groups");

    if (is_array($a_groups)) {
        foreach($a_groups as $group) {
            $bemkurz = $group["bemerkung"];
            // Bemerkung kuerzen auf z.B. 60 Zeichen
            /*	        if (strlen($bemkurz) > 60) {
	            $bemkurz = substr($bemkurz,0,60);
	            $lastws = strrpos($bemkurz, " ");
	            if ($lastws) $bemkurz = substr($bemkurz,0,$lastws);
	            $bemkurz .= " ...";
            }
            */

            $tpl->set_var(array(
                    "bezeichnung" => $group["bezeichnung"],
                    "bemerkung" => $bemkurz,
                    "gusers"    => $group["users"],
                    "gediturl"  => $sec_web."groupedit.php?gid=".$group["gid"]."&mode=edit",
                    "gdelurl"   => $sec_web.'groupedit.php?gid='.$group["gid"].'&mode=del',
                    "timestamp" => formatSQLDate($group["erstellt"])
            ));
            $tpl->parse("groups","group", true);
        }
    }
} // end groups

// hier wird ermittelt, wieviel Speicherplatz noch zur Verfügung steht
// in Megabytes
$df = exec( "du -hsk " . $d_root );
$disk_free = $disk_total - ( $df / 1024 );

//
// End of Output /////////////////////////////////////////////////
//
$out = ob_get_contents();
ob_end_clean();


$tpl->set_var(array(
        "TITLEADD" => "-- Administration",
        "PERSONNAME" => "Willkommen ".$_SESSION['s_name'],
        "df_info" => "Festplattenspeicher verf&uuml;gbar: ". number_format( $disk_free, 2 , ",", ".") . " MB",
        "SECWEB" => $sec_web,
        "COMPLOGO" => $complogo,
        "COMPNAME" => $compname,
        "debug" => $out
));
$tpl->parse("result",array("MAIN","pagecss","amenu","admin"));
$tpl->p("result");

?>