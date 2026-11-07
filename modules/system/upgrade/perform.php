<?php
/** @var w2p_Core_CAppUI $AppUI */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
$perms = $AppUI->acl();

// let's see if the user has sys access
if (!canEdit('system')) {
	$AppUI->redirect(ACCESS_DENIED);
}

$titleBlock = new w2p_Theme_TitleBlock($AppUI->_('Upgrade System'), 'control-center.png', $m);
$titleBlock->addCrumb('?m=system', 'system admin');
$titleBlock->show();
?>
<style>
.update {
    font-size: medium;
}
.update img {
    float: left;
}
</style>
<div class="std update">
	<h3 class="h6 mt-3 title">Step 2: Update Database</h3>
	<?php
        $system = new CSystem();
        $errorMessages = $system->upgradeSystem();

		$updatesApplied = $system->getUpdatesApplied();
		if (count($updatesApplied) > 0) {
			foreach ($updatesApplied as $update) {
				?>
				<div class="my-2">Database update - <?php echo htmlspecialchars($update, ENT_QUOTES, 'UTF-8'); ?> - applied</div>
				<?php
			}
		} else {
			?>
			<div class="my-2">No database updates applied</div>
			<?php
		}

		$failureReport = $system->getFailureReport();
		if ('' !== $failureReport) {
			?>
			<div class="my-2"><b class="error">The system update stopped because a database update failed.</b></div>
			<pre class="my-2 p-2 border" style="white-space: pre-wrap;"><?php echo htmlspecialchars($failureReport, ENT_QUOTES, 'UTF-8'); ?></pre>
			<?php
		} elseif (count($errorMessages) > 0) {
			?>
			<div class="my-2"><b class="error">There were <?php echo count($errorMessages); ?> errors in the system update.</b></div>
			<?php
			foreach ($errorMessages as $message) {
				?>
				<div class="my-2"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
				<?php
			}
		} else {
			?>
			<div class="my-2">Your system update went smoothly without any errors.</div>
			<?php
		}
	?>
</div>