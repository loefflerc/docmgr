<?php

print "My system salt size is: ". CRYPT_SALT_LENGTH . "<br>\n";


include ("inc/db_wmcdb.inc.php");


$sql_getpw = " SELECT uid, passwort from kunde_bak ";

$db = new DB_Usermgmt;
$db->connect();

$db->query( $sql_getpw );

$arPw = array();
while ( $db->next_record() ) {
    $arPw[$db->f("uid")] = $db->f("passwort");
}
$db->free();


foreach( $arPw as $userid => $pw ) {
    $salt = substr( $pw, 0, 2);
    $sql_enc = " UPDATE kunde set passwort = '".crypt( $pw, $salt )."' WHERE uid = ".$userid;
    print $sql_enc." <br>\n";
    $db->query( $sql_enc );
}



?>
