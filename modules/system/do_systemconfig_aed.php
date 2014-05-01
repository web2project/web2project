<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    refactor to use a core controller

if (!canEdit('system')) {
	$AppUI->redirect(ACCESS_DENIED);
}

$obj = new w2p_System_Config();

__extract_from_systemconfig_aed();


foreach ($_POST['w2Pcfg'] as $name => $value) {
    $obj->config_name = $name;
	$obj->config_value = $value;

	// grab the appropriate id for the object in order to ensure
	// that the db is updated well (config_name must be unique)
	$obj->config_id = $_POST['w2PcfgId'][$name];
    $update = false;

    // This is really kludgy, but it works.. suggestions?
    if (strpos($name, '_pass') !== false) {
        if (1 == $_POST[$name.'_mod']) {
            $update = true;
        }
    } else {
        $update = true;
    }

    if ($update) {
        // prepare (and translate) the module name ready for the suffix
        $AppUI->setMsg('System Configuration');
        if ($obj->store()) {
            $AppUI->setMsg('updated', UI_MSG_OK, true);
        } else {
            $AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
        }
    }
}
$obj->cleanUp();
$AppUI->redirect('m=system&a=systemconfig');