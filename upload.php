<?php

// from PX: PHP Code Exchange

// This code only works on Linux-based servers ;)
// Some lines, like this one, are longer than this window and may have wrapped; you can get the original, if necessary, at the address shown in the code description

// File Upload Script for PHP/3 for Linux

// Released under the terms of the public GNU license
// Based upon code written by Rasmus Lerdorf and Boaz Yahav
// Modified for Linux by Saif Slatewala
// E-mail: saif_slatewala@india.com
// site :- http://systemprg.hypermart.net

// You need to write-enable a directory, named "/tmp", below the one you place this script in

//
//    $Id: upload.php,v 1.1.1.1 2004/04/14 17:30:55 chris Exp $
//

include_once ("inc/config.inc.php");
$error1 = 0;

// if files have been uploaded, process them
if (isset($_POST["action1"])) {
    include_once ("inc/db_wmcdb.inc.php");
    $q = new DB_Usermgmt;
?>

   <html>
   <head>
   <title>Datei Upload Ergebnisse</title>
   <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
   <link rel="stylesheet" href="<?php echo $stylesheet?>" type="text/css">
   </head>

   <body scroll="auto">
   <p><font face="Arial, Helvetica, sans-serif"><font size="+1">Datei Upload Ergebnisse</font><br><br>

   <?php
   // make reasonably sure the script does not time out on large files
   set_time_limit(7200);
   // assign our path in a form PHP for Windows understands
   //$path1 = AddSlashes("J:/WAMP/tmp/");
   $path1 = AddSlashes($d_root);
/*
   print "<pre>DEBUG:\n";
   var_dump( $_FILES );
   print "</pre>";
*/
   // loop through the  possible files
   for ($i = 0; $i < sizeof($_FILES['userfile']['name']); $i++) {
    if (!$_FILES['userfile']['size'][$i]) {
        print 'Dateilaenge ist 0. Admin rufen.<br>';
        // Dateilaenge ist 0
        continue;
    }

    // retrieve a file pointer from the temp directory
    $source = $_FILES['userfile']['tmp_name'][$i];
// TODO: Dateinamen auf Sonderzeichen untersuchen
// ���� - Leerzeichen
    $source_name = $_FILES['userfile']['name'][$i];
      // see if the file exists; non-existing file has name of "none"
      if(($source <> "none")&&($source <> "")){
         // no need to copy a file if directory not write-enabled
         if($error1 <> 1){
            // append file name to our path
            $dest = $path1.$source_name;
            if (!file_exists($dest)) {
                // copy the file from the temp directory to the upload directory, and test for success
                if(copy($source,$dest)){

                   echo "$source_name wurde erfolgreich hochgeladen<br>\n";
                   $query = "INSERT INTO dokument (dokname,pfad) VALUES ('DO','$source_name')";
                   $q->query($query);
                }
                else {
                   // you need to write-enable the upload directory
                   echo "Contact Admin: Upload Verzeichnis ist schreibgeschuetzt.\n";
                   // set flag
                   $error1 = 1;
                }
            } else {
                echo "Die Datei mit dem Namen $source_name existiert schon auf dem Server.<br>"
                    ."Es erfolgt kein Eintrag in die Datenbank. Um die Datei zu ersetzten, l&ouml;schen Sie zuerst die alte Datei.<br>";
            }
         }
         // delete the file from the temp directory
         unlink($source);
      }
   }
   ?>

   <br>
  <a href="<?php echo $sec_web; ?>/upload2.php">Meta Daten eingeben</a> | <a href="javascript:self.close();">schliessen</a>
  </font></p>
   </body>
   </html>

<?php
} else {
    // else, prompt for the files
    // files will be uploaded into the server's temp directory for PHP
?>

   <html>
   <head>
   <title>Datei Upload</title>
   <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
   <link rel="stylesheet" href="<?php echo $stylesheet?>" type="text/css">
   </head>

   <body scroll="auto">
   <h1>Datei Upload</h1>
   <p><br>
   Benutzen Sie die 'Durchsuchen'/'Browse'-Schaltfl&auml;che um die Dateien auszuw&auml;hlen. Dann
   klicken Sie die &quot;Start&quot; Schaltfl&auml;che. Nachdem die Dateien auf den Server transferiert wurden,
   sehen Sie einen Ergebnisbericht.<br>
   <form method="post" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF'] ?>">
           <input type="hidden" name="action1" value="1">
<?php
    $filnum = $_GET["f_filnum"];
    for ($i=1; $i <= $filnum; $i++) {
        echo "Datei $i: <input type='file' name='userfile[]' size='30'><br>";
    }
?>
<br>
           <input type="submit" value="Start">
           <input type="submit" value="Abbrechen" onclick='parent.close()'>
   </form>
   </p>
   </body>
   </html>

   <?php
}
?>
