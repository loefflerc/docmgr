<?php
//
//    $Id: upload2.php,v 1.1.1.1 2004/04/14 17:30:56 chris Exp $
//

include_once("inc/config.inc.php");
include_once ("inc/db_wmcdb.inc.php");
$q = new DB_Usermgmt;

if (isset($_POST["submit"])) {
    $query = "UPDATE dokument SET "
            ."dokname = '".$_POST['f_dokname']."', "
            ."bemerkung = '".$_POST['f_bemerkung']."', "
            ."version = '".$_POST['f_version']."', "
            ."ist_neu = ".$_POST['f_neu']
            ." WHERE dokid = ". $_POST['f_dokid'];
    print "<!-- Update: $query -->";
    $q->query($query);
}

$query = "SELECT * FROM dokument WHERE dokname = 'DO' ORDER by erstellt DESC";

$q->query( $query );
$numdocs = $q->nf() - 1;
$thisclose = "";
if ( $numdocs < 0 ) {
    $numdocs = 0;
    $thisclose = '
    <script language="JavaScript" type="text/javascript"><!--
      opener.location.reload();
      self.close();
      //-->
    </script>';
}
$q->next_record();
?>
<html>
<head>
<title>Datei Metadaten  eingeben</title>
<meta name="author" content="Christoph L&ouml;ffler">
<meta name="generator" content="Ulli Meybohms HTML EDITOR">
<link rel="stylesheet" href="<?php echo $stylesheet?>" type="text/css">
</head>
<body scroll="auto">
<?php print $thisclose; ?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
<input type="hidden" name="f_dokid" value="<?php echo $q->f('dokid') ?>">
  <table border="0" cellspacing="0" cellpadding="1">
    <tr>
      <td width="20">&nbsp;</td>
      <td width="20">Dateiname:</td>
      <td>
        <?php echo $q->f("pfad");?>
      </td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>Dokumentname</td>
      <td>
        <input type="text" name="f_dokname" size="30" maxlength="60">
      </td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td valign="top">Bemerkungen</td>
      <td>
        <textarea name="f_bemerkung" rows="4" cols="30"></textarea>
      </td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td valign="top">Version</td>
      <td>
        <input type="text" name="f_version" size="30" maxlength="30">
      </td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td valign="top">Neu</td>
      <td>
        <input type="radio" name="f_neu" value="1" checked="checked">Ja
        <input type="radio" name="f_neu" value="0">Nein
      </td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td>
        <input type="submit" name="submit" value="Speichern">&nbsp;
        <input type="button" name="" value="Abbrechen" onClick="javascript:opener.location.reload();self.close();">
      </td>
    </tr>

  </table>
  </form>
<?php
	// Wieviel Dateien sind noch zu bearbeiten?
    print "Noch $numdocs Datei" .(($numdocs > 1) ? "en" : "");
    print " zu bearbeiten.";

?>
</body>
</html>
