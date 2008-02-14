<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI, $cal_sdf;
$AppUI->loadCalendarJS();

$project_id = intval(w2PgetParam($_GET, 'project_id', 0));
$company_id = intval(w2PgetParam($_GET, 'company_id', 0));
$contact_id = intval(w2PgetParam($_GET, 'contact_id', 0));

$structprojs = getProjects();
unset($structprojs[$project_id]);
$structprojects = arrayMerge(array('0' => array(0 => 0, 1 => '(' . $AppUI->_('No Parent') . ')', 2 => '')), $structprojs);

$perms = &$AppUI->acl();
// check permissions for this record
$canEdit = $perms->checkModuleItem($m, 'edit', $project_id);
$canAuthor = $perms->checkModuleItem($m, 'add');
if ((!$canEdit && $project_id > 0) || (!$canAuthor && $project_id == 0)) {
	$AppUI->redirect('m=public&a=access_denied');
}

// get a list of permitted companies
require_once ($AppUI->getModuleClass('companies'));

$row = new CCompany();
$companies = $row->getAllowedRecords($AppUI->user_id, 'company_id,company_name', 'company_name');
$companies = arrayMerge(array('0' => ''), $companies);

// pull users
$users = w2PgetUsers();

// load the record data
$row = new CProject();

if (!$row->load($project_id, false) && $project_id > 0) {
	$AppUI->setMsg('Project');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect();
} elseif (count($companies) < 2 && $project_id == 0) {
	$AppUI->setMsg('noCompanies', UI_MSG_ERROR, true);
	$AppUI->redirect();
}

if ($project_id == 0 && $company_id > 0) {
	$row->project_company = $company_id;
}

// add in the existing company if for some reason it is dis-allowed
if ($project_id && !array_key_exists($row->project_company, $companies)) {
	$q = new DBQuery;
	$q->addTable('companies');
	$q->addQuery('company_name');
	$q->addWhere('companies.company_id = ' . $row->project_company);
	$companies[$row->project_company] = $q->loadResult();
	$q->clear();
}

// get critical tasks (criteria: task_end_date)
$criticalTasks = ($project_id > 0) ? $row->getCriticalTasks() : null;

// get ProjectPriority from sysvals
$projectPriority = w2PgetSysVal('ProjectPriority');

// format dates
$df = $AppUI->getPref('SHDATEFORMAT');

$start_date = new CDate($row->project_start_date);

$end_date = intval($row->project_end_date) ? new CDate($row->project_end_date) : null;
$actual_end_date = intval($criticalTasks[0]['task_end_date']) ? new CDate($criticalTasks[0]['task_end_date']) : null;
$style = (($actual_end_date > $end_date) && !empty($end_date)) ? 'style="color:red; font-weight:bold"' : '';

// setup the title block
$ttl = $project_id > 0 ? 'Edit Project' : 'New Project';
$titleBlock = new CTitleBlock($ttl, 'applet3-48.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=projects', 'projects list');
if ($project_id != 0) {
	$titleBlock->addCrumb('?m=projects&a=view&project_id=' . $project_id, 'view this project');
}
$titleBlock->show();

//Build display list for departments
$company_id = $row->project_company;
$selected_departments = array();
if ($project_id) {
	$q = &new DBQuery;
	$q->addTable('project_departments', 'pd');
	$q->addTable('departments', 'deps');
	$q->addQuery('department_id');
	$q->addWhere('project_id = ' . $project_id);
	$q->addWhere('pd.department_id = deps.dept_id');
	$department = new CDepartment;
	$department->setAllowedSQL($AppUI->user_id, $q);
	$selected_departments = $q->loadColumn();
}
$departments_count = 0;
$department_selection_list = getDepartmentSelectionList($company_id, $selected_departments);
if ($department_selection_list != '' || $project_id) {
	$department_selection_list = ($AppUI->_('Departments') . '<br />' . "\n" . '<select name="dept_ids[]" class="text">' . "\n" . '<option value="0"></option>' . "\n" . "{$department_selection_list}\n" . '</select>');
} else {
	$department_selection_list = '<input type="button" class="button" value="' . $AppUI->_('Select department...') . '" onclick="javascript:popDepartment();" /><input type="hidden" name="project_departments"';
}

