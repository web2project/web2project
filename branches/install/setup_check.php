<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

global $cfgDir, $cfgFile, $failedImg, $filesDir, $locEnDir, $okImg, $tblwidth, $tmpDir;

$failedImg = str_replace('./style/', '../style/', w2PshowImage('stock_cancel-16.png', 16, 16, 'Failed'));
$okImg = str_replace('./style/', '../style/', w2PshowImage('stock_ok-16.png', 16, 16, 'OK'));
$cfgDir = W2P_BASE_DIR . '/includes';
$cfgFile = W2P_BASE_DIR . '/includes/config.php';
$filesDir = W2P_BASE_DIR . '/files';
$locEnDir = W2P_BASE_DIR . '/locales/en';
$tmpDir = W2P_BASE_DIR . '/files/temp';
$tblwidth = '75%';
$chmod = 0777;

$maxfileuploadsize = min(w2PgetIniSize(ini_get('upload_max_filesize')), w2PgetIniSize(ini_get('post_max_size')));
$memory_limit = w2PgetIniSize(ini_get('memory_limit'));
if ($memory_limit > 0 && $memory_limit < $maxfileuploadsize) {
	$maxfileuploadsize = $memory_limit;
}
// Convert back to human readable numbers
if ($maxfileuploadsize > 1048576) {
	$maxfileuploadsize = (int)($maxfileuploadsize / 1048576) . 'M';
} elseif ($maxfileuploadsize > 1024) {
	$maxfileuploadsize = (int)($maxfileuploadsize / 1024) . 'K';
}

$info = array();
$fatal = array();
$warnings = array();
if (version_compare(phpversion(), '4.1', '<')) {
	$info['phpversion'] = '<b class="error">' . $failedImg . ' (' . phpversion() . '): webProject will not work. Please upgrade PHP!</b>';
	$fatal[] = '<b class="error">' . $failedImg . ' PHP Version (' . phpversion() . '): webProject will not work. Please upgrade PHP!</b>';
} else {
	$info['phpversion'] = '<b class="ok">' . $okImg . '</b><span class="item"> (' . phpversion() . ')</span>';
}

if (php_sapi_name() != 'cgi') {
	$info['cgi'] = '<b class="ok">' . $okImg . '</b><span class="item"> (' . php_sapi_name() . ')</span>';
} else {
	$info['cgi'] = '<b class="error">' . $failedImg . ' CGI mode is likely to have problems</b>';
	$warnings[] = 'CGI mode is likely to have problems';
}

if (extension_loaded('gd')) {
	$info['gd'] = '<b class="ok">' . $okImg . '</b>';
} else {
	$info['gd'] = '<b class="error">' . $failedImg . '</b> GANTT Chart functionality may not work correctly.';
	$warnings[] = 'GD library is missing. GANTT Chart functionality may not work correctly.';
}

if (extension_loaded('zlib')) {
	$info['zlib'] = '<b class="ok">' . $okImg . '</b>';
} else { 
	$info['zlib'] = '<b class="error">' . $failedImg . '</b> Some non-core modules such as Backup may have restricted operation.';
	$warnings[] = 'No Zlib support. Some non-core modules such as Backup may have restricted operation.';
}

if (ini_get('file_uploads')) {
	$info['uploads'] = '<b class="ok">' . $okImg . '</b><span class="item"> (Max File Upload Size: ' . $maxfileuploadsize . ')</span>';
} else {
	$info['uploads'] = '<b class="error">' . $failedImg . '</b><span class="warning"> Upload functionality will not be available</span>';
	$warnings[] = 'File Upload functionality will not be available';
}

if (!function_exists('mysql_connect')) {
	$fatal[] = '<b class="error">' . $failedImg . ' Mysql is not available!</b>';
}
if (!is_writable($cfgFile) || !is_writable($cfgDir)) {
	$fatal[] = '<b class="error">' . $failedImg . ' It is not possible to write to /includes or includes/config.php is not writeable!</b>';
}
if (!is_writable($filesDir)) {
	$warnings[] = 'File Upload may not work because the Files folder is not writeable!';
}
if (!is_writable($tmpDir)) {
	$warnings[] = 'PDFs may not render because the Files Temp folder is not writeable!';
}
if (!is_writable($locEnDir)) {
	$warnings[] = 'Translation Management service may not work properly because the Locales en folder is not writeable!';
}
if (ini_get('safe_mode')) {
	$warnings[] = 'PHP Safe Mode is on. You may have problems with File Uploads and Gantt Charts!';
}
if (ini_get('register_globals')) {
	$warnings[] = 'Though web2Project is protected against PHP Register Globals attacks you may want to turn it off for extra safety.';
}
if (ini_get('session.use_trans_sid')) {
	$warnings[] = 'PHP Use Trans Sid is ON. You should turn it off to avoid session hijacking!';
}
?>

