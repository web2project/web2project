<?php /* $Id: addedituser.php 1517 2010-12-05 08:07:54Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/trunk/modules/admin/addedituser.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$user_id = (int) w2PgetParam($_GET, 'user_id', 0);
$contact_id = (int) w2PgetParam($_GET, 'contact_id', 0);

if ($user_id == 0) {
	$canEdit = $canAuthor;
}

if ($canEdit) {
	$canEdit = $perms->checkModuleItem('users', ($user_id ? 'edit' : 'add'), $user_id);
}

// check permissions
if (!$canEdit && $user_id != $AppUI->user_id) {
	$AppUI->redirect('m=public&a=access_denied');
}

$perms = &$AppUI->acl();
$crole = new CRole;
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

//TODO: These queries should be replaced with the standard load methods.
if ($contact_id) {
	$q = new w2p_Database_Query;
	$q->addTable('contacts', 'con');
	$q->addQuery('con.*, company_id, company_name, dept_name');
	$q->addJoin('companies', 'com', 'contact_company = company_id');
	$q->addJoin('departments', 'dep', 'dept_id = contact_department');
	$q->addWhere('con.contact_id = ' . (int)$contact_id);
} else {
	$q = new w2p_Database_Query;
	$q->addTable('users', 'u');
	$q->addQuery('u.*');
	$q->addQuery('con.*, company_id, company_name, dept_name');
	$q->addJoin('contacts', 'con', 'user_contact = contact_id', 'inner');
	$q->addJoin('companies', 'com', 'contact_company = company_id');
	$q->addJoin('departments', 'dep', 'dept_id = contact_department');
	$q->addWhere('u.user_id = ' . (int)$user_id);
}
$user = $q->loadHash();

if (!$user && $user_id > 0) {
	$titleBlock = new CTitleBlock('Invalid User ID', 'helix-setup-user.png', $m, $m . '.' . $a);
	$titleBlock->addCrumb('?m=admin', 'users list');
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
	$titleBlock = new CTitleBlock($ttl, 'helix-setup-user.png', $m, $m . '.' . $a);
	if (canView('admin') && canView('users')) {
		$titleBlock->addCrumb('?m=admin', 'users list');
	}
	if ($user_id) {
		$titleBlock->addCrumb('?m=admin&a=viewuser&user_id=' . $user_id, 'view this user');
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
    } else if (form.contact_birthday && form.contact_birthday.value.length > 0) {
        dar = form.contact_birthday.value.split("-");
        if (dar.length < 3) {
            alert("<?php echo $AppUI->_('adminInvalidBirthday', UI_OUTPUT_JS); ?>");
            form.contact_birthday.focus();
        } else if (isNaN(parseInt(dar[0],10)) || isNaN(parseInt(dar[1],10)) || isNaN(parseInt(dar[2],10))) {
            alert("<?php echo $AppUI->_('adminInvalidBirthday', UI_OUTPUT_JS); ?>");
            form.contact_birthday.focus();
        } else if (parseInt(dar[1],10) < 1 || parseInt(dar[1],10) > 12) {
            alert("<?php echo $AppUI->_('adminInvalidMonth', UI_OUTPUT_JS) . ' ' . $AppUI->_('adminInvalidBirthday', UI_OUTPUT_JS); ?>");
            form.contact_birthday.focus();
        } else if (parseInt(dar[2],10) < 1 || parseInt(dar[2],10) > 31) {
            alert("<?php echo $AppUI->_('adminInvalidDay', UI_OUTPUT_JS) . ' ' . $AppUI->_('adminInvalidBirthday', UI_OUTPUT_JS); ?>");
            form.contact_birthday.focus();
        } else if(parseInt(dar[0],10) < 1900 || parseInt(dar[0],10) > 2020) {
            alert("<?php echo $AppUI->_('adminInvalidYear', UI_OUTPUT_JS) . ' ' . $AppUI->_('adminInvalidBirthday', UI_OUTPUT_JS); ?>");
            form.contact_birthday.focus();
        } else {
            form.submit();
        }
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

<form name="editFrm" action="./index.php?m=admin" method="post" accept-charset="utf-8">
	<input type="hidden" name="user_id" value="<?php echo (int) $user['user_id']; ?>" />
	<input type="hidden" name="contact_id" value="<?php echo (int) $user['contact_id']; ?>" />
	<input type="hidden" name="dosql" value="do_user_aed" />
	<input type="hidden" name="username_min_len" value="<?php echo w2PgetConfig('username_min_len'); ?>)" />
	<input type="hidden" name="password_min_len" value="<?php echo w2PgetConfig('password_min_len'); ?>)" />
    <table width="100%" border="0" cellpadding="0" cellspacing="1" class="std">
        <tr>
            <td align="right" width="35%" nowrap="nowrap">* <?php echo $AppUI->_('Login Name'); ?>:</td>
            <td>
            <?php
                if ($user["user_username"]) {
                    echo '<input type="hidden" class="text" name="user_username" value="' . $user['user_username'] . '" />';
                    echo '<strong>' . $user["user_username"] . '</strong>';
                } else {
                    echo '<input type="text" class="text" name="user_username" value="' . $user['user_username'] . '" maxlength="255" size="40" />';
                }
            ?>
            </td>
        </tr>
        <?php if ($canEdit) { // prevent users without read-write permissions from seeing and editing user type ?>
        <tr>
            <td align="right" nowrap="nowrap"> <?php echo $AppUI->_('User Type'); ?>:</td>
            <td>
                <?php
                echo arraySelect($utypes, 'user_type', 'class=text size=1', $user['user_type'], true);
                ?>
            </td>
        </tr>
        <?php } // End of security ?>
        <?php if ($canEdit && !$user_id) { ?>
        <tr>
            <td align="right" nowrap="nowrap">* <?php echo $AppUI->_('User Role'); ?>:</td>
            <td><?php echo arraySelect($roles_arr, 'user_role', 'size="1" class="text"', '', true); ?></td>
        </tr>
        <?php }

            if (!$user["user_id"]) {
        ?>
        <tr>
            <td align="right" nowrap="nowrap">* <?php echo $AppUI->_('Password'); ?>:</td>
            <td><input type="password" class="text" name="user_password" value="<?php echo $user['user_password']; ?>" maxlength="32" size="32" onKeyUp="checkPassword(this.value);" /> </td>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap">* <?php echo $AppUI->_('Confirm Password'); ?>:</td>
            <td><input type="password" class="text" name="password_check" value="<?php echo $user['user_password']; ?>" maxlength="32" size="32" /> </td>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Password Strength'); ?></td>
            <td>
                <div class="text" style="width: 135px;">
                    <div id="progressBar" style="font-size: 1px; height: 15px; width: 0px;">
                    </div>
                </div>
            </td>
        </tr>
        <?php }
        ?>
        <tr>
            <td align="right" nowrap="nowrap">* <?php echo $AppUI->_('Name'); ?>:</td>
            <td><input type="text" class="text" name="contact_first_name" value="<?php echo $user['contact_first_name']; ?>" maxlength="50" /> <input type="text" class="text" name="contact_last_name" value="<?php echo $user['contact_last_name']; ?>" maxlength="50" /></td>
        </tr>
        <?php if ($canEdit) { ?>
        <tr>
            <td align="right" nowrap="nowrap"> <?php echo $AppUI->_('Company'); ?>:</td>
            <td>
                <?php
                echo arraySelect($companies, 'contact_company', 'class=text size=1', $user['contact_company']);
                ?>
            </td>
        </tr>
        <?php } ?>
        <tr>
            <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Department'); ?>:</td>
            <td>
                <input type="hidden" name="contact_department" value="<?php echo $user['contact_department']; ?>" />
                <input type="text" class="text" name="dept_name" value="<?php echo $user['dept_name']; ?>" size="40" disabled="disabled" />
                <input type="button" class="button" value="<?php echo $AppUI->_('select dept'); ?>..." onclick="popDept()" />
            </td>
        </tr>
        <tr>
            <td align="right" nowrap="nowrap">* <?php echo $AppUI->_('Email'); ?>:</td>
            <td><input type="text" class="text" name="contact_email" value="<?php echo $user['contact_email']; ?>" maxlength="255" size="40" /> </td>
        </tr>
        <tr>
            <td align="right" valign="top" nowrap="nowrap"><?php echo $AppUI->_('Email') . ' ' . $AppUI->_('Signature'); ?>:</td>
            <td><textarea class="text" cols="50" name="user_signature" style="height: 50px"><?php echo $user["user_signature"]; ?></textarea></td>
        </tr>
        <?php if ($user_id) { ?>
            <tr>
                <td align="right" nowrap="nowrap"><a href="?m=contacts&a=addedit&contact_id=<?php echo $user['contact_id']; ?>"><?php echo $AppUI->_(array('edit', 'contact info')); ?></a></td>
                <td>&nbsp;</td>
            </tr>
        <?php } ?>
        <tr>
            <td align="right" nowrap="nowrap">* <?php echo $AppUI->_('Required Fields'); ?></td>
            <td></td>
        <tr>
            <td align="left">
                <input type="button" value="<?php echo $AppUI->_('back'); ?>" onclick="javascript:history.back(-1);" class="button" />
            </td>
            <?php if ($canEdit && !$user_id) { ?>
                <td width="100%">
                    &nbsp;
                </td>
                <td nowrap="nowrap" align="right">
                    <label for="send_user_mail"><?php echo $AppUI->_('Inform new user of their account details?'); ?></label>
                </td>
            <?php } ?>
            <td nowrap="nowrap" align="right">
                <?php if ($canEdit && !$user_id) { ?>
                    <input type="checkbox" value="1" name="send_user_mail" id="send_user_mail" />&nbsp;&nbsp;&nbsp;
                <?php } ?>
                <input type="button" value="<?php echo $AppUI->_('submit'); ?>" onclick="submitIt()" class="button" />
            </td>
        </tr>
        <?php } ?>
    </table>
</form>