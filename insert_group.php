<?php
/**
 *     $Id: insert_group.php,v 1.1.1.1 2004/04/14 17:30:54 chris Exp $
 */



include_once ("inc/config.inc.php");

ob_start();
session_start();

if (!isset($_SESSION['s_login']) || ($_SESSION['s_login'] <> 1) || ($_SESSION['s_userName'] != "admin")) {
    session_destroy();
    header("Location: http://".$_SERVER["SERVER_NAME"].$sec_web."/index.php");
}

include_once ("inc/db_wmcdb.inc.php");
include_once("inc/template.inc.php");

$q = new DB_Usermgmt;
$title = "Gruppe anlegen";
$mark = 1;


if (isset($_POST['submit']) && $_POST['f_group'] != "") {
    $group = $_POST['f_group'];
    $bemerkung = $_POST['f_bemerkung'];

    $query = "SELECT * FROM gruppe WHERE bezeichnung = '$group'";
    $q->query($query);

    if ($q->nf() == 0) {

        $mark = 0; // Formular nicht anzeigen

        $query = " INSERT INTO gruppe (bezeichnung, bemerkung) VALUES ('$group', '$bemerkung') ";
        $q->query($query);

        //$lastid = mysql_insert_id($q->Link_ID);
        //print "<br>GruppenID: ".$lastid;

        print "<p>Die Gruppe $group wurde erfolgreich angelegt.</p>";
        print "<p><a href='".$_SERVER['PHP_SELF']."'>noch eine Gruppe anlegen</a> | <a href='javascript:opener.location.reload();self.close();'>Fenster schliessen</a></p>";

    } else {
        // Gruppe gibts schon
        $mark = 1;

        print "<p class='error'>Die Gruppe $group existiert schon. Bitte einen anderen Namen eingeben.</p>";
    }
}

if ($mark == 1) {
    ?>

<form name="groupNeu" method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
    <table border="0" cellspacing="0" cellpadding="2">
        <tr>
            <td>Name der Gruppe </td>
            <td width="20">&nbsp;</td>
            <td>
                <input type="text" name="f_group" value="<?php 
                if (isset($_POST['f_group'])) {
                    echo $_POST['f_group'];
                }
                ?>">

            </td>
        </tr>
        <tr>
            <td>Bemerkungen</td>
            <td width="20">&nbsp;</td>
            <td>
                <textarea name="f_bemerkung" ><?php 
                if (isset($_POST['f_bemerkung'])) {
                    echo $_POST['f_bemerkung'];
                }   ?></textarea>
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>
                <input type="submit" name="submit" value="Speichern">
                <input type="submit" name="button2" value="Abbrechen" onClick="opener.location.reload();self.close();">
            </td>
        </tr>
    </table>
</form>

    <?php
} // end if mark
/** DEBUG
 if (is_array($_POST)) {
 print '<table border="0" cellspacing="0" cellpadding="2">';
 foreach($_POST as $key => $value) {
 if (!is_array($value)) {
 print "<tr><td>$key</td><td width='20'></td><td>$value</td></tr>\n";
 } else {
 foreach($value as $part) {
 print "<tr><td>$key</td><td width='20'></td><td>$part</td></tr>\n";
 }
 }
 }
 print '</table>';
 }
 end DEBUG */
$out = ob_get_contents();
ob_end_clean();

$tpl = new Template();

$tpl->set_file(array(
        "gins" => "tpl/little_win.tpl.html",
        "PAGESTYLES" => $stylesheet
));
$tpl->parse("pagecss","PAGESTYLES");

$tpl->set_var(array(
        "title" => $title,
        "bgcolor" => $bgcolor,
        "body"  => $out
));
$tpl->parse("result","gins");
$tpl->p("result");

?>