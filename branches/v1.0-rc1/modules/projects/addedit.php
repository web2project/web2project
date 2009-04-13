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
$canEdit = $perms->checkModuleItem('projects', 'edit', $project_id);
$canAuthor = $perms->checkModuleItem('projects', 'add');
if ((!$canEdit && $project_id > 0) || (!$canAuthor && $project_id == 0)) {
	$AppUI->redirect('m=public&a=access_denied');
}

// pull users
$users = w2PgetUsers();

// load the record data
$project = new CProject();

// get a list of permitted companies
require_once ($AppUI->getModuleClass('companies'));

$company = new CCompany();
$companies = $company->getAllowedRecords($AppUI->user_id, 'company_id,company_name', 'company_name');
$companies = arrayMerge(array('0' => ''), $companies);

if (!$project->load($project_id, false) && $project_id > 0) {
	$AppUI->setMsg('Project');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect();
} elseif (count($companies) < 2 && $project_id == 0) {
	$AppUI->setMsg('noCompanies', UI_MSG_ERROR, true);
	$AppUI->redirect();
}

if ($project_id == 0 && $company_id > 0) {
	$project->project_company = $company_id;
}

// add in the existing company if for some reason it is dis-allowed
if ($project_id && !array_key_exists($project->project_company, $companies)) {
	$companies[$project->project_company] = $company->load($project->project_company)->company_name;
}

// get critical tasks (criteria: task_end_date)
$criticalTasks = ($project_id > 0) ? $project->getCriticalTasks() : null;

// get ProjectPriority from sysvals
$projectPriority = w2PgetSysVal('ProjectPriority');

// format dates
$df = $AppUI->getPref('SHDATEFORMAT');

$start_date = new CDate($project->project_start_date);

$end_date = intval($project->project_end_date) ? new CDate($project->project_end_date) : null;
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
$company_id = $project->project_company;
$selected_departments = array();
if ($project_id) {
	$myDepartments = CProject::getDepartments($AppUI, $project_id);
	$selected_departments = array_keys($myDepartments);
}

$departments_count = 0;
$department_selection_list = getDepartmentSelectionList($company_id, $selected_departments);
if ($department_selection_list != '' || $project_id) {
	$department_selection_list = ($AppUI->_('Departments') . '<br /><select name="dept_ids[]" class="text"><option value="0"></option>' . $department_selection_list . '</select>');
} else {
	$department_selection_list = '<input type="button" class="button" value="' . $AppUI->_('Select department...') . '" onclick="javascript:popDepartment();" /><input type="hidden" name="project_departments"';
}

// Get contacts list
$selected_contacts = array();

