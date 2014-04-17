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

if (!$dept_id && !$company_id) {
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
$titleBlock->addCrumb('?m=' . $m, $m . ' list');
$titleBlock->addViewLink('company', $company_id);
if ($dept_id) {
    $titleBlock->addCrumb('?m=departments&a=view&dept_id=' . $dept_id, 'view this department');
}
$titleBlock->show();

// load the department types
$types = w2PgetSysVal('DepartmentType');
$countries = array('' => $AppUI->_('(Select a Country)')) + w2PgetSysVal('GlobalCountriesPreferred') +
		array('-' => '----') + w2PgetSysVal('GlobalCountries');
$dept_parent = ($department->dept_parent) ? $department->dept_parent : $dept_parent;
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
            <p>
                <?php $form->showLabel('Company Name'); ?>
                <?php echo $companyName; ?>
            </p>
            <p>
                <?php $form->showLabel('Name'); ?>
                <?php $form->showField('dept_name', $department->dept_name, array('maxlength' => 255)); ?>
            </p>
            <?php
            if (count($depts) > 0) {
                ?>
                <p>
                    <?php $form->showLabel('Parent'); ?>
                    <?php $form->showField('dept_parent', $department->dept_parent, array(), $depts); ?>
                </p>
            <?php
            } else {
                echo '<input type="hidden" name="dept_parent" value="0">';
            }
            ?>
            <p>
                <?php $form->showLabel('Email'); ?>
                <?php $form->showField('dept_email', $department->dept_email, array('maxlength' => 255)); ?>
            </p>
            <p>
                <?php $form->showLabel('Phone'); ?>
                <?php $form->showField('dept_phone', $department->dept_phone, array('maxlength' => 30)); ?>
            </p>
            <p>
                <?php $form->showLabel('URL'); ?>
                <?php $form->showField('dept_url', $department->dept_url, array('maxlength' => 255)); ?>
            </p>
            <p>
                <?php $form->showLabel('Description'); ?>
                <?php $form->showField('dept_desc', $department->dept_desc); ?>
            </p>
            <?php $form->showCancelButton(); ?>
        </div>
        <div class="column right">
            <p>
                <?php $form->showLabel('Address1'); ?>
                <?php $form->showField('dept_address1', $department->dept_address1, array('maxlength' => 255)); ?>
            </p>
            <p>
                <?php $form->showLabel('Address2'); ?>
                <?php $form->showField('dept_address2', $department->dept_address2, array('maxlength' => 255)); ?>
            </p>
            <p>
                <?php $form->showLabel('City'); ?>
                <?php $form->showField('dept_city', $department->dept_city, array('maxlength' => 50)); ?>
            </p>
            <p>
                <?php $form->showLabel('State'); ?>
                <?php $form->showField('dept_state', $department->dept_state, array('maxlength' => 50)); ?>
            </p>
            <p>
                <?php $form->showLabel('Zip'); ?>
                <?php $form->showField('dept_zip', $department->dept_zip, array('maxlength' => 15)); ?>
            </p>
            <p>
                <?php $form->showLabel('Country'); ?>
                <?php $form->showField('dept_country', $department->dept_country, array(), $countries); ?>
            </p>
            <p>
                <?php $form->showLabel('Fax'); ?>
                <?php $form->showField('dept_fax', $department->dept_fax, array('maxlength' => 30)); ?>
            </p>
            <p>
                <?php $form->showLabel('Owner'); ?>
                <?php
                $perms = &$AppUI->acl();
                $users = $perms->getPermittedUsers('departments');
                ?>
                <?php $form->showField('dept_owner', $department->dept_owner, array(), $users); ?>
            </p>
            <p>
                <?php $form->showLabel('Type'); ?>
                <?php $form->showField('dept_type', $department->dept_type, array(), $types); ?>
            </p>
            <?php $form->showSaveButton(); ?>
        </div>
    </div>
</form>