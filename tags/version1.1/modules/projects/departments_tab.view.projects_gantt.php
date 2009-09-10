<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $a, $addPwOiD, $AppUI, $buffer, $company_id, $department, $dept_id, 
	$dept_ids, $min_view, $m, $priority, $projects, $tab, $user_id;

$perms = &$AppUI->acl();
$df = $AppUI->getPref('SHDATEFORMAT');

$pstatus = w2PgetSysVal('ProjectStatus');

if (isset($_POST['proFilter'])) {
	$AppUI->setState('DeptProjectIdxFilter', $_POST['proFilter']);
}
$proFilter = $AppUI->getState('DeptProjectIdxFilter') !== null ? $AppUI->getState('DeptProjectIdxFilter') : '-3';

$projFilter = arrayMerge(array('-1' => 'All Projects'), $pstatus);
$projFilter = arrayMerge(array('-2' => 'All w/o in progress'), $projFilter);
$projFilter = arrayMerge(array('-3' => 'All w/o archived'), $projFilter);
natsort($projFilter);

// load the companies class to retrieved denied companies
require_once ($AppUI->getModuleClass('companies'));

// retrieve any state parameters
if (isset($_GET['tab'])) {
	$AppUI->setState('DeptProjIdxTab', w2PgetParam($_GET, 'tab', null));
}

if (isset($_POST['show_form'])) {
	$AppUI->setState('addProjWithOwnerInDep', w2PgetParam($_POST, 'add_pwoid', 0));
}
$addPwOiD = $AppUI->getState('addProjWithOwnerInDep') ? $AppUI->getState('addProjWithOwnerInDep') : 0;

$extraGet = '&user_id=' . $user_id;
?>
<table width="100%" border="0" cellpadding="3" cellspacing="1" class="tbl">
<tr>
	<td align="center" width="100%" nowrap="nowrap" colspan="6">&nbsp;</td><td align="right" nowrap="nowrap"><form action="?m=departments&tab=<?php echo $tab; ?>" method="post" name="checkPwOiD"><input type="checkbox" name="add_pwoid" id="add_pwoid" onclick="document.checkPwOiD.submit()" <?php echo $addPwOiD ? 'checked="checked"' : ''; ?>  accept-charset="utf-8"/><label for="add_pwoid"><?php echo $AppUI->_('Show Projects whose Owner is Member of the Dep.'); ?>?</label><input type="hidden" name="show_form" value="1" /></form></td>
</tr>
</table>
<?php
$min_view = true;
/*
 *  TODO:  This is a *nasty* *nasty* kludge that should be cleaned up.
 * Unfortunately due to the global variables from dotProject, we're stuck with
 * this mess for now.
 * 
 * My God have mercy on our souls for the atrocity we're about to commit.
 */ 
$tmpDepartments = $department;
$department = $dept_id; 
require (W2P_BASE_DIR . '/modules/projects/viewgantt.php');
$department = $tmpDepartments;