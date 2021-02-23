<?php
/**
 * $Id: sendpdf.php,v 1.1.1.1 2004/04/14 17:30:55 chris Exp $
 */

include_once("inc/config.inc.php");
include_once("inc/functions.inc.php");
session_start();
if (!isset($_SESSION['s_login'])) {
    header("Location: http://".$_SERVER["SERVER_NAME"].$sec_web."/index.php");
}

include_once ("inc/db_wmcdb.inc.php");
$q = new DB_Usermgmt;

function get_file( $file ) {
    $size = filesize( $file );
    $ct = getContentType( $file );
    /*
       Content Disposition according to RFC 1806 and RFC 2183
       Filenames must be in ASCII according RFC 1521
       encoding neccessary according RFC 2231
       only works in Mozilla 1.5 or greater
    */
    $BrowserIsGecko15up = false;
    $strBrowser = $_SERVER['HTTP_USER_AGENT'];
    if ( strpos( $strBrowser,"Gecko") ) {
        $pos1 =  strpos( $strBrowser, "rv:");
        $geckoRev = floatval( substr($strBrowser, $pos1+3, 3 ) );
        if ( $geckoRev >= 1.5 ) {
            $BrowserIsGecko15up = true;
        }
    }
    if ( $BrowserIsGecko15up ) {
        $disp_file = "*=\"iso-8859-1'de'"
                . str_replace(
                array(" ",  "Ä",  "ä",  "Ö",  "ö",  "Ü",  "ü",  "ß"  ),
                array("%20","%C4","%E4","%D6","%F6","%DC","%FC","%DF"),
                basename($file)
                )."\"";
    } else {
        $disp_file = '="'.basename( $file ).'"';
    }

    header( 'Content-Type: ' . $ct );
    if( preg_match("/MSIE 5.5/", $_SERVER['HTTP_USER_AGENT']))
        $att = "";      //  ?? IE 5.5, not tested
    else $att = " attachment; ";
    header("Pragma: no-cache");                             // HTTP/1.0
    header("Cache-Control: no-cache, must-revalidate");     // HTTP/1.1
    header( 'Content-Disposition: '.$att.'filename' . $disp_file .'; filename='. $addfile);
    header( 'Content-Length: ' . $size );
    header( 'Content-Transfer-Encoding: binary' );
    readfile( $file );
    /*
    $fp = fopen("get_pdf_log.txt","a");
    fputs($fp, '['.time().'] - '.basename($file).' - '.$disp_file.' - '.$size.' ['.$_SERVER['HTTP_USER_AGENT'].'] ( '
            . ( $BrowserIsGecko15up ? ' isG1.5up ' : ' noG1.5up ' )." )\n");
    fclose($fp);
    */
}

function getContentType( $strFile ) {
    $type = 'application/octet-stream';
    $suffix = getSuffix( $strFile );
    if ( $suffix == 'pdf' ) $type = 'application/pdf';
    if ( $suffix == 'zip' ) $type = 'application/zip';
    if ( $suffix == 'rar' ) $type = 'x-application/rar';
    return $type;
}

function log_db($file) {
    global $q;
    if (isset($_SESSION['s_uid'])) {
        $uid = $_SESSION['s_uid'];
    } else if (isset($_SESSION["s_uid"])) {
        $uid = $_SESSION["s_uid"];
    } else {
        $uid = -1;
    }

    // Name und Bemerkung des Dokuments aus der Tabelle holen
    $query = "SELECT dokname, version, bemerkung, erstellt FROM dokument WHERE pfad ='$file'";
    /*    $fp = fopen("stat_log.txt","a");
    fputs($fp, time()." - Query: ".$query." - $q->error".."\n");
    fclose($fp);
    */
    $q->query($query);
    if ($q->next_record()) {
        $ddokname = $q->f("dokname");
        $dversion = $q->f("version");
        $dbemerkung = $q->f("bemerkung");
        $ddokzeit = $q->f("erstellt");
    }

    // IP und Browserkennung
    $dIP = addslashes( $_SERVER["REMOTE_ADDR"] );
    $dbrowser = addslashes($_SERVER["HTTP_USER_AGENT"]);

    ob_start();
    // Ueber Linux Programm per DNS aufloesen
    passthru('nslookup -sil ' . $dIP);
    $strWho = ob_get_contents();
    ob_end_clean();

    $pos = strpos($strWho, 'name = ') + 7;
    $dhost = addslashes( substr($strWho, $pos, -3) );
    if ( strlen ($dhost) > 255 ) {
        $dhost = substr( $dhost, 0, 255 );
    }

    $query = " INSERT INTO statistics ( uid,    dokname,  version,     dokzeit,    bemerkung,     host,     browser ) "
            ." VALUES ( $uid, '$ddokname', '$dversion', '$ddokzeit', '$dbemerkung', '$dhost', '$dbrowser' )";
    $q->query($query);

}

if (!isset($id) && (isset($_GET['id']) || isset($_POST['id']))) {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
    } else {
        $id = $_POST['id'];
    }
} else if (!isset($id)) {
    $id = '';
}


if ($id != '') {
    $filedir = $d_root;
    $file = $_SESSION['s_docs'][$id]["url"];
    $filename = sprintf("%s%s", $filedir, $file);
    get_file($filename);
    log_db($file);
}

exit;

?>
