<?php
/** @var w2p_Core_CAppUI $AppUI */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
// @todo    remove database query

##
## add or edit a user preferences
##

$user_id = (int) w2PgetParam($_GET, 'user_id', '0');
$perms = &$AppUI->acl();
// check permissions for this record
$canEdit = canEdit('system');
// Check permissions
if (!$canEdit && $user_id != $AppUI->user_id) {
	$AppUI->redirect(ACCESS_DENIED);
}

// load the preferences
$prefs = getPreferences($user_id);

// get the user name
if ($user_id) {
	$user = CContact::getContactByUserid($user_id);
} else {
	$user = 'Default';
}

$titleBlock = new w2p_Theme_TitleBlock('Edit User Preferences', 'myevo-weather.png', $m);
$perms = &$AppUI->acl();
if ($canEdit) {
	$titleBlock->addCrumb('?m=system', 'system admin');
	$titleBlock->addCrumb('?m=system&a=systemconfig', 'system configuration');
}
$titleBlock->show();
?>
<script language="javascript" type="text/javascript">
function submitIt() {
	var form = document.changeuser;
	// Collate the checked states of the task log stuff
	var defs = document.getElementById('task_log_email_defaults');
	var mask = 0;
	if (form.tl_assign.checked) {
		mask += 1;
	}
	if (form.tl_task.checked) {
		mask += 2;
	}
	if (form.tl_proj.checked) {
		mask += 4;
	}
	defs.value = mask;
	var expanded = document.getElementById('tasks_expanded');
	var mask = 0;
	if (form.expanded.checked) {
		mask += 1;
	}
	expanded.value = mask;

	defs.value = mask;
	form.submit();
}
</script>
<?php

$form = new w2p_Output_HTML_FormHelper($AppUI);

?>
<form name="changeuser" action="./index.php?m=<?php echo $m; ?>" method="post" accept-charset="utf-8" class="system-preference">
	<input type="hidden" name="dosql" value="do_preference_aed" />
	<input type="hidden" name="pref_user" value="<?php echo $user_id; ?>" />
	<input type="hidden" name="del" value="0" />
    <?php echo $form->addNonce(); ?>

<div class="std addedit preference">
<div class="column left w2p-wide">
    <h2 class="h6 border-bottom pb-1"><?php echo $AppUI->_('User Preferences'); ?>: <?php echo $user_id ? $user : $AppUI->_('Default'); ?></h2>

<div class="d-flex flex-wrap gap-2 mb-2">
	<label><?php echo $AppUI->_('Locale'); ?>:</label>
	<div>
        <?php
            // read the installed languages
            $LANGUAGES = $AppUI->loadLanguages();
            $temp = $AppUI->setWarning(false);
            $langlist = array();
            foreach ($LANGUAGES as $lang => $langinfo) {
                $langlist[$lang] = $langinfo[1];
            }
            /*
             * NOTE: While it may seem egocentric to force US English as the default language, without this line, the
             *   language defaults to whatever is first in the dropdown.. which is Czech at the time of this writing.
             *   Since English is more widespread, I don't feel bad. ~ caseysoftware/caseydk 24 July 2013
             */
            $prefs['LOCALE'] = ('' == $prefs['LOCALE'] || 'en' == $prefs['LOCALE']) ? 'en_US' : $prefs['LOCALE'];
            echo arraySelect($langlist, 'pref_name[LOCALE]', 'class=text size=1', $prefs['LOCALE'], true);
            $AppUI->setWarning($temp);
        ?>
	</div>
</div>

<div class="d-flex flex-wrap gap-2 mb-2">
	<label><?php echo $AppUI->_('Timezone'); ?>:</label>
	<div>
        <?php
            $timezones = w2PgetSysVal('Timezones');
            /*
             * NOTE: While it may seem egocentric to force UTC as the default timezone, without this line,
             *    the timezone defaults to whatever is first in the dropdown. ~ caseysoftware/caseydk 24 July 2013
             */
            $prefs['TIMEZONE'] = ('' == $prefs['TIMEZONE']) ? 'UTC' : $prefs['TIMEZONE'];
            echo arraySelect($timezones, 'pref_name[TIMEZONE]', 'class=text size=1', $prefs['TIMEZONE'], true);
        ?>
	</div>
