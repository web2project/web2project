<?php
	if (!defined('W2P_BASE_DIR')) {
		die('You should not access this file directly.');
	}

	/*
	 * TODO: There needs to be a check in here to make sure the person attempting
	 * the upgrade has system edit permissions.
	 */
?>
<table cellspacing="0" cellpadding="3" border="0" class="tbl" width="90%" align="center" style="margin-top: 20px;">
	<tr>
		<td class="title" colspan="2">Step 2: Update Database</td>
	</tr>
	<?php
		$errorMessages = $manager->upgradeSystem();

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
	<tr>
		<td class="item" align="center" colspan="2"><br/><b><a href="../index.php?m=system&amp;a=systemconfig&amp;reset=1">Login and Check the web2project System Environment</a></b></td>
	</tr>
</table>