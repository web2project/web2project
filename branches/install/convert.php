<?php /* $Id$ $URL$ */

/*
If you don't do this the sysval will only have one record.
The other way of solving this is to create script to do that for the user something like (this is not tested and is only for mental reference):
*/
require_once '../base.php';
require_once '/home/dpserver/public_html/cs' . '/includes/config.php';

//require_once '/home/dpserver/public_html/cs' . '/includes/main_functions.php';
require_once '/home/dpserver/public_html/cs' . '/includes/db_adodb.php';
require_once '/home/dpserver/public_html/cs' . '/classes/ui.class.php';
require_once '/home/dpserver/public_html/cs' . '/classes/permissions.class.php';
require_once '/home/dpserver/public_html/cs' . '/includes/session.php';

$_SESSION['AppUI'] = new CAppUI;
$AppUI = &$_SESSION['AppUI'];

$q = new DBQuery();
$q->addTable('sysvals');
$sysvals = $q->loadList();
$q->clear();
require_once ('../modules/system/syskeys/syskeys.class.php');
foreach ($sysvals as $sysval) {
    $sysval_obj = new CSysVal();
    $sysval_obj->bind($sysval);
//print_r($sysval_obj);
    $sysval_obj->store();
}

/* 
 *  Basically  we capture all the records from the sysvals table and we store
 * them using the new sysvals schema which does the separation of the values
 * into the sysvals table back again.
 */

?>