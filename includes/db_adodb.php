<?php
/*
Based on Leo West's (west_leo@yahooREMOVEME.com):
lib.DB
Database abstract layer
-----------------------
ADODB VERSION
-----------------------
A generic database layer providing a set of low to middle level functions
originally written for WEBO project, see webo source for "real life" usages
*/
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

require_once (W2P_BASE_DIR . '/lib/adodb/adodb.inc.php');

$db = NewADOConnection(w2PgetConfig('dbtype'));

$connection = new w2p_Database_Connection($db);
$connection->connect(w2PgetConfig('dbhost'), w2PgetConfig('dbname'), w2PgetConfig('dbuser'), w2PgetConfig('dbpass'), w2PgetConfig('dbpersist'));

$charset = w2PgetConfig('dbchar', 'utf8');
/** This explicitly sets the character set of the connection. */
//if ('mysql' == w2PgetConfig('dbtype') && '' != $charset) {
//    $sql = "SET NAMES $charset";
//    $db->Execute($sql);
//}

/*
* Having successfully established the database connection now,
* we will hurry up to load the system configuration details from the database.
*/

$sql = 'SELECT config_name, config_value, config_type FROM ' . w2PgetConfig('dbprefix') . 'config';
$rs = $db->Execute($sql);

if ($rs) { // Won't work in install mode.
    $rsArr = $rs->GetArray();

    switch (strtolower(trim(w2PgetConfig('dbtype')))) {
        case 'oci8':
        case 'oracle':
            foreach ($rsArr as $c) {
                if ($c['CONFIG_TYPE'] == 'checkbox') {
                    $c['CONFIG_VALUE'] = ($c['CONFIG_VALUES'] == 'true') ? true : false;
                }
                $w2Pconfig[$c['CONFIG_NAME']] = $c['CONFIG_VALUE'];
            }
            break;
        default:
        //mySQL
            foreach ($rsArr as $c) {
                if ($c['config_type'] == 'checkbox') {
                    $c['config_value'] = ($c['config_value'] == 'true') ? true : false;
                }
                $w2Pconfig[$c['config_name']] = $c['config_value'];
            }
    }
}
