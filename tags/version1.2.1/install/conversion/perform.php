<?php
	if (!defined('W2P_BASE_DIR')) {
		die('You should not access this file directly.');
	}
?>
<table cellspacing="0" cellpadding="3" border="0" class="tbl" width="90%" align="center" style="margin-top: 20px;">
	<tr>
		<td class="title" colspan="2">Step 2: Update Database &amp; Write Configuration</td>
	</tr>
	<?php
		$errorMessages = $manager->convertDotProject();

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
				<td colspan="2">Note: Errors noting 'Duplicate entry', 'Table already exists', or 'Unknown table' may not be problems.  It's possible that your dotProject database was not the version it claimed to be.</td>
			</tr>
			<?php
		} else {
			?>
			<tr>
				<td colspan="2">Your system update went smoothly without any errors.</td>
			</tr>
			<?php
		}

		$dpConfig = $manager->getConfigOptions();
		$config = $manager->createConfigString($dpConfig);

		if ((is_writable(W2P_BASE_DIR.'/includes/config.php')  || !is_file(W2P_BASE_DIR.'/includes/config.php')) && ($fp = @fopen(W2P_BASE_DIR.'/includes/config.php', 'w'))) {
			fputs( $fp, $config, strlen( $config ) );
			fclose( $fp );
			$cFileMsg = 'Config file written successfully'."\n";
		} else {
			$cFileErr = true;
			$cFileMsg = 'Config file could not be written'."\n";
		}
	?>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr>
		<td class="title">Config File Creation Feedback:</td>
		<td class="item" align="left"><b style="color:<?php echo $cFileErr ? 'red' : 'green'; ?>"><?php echo $cFileMsg; ?></b></td>
	</tr>
	<?php if ($cFileErr) { ?>
		<tr>
			<td class="item" align="left" colspan="2">The following content should go to ./includes/config.php. Create that text file manually and copy the following lines in by hand.  This file must be readable by the webserver.</td>
		</tr>
		<tr>
			<td class="item" align="left" colspan="2" style="text-align: center;"><b class="error">Failure to create this file as instructed will render your web2project installation non-operational.<br />Save yourself (and us!) some time and please follow directions. Thanks.</b></td>
		</tr>
		<tr>
			<td align="center" colspan="2"><textarea class="button" name="dbhost" cols="100" rows="20" title="Content of config.php for manual creation." /><?php echo $msg.$config; ?></textarea></td>
		</tr>
	<?php } ?>
	<tr>
		<td class="item" align="center" colspan="2"><br/><b><a href="../index.php?m=system&amp;a=systemconfig&amp;reset=1">Login and Check the web2project System Environment</a></b></td>
	</tr>
</table>