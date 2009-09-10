<?php /* $Id$ $URL$ */
$dialog = w2PgetParam($_GET, 'dialog', 0);
if ($dialog) {
	$page_title = '';
} else {
	$page_title = ($w2Pconfig['page_title'] == 'web2Project') ? $w2Pconfig['page_title'] . '&nbsp;' . $AppUI->getVersion() : $w2Pconfig['page_title'];
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta name="Description" content="web2Project Snowball Style" />
	<meta name="Version" content="<?php echo $AppUI->getVersion(); ?>" />
	<meta http-equiv="Content-Type" content="text/html;charset=<?php echo isset($locale_char_set) ? $locale_char_set : 'UTF-8'; ?>" />
	<title><?php echo @w2PgetConfig('page_title'); ?></title>
	<link rel="stylesheet" type="text/css" href="./style/<?php echo $uistyle; ?>/main.css" media="all" charset="utf-8"/>
	<style type="text/css" media="all">@import "./style/<?php echo $uistyle; ?>/main.css";</style>
	<link rel="shortcut icon" href="./style/<?php echo $uistyle; ?>/favicon.ico" type="image/ico" />
	<?php 
		if (isset($xajax) && is_object($xajax)) {
			$xajax->printJavascript(w2PgetConfig('base_url') . '/lib/xajax');
		}
	?>
	<?php $AppUI->loadHeaderJS(); ?>
</head>

<body onload="this.focus();">
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
	<td>
		<table width="100%" cellpadding="0" cellspacing="0" border="0">
		<tr>
			<th style="background: url(style/<?php echo $uistyle; ?>/title_bkgd.jpg);" align="left">
				<!-- Product Version would go here-->
				&nbsp;
			</th>
			<th style="background: url(style/<?php echo $uistyle; ?>/title_bkgd.jpg);" align="right" width="123"><a href='http://www.web2project.net/' <?php if ($dialog)
			echo 'target="_blank"'; ?>><?php echo w2PtoolTip('web2Project v. ' . $AppUI->getVersion(), 'click to visit web2Project site', true);?><img src="style/<?php echo $uistyle; ?>/title.jpg" border="0" class="banner" align="left" /><?php echo w2PendTip();?></th>
			<th style="background: url(style/<?php echo $uistyle; ?>/title_bkgd.jpg);" align="right" width="5">
				<!--a little spacer-->
				&nbsp;
			</th>
		</tr>
		</table>
	</td>
</tr>
<?php if (!$dialog) {
	$perms = &$AppUI->acl();
?>
<tr>
	<td align="left">
	<table width="100%" cellpadding="0" cellspacing="0" width="100%">
	<tbody>
	<form name="frm_new" method="GET" action="./index.php" accept-charset="utf-8">
<?php
	echo '<input type="hidden" name="a" value="addedit" />';

	//build URI string
	if (isset($company_id)) {
		echo '<input type="hidden" name="company_id" value="' . $company_id . '" />';
	}
	if (isset($task_id)) {
		echo '<input type="hidden" name="task_parent" value="' . $task_id . '" />';
	}
	if (isset($file_id)) {
		echo '<input type="hidden" name="file_id" value="' . $file_id . '" />';
	}
?>
	<tr class="nav">
		<td>
      <?php echo buildHeaderNavigation($AppUI, 'ul', 'li'); ?>
		</td>
<?php
	if ($AppUI->user_id > 0) {
		//Do this check in case we are not using any user id, for example for external uses
		echo '<td nowrap="nowrap" align="right">';
		$newItem = array('' => '- New Item -');
		if ($perms->checkModule('companies', 'add')) {
			$newItem['companies'] = 'Company';
		}
		if ($perms->checkModule('contacts', 'add')) {
			$newItem['contacts'] = 'Contact';
		}
		if ($perms->checkModule('calendar', 'add')) {
			$newItem['calendar'] = 'Event';
		}
		if ($perms->checkModule('files', 'add')) {
			$newItem['files'] = 'File';
		}
		if ($perms->checkModule('projects', 'add')) {
			$newItem['projects'] = 'Project';
		}
		if ($perms->checkModule('admin', 'add')) {
			$newItem['admin'] = 'User';
		}
		echo arraySelect($newItem, 'm', 'style="font-size:10px" onchange="f=document.frm_new;mod=f.m.options[f.m.selectedIndex].value;if (mod == \'admin\') document.frm_new.a.value=\'addedituser\';if(mod) f.submit();"', '', true);
		echo '</td>';
	}
	$df = $AppUI->getPref('SHDATEFORMAT');
	$df .= ' ' . $AppUI->getPref('TIMEFORMAT');
	$cf = $df;
	$cal_df = $cf;
	$cal_df = str_replace('%S', '%s', $cal_df);
	$cal_df = str_replace('%M', '%i', $cal_df);
	$cal_df = str_replace('%p', '%a', $cal_df);
	$cal_df = str_replace('%I', '%h', $cal_df);
	$cal_df = str_replace('%b', '%M', $cal_df);
	$cal_df = str_replace('%', '', $cal_df);
	$df = $cal_df;
?>
	</tr>
	</form>
	<tr>
		<td colspan="2" valign="top" style="background: url(style/<?php echo $uistyle; ?>/nav_shadow.jpg);" align="left">
			<img width="1" height="13" src="style/<?php echo $uistyle; ?>/nav_shadow.jpg"/>
		</td>
	</tr>
	</tbody>
	</table>
	</td>
</tr>
<tr>
	<table cellspacing="0" cellpadding="3" border="0" width="100%">
	<tr>
		<td width="75%">
			<table cellspacing="0" cellpadding="3" border="0" width="100%">
			<tr>
				<td><?php echo $AppUI->_('Welcome') . ' ' . ($AppUI->user_id > 0 ? ($AppUI->user_first_name . ' ' . $AppUI->user_last_name . '. ' . $AppUI->_('Server time is') . ' ' . date($df)) : $outsider); ?></td>
			</tr>
			</table>
		</td>
<?php
	if ($AppUI->user_id > 0) {
		//Just show this stuff if there is a user logged in

?>
		<td width="170" valign="middle" nowrap="nowrap"><table><tr><form name="frm_search" action="?m=smartsearch" method="POST" accept-charset="utf-8"><td>
             	     <?php 
						if ($perms->checkModule('smartsearch', 'access')) {					  
							echo w2PshowImage('search.png') ?>&nbsp;<input class="text" size="20" type="text" id="keyword" name="keyword" value="<?php echo $AppUI->_('Global Search') . '...'; ?>" onclick="document.frm_search.keyword.value=''" onblur="document.frm_search.keyword.value='<?php echo $AppUI->_('Global Search') . '...'; ?>'" />
             	     <?php
					  	} else {
					  		echo '&nbsp;';
					  	}
						?> 
        </td></form></tr></table></td>
		<td width="275" nowrap="nowrap">		
				<table cellspacing="0" cellpadding="3" border="0" width="100%">
				<tr>
					<td nowrap="nowrap" align="right">
						<a class="button" href="javascript: void(0);" onclick="javascript:window.open('?m=help&dialog=1&hid=', 'contexthelp', 'width=800, height=600, left=50, top=50, scrollbars=yes, resizable=yes')"><span><?php echo $AppUI->_('Help'); ?></span></a>
					</td>
					<td nowrap="nowrap" align="right">
						<a class="button" href="./index.php?m=admin&a=viewuser&user_id=<?php echo $AppUI->user_id; ?>"><span><?php echo $AppUI->_('My Info'); ?></span></a>
					</td>
		<?php
		if ($perms->checkModule('tasks', 'access')) {
?>
					<td nowrap="nowrap" align="right">
						<a class="button" href="./index.php?m=tasks&a=todo"><span><b><?php echo $AppUI->_('Todo'); ?></b></span></a>
					</td>
		<?php
		}
		if ($perms->checkModule('calendar', 'access')) {
			$now = new CDate();
?>
					<td nowrap="nowrap" align="right">
						<a class="button" href="./index.php?m=calendar&a=day_view&date=<?php echo $now->format(FMT_TIMESTAMP_DATE); ?>"><span><?php echo $AppUI->_('Today'); ?></span></a>
					</td>
		<?php } ?>
						<td nowrap="nowrap" align="right">
							<a class="button" href="./index.php?logout=-1"><span><?php echo $AppUI->_('Logout'); ?></span></a>
						</td>
					</td>
				</tr>
				</table>
		</td>
<?php } ?>
	</tr>
	</table>
	</td>
</tr>
<?php } // END showMenu ?>
</table>

<table width="100%" cellspacing="0" cellpadding="4" border="0">
<tr>
<td valign="top" align="left" width="98%">
<?php
echo $AppUI->getMsg();
$AppUI->boxTopRendered = false;
if ($m == 'help' && function_exists('styleRenderBoxTop')) {
	echo styleRenderBoxTop();
}
?>