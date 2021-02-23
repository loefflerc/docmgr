<?php
/**
 *  $Id: functions.inc.php,v 1.1.1.1 2004/04/14 17:31:01 chris Exp $
 */

//gibt die Dateiendung zurueck als String
function getSuffix($strFileName) {
    $path_parts = pathinfo($strFileName);
    return $path_parts["extension"];
}

// gibt Bild des Icons zu Suffix als String zurueck
function getFileIcon($strSuffix) {
    $strSuffix = strtolower($strSuffix);
    global $sec_web;
    $icon_dir = $sec_web."img/";
    $icon_undef = "unknown.gif";
    $icon_res = array (
            "zip" => "zip_ico.png",
            "rar" => "rar_ico.png",
            "pdf" => "acrobat.png",
            "exe" => "exec.png",
            "dot" => "dot_ico.png",
            "lst" => "listlbl_ico.png",
            "vbs" => "vbs_ico.png"
    );
    if (array_key_exists($strSuffix, $icon_res)) {
        return $icon_dir . $icon_res[$strSuffix];
    } else {
        return $icon_dir . $icon_undef;
    }
}

function formatSQLDate($ts) {
    $string = sprintf("%s",$ts);
    $pos = strstr($string, '-');
    if ( $pos === false ) {
        $year = substr($string,0,4);
        $month = substr($string,4,2);
        $day = substr($string,6,2);
        $hour = substr($string,8,2);
        $min = substr($string,10,2);
    } else {
        list( $date, $time ) = split( ' ', $string );
        $year = substr($date,0,4);
        $month = substr($date,5,2);
        $day = substr($date,8,2);
        $hour = substr($time,0,2);
        $min = substr($time,3,2);
    }
    return "$day.$month.$year&nbsp;um&nbsp;$hour:$min";
}
?>