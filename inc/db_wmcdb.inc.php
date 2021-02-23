<?php
/**
 *  $Id: db_wmcdb.inc.php,v 1.1.1.1 2004/04/14 17:31:01 chris Exp $
 *
 */
include_once("inc/config.inc.php");
include ($_SERVER["DOCUMENT_ROOT"] . $sec_web . "inc/db_mysql.inc.php");

class DB_Usermgmt extends DB_Sql {

    /* public: connection parameters */
    var $Host     = DB_HOST;
    var $Database = DB_NAME;
    var $User     = DB_USER;
    var $Password = DB_PASS;

    function haltmsg($msg) {
        printf("</td></table><b>Database error:</b> %s<br>\n", $msg);
        printf("<b>MySQL Error</b>: %s (%s)<br>\n",
                $this->Errno, $this->Error);
        printf("Bitte nehmen Sie Kontakt mit webadmin@xtraport.de auf und berichten Sie ");
        printf("die genaue Fehlermeldung.<br>Danke.<br>\n");
    }
}
?>