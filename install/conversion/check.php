<?php
	if (!defined('W2P_BASE_DIR')) {
		die('You should not access this file directly.');
	}
	
	$failedImg = '<img src="../style/web2project/images/log-error.gif" width="16" height="16" align="middle" alt="Failed"/>';
	$okImg = '<img src="../style/web2project/images/log-notice.gif" width="16" height="16" align="middle" alt="OK"/>';
	
	$continue = true;
?>
<table cellspacing="0" cellpadding="3" border="0" class="tbl" width="90%" align="center" style="margin-top: 20px;">
	<tr>
		<td class="title" colspan="2">Step 1: Check System Settings</td>
	</tr>
	<tr>
		<td colspan="2">
			There is an initial Check for (minimal) requirements appended below for 
			troubleshooting. At minimum, a database and corresponding database 
			connection must be available in addition to PHP5, the GD libraries 
			installed for Gantt charts, and file_uploads should be allowed.  In 
			addition ../includes/config.php should be writable for the webserver.
		</td>
	</tr>
	<tr>
		<td class="title" colspan="2">Confirm Requirements</td>
	</tr>
	<tr>
		<td class="item">PHP Version &gt;= 5.0</td>
		<td align="left">
			<?php
			if (version_compare(phpversion(), '5.0', '<=')) {
        echo '<b class="error">'.$failedImg.' ('.phpversion().'): web2Project requires PHP 5.0+. Please upgrade!</b>';
				$continue = false;
			} else {
				echo '<b class="ok">'.$okImg.'</b> <span class="item">('.phpversion().')</span>';
			}
			?>
		</td>
	</tr>
	<tr>
		<td class="item">GD Support (for GANTT Charts)</td>
		<td align="left">
		<?php 
			if (!extension_loaded('gd')) {
				echo '<b class="error">'.$failedImg.'</b> <span class="item">GANTT Chart functionality may not work correctly.</span>';
				$continue = false;
			} else {
				echo '<b class="ok">'.$okImg.'</b>';
			}
		?>
		</td>
	</tr>
	<tr>
		<td class="item">File Uploads</td>
		<td align="left">
		<?php 
			if (!ini_get('file_uploads') && is_writable(W2P_BASE_DIR.'/files')) {
        echo '<b class="error">'.$failedImg.'</b> <span class="warning">Upload functionality will not be available, please make the ./files directory writable.</span>';
				$continue = false;
			} else {
				echo '<b class="ok">'.$okImg.'</b> <span class="item">(Max File Upload Size: '. $manager->getMaxFileUpload() .')</span>';
			}
		?>
		</td>
	</tr>
	<tr>
		<td class="title" colspan="2"><br />Check requirements for optional components</td>
	</tr>
	<tr>
		<td class="item">Session Save Path writable?</td>
		<td align="left">
			<?php 
				$sspath = ini_get('session.save_path');
				if (! $sspath) {
					echo '<b class="error">'.$failedImg.'</b> <span class="warning">session.save_path</span> <b class="error">is not set</b>';
				} else if (is_dir($sspath) && is_writable($sspath)) {
					echo "<b class='ok'>$okImg</b> <span class='item'>($sspath)</span>";
				} else {
					echo '<b class="error">'.$failedImg.'</b> <span class="warning">'.$sspath.'</span><b class="error"> not existing or not writable</b>';
				}
			?>
		</td>
	</tr>
  <tr>
    <td class="item">Server API</td>
    <td align="left">
    <?php 
      if (strpos(strtolower(php_sapi_name()), 'cgi') !== false) {
        echo '<b class="error">'.$failedImg.' CGI mode is likely to have problems</b>';
      } else {
        echo '<b class="ok">'.$okImg.'</b> <span class="item">('.php_sapi_name().')</span>';
      }
    ?>
    </td>
  </tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
		<td class="item" align="left" colspan="2" style="text-align: center;"><b class="error">Please confirm that your database has been backed up properly before running this process.  You can never have too many backups.</b></td>
	</tr>
	<tr>
	  <td colspan="2" align="center">
		  <form action="<?php echo $baseUrl; ?>/index.php" method="post" name="form" id="form" accept-charset="utf-8">
		  	<?php if ($continue) { ?>
		  		<input type="hidden" name="step" value="perform" />
		  		<input class="button" type="submit" name="next" value="Perform Conversion &raquo;" />
		  	<?php } else { ?>
		  		<input class="button" type="button" value="Conversion Stopped" onClick="alert('The above issues must be fixed before continuing.')" />
		  	<?php } ?> 
			</form>
		</td>
	</tr>
</table>