<?php
/*
 *
 * This file is mostly a placeholder for now.. just wanted to save the results. 
 *  
require_once 'base.php';
require_once W2P_BASE_DIR . '/includes/config.php';

// tweak for pathname consistence on windows machines
require_once W2P_BASE_DIR . '/includes/main_functions.php';
require_once W2P_BASE_DIR . '/includes/db_adodb.php';

require_once W2P_BASE_DIR . '/classes/ui.class.php';
require_once W2P_BASE_DIR . '/classes/permissions.class.php';
require_once W2P_BASE_DIR . '/includes/session.php';

$_SESSION['AppUI'] = new CAppUI;
$AppUI = &$_SESSION['AppUI'];

// load the commonly used classes
require_once ($AppUI->getSystemClass('date'));
require_once ($AppUI->getSystemClass('w2p'));
require_once ($AppUI->getSystemClass('query'));

$q = new DBQuery();
$q->addTable('sysvals');
$sysvals = $q->loadList();
$q->clear();
require_once ($AppUI->getModuleClass('system/syskeys'));
foreach ($sysvals as $sysval) {
    $sysval_obj = new CSysVal();
    $sysval_obj->bind($sysval);
    $sysval_obj->store();
}
*/
?>