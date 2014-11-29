<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
$object_id = (int) w2PgetParam($_GET, 'user_id', 0);

$tab = $AppUI->processIntState('UserVwTab', $_GET, 'tab', 0);
$addPwT = $AppUI->processIntState('addProjWithTasks', $_POST, 'add_pwt', 0);

$object = new CUser();

if (!$object->load($object_id)) {
    $AppUI->redirect(ACCESS_DENIED);
}

$canEdit   = $object->canEdit();

$object->loadFull($object_id);

global $addPwT, $company_id, $dept_ids, $department, $min_view, $m, $a;

if ($object_id != $AppUI->user_id && (!$perms->checkModuleItem('users', 'view', $object_id) || !$perms->checkModuleItem('users', 'view', $object_id))) {
    $AppUI->redirect(ACCESS_DENIED);
}

$company_id = $AppUI->getState('UsrProjIdxCompany') !== null ? $AppUI->getState('UsrProjIdxCompany') : $AppUI->user_company;

$company_prefix = 'company_';

if (isset($_POST['department'])) {
    $AppUI->setState('UsrProjIdxDepartment', $_POST['department']);

    //if department is set, ignore the company_id field
    unset($company_id);
}
$department = $AppUI->getState('UsrProjIdxDepartment') !== null ? $AppUI->getState('UsrProjIdxDepartment') : $company_prefix . $AppUI->user_company;

//if $department contains the $company_prefix string that it's requesting a company and not a department.  So, clear the
// $department variable, and populate the $company_id variable.
if (!(strpos($department, $company_prefix) === false)) {
    $company_id = substr($department, strlen($company_prefix));
    $AppUI->setState('UsrProjIdxCompany', $company_id);
    unset($department);
}

$contact = new CContact();
$contact->contact_id = $object->user_contact;
$methods = $contact->getContactMethods();
$methodLabels = w2PgetSysVal('ContactMethods');

$countries = w2PgetSysVal('GlobalCountries');
// setup the title block
$titleBlock = new w2p_Theme_TitleBlock('View User', 'icon.png', $m);
$titleBlock->addCrumb('?m=' . $m, $m . ' list');
if ($canEdit || $object_id == $AppUI->user_id) {
    $titleBlock->addCell('<div class="crumb"><ul><li><a href="javascript: void(0);" onclick="popChgPwd();return false"><span>' . $AppUI->_('change password') . '</span></a></li></ul></div>');
    $titleBlock->addCrumb('?m=users&a=addedit&user_id='.$object_id, 'edit this user');
    $titleBlock->addCrumb('?m=contacts&a=addedit&contact_id='.$object->user_contact, 'edit this contact');
    $titleBlock->addCrumb('?m=system&a=addeditpref&user_id='.$object_id, 'edit preferences');
}
$titleBlock->show();
?>
<script language="javascript" type="text/javascript">
    <?php
        // security improvement:
        // some javascript functions may not appear on client side in case of user not having write permissions
        // else users would be able to arbitrarily run 'bad' functions
        if ($canEdit || $object_id == $AppUI->user_id) {
    ?>
    function popChgPwd()
    {
        window.open( './index.php?m=public&a=chpwd&dialog=1&user_id=<?php echo $object->user_id; ?>', 'chpwd', 'top=250,left=250,width=350, height=220, scrollbars=no' );
    }
    <?php } ?>
</script>
<?php
$utypes = w2PgetSysVal('UserType');

include $AppUI->getTheme()->resolveTemplate($m . '/' . $a);

// tabbed information boxes
$min_view = true;
$tabBox = new CTabBox('?m=users&a=view&user_id='.$object_id, '', $tab);
$tabBox->add(W2P_BASE_DIR . '/modules/users/vw_usr_log', 'User Log');
$tabBox->add(W2P_BASE_DIR . '/modules/users/vw_usr_perms', 'Permissions');
$tabBox->add(W2P_BASE_DIR . '/modules/users/vw_usr_roles', 'Roles');
$tabBox->show();
