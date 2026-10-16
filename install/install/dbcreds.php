<?php
	if (!defined('W2P_BASE_DIR')) {
		die('You should not access this file directly.');
	}
	
$timezones = w2PgetTimezonesForInstall();
        
?>
<form action="<?php echo W2P_BASE_URL; ?>/index.php" method="post" name="form" id="form" accept-charset="utf-8">
	<input type="hidden" name="step" value="perform" />
    <div class="std update">
		<h3 class="h6 mt-3 title">Step 2: Database Settings</h3>
		<div class="my-2">
				This installer does not create your database, it only loads it. 
				Therefore, you have to create your own database and database user 
				before continuing.  The database user must have permission to create 
				tables.  Once you have those user credentials, please use them here. 
			</div>
		<div class="row py-1 border-bottom">
		    <div class="col-sm-5 item">Database Server Type</div>
		    <div class="col-sm-7">
				<select name="dbtype" size="1" style="width:200px;" class="text" disabled="true">
					<option value="mysqli" selected="selected">MySQL</option>
				</select>
			</div>
		</div>
		<div class="row py-1 border-bottom">
		    <div class="col-sm-5 item">Database Host Name</div>
		    <div class="col-sm-7">
				<input class="text" type="text" name="dbhost" value="" title="The name of the host the database server is installed on" />
			</div>
		</div>
		<div class="row py-1 border-bottom">
		    <div class="col-sm-5 item">Database Name</div>
		    <div class="col-sm-7"><input class="text" type="text" name="dbname" value="" title="The name of the database web2project will use and/or install" /></div>
		</div>
		<div class="row py-1 border-bottom">
		    <div class="col-sm-5 item">Database User Name</div>
		    <div class="col-sm-7"><input class="text" type="text" name="dbuser" value="" title="The database user that web2project uses for database connection" /></div>
		</div>
		<div class="row py-1 border-bottom">
		    <div class="col-sm-5 item">Database User Password</div>
		    <div class="col-sm-7"><input class="text" type="password" name="dbpass" value="" title="The password for the above user." /></div>
		</div>
		<div class="row py-1 border-bottom">
		    <div class="col-sm-5 item">Use Persistent Connection?</div>
		    <div class="col-sm-7"><input type="checkbox" name="dbpersist" value="1" title="Use a persistent Connection to your Database Server." /></div>
		</div>
		<div class="row py-1 border-bottom">
		    <div class="col-sm-5 item">Specify a password for the Admin account<br />
			<span style="font-style: italic; ">After installation is complete, you will log in using the username "admin" (no quotes) and this password.  If you do not provide one, "passwd" (no quotes) will be used.</span>
			</div>
		    <div class="col-sm-7">
				<input class="text" type="password" name="adminpass" value="" title="The password for the admin user." />
			</div>
		</div>
                <div class="row py-1 border-bottom">
                    <div class="col-sm-5 item">Specify a default server timezone</div>
                    <div class="col-sm-7">
                        <?php echo arraySelect($timezones, 'system_timezone', 'class="text"', 'America/New_York'); ?>
                    </div>
                </div>
                <div class="row py-1 border-bottom">
                    <div class="col-sm-5 item">Specify a default timezone for users</div>
                    <div class="col-sm-7">
                        <?php echo arraySelect($timezones, 'user_timezone', 'class="text"', 'America/New_York'); ?>
                    </div>
                </div>
		<div class="my-2 text-center item">
				<b class="error">
					If you haven't created your database yet, your installation will 
					fail and the next screen will have lots of errors.<br />Save yourself 
					(and us!) some time and make sure your database exists.  Thanks! 
				</b>
			</div>
		<div class="my-2 text-center">						
				<input class="button" type="submit" name="do_db" value="install db only &raquo;" title="Try to set up the database with the given information." />
				&nbsp;<input class="button" type="submit" name="do_cfg" value="write config file only &raquo;" title="Write a config file with the details only." /><br /><br />
				Recommended: &nbsp;<input class="button" type="submit" name="do_db_cfg" value="install db &amp; write cfg &raquo;" title="Write config file and setup the database with the given information." />
			</div>
	</div>
</form>
