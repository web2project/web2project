<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    convert to template
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

global $AppUI, $cal_sdf;
$AppUI->loadCalendarJS();


$pstatus = w2PgetSysVal('ProjectStatus');
$ptype = w2PgetSysVal('ProjectType');

$structprojs = $project->getAllowedProjects($AppUI->user_id, false);
unset($structprojs[$project_id]);
foreach($structprojs as $key => $tmpInfo) {
    $structprojs[$key] = $tmpInfo['project_name'];
}
$structprojects = arrayMerge(array('0' => '(' . $AppUI->_('No Parent') . ')'), $structprojs);

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

$end_date = intval($project->project_end_date) ? new w2p_Utilities_Date($project->project_end_date) : null;
$actual_end_date = intval($criticalTasks[0]['task_end_date']) ? new w2p_Utilities_Date($criticalTasks[0]['task_end_date']) : null;
$style = (($actual_end_date > $end_date) && !empty($end_date)) ? 'style="color:red; font-weight:bold"' : '';

// setup the title block
$ttl = $project_id > 0 ? 'Edit Project' : 'New Project';
$titleBlock = new w2p_Theme_TitleBlock($ttl, 'icon.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=' . $m, $m . ' list');
$titleBlock->addViewLink('project', $project_id);
$titleBlock->show();

$canDelete = $project->canDelete();
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
<?php

$form = new w2p_Output_HTML_FormHelper($AppUI);

