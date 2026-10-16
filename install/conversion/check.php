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
	<h3 class="h6 mt-3 title">Check requirements for optional components</h3>
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
                echo '<b class="error">'.$failedImg.'</b> <span class="warning">'.$sspath.'</span><b class="error"> not existing or not writable</b>';
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
	<h3 class="h6 mt-3 title">Special note: Time zones in web2project vs dotProject</h3>
    <div class="my-2 item">
            To put it simply, dotproject did not support time zones at all.
            Web2Project also did not support time zone until v2.0 in June 2010.
            Therefore, when you convert your dotProject system to web2project,
            you may have some oddities with things like Task & Event times.
            Within Web2Project, there are three different time zone which work
            together for everything to function properly:
            <ul>
                <li>The first is the <strong>time zone in each User's Preferences</strong>. When you enter a time, the system will assume you've entered it relative to your own timezone and transparently adjust it to GMT/UTC for the database.</li>
                <li>The second is the <strong>time zone in the System Configuration</strong>. Since many modules use "date created" or "date updated" fields, the system will use create timestamps on the server and use the Server Timezone to adjust them to GMT/UTC.</li>
                <li>Finally, the third relevant time zone is setting is for the <strong>time zone in the Default User Preferences</strong>. New Users will automatically get this setting, so it may be useful to set the correct value immediately.</li>
            </ul>
        </div>
	<div class="my-2 text-center item"><b class="error">Please confirm that your database has been backed up properly before running this process.  You can never have too many backups.</b></div>
	<div class="my-2 text-center">
		  <form action="<?php echo W2P_BASE_URL; ?>/index.php" method="post" name="form" id="form" accept-charset="utf-8">
		  	<?php if ($continue) { ?>
		  		<input type="hidden" name="step" value="perform" />
		  		<input class="button" type="submit" name="next" value="Perform Conversion &raquo;" />
		  	<?php } else { ?>
		  		<input class="button" type="button" value="Conversion Stopped" onClick="alert('The above issues must be fixed before continuing.')" />
		  	<?php } ?> 
			</form>
		</div>
</div>