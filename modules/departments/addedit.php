<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template
$dept_id = (int) w2PgetParam($_GET, 'dept_id', 0);
$dept_parent = (int) w2PgetParam($_GET, 'dept_parent', 0);
$company_id = (int) w2PgetParam($_GET, 'company_id', 0);

$department = new CDepartment();
$department->dept_id = $dept_id;

$obj = $department;
$canAddEdit = $obj->canAddEdit();
$canAuthor = $obj->canCreate();
$canEdit = $obj->canEdit();
if (!$canAddEdit) {
	$AppUI->redirect(ACCESS_DENIED);
}

$obj = $AppUI->restoreObject();
if ($obj) {
    $department = $obj;
    $dept_id = $department->dept_id;
} else {
    $department->load($dept_id);
}
if (!$department && $dept_id > 0) {
    $AppUI->setMsg('Department');
    $AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
    $AppUI->redirect();
}

$company_id = $department->dept_id ? $department->dept_company : $company_id;

if (!$dept_id && $department->company_name === null) {
    $AppUI->setMsg('badCompany', UI_MSG_ERROR);
    $AppUI->redirect();
}

// collect all the departments in the company
if ($company_id) {
    $company = new CCompany();
    $company->load($company_id);
    $companyName = $company->company_name;
    $depts = $department->loadOtherDepts(null, $company_id, 0);
    $depts = arrayMerge(array('0' => '- ' . $AppUI->_('Select Department') . ' -'), $depts);
}

// setup the title block
$ttl = $dept_id > 0 ? 'Edit Department' : 'Add Department';
$titleBlock = new w2p_Theme_TitleBlock($ttl, 'icon.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=companies', 'companies list');
$titleBlock->addCrumb('?m=companies&a=view&company_id=' . $company_id, 'view this company');
$titleBlock->addCrumb('?m=departments', 'department list');
if ($dept_id) {
    $titleBlock->addCrumb('?m=departments&a=view&dept_id=' . $dept_id, 'view this department');
}
$titleBlock->show();

// load the department types
$types = w2PgetSysVal('DepartmentType');
$countries = array('' => $AppUI->_('(Select a Country)')) + w2PgetSysVal('GlobalCountriesPreferred') +
		array('-' => '----') + w2PgetSysVal('GlobalCountries');

?>
<script language="javascript" type="text/javascript">
function testURL( x ) {
	var test = 'document.editFrm.dept_url.value';
	test = eval(test);
	if (test.length > 6) {
		newwin = window.open( 'http://' + test, 'newwin', '' );
	}
}

function submitIt() {
	var form = document.editFrm;
	if (form.dept_name.value.length < 2) {
		alert( '<?php echo $AppUI->_('deptValidName', UI_OUTPUT_JS); ?>' );
		form.dept_name.focus();
	} else {
		form.submit();
	}
}
</script>
<?php

$form = new w2p_Output_HTML_FormHelper($AppUI);

?>
<form name="editFrm" action="?m=<?php echo $m; ?>" method="post" accept-charset="utf-8" class="addedit departments">
	<input type="hidden" name="dosql" value="do_dept_aed" />
	<input type="hidden" name="dept_id" value="<?php echo $dept_id; ?>" />
	<input type="hidden" name="dept_company" value="<?php echo $company_id; ?>" />
    <?php echo $form->addNonce(); ?>

    <div class="std addedit departments">
        <div class="column left">
            <p><label><?php echo $AppUI->_('Company Name'); ?>:</label><?php echo $companyName; ?></p>
            <p>
                <label><?php echo $AppUI->_('Name'); ?>:</label>
                <input type="text" class="text" name="dept_name" value="<?php echo $department->dept_name; ?>" size="50" maxlength="255" />
            </p>
            <?php
            if (count($depts) > 0) {
                ?>
                <p>
                    <label><?php echo $AppUI->_('Parent'); ?>:</label>
                    <?php
                    $dept_parent = ($department->dept_parent) ? $department->dept_parent : $dept_parent;
                    echo arraySelect($depts, 'dept_parent', 'class=text size=1', $dept_parent);
                    ?>
                </p>
            <?php
            } else {
                echo '<input type="hidden" name="dept_parent" value="0">';
            }
            ?>
            <p>
                <label><?php echo $AppUI->_('Email'); ?>:</label>
                <input type="text" class="text" name="dept_email" value="<?php echo $department->dept_email; ?>" size="50" maxlength="255" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Phone'); ?>:</label>
                <input type="text" class="text" name="dept_phone" value="<?php echo $department->dept_phone; ?>" maxlength="30" />
            </p>
            <p>
                <label><?php echo $AppUI->_('URL'); ?>:</label>
                <input type="text" class="text" value="<?php echo $department->dept_url; ?>" name="dept_url" size="50" maxlength="255" />
                <a href="javascript: void(0);" onclick="testURL('dept_url')">[<?php echo $AppUI->_('test'); ?>]</a>
            </p>
            <p>
                <label><?php echo $AppUI->_('Description'); ?>:</label>
                <textarea name="dept_desc"><?php echo $department->dept_desc; ?></textarea>
            </p>
            <p><input type="button" value="back" class="cancel button btn btn-danger" onclick="javascript:history.back(-1);" /></p>
        </div>
        <div class="column right">
            <p>
                <label><?php echo $AppUI->_('Address'); ?>1:</label>
                <input type="text" class="text" name="dept_address1" value="<?php echo $department->dept_address1; ?>" size="50" maxlength="255" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Address'); ?>2:</label>
                <input type="text" class="text" name="dept_address2" value="<?php echo $department->dept_address2; ?>" size="50" maxlength="255" />
            </p>
            <p>
                <label><?php echo $AppUI->_('City'); ?>:</label>
                <input type="text" class="text" name="dept_city" value="<?php echo $department->dept_city; ?>" size="50" maxlength="50" />
            </p>
            <p>
                <label><?php echo $AppUI->_('State'); ?>:</label>
                <input type="text" class="text" name="dept_state" value="<?php echo $department->dept_state; ?>" maxlength="50" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Zip'); ?>:</label>
                <input type="text" class="text" name="dept_zip" value="<?php echo $department->dept_zip; ?>" maxlength="15" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Country'); ?>:</label>
                <?php
                echo arraySelect($countries, 'dept_country', 'size="1" class="text"', $department->dept_country ? $department->dept_country : 0);
                ?>
            </p>
            <p>
                <label><?php echo $AppUI->_('Fax'); ?>:</label>
                <input type="text" class="text" name="dept_fax" value="<?php echo $department->dept_fax; ?>" maxlength="30" />
            </p>
            <p>
                <label><?php echo $AppUI->_('Owner'); ?>:</label>
                <?php
                // check permissions for this record
                $perms = &$AppUI->acl();
                // collect all active users for the department owner list
                $users = $perms->getPermittedUsers('departments');
                echo arraySelect($users, 'dept_owner', 'size="1" class="text"', $department->dept_owner);
                ?>
            </p>
            <p>
                <label><?php echo $AppUI->_('Type'); ?>:</label>
                <?php
                echo arraySelect($types, 'dept_type', 'size="1" class="text"', $department->dept_type, true);
                ?>
            </p>
            <p><input type="button" value="save" class="save button btn btn-primary" onclick="submitIt()" /></p>
        </div>
    </div>
</form>