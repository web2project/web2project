<?php
	if (!defined('W2P_BASE_DIR')) {
		die('You should not access this file directly.');
	}

	$failedImg = '<img src="../style/web2project/images/log-error.gif" width="16" height="16" align="middle" alt="Failed"/>';
	$okImg = '<img src="../style/web2project/images/log-notice.gif" width="16" height="16" align="middle" alt="OK"/>';

	$continue = true;
?>
<div class="std update">
	<h3 class="h6 mt-3 title">Step 1: Check System Settings</h3>
	<div class="my-2">
			There is an initial Check for (minimal) requirements appended below for
			troubleshooting. At minimum, a database and corresponding database
			connection must be available in addition to PHP5, the GD libraries
			installed for Gantt charts, and file_uploads should be allowed.  In
			addition ../includes/config.php should be writable for the webserver.
		</div>
	<h3 class="h6 mt-3 title">Confirm Requirements</h3>
	<div class="row py-1 border-bottom">
	    <div class="col-sm-5 item">PHP Version &gt;= <?= MIN_PHP_VERSION; ?></div>
	    <div class="col-sm-7">
			<?php
			if (version_compare(PHP_VERSION, MIN_PHP_VERSION, '<')) {
                echo '<b class="error">'.$failedImg.' ('.PHP_VERSION.'): web2Project requires PHP '.MIN_PHP_VERSION.'+. Please upgrade!</b>';
				$continue = false;
			} else {
				echo '<b class="ok">'.$okImg.'</b> <span class="item">('.PHP_VERSION.')</span>';
			}
			?>
		</div>
	</div>
	<div class="row py-1 border-bottom">
	    <div class="col-sm-5 item">GD Support (for GANTT Charts)</div>
	    <div class="col-sm-7">
		<?php
			if (!extension_loaded('gd')) {
				echo '<b class="error">'.$failedImg.'</b> <span class="item">GANTT Chart functionality may not work correctly.</span>';
				$continue = false;
			} else {
				echo '<b class="ok">'.$okImg.'</b>';
			}
		?>
		</div>
	</div>
	<div class="row py-1 border-bottom">
	    <div class="col-sm-5 item">File Uploads</div>
	    <div class="col-sm-7">
		<?php
			if (!ini_get('file_uploads') && is_writable(W2P_BASE_DIR.'/files')) {
        echo '<b class="error">'.$failedImg.'</b> <span class="warning">Upload functionality will not be available, please make the ./files directory writable.</span>';
				$continue = false;
			} else {
				echo '<b class="ok">'.$okImg.'</b> <span class="item">(Max File Upload Size: '. $manager->getMaxFileUpload() .')</span>';
			}
		?>
		</div>
	</div>
	<h3 class="h6 mt-3 title">Database Connectors</h3>
	<div class="my-2 item">
			<p>The next tests check for database support compiled with php. We use
			the ADODB database abstraction layer which comes with drivers for many
			databases. Consult the ADODB documentation for details.  For the moment
			only MySQL is fully supported, so you need to make sure it is available.</p>
		</div>
	<div class="row py-1 border-bottom">
	    <div class="col-sm-5 item">MySQL Support</div>
	    <div class="col-sm-7">
		<?php
			if (!function_exists('mysqli_connect')) {
				echo '<b class="error"><span class="warning">'.$failedImg.' Not available</span>';
				$continue = false;
			} else {
				echo '<b class="ok">'.$okImg.'</b>';
			}
		?>
		</div>
	</div>
	<h3 class="h6 mt-3 title">Check for Directory and File Permissions</h3>
	<div class="my-2 item">If the message 'World Writable' appears after
		a file/directory, then Permissions for this File have been set to allow
		all users to write to this file/directory.  Consider changing this to a
		more restrictive setting to improve security. You will need to do this manually.</div>
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
	<div class="row py-1 border-bottom">
	    <div class="col-sm-5 item">./includes/config.php writable?</div>
	    <div class="col-sm-7"><?php echo ( is_writable( $manager->getConfigFile() ) || is_writable( $manager->getConfigDir ))  ? '<b class="ok">'.$okImg.'</b>'.$okMessage : '<b class="error">'.$failedImg.'</b><span class="warning"> Configuration process can still be continued. Configuration file will be displayed at the end, just copy & paste this and upload.</span>';?></div>
	</div>
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
	<div class="row py-1 border-bottom">
	    <div class="col-sm-5 item">./files writable?</div>
	    <div class="col-sm-7"><?php echo is_writable( $manager->getUploadDir() ) ? '<b class="ok">'.$okImg.'</b>'.$okMessage : '<b class="error">'.$failedImg.'</b><span class="warning"> File upload functionality will be disabled</span>';?></div>
	</div>
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
	<div class="row py-1 border-bottom">
	    <div class="col-sm-5 item">./locales/en writable?</div>
	    <div class="col-sm-7"><?php echo is_writable( $manager->getLanguageDir() ) ? '<b class="ok">'.$okImg.'</b>'.$okMessage : '<b class="error">'.$failedImg.'</b><span class="warning"> Translation files cannot be saved. Check /locales and subdirectories for permissions.</span>';?></div>
	</div>
	<h3 class="h6 mt-3 title">Check requirements for optional components</h3>
	<div class="row py-1 border-bottom">
	    <div class="col-sm-5 item">LDAP Support</div>
	    <div class="col-sm-7"><?php echo function_exists( 'ldap_connect' ) ? '<b class="ok">'.$okImg.'</b><span class="item"> </span>' : '<span class="warning">'.$failedImg.' Not available</span>';?></div>
	</div>
	<div class="row py-1 border-bottom">
	    <div class="col-sm-5 item">Zlib compression Support</div>
	    <div class="col-sm-7">
		<?php
			echo (!extension_loaded('zlib')) ? '<b class="error">'.$failedImg.'</b> <span class="item">Some non-core modules may have restricted operation.</span>' : '<b class="ok">'.$okImg.'</b>';
		?>
		</div>
	</div>
	<div class="row py-1 border-bottom">
	    <div class="col-sm-5 item">Session Save Path writable?</div>
	    <div class="col-sm-7">
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
		</div>
	</div>
  <div class="row py-1 border-bottom">
      <div class="col-sm-5 item">Server API</div>
      <div class="col-sm-7">
    <?php
      if (strpos(strtolower(php_sapi_name()), 'cgi') !== false) {
        echo '<b class="error">'.$failedImg.' CGI mode is likely to have problems</b>';
      } else {
        echo '<b class="ok">'.$okImg.'</b> <span class="item">('.php_sapi_name().')</span>';
      }
    ?>
    </div>
  </div>
	<h3 class="h6 mt-3 title"><br/>Recommended PHP Settings</h3>
	<div class="row py-1 border-bottom">
	    <div class="col-sm-5 item">Safe Mode = OFF?</div>
	    <div class="col-sm-7"><?php echo !ini_get('safe_mode') ? '<b class="ok">'.$okImg.'</b>' : '<b class="error">'.$failedImg.'</b><span class="warning"></span>';?></div>
	</div>
	<div class="row py-1 border-bottom">
	    <div class="col-sm-5 item">Register Globals = OFF?</div>
	    <div class="col-sm-7"><?php echo !ini_get('register_globals') ? '<b class="ok">'.$okImg.'</b>' : '<b class="error">'.$failedImg.'</b><span class="warning"> There are security risks with this turned ON</span>';?></div>
	</div>
	<div class="row py-1 border-bottom">
	    <div class="col-sm-5 item">Session Use Cookies = ON?</div>
	    <div class="col-sm-7"><?php echo ini_get('session.use_cookies') ? '<b class="ok">'.$okImg.'</b>' : '<b class="error">'.$failedImg.'</b><span class="warning"> Try setting to ON if you are experiencing problems logging in</span>';?></div>
	</div>
	<div class="row py-1 border-bottom">
	    <div class="col-sm-5 item">Session Use Trans Sid = OFF?</div>
	    <div class="col-sm-7"><?php echo ((!ini_get('session.use_only_cookies') && !ini_get('session.use_trans_sid')) || ini_get('session.use_only_cookies')) ? '<b class="ok">'.$okImg.'</b>' : '<b class="error">'.$failedImg.'</b><span class="warning"> There are security risks with this turned ON</span>';?></div>
	</div>
	<h3 class="h6 mt-3 title"><br/>Other Recommendations</h3>
	<div class="row py-1 border-bottom">
	    <div class="col-sm-5 item">Supported Web Server?</div>
	    <div class="col-sm-7">
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
	  </div>
	</div>
	<div class="row py-1 border-bottom">
	    <div class="col-sm-5 item">Standards Compliant Browser?</div>
	    <div class="col-sm-7">
			<?php echo (stristr($_SERVER['HTTP_USER_AGENT'], 'msie') == false) ? '<b class="ok">'.$okImg.'</b><span class="item"> ('.$_SERVER['HTTP_USER_AGENT'].')</span>' : '<b class="error">'.$failedImg.'</b><span class="warning">
			It seems you are using Internet Explorer.  While the web2Project team works to maintain compatibility with all of the major browsers, some minor differences in CSS/layout rendering or even functionality might affect you.  Please consider using Internet Explorer 8+, Firefox, Chrome, Safari, or Opera as an alternative.
			</span>';?></div>
	</div>
	<div class="my-2 text-center">
		  <form action="<?php echo W2P_BASE_URL; ?>/index.php" method="post" name="form" id="form" accept-charset="utf-8">
		  	<?php if ($continue) { ?>
		  		<input type="hidden" name="step" value="dbcreds" />
		  		<input class="button" type="submit" name="next" value="Set System Credentials &raquo;" />
		  	<?php } else { ?>
		  		<input class="button" type="button" value="Installation Stopped" onClick="alert('The above issues must be fixed before continuing.')" />
		  	<?php } ?>
			</form>
		</div>
</div>