// Get contacts list
$selected_contacts = array();
if ($project_id) {
	$q = &new DBQuery;
	$q->addTable('project_contacts');
	$q->addQuery('contact_id');
	$q->addWhere('project_id = ' . $project_id);
	$res = &$q->exec();
	for ($res; !$res->EOF; $res->MoveNext()) {
		$selected_contacts[] = $res->fields['contact_id'];
	}
	$q->clear();
}
if ($project_id == 0 && $contact_id > 0) {
	$selected_contacts[] = '' . $contact_id;
}
?>
<script language="javascript">
function setColor(color) {
	var f = document.editFrm;
	if (color) {
		f.project_color_identifier.value = color;
	}
	//test.style.background = f.project_color_identifier.value;
	document.getElementById('test').style.background = '#' + f.project_color_identifier.value; 		//fix for mozilla: does this work with ie? opera ok.
}

function setShort() {
	var f = document.editFrm;
	var x = 10;
	if (f.project_name.value.length < 11) {
		x = f.project_name.value.length;
	}
	if (f.project_short_name.value.length == 0) {
		f.project_short_name.value = f.project_name.value.substr(0,x);
	}
}

function setDate( frm_name, f_date ) {
	fld_date = eval( 'document.' + frm_name + '.' + f_date );
	fld_real_date = eval( 'document.' + frm_name + '.' + 'project_' + f_date );
	if (fld_date.value.length>0) {
      if ((parseDate(fld_date.value))==null) {
            alert('The Date/Time you typed does not match your prefered format, please retype.');
            fld_real_date.value = '';
            fld_date.style.backgroundColor = 'red';
        } else {
        	fld_real_date.value = formatDate(parseDate(fld_date.value), 'yyyyMMdd');
        	fld_date.value = formatDate(parseDate(fld_date.value), '<?php echo $cal_sdf ?>');
            fld_date.style.backgroundColor = '';
  		}
	} else {
      	fld_real_date.value = '';
	}
}

function submitIt() {
	var f = document.editFrm;
	var msg = '';

	/*
	if (f.project_end_date.value > 0 && f.project_end_date.value < f.project_start_date.value) {
		msg += "\n<?php echo $AppUI->_('projectsBadEndDate1'); ?>";
	}
	if (f.project_actual_end_date.value > 0 && f.project_actual_end_date.value < f.project_start_date.value) {
		msg += "\n<?php echo $AppUI->_('projectsBadEndDate2'); ?>";
	}
	*/

	<?php
/*
** Automatic required fields generated from System Values
*/
$requiredFields = w2PgetSysVal('ProjectRequiredFields');
echo w2PrequiredFields($requiredFields);
?>

	if (msg.length < 1) {
		f.submit();
	} else {
		alert(msg);
	}
}

var selected_contacts_id = '<?php echo implode(',', $selected_contacts); ?>';

function popContacts() {
	window.open('./index.php?m=public&a=contact_selector&dialog=1&call_back=setContacts&selected_contacts_id='+selected_contacts_id, 'contacts','height=600,width=400,resizable,scrollbars=yes');
}

function setContacts(contact_id_string){
	if(!contact_id_string){
		contact_id_string = '';
	}
	document.editFrm.project_contacts.value = contact_id_string;
	document.editFrm.email_project_contacts.value = contact_id_string;
	selected_contacts_id = contact_id_string;
}

var selected_departments_id = '<?php echo implode(',', $selected_departments); ?>';

function popDepartment() {
        var f = document.editFrm;
	var url = './index.php?m=public&a=selector&dialog=1&callback=setDepartment&table=departments&company_id='
            + f.project_company.options[f.project_company.selectedIndex].value
            + '&dept_id='
            + selected_departments_id;
//prompt('',url);
        window.open(url,'dept','left=50,top=50,height=250,width=400,resizable');

//	window.open('./index.php?m=public&a=selector&dialog=1&call_back=setDepartment&selected_contacts_id='+selected_contacts_id, 'contacts','height=600,width=400,resizable,scrollbars=yes');
}

function setDepartment(department_id_string){
	if(!department_id_string){
		department_id_string = '';
	}
	document.editFrm.project_departments.value = department_id_string;
	selected_departments_id = department_id_string;
}

</script>

