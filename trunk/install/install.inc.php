<?php
	// $Id: install.inc.php,v 1.2.2.2 2007/02/26 21:04:48 merlinyoda Exp $
	
	// Provide fake interface classes and installation functions
	// so that most db shortcuts will work without, for example, an AppUI instance.
	
	// Defines required by setMsg, these are different to those used by the real CAppUI.
	
	define( 'UI_MSG_OK', '');
	define('UI_MSG_ALERT', 'Warning: ');
	define('UI_MSG_WARNING', 'Warning: ');
	define('UI_MSG_ERROR', 'ERROR: ');
	#
	# function to output a message
	# currently just outputs it expecting there to be a pre block.
	# but could be changed to format it better - and only needs to be done here.
	# The flush is called so that the user gets progress as it occurs. It depends
	# upon the webserver/browser combination though.
	#
	
	class InstallerUI {
	
		var $user_id = 0;
	
		function setMsg($msg, $msgno = '', $append=false)
		{
			return w2pmsg($msgno . $msg);
		}
	}

	function w2pmsg($msg)
	{
	 echo $msg . "\n";
	 flush();
	}

	#
	# function to return a default value if a variable is not set
	#
	
	function InstallDefVal($var, $def) {
		return isset($var) ? $var : $def;
	}

	/*
	* Utility function to split given SQL-Code
	* @param $sql string SQL-Code
	* @param $last_update string last update that has been installed
	*/
	function InstallSplitSql($sql, $last_update) {
		global $lastDBUpdate;

		$buffer = array();
		$ret = array();

		$sql = trim($sql);

		$matched =  preg_match_all('/\n#\s*(\d{8})\b/', $sql, $matches);
		if ($matched) {
			// Used for updating from previous versions, even if the update
			// is not correctly set.
			$len = count($matches[0]);
			$lastDBUpdate = $matches[1][$len-1];
		}
	 
		if ($last_update && $last_update != '00000000') {
			// Find the first occurrance of an update that is
			// greater than the last_update number.
			w2pmsg("Checking for previous updates");
			if ($matched) {
				for ($i = 0; $i < $len; $i++) {
					if ((int)$last_update < (int)$matches[1][$i]) {
						// Remove the SQL up to the point found
						$match = '/^.*' . trim($matches[0][$i]) . '/Us';
						$sql = preg_replace($match, "", $sql);
						break;
					}
				}
				// If we run out of indicators, we need to debunk, otherwise we will reinstall
				if ($i == $len) {
					return $ret;
				}
			}
		}
		$sql = ereg_replace("\n#[^\n]*\n", "\n", $sql);

		return explode(';', $sql);
	}

	function InstallLoadSQL($sqlfile, $last_update = null, $adminpass = '')
	{
	 global $dbErr, $dbMsg, $db;
	
	 // Don't complain about missing files.
		if (! file_exists($sqlfile)) {
			return;
		}
		$mqr = @get_magic_quotes_runtime();
		@set_magic_quotes_runtime(0);
	
		$pieces = array();
		if ($sqlfile) {
			$query = fread(fopen($sqlfile, "r"), filesize($sqlfile));
			if ($adminpass != '') {
				$query = str_replace('passwd', $adminpass, $query);
			}
			$pieces  = InstallSplitSql($query, $last_update);
		}
		@set_magic_quotes_runtime($mqr);
		$errors = 0;
		$piece_count = count($pieces);
	
		for ($i=0; $i<$piece_count; $i++) {
			$pieces[$i] = trim($pieces[$i]);
			if(!empty($pieces[$i]) && $pieces[$i] != "#") {
				if (!$result = $db->Execute($pieces[$i])) {
					$errors++;
					$dbErr = true;
					$dbMsg .= $db->ErrorMsg().'<br>';
				}
			}
		}
		w2pmsg("There were $errors errors in $piece_count SQL statements");
	}

	function InstallGetVersion($mode, $db) {
		$result = array(
			'last_db_update' => '',
			'last_code_update' => '',
			'code_version' => '1.0.2',
			'db_version' => '1'
		);
		if ($mode == 'upgrade') {
			$res = $db->Execute('SELECT * FROM dpversion LIMIT 1');
			if ($res && $res->RecordCount() > 0) {
				$row = $res->FetchRow();
				$result['last_db_update'] = str_replace('-', '', $row['last_db_update']);
				$result['last_code_update'] = str_replace('-', '', $row['last_code_update']);
				$result['code_version'] = $row['code_version'] ? $row['code_version'] : '1.0.2';
				$result['db_version'] = $row['db_version'] ? $row['db_version'] : '1';
			}
		}
		return $result;
	}
?>