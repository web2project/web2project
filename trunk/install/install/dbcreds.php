<?php
	if (!defined('W2P_BASE_DIR')) {
		die('You should not access this file directly.');
	}
	
?>
<form action="<?php echo $baseUrl; ?>/index.php" method="post" name="form" id="form">
	<input type="hidden" name="step" value="perform" />
	<table cellspacing="0" cellpadding="3" border="0" class="tbl" width="90%" align="center" style="margin-top: 20px;">
		<tr>
			<td class="title" colspan="2">Step 2: Database Settings</td>
		</tr>
		<tr>
			<td colspan="2">
				This installer does not create your database, it only loads it. 
				Therefore, you have to create your own database and database user 
				before continuing.  The database user must have permission to create 
				tables.  Once you have those user credentials, please use them here. 
			</td>
		</tr>
		<tr>
			<td class="item" width="50%">Database Server Type</td>
			<td align="left">
				<select name="dbtype" size="1" style="width:200px;" class="text" disabled="true">
					<option value="mysql" selected="selected">MySQL</option>
				</select>
			</td>
		</tr>
		<tr>
			<td class="item">Database Host Name</td>
			<td align="left">
				<input class="button" type="text" name="dbhost" value="" title="The name of the host the database server is installed on" />
			</td>
		</tr>
		<tr>
			<td class="item">Database Name</td>
			<td align="left"><input class="button" type="text" name="dbname" value="" title="The name of the database web2project will use and/or install" /></td>
		</tr>
		<tr>
			<td class="item">Database User Name</td>
			<td align="left"><input class="button" type="text" name="dbuser" value="" title="The database user that web2project uses for database connection" /></td>
		</tr>
		<tr>
			<td class="item">Database User Password</td>
			<td align="left"><input class="button" type="password" name="dbpass" value="" title="The password for the above user." /></td>
		</tr>
		<tr>
			<td class="item">Use Persistent Connection?</td>
			<td align="left"><input type="checkbox" name="dbpersist" value="1" title="Use a persistent Connection to your Database Server." /></td>
		</tr>
		<tr>
			<td class="item">Specify a password for the Admin account<br />
			<span style="font-style: italic; ">After installation is complete, you will log in using the username "admin" (no quotes) and this password.  If you do not provide one, "passwd" (no quotes) will be used.</span>
			</td>
			<td align="left">
				<input class="button" type="password" name="adminpass" value="" title="The password for the admin user." />
			</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td class="item" align="left" colspan="2" style="text-align: center;">
				<b class="error">
					If you haven't created your database yet, your installation will 
					fail and the next screen will have lots of errors.<br />Save yourself 
					(and us!) some time and make sure your database exists.  Thanks! 
				</b>
			</td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td align="center" colspan="2">						
				<input class="button" type="submit" name="do_db" value="install db only &raquo;" title="Try to set up the database with the given information." />
				&nbsp;<input class="button" type="submit" name="do_cfg" value="write config file only &raquo;" title="Write a config file with the details only." /><br /><br />
				Recommended: &nbsp;<input class="button" type="submit" name="do_db_cfg" value="install db &amp; write cfg &raquo;" title="Write config file and setup the database with the given information." />
			</td>
		</tr>
	</table>
</form>