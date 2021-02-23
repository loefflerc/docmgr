<?php
/**
 * $Id: insert_user.php,v 1.1.1.1 2004/04/14 17:30:55 chris Exp $
 */

include_once("inc/config.inc.php");
ob_start();
session_start();

if (!isset($_SESSION['s_login']) || ($_SESSION['s_login'] <> 1) || ($_SESSION['s_userName'] != "admin")) {
    session_destroy();
    header("Location: http://".$_SERVER["SERVER_NAME"].$sec_web."/index.php");
}

if (isset($_POST['f_anrede'])) {
    $f_anrede = $_POST['f_anrede'];
} else {
    $f_anrede = 0;
}

include_once("inc/db_wmcdb.inc.php");
include_once("inc/template.inc.php");

$q = new DB_Usermgmt;
$title = "Benutzer anlegen";

?>
<form name="userNeu" method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
  <table border="0" cellspacing="0" cellpadding="2">
    <tr>
      <td>Anrede </td>
      <td width="20">&nbsp;</td>
      <td>
        <select name="f_anrede" size="1">
          <option value="0"<?php if(!isset($f_anrede) || "0" == $f_anrede) echo " selected"; ?>>w&auml;hlen</option>
          <option value="1"<?php if("1" == $f_anrede) echo " selected";?>>Herr</option>
          <option value="2"<?php if("2" == $f_anrede) echo " selected";?>>Frau</option>
        </select>
      </td>
      <td width="20">&nbsp;</td>
      <td rowspan="2">Gruppe<br>(Mehrfachauswahl mit STRG-Taste):</td>
    </tr>
    <tr>
      <td>Vorname</td>
      <td>&nbsp;</td>
      <td>
        <input type="text" name="f_vorname" value="<?php
        if (isset($_POST['f_vorname'])) {
            echo $_POST['f_vorname'];
        }
        ?>" maxlength="15">
      </td>
      <td>&nbsp;</td>

    </tr>
    <tr>
      <td>Nachname</td>
      <td>&nbsp;</td>
      <td>
        <input type="text" name="f_nachname" value="<?php
        if (isset($_POST['f_nachname'])) {
            echo $_POST['f_nachname'];
        }
        ?>" maxlength="30">
      </td>
      <td>&nbsp;</td>
      <td rowspan="5">
        <select name="f_groups[]" size="6" multiple>
		<?php
			$query = "select * from gruppe";
			$q->query($query);

			while($q->next_record()) {
				print "<option value='".$q->f("gid")."'>";
				print $q->f("bezeichnung");
				print "</option>\n";
			}
		?>
        </select>
      </td>
    </tr>
    <tr>
      <td>EMail</td>
      <td>&nbsp;</td>
      <td>
        <input type="text" name="f_email" value="<?php
        if (isset($_POST['f_email'])) {
            echo $_POST['f_email'];
        }
        ?>" maxlength="60">
      </td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>Benutzername</td>
      <td>&nbsp;</td>
      <td>
        <input type="text" name="f_username" value="<?php
        if (isset($_POST['f_username'])) {
            echo $_POST['f_username'];
        }
        ?>" maxlength="60">
      </td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>Passwort</td>
      <td>&nbsp;</td>
      <td>
        <input type="password" name="f_pw1">
      </td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>Passwort wiederholen</td>
      <td>&nbsp;</td>
      <td>
        <input type="password" name="f_pw2">
      </td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>Bemerkung</td>
      <td>&nbsp;</td>
      <td colspan="3">
        <textarea name="f_bemerkung" rows="3" cols="45"><?php
        if (isset($_POST['f_bemerkung'])) {
            echo $_POST['f_bemerkung'];
        }
        ?></textarea>
      </td>

    </tr>
    <tr>
      <td>&nbsp;</td>
      <td colspan="3">
        <input type="submit" name="submit" value="Speichern">&nbsp;<input type="reset" name="button2" value="Abbrechen" onClick="opener.location.reload();self.close();">
      </td>
      <td>&nbsp;</td>
    </tr>
  </table>
  </form>

<?php
	if (isset($_POST['submit']) && $_POST['f_pw1'] != "" && ($_POST['f_pw1'] == $_POST['f_pw2'])) {
	    $username = htmlentities($_POST['f_username']);
	    $salt = substr( $_POST['f_pw1'], 0, 2);
	    $passwort = crypt( $_POST['f_pw1'], $salt );
	    $nachname = htmlentities($_POST['f_nachname']);
	    $vorname = htmlentities($_POST['f_vorname']);
	    $email = htmlentities($_POST['f_email']);
        $anrede = htmlentities($_POST['f_anrede']);
        $bemerkung = htmlentities($_POST['f_bemerkung']);

        // ist der Benutzernamen schon vergeben?
        $query = "SELECT * FROM kunde WHERE username = '$username'";
        $q->query($query);
        if ($q->nf() > 0) {
            print "Es existiert bereits ein Benutzer mit diesem Benutzernamen.<br>Bitte w&auml;hlen Sie einen anderen Benutzernamen.";
        } else if (strlen($_POST['f_pw1']) < $minpasswd_length or strlen($_POST['f_pw1']) > $maxpasswd_length) {
            print "Das Passwort liegt ausserhalb der vorgegebenen Beschr�nkung.<br>Das Passwort soll mindestens
            $minpasswd_length Zeichen lang sein und maximal $maxpasswd_length Zeichen.";
        } else {
          	$query = "INSERT INTO kunde (username,passwort,Nachname,Vorname,anrede,email, bemerkung) ";
            $query .= "VALUES ('$username','$passwort','$nachname','$vorname','$anrede','$email', '$bemerkung')";
            print '<br>QUERY: '.$query;

            $q->query($query);

            $lastid = mysql_insert_id($q->Link_ID);
            $groups = array();
            $groups = $_POST['f_groups'];
            for ($i=0; $i < sizeof($groups); $i++ ) {
                $query = "INSERT INTO kunde_gruppe_rel (gid,uid) VALUES (".$groups[$i].",$lastid)";
                //print "<br>".$query;
                $q->query($query);
            }
        }   // End if Benutzer einf�gen wenn noch nicht vorhanden

    }

    $out = ob_get_contents();
    ob_end_clean();

/*    $fp = fopen ("debug.txt","w");
    fputs($fp, $out);
    fclose($fp);
*/
    $tpl = new Template();

    $tpl->set_file(array(
            "gins" => "tpl/little_win.tpl.html",
            "PAGESTYLES" => $stylesheet
            ));
    $tpl->parse("pagecss","PAGESTYLES");

    $tpl->set_var(array(
                "title" => $title,
                "body"  => $out
            ));
    $tpl->parse("result","gins");
    $tpl->p("result");

?>