<table cellspacing="1" cellpadding="1" border="0" width='100%' class="std">
<tr>
<td>
<table width="100%">
<form name="editFrm" action="./index.php?m=projects" method="post">
	<input type="hidden" name="dosql" value="do_project_aed" />
	<input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
	<input type="hidden" name="project_creator" value="<?php echo is_null($row->project_creator) ? $AppUI->user_id : $row->project_creator; ?>" />
	<input name='project_contacts' type='hidden' value="<?php echo implode(',', $selected_contacts); ?>" />
<tr>
	<td>
		<input class="button" type="button" name="cancel2" value="<?php echo $AppUI->_('cancel'); ?>" onclick="javascript:if(confirm('Are you sure you want to cancel.')){location.href = './index.php?m=projects';}" />
	</td>
	<td align="right">
		<input class="button" type="button" name="btnFuseAction2" value="<?php echo $AppUI->_('submit'); ?>" onclick="submitIt();" />
	</td>
</tr>
<tr>
	<td width="50%" valign="top">
		<table cellspacing="0" cellpadding="2" border="0">
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project Name'); ?></td>
			<td width="100%" colspan="2">
				<input type="text" name="project_name" value="<?php echo w2PformSafe($row->project_name); ?>" size="25" maxlength="50" onblur="setShort();" class="text" /> *
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Parent Project'); ?></td>
			<td colspan="2">
                    <?php echo arraySelectTree($structprojects, 'project_parent', 'style="width:250px;" class="text"', $row->project_parent ? $row->project_parent : 0) ?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project Owner'); ?></td>
			<td colspan="2">
<?php echo arraySelect($users, 'project_owner', 'size="1" style="width:200px;" class="text"', $row->project_owner ? $row->project_owner : $AppUI->user_id) ?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Company'); ?></td>
			<td width="100%" nowrap="nowrap" colspan="2">
<?php
echo arraySelect($companies, 'project_company', 'class="text" size="1"', $row->project_company);
?> *</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project Location'); ?></td>
				<td width="100%" colspan="2">
					<input type="text" name="project_location" value="<?php echo w2PformSafe($row->project_location); ?>" size="25" maxlength="50" class="text" />
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Start Date'); ?></td>
			<td nowrap="nowrap">	 
				<input type="hidden" name="project_start_date" id="project_start_date" value="<?php echo $start_date ? $start_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
				<input type="text" name="start_date" id="start_date" onchange="setDate('editFrm', 'start_date');" value="<?php echo $start_date ? $start_date->format($df) : ""; ?>" class="text" />
				<a href="#" onclick="return showCalendar('start_date', '<?php echo $df ?>', 'editFrm', null, true)">
					<img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
				</a>
			</td>
			<td rowspan="6" valign="top">
					<?php
if ($AppUI->isActiveModule('contacts') && $perms->checkModule('contacts', 'view')) {
	echo '<input type="button" class="button" value="' . $AppUI->_('Select contacts...') . '" onclick="javascript:popContacts();" />';
}
// Let's check if the actual company has departments registered
if ($department_selection_list != '') {
?>
								<br />
								<?php echo $department_selection_list; ?>
							<?php
}
?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Target Finish Date'); ?></td>
			<td nowrap="nowrap">	
				<input type="hidden" name="project_end_date" id="project_end_date" value="<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
				<input type="text" name="end_date" id="end_date" onchange="setDate('editFrm', 'end_date');" value="<?php echo $end_date ? $end_date->format($df) : ''; ?>" class="text" />
				<a href="#" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'editFrm', null, true)">
					<img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
				</a>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Target Budget'); ?> <?php echo $w2Pconfig['currency_symbol'] ?></td>
			<td>
				<input type="Text" name="project_target_budget" value="<?php echo @$row->project_target_budget; ?>" maxlength="10" class="text" />
			</td>
		</tr>
		<tr>
			<td colspan="2"><hr noshade="noshade" size="1" /></td>
		</tr>