?>
<form name="editFrm" action="?m=<?php echo $m; ?>" method="post" accept-charset="utf-8" class="addedit projects">
	<input type="hidden" name="dosql" value="do_project_aed" />
	<input type="hidden" name="project_id" value="<?php echo $project_id; ?>" />
	<input type="hidden" name="project_creator" value="<?php echo is_null($project->project_creator) ? $AppUI->user_id : $project->project_creator; ?>" />
	<input type="hidden" name="project_contacts" id="project_contacts" value="<?php echo implode(',', $selected_contacts); ?>" />
    <input type="hidden" name="datePicker" value="project" />
    <?php echo $form->addNonce(); ?>

    <div class="std addedit projects">
        <div class="column left">
            <p>
                <?php $form->showLabel('Name'); ?>
                <?php
                $options = array();
                $options['maxlength'] = 255;
                $options['onBlur'] = 'setShort()';
                $form->showField('project_name', $project->project_name, $options); ?>
            </p>
            <p>
                <?php $form->showLabel('Parent Project'); ?>
                <?php echo arraySelect($structprojects, 'project_parent', 'size="1" style="width:250px;" class="text"', $project->project_parent ? $project->project_parent : 0) ?>
            </p>
            <p>
                <?php $form->showLabel('Company'); ?>
                <?php echo arraySelect($companies, 'project_company', 'class="text" size="1"', $project->project_company); ?>
            </p>
            <?php
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
                    $department_selection_list = '<p>' . $form->addLabel('Departments') . '<select name="project_departments[]" multiple="multiple" class="text"><option value="0"></option>' . $department_selection_list . '</select></p>';
                } else {
                    $department_selection_list = '<input type="button" class="button" value="' . $AppUI->_('Select department...') . '" onclick="javascript:popDepartment();" /><input type="hidden" name="project_departments"';
                }
                // Let's check if the actual company has departments registered
                if ($department_selection_list != '') {
                    echo $department_selection_list;
                }
            }
            ?>
            <p>
                <?php $form->showLabel('Project Owner'); ?>
                <?php
                // pull users
                $perms = &$AppUI->acl();
                $users = $perms->getPermittedUsers('projects');
                ?>
                <?php $form->showField('project_owner', $project->project_owner, array(), $users); ?>
            </p>
            <p>
                <?php $form->showLabel('Contacts'); ?>
                <input type="button" class="button btn btn-primary btn-mini" value="<?php echo $AppUI->_('Select contacts...'); ?>" onclick="javascript:popContacts();" />
            </p>
            <p>
                <?php $form->showLabel('Start Date'); ?>
                <?php $form->showField('project_start_date', $project->project_start_date); ?>
            </p>
            <p>
                <?php $form->showLabel('Target Finish Date'); ?>
                <?php $form->showField('project_end_date', $project->project_end_date); ?>
            </p>
            <p>
                <?php $form->showLabel('Actual Finish Date'); ?>
                <?php
                if ($project_id > 0) {
                    echo $actual_end_date ? '<a href="?m=tasks&a=view&task_id=' . $criticalTasks[0]['task_id'] . '">' : '';
                    echo $actual_end_date ? '<span ' . $style . '>' . $actual_end_date->format($df) . '</span>' : '-';
                    echo $actual_end_date ? '</a>' : '';
                } else {
                    echo $AppUI->_('Dynamically calculated');
                }
                ?>
            </p>
            <p>
                <?php $form->showLabel('Project Location'); ?>
                <?php $form->showField('project_location', $project->project_location, array('maxlength' => 50)); ?>
            </p>
            <?php if (w2PgetConfig('budget_info_display', false)) { ?>
            <p>
                <?php $form->showLabel('Target Budgets'); ?>
                &nbsp;
            </p>
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
                <p>
                    <?php $form->showLabel($AppUI->_($category)); ?>
                    <?php echo $w2Pconfig['currency_symbol']; ?> <?php $form->showField("budget_<?php echo $id; ?>", $amount, array('maxlength' => 15)); ?>
                </p>
                <?php
            }
            ?>
            <p>
                <?php $form->showLabel('Total Target Budget'); ?>
                <?php echo $w2Pconfig['currency_symbol'] ?> <?php echo formatCurrency($totalBudget, $AppUI->getPref('CURRENCYFORM')); ?>
            </p>
            <p>
                <?php $form->showLabel('Actual Budget'); ?>
                <?php
                if ($project_id > 0) {
                    echo $w2Pconfig['currency_symbol'] . '&nbsp;' . formatCurrency($project->project_actual_budget, $AppUI->getPref('CURRENCYFORM'));
                } else {
                    echo $AppUI->_('Dynamically calculated');
                }
                ?>
            </p>
            <?php } ?>
            <?php $form->showCancelButton(); ?>
        </div>
        <div class="column right">
            <p>
                <?php $form->showLabel('Priority'); ?>
                <?php $form->showField('project_priority', (int) $project->project_priority, array(), $projectPriority); ?>
            </p>
            <p>
                <?php $form->showLabel('Short Name'); ?>
                <?php $form->showField('project_short_name', $project->project_short_name, array('maxlength' => 10)); ?>
            </p>
            <p>
                <?php $form->showLabel('Color Identifier'); ?>
                <input type="text" name="project_color_identifier" value="<?php echo ($project->project_color_identifier) ? $project->project_color_identifier : 'FFFFFF'; ?>" size="10" maxlength="6" onblur="setColor();" class="text" /> *
                <a href="javascript: void(0);" onclick="newwin=window.open('./index.php?m=public&a=color_selector&dialog=1&callback=setColor', 'calwin', 'width=320, height=300, scrollbars=no');"><?php echo $AppUI->_('change color'); ?></a>
                <a href="javascript: void(0);" onclick="newwin=window.open('./index.php?m=public&a=color_selector&dialog=1&callback=setColor', 'calwin', 'width=320, height=300, scrollbars=no');"><span id="test" style="border:solid;border-width:1;border-right-width:0;background:#<?php echo ($project->project_color_identifier) ? $project->project_color_identifier : 'FFFFFF'; ?>;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><span style="border:solid;border-width:1;border-left-width:0;background:#FFFFFF">&nbsp;&nbsp;</span></a>
            </p>
            <p>
                <?php $form->showLabel('Project Type'); ?>
                <?php $form->showField('project_type', (int) $project->project_type, array(), $ptype); ?>
            </p>
            <p>
                <table width="100%" bgcolor="#cccccc">
                    <tr>
                        <td><?php echo $AppUI->_('Status'); ?> *</td>
                        <td nowrap="nowrap"><?php echo $AppUI->_('Progress'); ?></td>
                        <td><?php echo $AppUI->_('Active'); ?>?</td>
                    </tr>
                    <tr>
                        <td>
                            <?php $form->showField('project_status', $project->project_status, array(), $pstatus); ?>
                        </td>
                        <td>
                            <strong><?php echo sprintf("%.1f%%", $project->project_percent_complete); ?></strong>
                        </td>
                        <td>
                            <input type="checkbox" value="1" name="project_active" <?php echo $project->project_active || $project_id == 0 ? 'checked="checked"' : ''; ?> />
                        </td>
                    </tr>
                </table>
            </p>
            <p>
                <?php $form->showLabel('Import tasks from'); ?>
                <?php
                $templates = $project->loadAll('project_name', 'project_status = ' . w2PgetConfig('template_projects_status_id'));
                $templateProjects[] = '';
                foreach($templates as $key => $data) {
                    $templateProjects[$key] = $data['project_name'];
                }
                echo arraySelect($templateProjects, 'import_tasks_from', 'size="1" class="text"', -1, false);
                ?>
            </p>
            <p>
                <?php $form->showLabel('Description'); ?>
                <?php $form->showField('project_description', $project->project_description); ?>
            </p>
            <p>
                <?php $form->showLabel('Notify by Email'); ?>
                <input type="checkbox" name="email_project_owner_box" id="email_project_owner_box" <?php echo ($tt ? 'checked="checked"' : '');?> />
                <?php echo $AppUI->_('Project Owner'); ?>
                <input type="hidden" name="email_project_owner" id="email_project_owner" value="<?php echo ($project->project_owner ? $project->project_owner : '0');?>" />
                <input type='checkbox' name='email_project_contacts_box' id='email_project_contacts_box' <?php echo ($tp ? 'checked="checked"' : ''); ?> />
                <?php echo $AppUI->_('Project Contacts'); ?>
            </p>
            <p>
                <?php $form->showLabel('URL'); ?>
                <?php $form->showField('project_url', $project->project_url, array('maxlength' => 255)); ?>
            </p>
            <p>
                <?php $form->showLabel('Staging URL'); ?>
                <?php $form->showField('project_demo_url', $project->project_demo_url, array('maxlength' => 255)); ?>
            </p>
            <?php
            $custom_fields = new w2p_Core_CustomFields($m, $a, $project->project_id, 'edit');
            echo '<p>' . $custom_fields->getHTML() . '</p>';
            ?>
            <?php $form->showSaveButton(); ?>
        </div>
    </div>
</form>