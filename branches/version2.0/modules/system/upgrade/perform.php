<?php /* $Id: index.php 202 2008-08-11 22:35:39Z pedroix $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/branches/v1.0-rc1/modules/system/index.php $ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
$perms = $AppUI->acl();

// let's see if the user has sys access
if (!canEdit('system')) {
	$AppUI->redirect('m=public&a=access_denied');
}

$titleBlock = new CTitleBlock($AppUI->_('Upgrade System'), 'control-center.png', $m, $m . '.' . $a);
$titleBlock->addCrumb('?m=system', 'system admin');
$titleBlock->show();
?>
<table class="std" width="100%" border="0" cellpadding="0" cellspacing="5">
	<tr>
		<td class="title" colspan="2">Step 2: Update Database</td>
	</tr>
	<?php
        $system = new CSystem();
        $errorMessages = $system->upgradeSystem();

		$updatesApplied = $system->getUpdatesApplied();
		if (count($updatesApplied) > 0) {
			foreach ($updatesApplied as $update) {
				?>
				<tr><td colspan="2">Database update - <?php echo $update; ?> - applied</td></tr>
				<?php
			}
		} else {
			?>
			<tr><td colspan="2">No database updates applied</td></tr>
			<?php
		}

		if (count($errorMessages) > 0) {
			?>
			<tr>
				<td colspan="2"><b class="error">There were <?php echo count($errorMessages); ?> errors in the system update.</b></td>
			</tr>
			<?php
			foreach ($errorMessages as $message) {
				?>
				<tr><td colspan="2"><?php echo $message; ?></td></tr>
				<?php
			}
			?>
			<tr>
				<td colspan="2">Note: Errors noting 'Duplicate entry', 'Table already exists', or 'Unknown table' may not be problems.</td>
			</tr>
			<?php
		} else {
			?>
			<tr>
				<td colspan="2">Your system update went smoothly without any errors.</td>
			</tr>
			<?php
		}
	?>
	<tr><td colspan="2">&nbsp;</td></tr>
</table>