<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Actual Finish Date'); ?></td>
			<td nowrap="nowrap">
                                <?php if ($project_id > 0) { ?>
                                        <?php echo $actual_end_date ? '<a href="?m=tasks&a=view&task_id=' . $criticalTasks[0]['task_id'] . '">' : ''; ?>
                                        <?php echo $actual_end_date ? '<span ' . $style . '>' . $actual_end_date->format($df) . '</span>' : '-'; ?>
                                        <?php echo $actual_end_date ? '</a>' : ''; ?>
                                <?php } else {
	echo $AppUI->_('Dynamically calculated');
} ?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Actual Budget'); ?> <?php echo $w2Pconfig['currency_symbol'] ?></td>
			<td>
				<input type="text" name="project_actual_budget" value="<?php echo @$row->project_actual_budget; ?>" size="10" maxlength="10" class="text"/>
			</td>
		</tr>
		<tr>
			<td colspan="3"><hr noshade="noshade" size="1" /></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('URL'); ?></td>
			<td colspan="2">
				<input type="text" name="project_url" value='<?php echo @$row->project_url; ?>' size="40" maxlength="255" class="text" />
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Staging URL'); ?></td>
			<td colspan="2">
				<input type="Text" name="project_demo_url" value='<?php echo @$row->project_demo_url; ?>' size="40" maxlength="255" class="text" />
			</td>
		</tr>
		<tr>
			<td align="right" colspan="3">
			<?php
require_once ($AppUI->getSystemClass('CustomFields'));
$custom_fields = new CustomFields($m, $a, $row->project_id, 'edit');
$custom_fields->printHTML();
?>
			</td>
		</tr>
		</table>
	</td>
	<td width="50%" valign="top">
		<table cellspacing="0" cellpadding="2" border="0" width="100%">
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Priority'); ?></td>
			<td nowrap ="nowrap">
				<?php echo arraySelect($projectPriority, 'project_priority', 'size="1" class="text"', ($row->project_priority ? $row->project_priority : 0), true); ?> *
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Short Name'); ?></td>
			<td colspan="3">
				<input type="text" name="project_short_name" value="<?php echo w2PformSafe(@$row->project_short_name); ?>" size="10" maxlength="10" class="text" /> *
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Color Identifier'); ?></td>
			<td nowrap="nowrap">
				<input type="text" name="project_color_identifier" value="<?php echo (@$row->project_color_identifier) ? @$row->project_color_identifier : 'FFFFFF'; ?>" size="10" maxlength="6" onblur="setColor();" class="text" /> *
			</td>
			<td nowrap="nowrap" align="right">
				<a href="#" onclick="newwin=window.open('./index.php?m=public&a=color_selector&dialog=1&callback=setColor', 'calwin', 'width=320, height=300, scrollbars=no');"><?php echo $AppUI->_('change color'); ?></a>
			</td>
			<td nowrap="nowrap">
				<a href="#" onclick="newwin=window.open('./index.php?m=public&a=color_selector&dialog=1&callback=setColor', 'calwin', 'width=320, height=300, scrollbars=no');"><span id="test" style="background:#<?php echo (@$row->project_color_identifier) ? @$row->project_color_identifier : 'FFFFFF'; ?>;"><img src="<?php echo w2PfindImage('shim.gif'); ?>" border="1" width="40" height="20" /></span></a>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project Type'); ?></td>
			<td colspan="3">
				<?php echo arraySelect($ptype, 'project_type', 'size="1" class="text"', $row->project_type, true); ?> *
			</td>
		</tr>
		<tr>
			<td colspan="4">
				<table width="100%" bgcolor="#cccccc">
				<tr>
					<td><?php echo $AppUI->_('Status'); ?> *</td>
					<td nowrap="nowrap"><?php echo $AppUI->_('Progress'); ?></td>
					<td><?php echo $AppUI->_('Active'); ?>?</td>
				</tr>
				<tr>
					<td>
						<?php echo arraySelect($pstatus, 'project_status', 'size="1" class="text"', $row->project_status, true); ?>
					</td>
					<td>
						<strong><?php echo sprintf("%.1f%%", @$row->project_percent_complete); ?></strong>
					</td>
					<td>
						<input type="checkbox" value="1" name="project_active" <?php echo $row->project_active || $project_id == 0 ? 'checked="checked"' : ''; ?> />
					</td>
				</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td align="left" nowrap="nowrap">
				<?php echo $AppUI->_('Import tasks from'); ?>:<br/>
			</td>
			<td colspan="3">
