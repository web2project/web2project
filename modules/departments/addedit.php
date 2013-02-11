<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

// Add / Edit Company
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

// load the record data
$obj = $AppUI->restoreObject();
if ($obj) {
    $department = $obj;
    $dept_id = $department->dept_id;
} else {
    $department->loadFull(null, $dept_id);
}
if (!$department && $dept_id > 0) {
    $AppUI->setMsg('Department');
    $AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
    $AppUI->redirect();
}

$companyName = $department->company_name;

$company_id = $dept_id ? $department->dept_company : $company_id;

if (!$dept_id && $department->company_name === null) {
    $AppUI->setMsg('badCompany', UI_MSG_ERROR);
    $AppUI->redirect();
}

// collect all the departments in the company
if ($company_id) {
    $company = new CCompany();
    $company->loadFull(null, $company_id);
    $companyName = $company->company_name;
    $depts = $department->loadOtherDepts(null, $company_id, 0);
    $depts = arrayMerge(array('0' => '- ' . $AppUI->_('Select Department') . ' -'), $depts);
}

// setup the title block
$ttl = $dept_id > 0 ? 'Edit Department' : 'Add Department';
$titleBlock = new w2p_Theme_TitleBlock($ttl, 'departments.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=departments', 'department list');
$titleBlock->addCrumb('?m=companies', 'companies list');
$titleBlock->addCrumb('?m=companies&a=view&company_id=' . $company_id, 'view this company');
$titleBlock->addCrumb('?m=departments&a=view&dept_id=' . $dept_id, 'view this department');
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

<form name="editFrm" action="?m=departments" method="post" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_dept_aed" />
	<input type="hidden" name="dept_id" value="<?php echo $dept_id; ?>" />
	<input type="hidden" name="dept_company" value="<?php echo $company_id; ?>" />
	<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std addedit">
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Department Company'); ?>:</td>
			<td><strong><?php echo $companyName; ?></strong></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Department Name'); ?>:</td>
			<td>
				<input type="text" class="text" name="dept_name" value="<?php echo $department->dept_name; ?>" size="50" maxlength="255" />
				<span class="smallNorm">(<?php echo $AppUI->_('required'); ?>)</span>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Email'); ?>:</td>
			<td>
				<input type="text" class="text" name="dept_email" value="<?php echo $department->dept_email; ?>" size="50" maxlength="255" />
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Phone'); ?>:</td>
			<td>
				<input type="text" class="text" name="dept_phone" value="<?php echo $department->dept_phone; ?>" maxlength="30" />
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Fax'); ?>:</td>
			<td>
				<input type="text" class="text" name="dept_fax" value="<?php echo $department->dept_fax; ?>" maxlength="30" />
			</td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Address'); ?>1:</td>
			<td><input type="text" class="text" name="dept_address1" value="<?php echo $department->dept_address1; ?>" size="50" maxlength="255" /></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Address'); ?>2:</td>
			<td><input type="text" class="text" name="dept_address2" value="<?php echo $department->dept_address2; ?>" size="50" maxlength="255" /></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('City'); ?>:</td>
			<td><input type="text" class="text" name="dept_city" value="<?php echo $department->dept_city; ?>" size="50" maxlength="50" /></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('State'); ?>:</td>
			<td><input type="text" class="text" name="dept_state" value="<?php echo $department->dept_state; ?>" maxlength="50" /></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Zip'); ?>:</td>
			<td><input type="text" class="text" name="dept_zip" value="<?php echo $department->dept_zip; ?>" maxlength="15" /></td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Country'); ?>:</td>
			<td>
				<?php
					echo arraySelect($countries, 'dept_country', 'size="1" class="text"', $department->dept_country ? $department->dept_country : 0);
				?>
			</td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('URL'); ?><a name="x"></a></td>
			<td>
				<input type="text" class="text" value="<?php echo $department->dept_url; ?>" name="dept_url" size="50" maxlength="255" />
				<a href="javascript: void(0);" onclick="testURL('dept_url')">[<?php echo $AppUI->_('test'); ?>]</a>
			</td>
		</tr>
		<?php
			if (count($depts) > 0) {
				?>
				<tr>
					<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Department Parent'); ?>:</td>
					<td>
						<?php
                            $dept_parent = ($department->dept_parent) ? $department->dept_parent : $dept_parent;
							echo arraySelect($depts, 'dept_parent', 'class=text size=1', $dept_parent);
						?>
					</td>
				</tr>
				<?php 
			} else {
				echo '<input type="hidden" name="dept_parent" value="0">';
			}
		?>
		<tr>
			<td align="right"><?php echo $AppUI->_('Owner'); ?>:</td>
			<td>
				<?php
                    // check permissions for this record
                    $perms = &$AppUI->acl();
					// collect all active users for the department owner list
					$users = $perms->getPermittedUsers('projects');
					$owners =array('' => $AppUI->_('(Select a user)')) +  $users;
                    $owner_id = ($department->dept_owner) ? $department->dept_owner : $AppUI->user_id;
					echo arraySelect($owners, 'dept_owner', 'size="1" class="text"', $owner_id);
				?>
			</td>
		</tr>
		<tr>
			<td align="right"><?php echo $AppUI->_('Type'); ?>:</td>
			<td>
				<?php
					echo arraySelect($types, 'dept_type', 'size="1" class="text"', $department->dept_type, true);
				?>
			</td>
		</tr>
		<tr>
			<td align="right" valign="top" nowrap="nowrap"><?php echo $AppUI->_('Description'); ?>:</td>
			<td align="left">
				<textarea cols="70" rows="10" class="textarea" name="dept_desc"><?php echo $department->dept_desc; ?></textarea>
			</td>
		</tr>
		<tr>
			<td>
				<input type="button" value="<?php echo $AppUI->_('back'); ?>" class="button" onclick="javascript:history.back(-1);" />
			</td>
			<td align="right">
				<input type="button" value="<?php echo $AppUI->_('submit'); ?>" class="button" onclick="submitIt()" />
			</td>
		</tr>
	</table>
</form>