<table class="text" style="background-color:#FFFFFF;" cellspacing="0" cellpadding="3" border="0" width="<?php echo $tblwidth; ?>" align="center">
<tr>
	<td width="18"><?php echo str_replace('./style/', '../style/', w2PshowImage('log-info.gif', 16, 16, 'System Information')); ?></td> 
	<td width="100%"><a onclick="expand_collapse('sysinfo')" style="display: block;" name="fp" href="javascript: void(0);">
		<b>System Information</b>&nbsp;<font size="1">(show|hide)</font></a>
	</td>
	<td width="12" align="right" colspan="1">
<?php
	echo '<a href="javascript: void(0);" name="fbt" style="display:block" onclick="expand_collapse(\'sysinfo\');">';
	echo '<img id="sysinfo_expand" src="' . str_replace('./style/', '../style/', w2PfindImage('icons/expand.gif')) . '" width="12" height="12" border="0" style="display:"><img id="sysinfo_collapse" src="' . str_replace('./style/', '../style/', w2PfindImage('icons/collapse.gif')) . '" width="12" height="12" border="0" style="display:none"></a>';
?>
    </td>
</tr>
<tr id="sysinfo" style="visibility: collapse; display: none;">
	<td colspan="3">
		<table width="100%" cellspacing="0" cellpadding="0" border="0" class="tbl" width="<?php echo $tblwidth; ?>" align="left">
		<tr>
			<td class="title" colspan="2">PHP Requirements</td>
		</tr>
		<tr>
			<td>PHP Version &gt;= 4.1</td>
			<td align="left"><?php echo $info['phpversion']; ?></td>
		</tr>
		<tr>
			<td>Server API</td>
			<td align="left"><?php echo $info['cgi']; ?></td>
		</tr>
		<tr>
			<td nowrap="nowrap">GD Support (for GANTT Charts)</td>
			<td align="left"><?php echo $info['gd']; ?></td>
		</tr>
		<tr>
			<td>Zlib compression Support</td>
			<td align="left"><?php echo $info['zlib']; ?></td>
		</tr>
		<tr>
			<td>File Uploads</td>
			<td align="left"><?php echo $info['uploads']; ?></td>
		</tr>
		<tr>
			<td>Session Save Path writable?</td>
			<td align="left">
		<?php
		$sspath = ini_get('session.save_path');
		if (!$sspath) {
			echo "<b class='error'>$failedImg Fatal:</b> <span class='item'>session.save_path</span> <b class='error'> is not set</b>";
		} elseif (is_dir($sspath) && is_writable($sspath)) {
			echo "<b class='ok'>$okImg</b> <span class='item'>($sspath)</span>";
		} else {
			echo "<b class='error'>$failedImg Fatal:</b> <span class='item'>$sspath</span><b class='error'> not existing or not writable</b>";
		}
		?>
			</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td class="title" colspan="2">Database Connectors</td>
		</tr>
		<tr>
			<td class="item" colspan="2">The next tests check for database support compiled with php. We use the ADODB database abstraction layer which comes with drivers for
			many databases. Consult the ADODB documentation for details.<br />For the moment only MySQL is fully supported, so you need to make sure it
			is available.</td>
		</tr>
		<tr>
			<td>iBase Support</td>
			<td align="left"><?php echo (function_exists('ibase_connect') && function_exists('ibase_server_info')) ? '<b class="ok">' . $okImg . '</b><span class="item"> (' . @ibase_server_info() . ')</span>' : '<span class="warning">' . $failedImg . ' Not available</span>'; ?></td>
		</tr>
		<tr>
			<td>LDAP Support</td>
			<td align="left"><?php echo function_exists('ldap_connect') ? '<b class="ok">' . $okImg . '</b><span class="item"> </span>' : '<span class="warning">' . $failedImg . ' Not available</span>'; ?></td>
		</tr>
		<tr>
			<td>MSSQL Server Support</td>
			<td align="left"><?php echo function_exists('mssql_connect') ? '<b class="ok">' . $okImg . '</b><span class="item"></span>' : '<span class="warning">' . $failedImg . ' Not available</span>'; ?></td>
		</tr>
		<tr>
			<td>MySQL Support</td>
			<td align="left"><?php echo function_exists('mysql_connect') ? '<b class="ok">' . $okImg . '</b><span class="item"> (' . @mysql_get_server_info() . ')</span>' : '<span class="warning">' . $failedImg . ' Not available</span>'; ?></td>
		</tr>
		<tr>
			<td>ODBC Support</td>
			<td align="left"><?php echo function_exists('odbc_connect') ? '<b class="ok">' . $okImg . '</b><span class="item"></span>' : '<span class="warning">' . $failedImg . ' Not available</span>'; ?></td>
		</tr>
		<tr>
			<td>Oracle Support</td>
			<td align="left"><?php echo function_exists('oci_connect') ? '<b class="ok">' . $okImg . '</b><span class="item"> (' . @ociserverversion() . ')</span>' : '<span class="warning">' . $failedImg . ' Not available</span>'; ?></td>
		</tr>
		<tr>
			<td>PostgreSQL Support</td>
			<td align="left"><?php echo function_exists('pg_connect') ? '<b class="ok">' . $okImg . '</b><span class="item"></span>' : '<span class="warning">' . $failedImg . ' Not available</span>'; ?></td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td class="title" colspan="2">Check for Directory and File Permissions</td>
		</tr>
		<tr>
			<td class="item" colspan="2">If the message 'World Writable' appears after a file/directory, then Permissions for this File have been set to allow all users to write to this file/directory.
			Consider changing this to a more restrictive setting to improve security. You will need to do this manually.</td>
		</tr>
		<?php
		$okMessage = '';
		if ((file_exists($cfgFile) && !is_writable($cfgFile)) || (!file_exists($cfgFile) && !(is_writable($cfgDir)))) {
		
			@chmod($cfgFile, $chmod);
			@chmod($cfgDir, $chmod);
			$filemode = @fileperms($cfgFile);
			if ($filemode & 2) {
				$okMessage = '<span class="error"> World Writable</span>';
			}
		
		}
		?>
		<tr>
			<td>./includes/config.php writable?</td>
			<td align="left"><?php echo (is_writable($cfgFile) || is_writable($cfgDir)) ? '<b class="ok">' . $okImg . '</b>' . $okMessage : '<b class="error">' . $failedImg . '</b><span class="warning"> Configuration process can still be continued. Configuration file will be displayed at the end, just copy & paste this and upload.</span>'; ?></td>
		</tr>
		<?php
		$okMessage = "";
		if (!is_writable($filesDir)) {
			@chmod($filesDir, $chmod);
		}
		$filemode = @fileperms($filesDir);
		if ($filemode & 2) {
			$okMessage = '<span class="error"> World Writable</span>';
		}
		?>
		<tr>
			<td>./files writable?</td>
			<td align="left"><?php echo is_writable($filesDir) ? '<b class="ok">' . $okImg . '</b>' . $okMessage : '<b class="error">' . $failedImg . '</b><span class="warning"> File upload functionality will be disabled</span>'; ?></td>
		</tr>
		<?php
		$okMessage = "";
		if (!is_writable($tmpDir))
			@chmod($tmpDir, $chmod);
		
		$filemode = @fileperms($tmpDir);
		if ($filemode & 2) {
			$okMessage = '<span class="error"> World Writable</span>';
		}
		?>
		<tr>
			<td>./files/temp writable?</td>
			<td align="left"><?php echo is_writable($tmpDir) ? '<b class="ok">' . $okImg . '</b>' . $okMessage : '<b class="error">' . $failedImg . '</b><span class="warning"> PDF report generation will be disabled</span>'; ?></td>
		</tr>
		<?php
		$okMessage = "";
		if (!is_writable($locEnDir)) {
			@chmod($locEnDir, $chmod);
		}
		$filemode = @fileperms($locEnDir);
		if ($filemode & 2) {
			$okMessage = '<span class="error"> World Writable</span>';
		}
		?>
		<tr>
			<td>./locales/en writable?</td>
			<td align="left"><?php echo is_writable($locEnDir) ? '<b class="ok">' . $okImg . '</b>' . $okMessage : '<b class="error">' . $failedImg . '</b><span class="warning"> Translation files cannot be saved. Check /locales and subdirectories for permissions.</span>'; ?></td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td class="title" colspan="2">Recommended PHP Settings</td>
		</tr>
		<tr>
			<td>Safe Mode = OFF?</td>
			<td align="left"><?php echo !ini_get('safe_mode') ? '<b class="ok">' . $okImg . '</b>' : '<b class="error">' . $failedImg . '</b><span class="warning"></span>'; ?></td>
		</tr>
		<tr>
			<td>Register Globals = OFF?</td>
			<td align="left"><?php echo !ini_get('register_globals') ? '<b class="ok">' . $okImg . '</b>' : '<b class="error">' . $failedImg . '</b><span class="warning"> There are security risks with this turned ON</span>'; ?></td>
		</tr>
		<tr>
			<td>Session AutoStart = ON?</td>
			<td align="left"><?php echo ini_get('session.auto_start') ? '<b class="ok">' . $okImg . '</b>' : '<b class="error">' . $failedImg . '</b><span class="warning"> Try setting to ON if you are experiencing a WhiteScreenOfDeath</span>'; ?></td>
		</tr>
		<tr>
			<td>Session Use Cookies = ON?</td>
			<td align="left"><?php echo ini_get('session.use_cookies') ? '<b class="ok">' . $okImg . '</b>' : '<b class="error">' . $failedImg . '</b><span class="warning"> Try setting to ON if you are experiencing problems logging in</span>'; ?></td>
		</tr>
		<tr>
			<td>Session Use Trans Sid = OFF?</td>
			<td align="left"><?php echo (!ini_get('session.use_only_cookies') && !ini_get('session.use_trans_sid')) ? '<b class="ok">' . $okImg . '</b>' : '<b class="error">' . $failedImg . '</b><span class="warning"> There are security risks with this turned ON</span>'; ?></td>
		</tr>
		</table>
	</td>
