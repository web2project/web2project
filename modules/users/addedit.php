<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
// @todo    convert to template
$object_id = (int) w2PgetParam($_GET, 'user_id', 0);
$contact_id = (int) w2PgetParam($_GET, 'contact_id', 0);


$object = new CUser();
$object->setId($object_id);

$canAddEdit = $object->canAddEdit();
$canAuthor = $object->canCreate();
$canEdit = $object->canEdit();
$canDelete = $object->canAddEdit();
if (!$canAddEdit) {
    $AppUI->redirect(ACCESS_DENIED);
}

$crole = new CSystem_Role;
$roles = $crole->getRoles();
// Format the roles for use in arraySelect
$roles_arr = array();
foreach ($roles as $role) {
    if ($role['name'] != 'Administrator') {
        $roles_arr[$role['id']] = $role['name'];
    } else {
        if ($perms->checkModuleItem('system', 'edit')) {
            $roles_arr[$role['id']] = $role['name'];
        }
    }
}
$roles_arr = arrayMerge(array(0 => ''), $roles_arr);

if ($contact_id) {
    $object = new CContact();
    $object->load($contact_id);
} else {
    $object = new CUser();
    $object->loadFull($object_id);
}

// pull companies
$company = new CCompany();
$companies = $company->getAllowedRecords($AppUI->user_id, 'company_id,company_name', 'company_name');
$companies = arrayMerge(array('0' => ''), $companies);

// setup the title block
$ttl = $object_id ? 'Edit User' : 'Add User';
$titleBlock = new w2p_Theme_TitleBlock($ttl, 'icon.png', $m);
$titleBlock->addCrumb('?m=' . $m, $m . ' list');
$titleBlock->addViewLink('user', $object_id);
$titleBlock->addViewLink('contact', $object->contact_id);

if ($object_id) {
    if ($canEdit || $object_id == $AppUI->user_id) {
        $titleBlock->addCrumb('?m=system&a=addeditpref&user_id=' . $object_id, 'edit preferences');
    }
    if ($canDelete) {
        $titleBlock->addCrumbDelete('delete User', $canDelete, $msg);
    }
}
$titleBlock->show();

$view = new w2p_Controllers_View($AppUI, $object, 'User');
echo $view->renderDelete();

$AppUI->getTheme()->addFooterJavascriptFile('js/passwordstrength.js');
?>
<script language="javascript" type="text/javascript">
    function submitIt(){
        var form = document.editFrm;
        if (form.user_username.value.length < <?php echo w2PgetConfig('username_min_len'); ?> && form.user_username.value != '<?php echo w2PgetConfig('admin_username'); ?>') {
            alert("<?php echo $AppUI->_('adminValidUserName', UI_OUTPUT_JS); ?>"  + <?php echo w2PgetConfig('username_min_len'); ?>);
            form.user_username.focus();
            <?php if ($canEdit && !$object_id) { ?>
        } else if (form.user_role.value <=0 ) {
            alert("<?php echo $AppUI->_('adminValidRole', UI_OUTPUT_JS); ?>");
            form.user_role.focus();
        } else if (form.user_password.value.length < <?php echo w2PgetConfig('password_min_len'); ?>) {
            alert("<?php echo $AppUI->_('adminValidPassword', UI_OUTPUT_JS); ?>" + <?php echo w2PgetConfig('password_min_len'); ?>);
            form.user_password.focus();
        } else if (form.user_password.value !=  form.password_check.value) {
            alert("<?php echo $AppUI->_('adminPasswordsDiffer', UI_OUTPUT_JS); ?>");
            form.user_password.focus();
            <?php } ?>
        } else if (form.contact_first_name.value.length < 1) {
            alert("<?php echo $AppUI->_('adminValidFirstName', UI_OUTPUT_JS); ?>");
            form.contact_first_name.focus();
        } else if (form.contact_last_name.value.length < 1) {
            alert("<?php echo $AppUI->_('adminValidLastName', UI_OUTPUT_JS); ?>");
            form.contact_last_name.focus();
        } else if (form.contact_email.value.length < 4) {
            alert("<?php echo $AppUI->_('adminInvalidEmail', UI_OUTPUT_JS); ?>");
            form.contact_email.focus();
        } else {
            form.submit();
        }
    }

    function popDept() {
        var f = document.editFrm;
        if (f.selectedIndex == 0) {
            alert('<?php echo $AppUI->_('Please select a company first!', UI_OUTPUT_JS); ?>');
        } else {
            window.open('./index.php?m=public&a=selector&dialog=1&callback=setDept&table=departments&company_id='
                + f.contact_company.options[f.contact_company.selectedIndex].value
                + '&dept_id='+f.contact_department.value,'dept','left=50,top=50,height=250,width=400,resizable')
        }
    }

    // Callback function for the generic selector
    function setDept( key, val ) {
        var f = document.editFrm;
        if (val != '') {
            f.contact_department.value = key;
            f.dept_name.value = val;
        } else {
            f.contact_department.value = '0';
            f.dept_name.value = '';
        }
    }
    </script>
    <?php
    /**
     * Note: This is an ugly little hack which makes sure the form stays on the screen in firefox for the wps-redmond
     *   theme. There must be a better way. It also appears in system/addeditpref.php and nowhere else.
     */
    $spacing = ('wps-redmond' == $AppUI->getPref('UISTYLE')) ? 70 : 0;
    echo '<div style="padding-top: ' . $spacing . 'px;"> </div>';
    ?>
</script>
<?php
/**
 * Note: This is an ugly little hack which makes sure the form stays on the screen in firefox for the wps-redmond
 *   theme. There must be a better way. It also appears in system/addeditpref.php and nowhere else.
 */
$spacing = ('wps-redmond' == $AppUI->getPref('UISTYLE')) ? 70 : 0;
echo '<div style="padding-top: ' . $spacing . 'px;"> </div>';

include $AppUI->getTheme()->resolveTemplate('users/addedit');