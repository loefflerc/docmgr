<?php

print "Login<br>\n";

if ( $_POST['submit'] ) {
    include ( "inc/db_wmcdb.inc.php" );
    $user = addslashes($_POST['f_name']);
    $input_pw = $_POST['f_pw'];
    $salt = substr( $input_pw, 0, 2 );
    $sql_getpw = " SELECT uid, Vorname, Nachname from kunde_sec WHERE username = '"
            . $user
            ."' AND passwort LIKE '"
            . crypt( $input_pw, $salt )."'";

    $db = new DB_Usermgmt;
    $db->connect();
    print "Query: $sql_getpw<br>";
    $db->query( $sql_getpw );

    if ( $db->num_rows() == 1 ) {
        $db->next_record();
        $uid = $db->f("uid");
        $name = $db->f("Vorname"). " " .$db->f("Nachname");
        print "hallo ". $name;
    } else {
        print "not authorized.";
    }
    $db->free();

}
?>
<form action="" method="post">
user: <input type="text" name="f_name" value=""><br>
passwort: <input type="password" name="f_pw" value=""><br>
<input type="submit" name="submit" value="enter">
</form>