</tr>
</table>
<table class="text" style="background-color:#FFFFFF;" cellspacing="0" cellpadding="3" border="0" class="tbl" width="<?php echo $tblwidth; ?>" align="center">
<tr>
	<td width="18"><?php echo str_replace('./style/', '../style/', w2PshowImage('log-error.gif', 16, 16, 'Fatal Errors')); ?></td> 
	<td width="100%"><a onclick="expand_collapse('fatal')" style="display: block;" name="fp" href="javascript: void(0);">
		<b>Fatal Errors</b>&nbsp;<font size="1">(show|hide)</font></a>
	</td>
	<td width="12" align="right" colspan="1">
<?php
	echo '<a href="javascript: void(0);" name="ft" style="display:block" onclick="expand_collapse(\'fatal\');">';
	echo '<img id="fatal_expand" src="' . str_replace('./style/', '../style/', w2PfindImage('icons/expand.gif')) . '" width="12" height="12" border="0" style="display:none"><img id="fatal_collapse" src="' . str_replace('./style/', '../style/', w2PfindImage('icons/collapse.gif')) . '" width="12" height="12" border="0" style="display:"></a>';
?>
    </td>
</tr>
<tr id="fatal" style="visibility:visible;display:;">
	<td colspan="3">
		<table width="100%" cellspacing="0" cellpadding="0" border="0" class="tbl" width="<?php echo $tblwidth; ?>" align="left">
		<?php
		if (count($fatal)) {
			foreach ($fatal as $frec) {
		?>
			<tr>
				<td align="left"><?php echo $frec; ?></td>
			</tr>
		<?php
			}
		} else {
		?>
			<tr>
				<td align="left"><?php echo $okImg; ?>&nbsp;There are no Fatal Errors.</td>
			</tr>
		<?php
		}
		?>
		</table>
	</td>