if ($project_id) {
	$myContacts = CProject::getContacts($AppUI, $project_id);
	$selected_contacts = array_keys($myContacts);
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

function popContacts() {
	var selected_contacts_id = document.getElementById('project_contacts').value;
	window.open('./index.php?m=public&a=contact_selector&dialog=1&call_back=setContacts&selected_contacts_id='+selected_contacts_id, 'contacts','height=600,width=400,resizable,scrollbars=yes');
}

function setContacts(contact_id_string){
	var selected_contacts_id = document.getElementById('project_contacts').value;
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
        window.open(url,'dept','left=50,top=50,height=250,width=400,resizable');
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
	<input type="hidden" name="project_creator" value="<?php echo is_null($project->project_creator) ? $AppUI->user_id : $project->project_creator; ?>" />
	<input type="hidden" name="project_contacts" id="project_contacts" value="<?php echo implode(',', $selected_contacts); ?>" />
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
				<input type="text" name="project_name" value="<?php echo htmlspecialchars($project->project_name, ENT_QUOTES); ?>" size="25" maxlength="50" onblur="setShort();" class="text" /> *
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Parent Project'); ?></td>
			<td colspan="2">
                    <?php echo arraySelectTree($structprojects, 'project_parent', 'style="width:250px;" class="text"', $project->project_parent ? $project->project_parent : 0) ?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project Owner'); ?></td>
			<td colspan="2">
				<?php echo arraySelect($users, 'project_owner', 'size="1" style="width:200px;" class="text"', $project->project_owner ? $project->project_owner : $AppUI->user_id) ?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Company'); ?></td>
			<td width="100%" nowrap="nowrap" colspan="2">
				<?php echo arraySelect($companies, 'project_company', 'class="text" size="1"', $project->project_company); ?> *
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project Location'); ?></td>
				<td width="100%" colspan="2">
					<input type="text" name="project_location" value="<?php echo w2PformSafe($project->project_location); ?>" size="25" maxlength="50" class="text" />
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Start Date'); ?></td>
			<td nowrap="nowrap">	 
				<input type="hidden" name="project_start_date" id="project_start_date" value="<?php echo $start_date ? $start_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
				<input type="text" name="start_date" id="start_date" onchange="setDate('editFrm', 'start_date');" value="<?php echo $start_date ? $start_date->format($df) : ''; ?>" class="text" />
				<a href="javascript: void(0);" onclick="return showCalendar('start_date', '<?php echo $df ?>', 'editFrm', null, true)">
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
						?><br /><?php 
						echo $department_selection_list;
					}
				?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Target Finish Date'); ?></td>
			<td nowrap="nowrap">	
				<input type="hidden" name="project_end_date" id="project_end_date" value="<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
				<input type="text" name="end_date" id="end_date" onchange="setDate('editFrm', 'end_date');" value="<?php echo $end_date ? $end_date->format($df) : ''; ?>" class="text" />
				<a href="javascript: void(0);" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'editFrm', null, true)">
					<img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
				</a>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Target Budget'); ?> <?php echo $w2Pconfig['currency_symbol'] ?></td>
			<td>
				<input type="Text" name="project_target_budget" value="<?php echo $project->project_target_budget; ?>" maxlength="10" class="text" />
			</td>
		</tr>
		<tr>
			<td colspan="2"><hr noshade="noshade" size="1" /></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Actual Finish Date'); ?></td>
			<td nowrap="nowrap">
        <?php if ($project_id > 0) {
          echo $actual_end_date ? '<a href="?m=tasks&a=view&task_id=' . $criticalTasks[0]['task_id'] . '">' : '';
          echo $actual_end_date ? '<span ' . $style . '>' . $actual_end_date->format($df) . '</span>' : '-';
          echo $actual_end_date ? '</a>' : '';
        } else {
					echo $AppUI->_('Dynamically calculated');
				} ?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Actual Budget'); ?> <?php echo $w2Pconfig['currency_symbol'] ?></td>
			<td>
				<input type="text" name="project_actual_budget" value="<?php echo $project->project_actual_budget; ?>" size="10" maxlength="10" class="text"/>
			</td>
		</tr>
		<tr>
			<td colspan="3"><hr noshade="noshade" size="1" /></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('URL'); ?></td>
			<td colspan="2">
				<input type="text" name="project_url" value='<?php echo $project->project_url; ?>' size="40" maxlength="255" class="text" />
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Staging URL'); ?></td>
			<td colspan="2">
				<input type="Text" name="project_demo_url" value='<?php echo $project->project_demo_url; ?>' size="40" maxlength="255" class="text" />
			</td>
		</tr>
		<tr>
			<td align="right" colspan="3">
				<?php
					require_once ($AppUI->getSystemClass('CustomFields'));
					$custom_fields = new CustomFields($m, $a, $project->project_id, 'edit');
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
				<?php echo arraySelect($projectPriority, 'project_priority', 'size="1" class="text"', ($project->project_priority ? $project->project_priority : 0), true); ?> *
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Short Name'); ?></td>
			<td colspan="3">
				<input type="text" name="project_short_name" value="<?php echo w2PformSafe($project->project_short_name); ?>" size="10" maxlength="10" class="text" /> *
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Color Identifier'); ?></td>
			<td nowrap="nowrap">
				<input type="text" name="project_color_identifier" value="<?php echo ($project->project_color_identifier) ? $project->project_color_identifier : 'FFFFFF'; ?>" size="10" maxlength="6" onblur="setColor();" class="text" /> *
			</td>
			<td nowrap="nowrap" align="right">
				<a href="javascript: void(0);" onclick="newwin=window.open('./index.php?m=public&a=color_selector&dialog=1&callback=setColor', 'calwin', 'width=320, height=300, scrollbars=no');"><?php echo $AppUI->_('change color'); ?></a>
			</td>
			<td nowrap="nowrap">
				<a href="javascript: void(0);" onclick="newwin=window.open('./index.php?m=public&a=color_selector&dialog=1&callback=setColor', 'calwin', 'width=320, height=300, scrollbars=no');"><span id="test" style="border:solid;border-width:1;border-right-width:0;background:#<?php echo ($project->project_color_identifier) ? $project->project_color_identifier : 'FFFFFF'; ?>;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="border:solid;border-width:1;border-left-width:0;background:#FFFFFF">&nbsp;&nbsp;</span></a>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project Type'); ?></td>
			<td colspan="3">
				<?php echo arraySelect($ptype, 'project_type', 'size="1" class="text"', $project->project_type, true); ?> *
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
						<?php echo arraySelect($pstatus, 'project_status', 'size="1" class="text"', $project->project_status, true); ?>
					</td>
					<td>
						<strong><?php echo sprintf("%.1f%%", $project->project_percent_complete); ?></strong>
					</td>
					<td>
						<input type="checkbox" value="1" name="project_active" <?php echo $project->project_active || $project_id == 0 ? 'checked="checked"' : ''; ?> />
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
				<textarea name="project_description" cols="50" rows="10" class="textarea"><?php echo w2PformSafe($project->project_description); ?></textarea>
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
if ($project->project_owner) {
	echo ($project->project_owner);
} else {
	echo '0';
}
?>' />
<input type='checkbox' name='email_project_contacts_box' id='email_project_contacts_box' <?php echo ($tp) ? 'checked="checked"' : ''; ?> />
<?php echo $AppUI->_('Project Contacts'); ?>
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

	$depts_list = CDepartment::getDepartmentList($AppUI, $company_id, $dept_parent);

	foreach ($depts_list as $dept_id => $dept_info) {
		$selected = in_array($dept_id, $checked_array) ? ' selected="selected"' : '';

		$parsed .= '<option value="' . $dept_id . '"' . $selected . '>' . str_repeat('&nbsp;', $spaces) . $dept_info['dept_name'] . '</option>';
		$parsed .= getDepartmentSelectionList($company_id, $checked_array, $dept_id, $spaces + 5);
	}

	return $parsed;
}
?>