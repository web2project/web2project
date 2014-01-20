<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

$user_id = (int) w2PgetParam($_GET, 'user_id', 0);
$contact_id = (int) w2PgetParam($_GET, 'contact_id', 0);

$perms = &$AppUI->acl();
if ($user_id == 0) {
    $canEdit = $canAuthor;
}

if ($canEdit) {
    $canEdit = $perms->checkModuleItem('users', ($user_id ? 'edit' : 'add'), $user_id);
}

// check permissions
if (!$canEdit && $user_id != $AppUI->user_id) {
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
    $contact = new CContact();
    $contact->loadFull(null, $contact_id);
    $user = get_object_vars($contact);
} else {
    $user = new CUser();
    $user->loadFull($user_id);
    $user = get_object_vars($user);
}

if (!$user && $user_id > 0) {
    $titleBlock = new w2p_Theme_TitleBlock('Invalid User ID', 'helix-setup-user.png', $m, $m . '.' . $a);
    $titleBlock->addCrumb('?m=users', 'users list');
    $titleBlock->show();
} else {
    if (!$user_id && !$contact_id) {
        $user['contact_id'] = 0;
    }
    // pull companies
    $company = new CCompany();
    $companies = $company->getAllowedRecords($AppUI->user_id, 'company_id,company_name', 'company_name');
    $companies = arrayMerge(array('0' => ''), $companies);

    // setup the title block
    $ttl = $user_id ? 'Edit User' : 'Add User';
    $titleBlock = new w2p_Theme_TitleBlock($ttl, 'icon.png', $m, $m . '.' . $a);
    if (canView('users')) {
        $titleBlock->addCrumb('?m=users', 'users list');
    }
    if ($user_id) {
        $titleBlock->addCrumb('?m=users&a=view&user_id=' . $user_id, 'view this user');
        if ($user['contact_id'] > 0) {
            $titleBlock->addCrumb('?m=contacts&a=view&contact_id='.$user['contact_id'], 'view this contact');
        }
        if ($canEdit || $user_id == $AppUI->user_id) {
            $titleBlock->addCrumb('?m=system&a=addeditpref&user_id=' . $user_id, 'edit preferences');
        }
    }
    $titleBlock->show();

    $AppUI->addFooterJavascriptFile('js/passwordstrength.js');
    ?>
    <script language="javascript" type="text/javascript">
        function submitIt(){
            var form = document.editFrm;
            if (form.user_username.value.length < <?php echo w2PgetConfig('username_min_len'); ?> && form.user_username.value != '<?php echo w2PgetConfig('admin_username'); ?>') {
                alert("<?php echo $AppUI->_('adminValidUserName', UI_OUTPUT_JS); ?>"  + <?php echo w2PgetConfig('username_min_len'); ?>);
                form.user_username.focus();
                <?php if ($canEdit && !$user_id) { ?>
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
    <?php

    $form = new w2p_Output_HTML_FormHelper($AppUI);

    ?>
    <form name="editFrm" action="?m=<?php echo $m; ?>" method="post" accept-charset="utf-8" class="admin addedit">
        <input type="hidden" name="user_id" value="<?php echo (int) $user['user_id']; ?>" />
        <input type="hidden" name="contact_id" value="<?php echo (int) $user['contact_id']; ?>" />
        <input type="hidden" name="dosql" value="do_user_aed" />
        <input type="hidden" name="username_min_len" value="<?php echo w2PgetConfig('username_min_len'); ?>)" />
        <input type="hidden" name="password_min_len" value="<?php echo w2PgetConfig('password_min_len'); ?>)" />
        <?php echo $form->addNonce(); ?>

        <div class="std addedit users">
            <div class="column left">
                <p>
                    <?php $form->showLabel('Login Name'); ?>
                    <?php
                    if ($user["user_username"]) {
                        echo '<input type="hidden" class="text" name="user_username" value="' . $user['user_username'] . '" />';
                        echo '<strong>' . $user["user_username"] . '</strong>';
                    } else {
                        echo '<input type="text" class="text" name="user_username" value="' . $user['user_username'] . '" maxlength="255" size="40" />';
                    }
                    ?>
                </p>
                <?php if (!$user["user_id"]) { ?>
                <p>
                    <?php $form->showLabel('Password'); ?>
                    <input type="password" class="text" name="user_password" value="<?php echo $user['user_password']; ?>" maxlength="32" size="32" onKeyUp="checkPassword(this.value);" />
                </p>
                <p>
                    <?php $form->showLabel('Confirm Password'); ?>
                    <input type="password" class="text" name="password_check" value="<?php echo $user['user_password']; ?>" maxlength="32" size="32" />
                </p>
                <?php } ?>
                <p>
                    <?php $form->showLabel('Name'); ?>
                    <input type="text" class="text" name="contact_first_name" value="<?php echo $user['contact_first_name']; ?>" maxlength="50" /> <input type="text" class="text" name="contact_last_name" value="<?php echo $user['contact_last_name']; ?>" maxlength="50" />
                </p>
                <p>
                    <?php $form->showLabel('Company'); ?>
                    <?php
                    echo arraySelect($companies, 'contact_company', 'class=text size=1', $user['contact_company']);
                    ?>
                </p>
                <p>
                    <?php $form->showLabel('Department'); ?>
                    <input type="hidden" name="contact_department" value="<?php echo $user['contact_department']; ?>" />
                    <input type="text" class="text" name="dept_name" value="<?php echo $user['dept_name']; ?>" size="40" disabled="disabled" />
                    <input type="button" class="button btn btn-primary btn-mini" value="<?php echo $AppUI->_('select dept'); ?>..." onclick="popDept()" />
                </p>
                <?php $form->showCancelButton(); ?>
            </div>
            <div class="column right">
                <?php if ($canEdit && !$user_id) { ?>
                <p>
                    <?php $form->showLabel('User Role'); ?>
                    <?php echo arraySelect($roles_arr, 'user_role', 'size="1" class="text"', '', true); ?>
                </p>
                <?php } ?>
                <?php if (!$user["user_id"]) { ?>
                <p>
                    <?php $form->showLabel('Password Strength'); ?>
                    <div class="text" style="width: 135px; margin-left: 18.5em">
                        <div id="progressBar"></div>
                    </div>
                </p>
                <?php } ?>
                <p>
                    <?php $form->showLabel('Email'); ?>
                    <input type="text" class="text" name="contact_email" value="<?php echo $user['contact_email']; ?>" maxlength="255" size="40" />
                </p>
                <p>
                    <?php $form->showLabel('Email Signature'); ?>
                    <textarea class="text" name="user_signature"><?php echo $user["user_signature"]; ?></textarea>
                </p>
                <p>
                    <?php $form->showLabel('Inform new user of account details?'); ?>
                    <input type="checkbox" value="1" name="send_user_mail" id="send_user_mail" />
                </p>
                <?php $form->showSaveButton(); ?>
            </div>
        </div>

    </form>
<?php } ?>