</tr>
</table>
<table class="text" style="background-color:#FFFFFF;" cellspacing="0" cellpadding="3" border="0" class="tbl" width="<?php echo $tblwidth; ?>" align="center">
<tr>
	<td width="18"><?php echo str_replace('./style/', '../style/', w2PshowImage('alert.gif', 16, 16, 'Warnings')); ?></td> 
	<td width="100%"><a onclick="expand_collapse('warnings')" style="display: block;" name="fp" href="javascript: void(0);">
		<b>Warnings</b>&nbsp;<font size="1">(show|hide)</font></a>
	</td>
	<td width="12" align="right" colspan="1">
<?php
	echo '<a href="javascript: void(0);" name="ft" style="display:block" onclick="expand_collapse(\'warnings\');">';
	echo '<img id="warnings_expand" src="' . str_replace('./style/', '../style/', w2PfindImage('icons/expand.gif')) . '" width="12" height="12" border="0" style="display:none"><img id="warnings_collapse" src="' . str_replace('./style/', '../style/', w2PfindImage('icons/collapse.gif')) . '" width="12" height="12" border="0" style="display:"></a>';
?>
    </td>
</tr>
<tr id="warnings" style="visibility:visible; display:;">
	<td colspan="3">
		<table width="100%" cellspacing="0" cellpadding="0" border="0" class="tbl" width="<?php echo $tblwidth; ?>" align="left">
		<?php
		if (count($warnings)) {
			foreach ($warnings as $wrec) {
		?>
			<tr>
				<td align="left">&bull;&nbsp;<?php echo $wrec; ?></td>
			</tr>
		<?php
			}
		} else {
		?>
			<tr>
				<td align="left"><?php echo $okImg; ?>&nbsp; There are no Warnings.</td>
			</tr>
		<?php
		}
		?>
		</table>
	</td>
</tr>
</table>
</form>