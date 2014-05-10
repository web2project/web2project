<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

global $AppUI, $company;

$items = $company->users($company->company_id);

$module = new w2p_System_Module();
$fields = $module->loadSettings('users', 'company_view');

if (0 == count($fields)) {
    // TODO: This is only in place to provide an pre-upgrade-safe
    //   state for versions earlier than v3.0
    //   At some point at/after v4.0, this should be deprecated
    $fieldList = array('user_username', 'contact_name', 'user_type');
    $fieldNames = array('Username', 'Name', 'Type');

    $module->storeSettings('users', 'company_view', $fieldList, $fieldNames);

    $fields = array_combine($fieldList, $fieldNames);
}
?>
    <a name="users-company_view"> </a>
<?php

$user_types = w2PgetSysVal('UserType');
$customLookups = array('user_type' => $user_types);

include $AppUI->getTheme()->resolveTemplate('companies/vw_users');