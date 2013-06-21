<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
global $AppUI, $cal_sdf;
$AppUI->loadCalendarJS();

$project_id = (int) w2PgetParam($_GET, 'project_id', 0);
$company_id = (int) w2PgetParam($_GET, 'company_id', $AppUI->user_company);
$contact_id = (int) w2PgetParam($_GET, 'contact_id', 0);
$project = new CProject();
$project->project_id = $project_id;

$obj = $project;
$canAddEdit = $obj->canAddEdit();
$canAuthor = $obj->canCreate();
$canEdit = $obj->canEdit();
if (!$canAddEdit) {
	$AppUI->redirect(ACCESS_DENIED);
}

// load the record data
$obj = $AppUI->restoreObject();
if ($obj) {
    $project = $obj;
    $project_id = $project->project_id;
} else {
    $project->loadFull(null, $project_id);
}
if (!$project && $project_id > 0) {
	$AppUI->setMsg('Project');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true);
	$AppUI->redirect();
}

$pstatus = w2PgetSysVal('ProjectStatus');
$ptype = w2PgetSysVal('ProjectType');

$structprojs = $project->getAllowedProjects($AppUI->user_id, false);
unset($structprojs[$project_id]);
$structprojs = array_map('temp_filterArrayForSelectTree', $structprojs);
$structprojects = arrayMerge(array('0' => array(0 => 0, 1 => '(' . $AppUI->_('No Parent') . ')', 2 => '')), $structprojs);

// get a list of permitted companies
$company = new CCompany();
$companies = $company->getAllowedRecords($AppUI->user_id, 'company_id,company_name', 'company_name');
$companies = arrayMerge(array('0' => ''), $companies);

