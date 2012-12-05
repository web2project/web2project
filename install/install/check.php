<?php
	if (!defined('W2P_BASE_DIR')) {
		die('You should not access this file directly.');
	}

	$failedImg = '<img src="../style/web2project/images/log-error.gif" width="16" height="16" align="middle" alt="Failed"/>';
	$okImg = '<img src="../style/web2project/images/log-notice.gif" width="16" height="16" align="middle" alt="OK"/>';

	$continue = true;
?>
<table cellspacing="0" cellpadding="3" border="0" class="tbl update" align="center">
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
		<td class="item">PHP Version &gt;= <?= MIN_PHP_VERSION; ?></td>
		<td align="left">
			<?php
			if (version_compare(PHP_VERSION, MIN_PHP_VERSION, '<')) {
                echo '<b class="error">'.$failedImg.' ('.PHP_VERSION.'): web2Project requires PHP '.MIN_PHP_VERSION.'+. Please upgrade!</b>';
				$continue = false;
			} else {
				echo '<b class="ok">'.$okImg.'</b> <span class="item">('.PHP_VERSION.')</span>';
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
		<td class="title" colspan="2"><br />Database Connectors</td>
	</tr>
	<tr>
		<td class="item" colspan="2">
			<p>The next tests check for database support compiled with php. We use
			the ADODB database abstraction layer which comes with drivers for many
			databases. Consult the ADODB documentation for details.  For the moment
			only MySQL is fully supported, so you need to make sure it is available.</p>
		</td>
	</tr>
	<tr>
		<td class="item">MySQL Support</td>
		<td align="left">
		<?php
			if (!function_exists('mysql_connect')) {
				echo '<b class="error"><span class="warning">'.$failedImg.' Not available</span>';
				$continue = false;
			} else {
				echo '<b class="ok">'.$okImg.'</b>';
			}
		?>
		</td>
	</tr>
	<tr>
		<td class="title" colspan="2"><br />Check for Directory and File Permissions</td>
	</tr>
	<tr>
		<td class="item" colspan="2">If the message 'World Writable' appears after
		a file/directory, then Permissions for this File have been set to allow
		all users to write to this file/directory.  Consider changing this to a
		more restrictive setting to improve security. You will need to do this manually.</td>
	</tr>
	<?php
		$okMessage = '';
		if (!file_exists($manager->getConfigFile()) && is_writable( $manager->getConfigDir() )) {
			$fh = fopen($manager->getConfigFile(), 'w');
			fclose($fh);
		}
		if ( (file_exists( $manager->getConfigFile() ) && !is_writable( $manager->getConfigFile() )) || (!file_exists( $manager->getConfigFile() ) && !(is_writable( $manager->getConfigDir() ))) ) {
		  @chmod( $manager->getConfigFile(), $chmod );
		  @chmod( $manager->getConfigDir(), $chmod );
			$filemode = @fileperms($manager->getConfigFile());
			if ($filemode & 2) {
				$okMessage='<span class="error"> World Writable</span>';
			}
		}
	?>
	<tr>
	  <td class="item">./includes/config.php writable?</td>
	  <td align="left"><?php echo ( is_writable( $manager->getConfigFile() ) || is_writable( $manager->getConfigDir ))  ? '<b class="ok">'.$okImg.'</b>'.$okMessage : '<b class="error">'.$failedImg.'</b><span class="warning"> Configuration process can still be continued. Configuration file will be displayed at the end, just copy & paste this and upload.</span>';?></td>
	</tr>
	<?php
		$okMessage = "";
		if (!is_writable( $manager->getUploadDir() )) {
			@chmod( $manager->getUploadDir(), $chmod );
		}
		$filemode = @fileperms($manager->getUploadDir());
		if ($filemode & 2) {
			$okMessage='<span class="error"> World Writable</span>';
		}
	?>
	<tr>
	  <td class="item">./files writable?</td>
	  <td align="left"><?php echo is_writable( $manager->getUploadDir() ) ? '<b class="ok">'.$okImg.'</b>'.$okMessage : '<b class="error">'.$failedImg.'</b><span class="warning"> File upload functionality will be disabled</span>';?></td>
	</tr>
	<?php
		$okMessage = "";
		if (!is_writable( $manager->getTempDir() )) {
			@chmod( $manager->getTempDir(), $chmod );
		}
		$filemode = @fileperms($manager->getTempDir());
		if ($filemode & 2) {
			$okMessage='<span class="error"> World Writable</span>';
		}
	?>
	<tr>
	  <td class="item">./files/temp writable?</td>
	  <td align="left"><?php echo is_writable( $manager->getTempDir() ) ? '<b class="ok">'.$okImg.'</b>'.$okMessage : '<b class="error">'.$failedImg.'</b><span class="warning"> PDF report generation will be disabled</span>';?></td>
	</tr>
	<?php
		$okMessage = "";
		if (!is_writable( $manager->getLanguageDir() )) {
			@chmod( $manager->getLanguageDir(), $chmod );
		}
		$filemode = @fileperms($manager->getLanguageDir());
		if ($filemode & 2) {
			$okMessage='<span class="error"> World Writable</span>';
		}
	?>
	<tr>
	  <td class="item">./locales/en writable?</td>
	  <td align="left"><?php echo is_writable( $manager->getLanguageDir() ) ? '<b class="ok">'.$okImg.'</b>'.$okMessage : '<b class="error">'.$failedImg.'</b><span class="warning"> Translation files cannot be saved. Check /locales and subdirectories for permissions.</span>';?></td>
	</tr>
	<tr>
		<td class="title" colspan="2"><br />Check requirements for optional components</td>
	</tr>
	<tr>
		<td class="item">LDAP Support</td>
		<td align="left"><?php echo function_exists( 'ldap_connect' ) ? '<b class="ok">'.$okImg.'</b><span class="item"> </span>' : '<span class="warning">'.$failedImg.' Not available</span>';?></td>
	</tr>
	<tr>
		<td class="item">Zlib compression Support</td>
		<td align="left">
		<?php
			echo (!extension_loaded('zlib')) ? '<b class="error">'.$failedImg.'</b> <span class="item">Some non-core modules may have restricted operation.</span>' : '<b class="ok">'.$okImg.'</b>';
		?>
		</td>
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
					echo '<b class="error">'.$failedImg.'</b> <span class="warning">'.$sspath.'</span><b class="error"> does not exist or is not writable</b>';
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
		<td class="title" colspan="2"><br/>Recommended PHP Settings</td>
	</tr>
	<tr>
	  <td class="item">Safe Mode = OFF?</td>
	  <td align="left"><?php echo !ini_get('safe_mode') ? '<b class="ok">'.$okImg.'</b>' : '<b class="error">'.$failedImg.'</b><span class="warning"></span>';?></td>
	</tr>
	<tr>
	  <td class="item">Register Globals = OFF?</td>
	  <td align="left"><?php echo !ini_get('register_globals') ? '<b class="ok">'.$okImg.'</b>' : '<b class="error">'.$failedImg.'</b><span class="warning"> There are security risks with this turned ON</span>';?></td>
	</tr>
	<tr>
	  <td class="item">Session Use Cookies = ON?</td>
	  <td align="left"><?php echo ini_get('session.use_cookies') ? '<b class="ok">'.$okImg.'</b>' : '<b class="error">'.$failedImg.'</b><span class="warning"> Try setting to ON if you are experiencing problems logging in</span>';?></td>
	</tr>
	<tr>
	  <td class="item">Session Use Trans Sid = OFF?</td>
	  <td align="left"><?php echo ((!ini_get('session.use_only_cookies') && !ini_get('session.use_trans_sid')) || ini_get('session.use_only_cookies')) ? '<b class="ok">'.$okImg.'</b>' : '<b class="error">'.$failedImg.'</b><span class="warning"> There are security risks with this turned ON</span>';?></td>
	</tr>
	<tr>
	  <td class="title" colspan="2"><br/>Other Recommendations</td>
	</tr>
	<tr>
	  <td class="item">Supported Web Server?</td>
	  <td align="left">
		  <?php
		  $webserver = strtolower($_SERVER['SERVER_SOFTWARE']);
		  if (strpos($webserver, 'apache') !== false || strpos($webserver, 'iis') !== false) {
			echo '<b class="ok">'.$okImg.'</b><span class="item"> ('.$webserver.')</span>';
		  } else {
			echo '<b class="error">'.$failedImg.'</b><span class="warning">';
			echo 'It seems you are using an unsupported web server.  Only Apache and IIS are fully supported by web2Project and using other web servers may result in unexpected problems.';
			echo '</span>';
		  }
		  ?>
	  </td>
	</tr>
	<tr>
	  <td class="item">Standards Compliant Browser?</td>
	  <td align="left">
			<?php echo (stristr($_SERVER['HTTP_USER_AGENT'], 'msie') == false) ? '<b class="ok">'.$okImg.'</b><span class="item"> ('.$_SERVER['HTTP_USER_AGENT'].')</span>' : '<b class="error">'.$failedImg.'</b><span class="warning">
			It seems you are using Internet Explorer.  While the web2Project team works to maintain compatibility with all of the major browsers, some minor differences in CSS/layout rendering or even functionality might affect you.  Please consider using Internet Explorer 8+, Firefox, Chrome, Safari, or Opera as an alternative.
			</span>';?></td>
	</tr>
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	<tr>
	  <td colspan="2" align="center">
		  <form action="<?php echo $baseUrl; ?>/index.php" method="post" name="form" id="form" accept-charset="utf-8">
		  	<?php if ($continue) { ?>
		  		<input type="hidden" name="step" value="dbcreds" />
		  		<input class="button" type="submit" name="next" value="Set System Credentials &raquo;" />
		  	<?php } else { ?>
		  		<input class="button" type="button" value="Installation Stopped" onClick="alert('The above issues must be fixed before continuing.')" />
		  	<?php } ?>
			</form>
		</td>
	</tr>
</table>
