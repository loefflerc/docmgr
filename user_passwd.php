<?php
/**
 *  Passwort eingeben für Benutzer
 */

include_once("inc/config.inc.php");
$html = '';

session_start();

if (!isset($_SESSION['s_login']) || ($_SESSION['s_login'] <> 1)) {
    session_destroy();
    header("Location: http://".$_SERVER["SERVER_NAME"].$sec_web."/index.php");
}

include_once("inc/db_wmcdb.inc.php");
include_once("inc/template.inc.php");


$tpl = new Template();

$tpl->set_file(array(
    "passwd" => "tpl/little_win.tpl.html",
    "PAGESTYLES" => $stylesheet
));
$tpl->parse("pagecss","PAGESTYLES");

if ( isset($_POST["submit"]) && $_POST["submit"] == "Speichern") {
    $showform = FALSE;
    $good_chars = "/([a-zA-Z0-9_-]+)/i";
    $title = "Daten werden gespeichert";
    if (isset($_POST["f_pass1"]) 
        && isset($_POST["f_pass2"])
        && $_POST["f_pass1"] == $_POST["f_pass2"]
        && strlen($_POST["f_pass1"]) >= $minpasswd_length
        && strlen($_POST["f_pass1"]) <= $maxpasswd_length
        && preg_match($good_chars, $_POST["f_pass1"] )) {
        // Passwoerter stimmen ueberein
        //$html .= "Passwoerter stimmen ueberein.";
        $q = new DB_Usermgmt;
        $salt = substr($_POST["f_pass1"], 0, 2);
        $sql = "UPDATE kunde SET passwort = '".crypt( $_POST["f_pass1"], $salt )
                ."' WHERE username = '".$_SESSION['s_userName']."'";
        $q->query($sql);
        $html .= "<p>Passwort gespeichert.<br></p>";
        $html .= "<input type='button' name='logout' value='Ausloggen' onClick='opener.location.href=\"logout.php\"; self.close();'>"
                ."&nbsp;<input type='button' name='cancel' value='Fenster schliessen' onClick='javascript:self.close();'>";
    } else {
        $showform = TRUE;
        $html .= "<span class='fehler'>Falsche Eingabe. Bitte erneut versuchen.</span><br>";
    }

} // if submit

if ( !isset($_POST["submit"]) || $showform === TRUE ) {

    $title = "Passwort &auml;ndern";
    $html .= "<p>Das Passwort darf zwischen <b>4</b> und maximal <b>12</b> Zeichen lang sein.<br>Bitte verwenden Sie nur Buchstaben und Zahlen und keine Sonderzeichen (',\",\$,&,/,?,|, oder &auml;hnliches). </p>";

    $tpl->set_file(array("form" => "tpl/editpasswForm.tpl.html"));
    $tpl->parse("content","form");

    $tpl->set_var(array(
            "formaction" => $self,
            "username" => $_SESSION['s_userName']
    ));
} // end if not submit

$tpl->set_var(array(
        "title" => $title,
        "body"  => $html
));
$tpl->parse("result","passwd");
$tpl->p("result");
?>