</div>

<?php if ($user_id && $prefs['TABVIEW'] != 1) { ?>
<div class="d-flex flex-wrap gap-2 mb-2">
	<label><?php echo $AppUI->_('Tabbed Box View'); ?>:</label>
	<div>
<?php
$tabview = array('either', 'tabbed', 'flat');
echo arraySelect($tabview, 'pref_name[TABVIEW]', 'class=text size=1', $prefs['TABVIEW'], true);
?>
	</div>
</div>
<?php } else { ?>
<input type="hidden" name="pref_name[TABVIEW]" value="1" />
<?php } ?>

<div class="d-flex flex-wrap gap-2 mb-2">
	<label><?php echo $AppUI->_('Short Date Format'); ?>:</label>
	<div>
<?php
// exmample date
$ex = new w2p_Utilities_Date();

$dates = array();
$f = '%d/%b/%Y';
$dates[$f] = $ex->format($f);
$f = '%d/%m/%Y';
$dates[$f] = $ex->format($f);
$f = '%d.%m.%Y';
$dates[$f] = $ex->format($f);
$f = '%b/%d/%Y';
$dates[$f] = $ex->format($f);
$f = '%m.%d.%Y';
$dates[$f] = $ex->format($f);
$f = '%m/%d/%Y';
$dates[$f] = $ex->format($f);
$f = '%Y/%b/%d';
$dates[$f] = $ex->format($f);
$f = '%Y/%m/%d';
$dates[$f] = $ex->format($f);
$f = '%Y-%m-%d';
$dates[$f] = $ex->format($f);
echo arraySelect($dates, 'pref_name[SHDATEFORMAT]', 'class=text size=1', $prefs['SHDATEFORMAT'], false);
?>
	</div>
</div>

<div class="d-flex flex-wrap gap-2 mb-2">
	<label><?php echo $AppUI->_('Time Format'); ?>:</label>
	<div>
<?php
// exmample date
$times = array();
$f = '%I:%M %p';
$times[$f] = $ex->format($f);
$f = '%H:%M';
$times[$f] = $ex->format($f) . ' (24)';
$f = '%H:%M:%S';
$times[$f] = $ex->format($f) . ' (24)';
echo arraySelect($times, 'pref_name[TIMEFORMAT]', 'class=text size=1', $prefs['TIMEFORMAT'], false);
?>
	</div>
</div>

<div class="d-flex flex-wrap gap-2 mb-2">
	<label><?php echo $AppUI->_('Currency Format'); ?>:</label>
	<div>
<?php
$currencies = array();
$currEx = 1234567.89;

foreach (array_keys($LANGUAGES) as $lang) {
	$currencies[$lang] = formatCurrency($currEx, $AppUI->setUserLocale($lang, false));
}
$prefs['CURRENCYFORM'] = ('' == $prefs['CURRENCYFORM']) ? 'en_US' : $prefs['CURRENCYFORM'];
echo arraySelect($currencies, 'pref_name[CURRENCYFORM]', 'class=text size=1', $prefs['CURRENCYFORM'], false);
?>
	</div>
</div>

<div class="d-flex flex-wrap gap-2 mb-2">
	<label><?php echo $AppUI->_('User Interface Style'); ?>:</label>
	<div>
<?php
$uis = $prefs['UISTYLE'] ? $prefs['UISTYLE'] : 'web2project';
$styles = $AppUI->readDirs('style');
unset($styles['_common']);
$temp = $AppUI->setWarning(false);
echo arraySelect($styles, 'pref_name[UISTYLE]', 'class=text size=1', $uis, true, true);
$AppUI->setWarning($temp);
?>
	</div>
</div>
<div class="d-flex flex-wrap gap-2 mb-2">
	<label><?php echo $AppUI->_('User Task Assignment Maximum'); ?>:</label>
	<div>
<?php
$tam = ($prefs['TASKASSIGNMAX'] > 0) ? $prefs['TASKASSIGNMAX'] : 100;
$taskAssMax = array();
for ($i = 5; $i <= 200; $i += 5) {
	$taskAssMax[$i] = $i . '%';
}
echo arraySelect($taskAssMax, 'pref_name[TASKASSIGNMAX]', 'class=text size=1', $tam, false);

