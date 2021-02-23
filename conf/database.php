<?php

$db_config = new Zend_Config(
    array(
        'database' => array(
            'adapter' => 'Mysqli',
            'params'  => array(
                'host'     => '127.0.0.1',
                'dbname'   => 'docmgr',
                'username' => 'root',
                'password' => 'markoff',
            )
        )
    )
);