if (count($companies) < 2 && $project_id == 0) {
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

$start_date = new w2p_Utilities_Date($project->project_start_date);

$end_date = intval($project->project_end_date) ? new w2p_Utilities_Date($project->project_end_date) : null;
$actual_end_date = intval($criticalTasks[0]['task_end_date']) ? new w2p_Utilities_Date($criticalTasks[0]['task_end_date']) : null;
$style = (($actual_end_date > $end_date) && !empty($end_date)) ? 'style="color:red; font-weight:bold"' : '';

// setup the title block
$ttl = $project_id > 0 ? 'Edit Project' : 'New Project';
$titleBlock = new w2p_Theme_TitleBlock($ttl, 'applet3-48.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=projects', 'projects list');
$canDelete = $project->canDelete();
if ($project_id != 0) {
	$titleBlock->addCrumb('?m=projects&a=view&project_id=' . $project_id, 'view this project');
}
$titleBlock->show();

// Get contacts list
$selected_contacts = array();

if ($project_id) {
	$myContacts = $project->getContactList();
	$selected_contacts = array_keys($myContacts);
}
if ($project_id == 0 && $contact_id > 0) {
	$selected_contacts[] = '' . $contact_id;
}

// Get the users notification options
$tl = $AppUI->getPref('TASKLOGEMAIL');
$ta = $tl & 1;
$tt = $tl & 2;
$tp = $tl & 4;
?>
<script language="javascript" type="text/javascript">

function setColor(color) {
	var f = document.editFrm;
	if (color) {
		f.project_color_identifier.value = color;
	}
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

function submitIt() {
	var f = document.editFrm;
	var msg = '';

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
    var project_company = document.getElementById('project_company').value;
	window.open('./index.php?m=public&a=contact_selector&dialog=1&call_back=setContacts&selected_contacts_id='+selected_contacts_id+'&company_id='+project_company, 'contacts','height=600,width=400,resizable,scrollbars=yes');
}

function setContacts(contact_id_string){
	if(!contact_id_string){
		contact_id_string = '';
	}
	document.editFrm.project_contacts.value = contact_id_string;
}

function popDepartment() {
        var f = document.editFrm;
	var url = './index.php?m=public&a=selector&dialog=1&callback=setDepartment&table=departments&company_id='
            + f.project_company.options[f.project_company.selectedIndex].value;
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

<form name="editFrm" action="./index.php?m=projects" method="post" accept-charset="utf-8">
	<input type="hidden" name="dosql" value="do_project_aed" />
	<input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
	<input type="hidden" name="project_creator" value="<?php echo is_null($project->project_creator) ? $AppUI->user_id : $project->project_creator; ?>" />
	<input type="hidden" name="project_contacts" id="project_contacts" value="<?php echo implode(',', $selected_contacts); ?>" />
    <input type="hidden" name="datePicker" value="project" />
    <table cellspacing="1" cellpadding="1" border="0" width='100%' class="std addedit">
        <tr>
            <td width="50%" valign="top">
                <table cellspacing="1" cellpadding="2" width="100%" class="well">
                    <tr>
                        <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project Name'); ?></td>
                        <td width="100%" colspan="2">
                            <input type="text" name="project_name" id="project_name" value="<?php echo htmlspecialchars($project->project_name, ENT_QUOTES); ?>" size="25" maxlength="255" onblur="setShort();" class="text" /> *
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
                            <?php
                                // pull users
                                $perms = &$AppUI->acl();
                                $users = $perms->getPermittedUsers('projects');
                                echo arraySelect($users, 'project_owner', 'size="1" style="width:200px;" class="text"', $project->project_owner ? $project->project_owner : $AppUI->user_id);
                            ?> *
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
                            <input type="text" name="start_date" id="start_date" onchange="setDate_new('editFrm', 'start_date');" value="<?php echo $start_date ? $start_date->format($df) : ''; ?>" class="text" />
                            <a href="javascript: void(0);" onclick="return showCalendar('start_date', '<?php echo $df ?>', 'editFrm', null, true, true)">
                                <img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
                            </a>
                        </td>
                        <td rowspan="6" valign="top">
                            <?php
                                if ($AppUI->isActiveModule('contacts') && canView('contacts')) {
                                    echo '<input type="button" class="button btn btn-primary btn-mini" value="' . $AppUI->_('Select contacts...') . '" onclick="javascript:popContacts();" />';
                                }

                                if ($AppUI->isActiveModule('departments') && canAccess('departments')) {
                                    //Build display list for departments
                                    $company_id = $project->project_company;
                                    $selected_departments = array();
                                    if ($project_id) {
                                        $myDepartments = $project->getDepartmentList();
                                        $selected_departments = (count($myDepartments) > 0) ? array_keys($myDepartments) : array();
                                    }

                                    $departments_count = 0;
                                    $department_selection_list = getDepartmentSelectionList($company_id, $selected_departments);
                                    if ($department_selection_list != '' || $project_id) {
                                        $department_selection_list = ($AppUI->_('Departments') . '<br /><select name="project_departments[]" multiple="multiple" class="text"><option value="0"></option>' . $department_selection_list . '</select>');
                                    } else {
                                        $department_selection_list = '<input type="button" class="button" value="' . $AppUI->_('Select department...') . '" onclick="javascript:popDepartment();" /><input type="hidden" name="project_departments"';
                                    }

                                    // Let's check if the actual company has departments registered
                                    if ($department_selection_list != '') {
                                        echo '<br />' . $department_selection_list;
                                    }
                                }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Target Finish Date'); ?></td>
                        <td nowrap="nowrap">
                            <input type="hidden" name="project_end_date" id="project_end_date" value="<?php echo $end_date ? $end_date->format(FMT_TIMESTAMP_DATE) : ''; ?>" />
                            <input type="text" name="end_date" id="end_date" onchange="setDate_new('editFrm', 'end_date');" value="<?php echo $end_date ? $end_date->format($df) : ''; ?>" class="text" />
                            <a href="javascript: void(0);" onclick="return showCalendar('end_date', '<?php echo $df ?>', 'editFrm', null, true, true)">
                                <img src="<?php echo w2PfindImage('calendar.gif'); ?>" width="24" height="12" alt="<?php echo $AppUI->_('Calendar'); ?>" border="0" />
                            </a>
                        </td>
                    </tr>
                    <?php if (w2PgetConfig('budget_info_display', false)) { ?>
                    <tr>
                        <td colspan="2">
                            <table class="budgets nowrap">
                                <tr>
                                    <td colspan="2"><?php echo $AppUI->_('Target Budgets'); ?> </td>
                                </tr>
                                <?php
                                    $billingCategory = w2PgetSysVal('BudgetCategory');
                                    $totalBudget = 0;
                                    foreach ($billingCategory as $id => $category) {
                                        $amount = 0;
                                        if (isset($project->budget[$id])) {
                                            $amount = $project->budget[$id]['budget_amount'];
                                        }
                                        $totalBudget += $amount;
                                        ?>
                                        <tr>
                                            <td>
                                                <?php echo $AppUI->_($category); ?>
                                            </td>
                                            <td>
                                                <?php echo $w2Pconfig['currency_symbol']; ?> <input name="budget_<?php echo $id; ?>" id="budget_<?php echo $id; ?>" type="text" value="<?php echo $amount; ?>" class="text" />
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                ?>
                                <tr>
                                    <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Total Target Budget'); ?></td>
                                    <td>
                                        <?php echo $w2Pconfig['currency_symbol'] ?> <?php echo formatCurrency($totalBudget, $AppUI->getPref('CURRENCYFORM')); ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3"><hr noshade="noshade" size="1" /></td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Actual Finish Date'); ?></td>
                        <td nowrap="nowrap">
                        <?php
                            if ($project_id > 0) {
                                echo $actual_end_date ? '<a href="?m=tasks&a=view&task_id=' . $criticalTasks[0]['task_id'] . '">' : '';
                                echo $actual_end_date ? '<span ' . $style . '>' . $actual_end_date->format($df) . '</span>' : '-';
                                echo $actual_end_date ? '</a>' : '';
                            } else {
                                echo $AppUI->_('Dynamically calculated');
                            }
                        ?>
                        </td>
                    </tr>
                    <?php if (w2PgetConfig('budget_info_display', false)) { ?>
                    <tr>
                        <td align="right" nowrap="nowrap"><?php echo $AppUI->_('Actual Budget'); ?> <?php echo $w2Pconfig['currency_symbol'] ?></td>
                        <td nowrap="nowrap">
                        <?php
                            if ($project_id > 0) {
                                echo formatCurrency($project->project_actual_budget, $AppUI->getPref('CURRENCYFORM'));
                            } else {
                                echo $AppUI->_('Dynamically calculated');
                            }
                        ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3"><hr noshade="noshade" size="1" /></td>
                    </tr>
                    <?php } ?>
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
                            $custom_fields = new w2p_Core_CustomFields($m, $a, $project->project_id, 'edit');
                            $custom_fields->printHTML();
                            ?>
                        </td>
                    </tr>
                </table>
            </td>
            <td width="50%" valign="top">
                <table cellspacing="1" cellpadding="2" width="100%" class="well">
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
                        <td nowrap="nowrap" align="left">
                            <a href="javascript: void(0);" onclick="newwin=window.open('./index.php?m=public&a=color_selector&dialog=1&callback=setColor', 'calwin', 'width=320, height=300, scrollbars=no');"><?php echo $AppUI->_('change color'); ?></a>
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
                        <td colspan="4">
                            <table cellspacing="0" cellpadding="2" border="0" width="100%">
                                <tr>
                                    <td valign="middle"><?php echo $AppUI->_('Notify by Email'); ?>:
                                        <input type="checkbox" name="email_project_owner_box" id="email_project_owner_box" <?php echo ($tt ? 'checked="checked"' : '');?> />
                                        <?php echo $AppUI->_('Project Owner'); ?>
                                        <input type="hidden" name="email_project_owner" id="email_project_owner" value="<?php echo ($project->project_owner ? $project->project_owner : '0');?>" />
                                        <input type='checkbox' name='email_project_contacts_box' id='email_project_contacts_box' <?php echo ($tp ? 'checked="checked"' : ''); ?> />
                                        <?php echo $AppUI->_('Project Contacts'); ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td>
                <input class="button btn btn-danger" type="button" name="cancel" value="<?php echo $AppUI->_('cancel'); ?>" onclick="javascript:if(confirm('Are you sure you want to cancel.')){location.href = './index.php?m=projects';}" />
            </td>
            <td class="right">
                <input class="button btn btn-primary" type="button" name="btnFuseAction" value="<?php echo $AppUI->_('submit'); ?>" onclick="submitIt();" />
            </td>
        </tr>
    </table>
</form>