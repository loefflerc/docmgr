<?php
/**
 * Datei ersetzten durch Upload einer anderen Version
 * 
 * $Id: docreplace.php,v 1.1.1.1 2004/04/14 17:30:54 chris Exp $
 */


session_start();
include_once("inc/config.inc.php");

if (!isset($_SESSION['s_login']) || ($_SESSION['s_login'] <> 1) || ($_SESSION['s_userName'] != "admin")) {
    session_destroy();
    header("Location: http://".$_SERVER["SERVER_NAME"].$sec_web."index.php");
}

if (isset($_POST['submit']) && $_POST["action2"] == 1 ) {
    include_once ("inc/db_wmcdb.inc.php");
    $q = new DB_Usermgmt;
    // pruefen, ob Datei denselben Namen hat
    $file_ok = false;
    if ( $_SESSION['s_repl_path'] == $_FILES['userfile']['name'] ) {
        $file_ok = true;
    }
    set_time_limit(600);                    // make reasonably sure the script does not time out on large files
    $path1 = AddSlashes($d_root);
    ?>
<html>
    <head>
        <title>Datei Upload Ergebnisse</title>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
        <link rel="stylesheet" href="<?=$stylesheet?>" type="text/css">
    </head>
    
    <body scroll="auto">
        <p><font face="Arial, Helvetica, sans-serif" size="+1">Datei Upload Ergebnisse</font></p><br><br>
                    <?php
                    echo "<!-- File: \n";
                    foreach($_FILES['userfile'] as $key => $val) {
                        print " - $key : $val \n";
                    }
                    echo " -->";
                    if(!$_FILES['userfile']['size']) {
                        print 'Dateil&auml;nge ist 0. Admin rufen oder erneut <a href="#" onclick="self.close();">versuchen</a>.<br>'   ;
                        // Dateilaenge ist 0
                        $file_ok = false;
                    } else {
                        print "Dateigr&ouml;sse: ".$_FILES['userfile']['size']."<br>";
                    }
                    $source = $_FILES['userfile']['tmp_name'];
                    $source_name = $_FILES['userfile']['name'];
                    
                    if(($source <> "none")&&($source <> "")) {                  // see if the file exists; non-existing file has name of "none"
                        $file_ok = true;
                        print "Source ok ($source)<br>";
                    } else {
                        $file_ok = false;
                    }
                    
                    if ($file_ok) {
                        $dest = $path1.$source_name;                        // append file name to our path
                        if (!file_exists($dest)) {
                            print 'Datei '.$dest.' ist nicht vorhanden, bitte benutzen Sie den Dateiupload.<br>';
                            $error1 = 1;
                        }
                        
                        if($error1 <> 1) {                                       // no need to copy a file if directory not write-enabled
                            if( !unlink($dest) ) {                              // Zieldatei loeschen
                                echo "L&ouml;schen der Datei $dest fehlgeschlagen.
                Eventuell ist das Verzeichnis oder die Datei schreibgesch&uuml;tzt.<br>\n";
                            } else {
                                print "Datei $dest gel&ouml;scht.<br>";
                            }
                            if(copy($source,$dest)) {                             // copy the file from the temp directory to the upload directory, and test for success
                                
                                echo "$source_name wurde erfolgreich hochgeladen und als $dest gespeichert.<br>\n";
                                $query = "UPDATE dokument set version = '".addslashes($_POST['f_version'])."' WHERE pfad = '$source_name'";
                                echo "<!-- QUERY: $query -->";
                                $q->query($query);
                                $query = " SELECT dokid FROM dokument WHERE pfad = '$source_name'";
                                $q->query($query);
                                if ( $q->next_record() ) {
                                    $dokid = $q->f('dokid');
                                }
                                if (isset($_POST['f_neu']) && $_POST['f_neu'] == 1) {
                                    // Kennzeichen neu setzen
                                    $query = " UPDATE dokument set ist_neu = 1 WHERE dokid = $dokid ";
                                    echo "<!-- QUERY: $query -->";
                                    $q->query( $query );
                                } else {
                                    // Kennzeichen neu loeschen
                                    $query = " UPDATE dokument set ist_neu = 0 WHERE dokid = $dokid ";
                                    echo "<!-- QUERY: $query -->";
                                    $q->query( $query );
                                }
                            }
                            else {
                                echo "Contact Admin: Upload Verzeichnis ist schreibgeschuetzt.\n";      // you need to write-enable the upload directory
                                $error1 = 1;                                      // set flag
                            }
                            unlink($source);                                        // delete the file from the temp directory
                        } else print "Fehler: $error1<br>";
                    }
                    
                    ?>
                <p><a href="javascript: parent.close();opener.reload();">Fenster schliessen</a></p>
    
    
    </body>
</html>
    <?php
} else {
    // Dokument bearbeiten
    if (isset($_GET["dokid"])) {
        $e_dokid = $_GET["dokid"];
    } else if (isset($_POST["dokid"])) {
        $e_dokid = $_POST["dokid"];
    } else {
        header("Location: http://".$_SERVER["SERVER_NAME"].$sec_web."admin.php");
        exit;
    }
    
    include_once("inc/functions.inc.php");
    include_once("inc/template.inc.php");
    include_once("inc/db_wmcdb.inc.php");
    
    $q = new DB_Usermgmt;
    $query1 = "SELECT * FROM dokument WHERE dokid = $e_dokid";
    $q->query($query1);
    if ( $q->next_record() ) {
        $repl_name = $q->f("dokname");
        $repl_path = $q->f("pfad");
        $repl_version = $q->f("version");
        $_SESSION['s_repl_name'] = $repl_name;
        $_SESSION['s_repl_path'] = $repl_path;
        $_SESSION['s_repl_version'] = $repl_version;
    }
    ?>
<html>
    <head>
        <title>Datei mit Upload ersetzen</title>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        <link rel="stylesheet" href="<?=$stylesheet?>" type="text/css">
    </head>
    
    <body scroll="auto">
        <h1>Datei mit Upload ersetzen</h1>
        <p>Die Datei <b>&nbsp;&nbsp;<?php
                    print  $repl_path."&nbsp;(Name: ".$repl_name.")</b>&nbsp;&nbsp;soll ersetzt werden.
      Bitte laden Sie die neue Datei mit demselben Dateinamen hoch.";
                    ?><br><br>
                Benutzen Sie die 'Durchsuchen'/'Browse'-Schaltfl&auml;che um die Dateien auszuw&auml;hlen. Dann
                klicken Sie die &quot;Start&quot; Schaltfl&auml;che. Nachdem die Dateien auf den Server transferiert wurden,
                sehen Sie einen Ergebnisbericht.<br>
                <form method="post" enctype="multipart/form-data" action="<?= $_SERVER['PHP_SELF'] ?>">
                    <input type="hidden" name="action2" value="1">
                    neue Version der Datei <?php print $repl_path; ?>:<br>&nbsp;&nbsp;&nbsp;<input type="file" name="userfile" size="30"><br>
                    Version:<br>&nbsp;&nbsp;&nbsp;<input type="text" name="f_version" size="20" value="<?php print $repl_version;?>"><br>
                    <input type="checkbox" name="f_neu" value="1" checked="checked"> Datei nach Upload auf <b>Neu</b> setzen.<br>
                    <br>
                    <input type="submit" name="submit" value="Start">
                    <input type="submit" name="cancel" value="Abbrechen" onclick='parent.close()'>
                </form>
        </p>
    </body>
</html>
    <?php
}
?>