<?php echo projectSelectWithOptGroup($AppUI->user_id, 'import_tasks_from', 'size="1" class="text"', false, $project_id); ?>
			</td>
		</tr>
		<tr>
			<td colspan="4">
				<?php echo $AppUI->_('Description'); ?><br />
				<textarea name="project_description" cols="50" rows="10" class="textarea"><?php echo w2PformSafe(@$row->project_description); ?></textarea>
			</td>
		</tr>
<tr valign="middle">
						<table cellspacing="0" cellpadding="2" border="0" width="100%">
							<td valign="middle"><?php echo $AppUI->_('Notify by Email'); ?>:
<?php
$tl = $AppUI->getPref('TASKLOGEMAIL');
$ta = $tl & 1;
$tt = $tl & 2;
$tp = $tl & 4;
?><input type='checkbox' name='email_project_owner_box' id='email_project_owner_box' <?php
if ($tt)
	echo "checked='checked'";
?> /><?php echo $AppUI->_('Project Owner'); ?>
		<input type='hidden' name='email_project_owner' id='email_project_owner'
		  value='<?php
if ($row->project_owner) {
	echo ($row->project_owner);
} else {
	echo '0';
}
?>' />
<input type='checkbox' name='email_project_contacts_box' id='email_project_contacts_box' <?php
if ($tp) {
	echo "checked='checked'";
}
?> /><?php echo $AppUI->_('Project Contacts'); ?>
<input type='hidden' name='email_project_contacts' id='email_project_contacts'
		  value='<?php
if ($row->project_id) {
	$q->clear();
	$q->addTable('project_contacts', 'pc');
	$q->addJoin('contacts', 'c', 'c.contact_id = pc.contact_id', 'inner');
	$q->addWhere('pc.project_id = "' . $row->project_id . '"');
	$q->addQuery('pc.contact_id');
	$q->addQuery('c.contact_first_name, c.contact_last_name');
	$req = &$q->exec();
	$cid = array();
	$proj_email_title = array();
	for ($req; !$req->EOF; $req->MoveNext()) {
		if (!in_array($req->fields['contact_id'], $cid)) {
			$cid[] = $req->fields['contact_id'];
			$proj_email_title[] = $req->fields['contact_first_name'] . ' ' . $req->fields['contact_last_name'];
		}
	}
	echo implode(',', $cid);
	$q->clear();
} else {
	echo '0';
}
?>' />
								<div align="left">
									<br />
									<br />
									<br />
									<h2><font color="#0066ff"><u></u></font></h2>
									<p>
			<p>
										
									</p>
								</div>
							</td>
							<td></td>
				</tr>
					<?php
//End Pedro A. Project Tabs

?>
				</table>
			</td>
		</tr>
          <tr>
          	<td>
          
          		<input class="button" type="button" name="cancel" value="<?php echo $AppUI->_('cancel'); ?>" onclick="javascript:if(confirm('Are you sure you want to cancel.')){location.href = './index.php?m=projects';}" />
          	</td>
          	<td align="right">
          		<input class="button" type="button" name="btnFuseAction" value="<?php echo $AppUI->_('submit'); ?>" onclick="submitIt();" />
          	</td>
          </tr>
          <tr>
          	<td colspan="2">
          * <?php echo $AppUI->_('requiredField'); ?>
          	</td>
          </tr>
</form>
</table>
</table>

<?php
function getDepartmentSelectionList($company_id, $checked_array = array(), $dept_parent = 0, $spaces = 0) {
	global $departments_count, $AppUI;
	$parsed = '';

	if ($departments_count < 6) {
		$departments_count++;
	}

	$q = new DBQuery;
	$q->addTable('departments');
	$q->addQuery('dept_id, dept_name');
	$q->addWhere('dept_parent = "' . $dept_parent . '" AND dept_company = "' . $company_id . '"');
	$q->addOrder('dept_name');
	$department = new CDepartment;
	$department->setAllowedSQL($AppUI->user_id, $q);

	$depts_list = $q->loadHashList('dept_id');

	foreach ($depts_list as $dept_id => $dept_info) {
		$selected = in_array($dept_id, $checked_array) ? ' selected="selected"' : '';

		$parsed .= '<option value="' . $dept_id . '"' . $selected . '>' . str_repeat('&nbsp;', $spaces) . $dept_info['dept_name'] . '</option>';
		$parsed .= getDepartmentSelectionList($company_id, $checked_array, $dept_id, $spaces + 5);
	}

	return $parsed;
}
?>