?>
	</div>
</div>
<div class="d-flex flex-wrap gap-2 mb-2">
	<label><?php echo $AppUI->_('Default Event Filter'); ?>:</label>
	<div>
<?php
$event_filter_list = array('my' => 'My Events', 'own' => 'Events I Created', 'all' => 'All Events');
echo arraySelect($event_filter_list, 'pref_name[EVENTFILTER]', 'class=text size=1', $prefs['EVENTFILTER'], true);
?>
	</div>
</div>
<div class="d-flex flex-wrap gap-2 mb-2">
	<label><?php echo $AppUI->_('Task Notification Method'); ?>:</label>
	<div>
<?php
$notify_filter = array(0 => 'Do not include task/event owner', 1 => 'Include task/event owner');

echo arraySelect($notify_filter, 'pref_name[MAILALL]', 'class=text size=1', $prefs['MAILALL'], true);

?>
	</div>
</div>
<div class="d-flex flex-wrap gap-2 mb-2">
	<label><?php echo $AppUI->_('Task Log Email Defaults'); ?>:</label>
	<div>
		<input type="hidden" name="pref_name[TASKLOGEMAIL]" id="task_log_email_defaults" value="<?php echo $prefs['TASKLOGEMAIL']; ?>" />
<?php

$taskLogEmail = (isset($prefs['TASKLOGEMAIL'])) ? (int) $prefs['TASKLOGEMAIL'] : 0;

$tl_assign = $taskLogEmail & 1;
$tl_task = $taskLogEmail & 2;
$tl_proj = $taskLogEmail & 4;
echo '<p><label for="tl_assign" class="form-check-label">' . $AppUI->_('Email Assignees') . '</label>&nbsp;<input type="checkbox" name="tl_assign" id="tl_assign"';
if ($tl_assign) {
	echo ' checked="checked"';
}
echo ' /></p>';
echo '<p><label for="tl_task" class="form-check-label">' . $AppUI->_('Email Task Contacts') . '</label>&nbsp;<input type="checkbox" name="tl_task" id="tl_task"';
if ($tl_task) {
	echo 'checked="checked"';
}
echo ' /></p>';
echo '<p><label for="tl_proj" class="form-check-label">' . $AppUI->_('Email Project Contacts') . '</label>&nbsp;<input type="checkbox" name="tl_proj" id="tl_proj"';
if ($tl_proj) {
	echo ' checked="checked"';
}
echo ' /></p>';
?>
	</div>
</div>
<div class="d-flex flex-wrap gap-2 mb-2">
	<label><?php echo $AppUI->_('Task Log Email Subject'); ?>:</label>
	<div>
		<input type="text" class="text" name="pref_name[TASKLOGSUBJ]" value="<?php echo $prefs['TASKLOGSUBJ']; ?>" />
	</div>
</div>
<div class="d-flex flex-wrap gap-2 mb-2">
	<label><?php echo $AppUI->_('Task Log Email Recording Method'); ?>:</label>
	<div>
        <?php
            $record_method['0'] = $AppUI->_('None');
            $record_method['1'] = $AppUI->_('Append to Log');
            echo arraySelect($record_method, 'pref_name[TASKLOGNOTE]', 'class="text" size="1"', $prefs['TASKLOGNOTE'], false);
        ?>
	</div>
</div>
<div class="d-flex flex-wrap gap-2 mb-2">
	<label><?php echo $AppUI->_('Show Parent Tasks Expanded by Default'); ?>:</label>
	<div>
		<input type="hidden" name="pref_name[TASKSEXPANDED]" id="tasks_expanded" value="<?php echo $prefs['TASKSEXPANDED']; ?>" />
        <?php
		echo '<input type="checkbox" name="expanded"';
		if ($prefs['TASKSEXPANDED']) {
			echo ' checked="checked"';
		}
		echo ' />';
        ?>
	</div>
</div>
</div>
<div class="w2p-form-actions">
    <?php $form->showCancelButton(); ?>
    <input class="button btn btn-primary" type="button" value="<?php echo $AppUI->_('save'); ?>" onclick="submitIt()" />
</div>
</div>
</form>