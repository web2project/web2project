<?php
	require_once W2P_BASE_DIR . '/lib/adodb/adodb.inc.php';

	class UpgradeManager {
		private $currentVersion = '';
		private $toConvert = false;
		private $toInstall = false;
		private $action = '';
		
		private $configDir = '';
		private $configFile = '';
		private $uploadDir = '';
		private $languageDir = '';
		private $tempDir = '';
		private $configOptions = array();
		
		public function getActionRequired() {

			if ($this->action == '') {
				$this->_prepareConfiguration();
				if (!file_exists($this->configFile)) {
					$this->action = 'install';
				} else {
					require_once $this->configFile;
	
					//TODO: Add check to see if the user has access to system admin
					if (isset($dPconfig)) {
						$this->configOptions = $dPconfig;
						$this->action = 'convert';
					} else {
						$this->configOptions = $w2Pconfig;
						$this->action = 'upgrade';
					}
				}
			}
			return $this->action;
		}

		private function _prepareConfiguration() {
			$this->configDir = W2P_BASE_DIR.'/includes';
			$this->configFile = W2P_BASE_DIR.'/includes/config.php';
			$this->uploadDir = W2P_BASE_DIR.'/files';
			$this->languageDir = W2P_BASE_DIR.'/locales/en';
			$this->tempDir = W2P_BASE_DIR.'/files/temp';
		}
		
		public function getConfigDir() {
			return $this->configDir;
		}
		public function getConfigFile() {
			return $this->configFile;
		}
		public function getUploadDir() {
			return $this->uploadDir;
		}
		public function getLanguageDir() {
			return $this->languageDir;
		}
		public function getTempDir() {
			return $this->tempDir;
		}
		public function getConfigOptions() {
			return $this->configOptions;
		}

		public function upgradeSystem() {
			$allErrors = array();

			$dbConn = $this->_openDBConnection();
			$sql = "SELECT max(db_version) FROM w2pversion";
			$res = $dbConn->Execute($sql);
			if ($res && $res->RecordCount() > 0) {
				$currentVersion = $res->fields['db_version'];
			}
			echo $currentVersion."<br />";

			$migrations = $this->_getMaxVersion();

			if ($currentVersion < count($migrations)) {
				$migration = 1;
				foreach ($migrations as $update) {
					$errorMessages = $this->_applySQLUpdates($update, $dbConn);
					$allErrors = array_merge($allErrors, $errorMessages);
					$sql = "UPDATE w2pversion SET db_version = $migration";
					$migration++;
					$dbConn->Execute($sql);
				}
			}

			return $allErrors;
		}
		private function _getMaxVersion() {
			$migrations = array();

			$path = W2P_BASE_DIR.'/install/sql';
			$dir_handle = @opendir($path) or die("Unable to open $path");

			while ($file = readdir($dir_handle)) {
			   $migration = substr($file, 0, 3);
			   if ((int) $migration > 1) {
			     $migrations[] = $file;
			   }
			}

			return $migrations;
		}



		private function _applySQLUpdates($sqlfile, $dbConn) {
			$sqlfile = W2P_BASE_DIR.'/install/sql/'.$sqlfile;
			if (!file_exists($sqlfile) || filesize($sqlfile) == 0) {
				return array();
			}

			$query = fread(fopen($sqlfile, "r"), filesize($sqlfile));
			$pieces  = $this->_splitSQLUpdates($query);
			$pieceCount = count($pieces);
			$errorMessages = array();

			for ($i=0; $i < $pieceCount; $i++) {
				$pieces[$i] = trim($pieces[$i]);
				if(!empty($pieces[$i]) && $pieces[$i] != "#") {
					/*
					 * While this seems like a good place to use the core classes, it's
					 * really not.  With all of the dependencies, it just gets to be a huge
					 * pain and ends up loading all kinds of unnecessary stuff.
					 */
					if (!$result = $dbConn->Execute($pieces[$i])) {
						$dbErr = true;
						$errorMessages[] = $dbConn->ErrorMsg();
					}
				}
			}

			return $errorMessages;
		}
		private function _splitSQLUpdates($sql) {
			return explode(';', $sql);
		}
		private function _openDBConnection() {
			/*
			 * While this seems like a good place to use the core classes, it's
			 * really not.  With all of the dependencies, it just gets to be a huge
			 * pain and ends up loading all kinds of unnecessary stuff.
			 */

			$db = NewADOConnection($this->configOptions['dbtype']);
			if(!empty($db)) {
			  $dbConnection = $db->Connect($this->configOptions['dbhost'], $this->configOptions['dbuser'], $this->configOptions['dbpass']);
			  if ($dbConnection) {
			    $existing_db = $db->SelectDB($this->configOptions['dbname']);
			  }
			} else { 
				$dbConnection = false;
			}
			return $db;
		}

		public function convertDotProject() {
			$dpVersion = '';

			$allErrors = array();
			$dbConn = $this->_openDBConnection();
			$sql = "SELECT * FROM dpversion ORDER BY db_version DESC";
			$res = $dbConn->Execute($sql);
			if ($res && $res->RecordCount() > 0) {
				$dpVersion = $res->fields['code_version'];
			}

			switch ($dpVersion) {
				case '2.0':
					$errorMessages = $this->_applySQLUpdates('dp20_to_201.sql', $dbConn);
					$allErrors = array_merge($allErrors, $errorMessages);
				case '2.0.1':
					$errorMessages = $this->_applySQLUpdates('dp201_to_202.sql', $dbConn);
					$allErrors = array_merge($allErrors, $errorMessages);
				case '2.0.2':
				case '2.0.3':
				case '2.0.4':
				case '2.1-rc1':
					$errorMessages = $this->_applySQLUpdates('dp21rc1_to_21rc2.sql', $dbConn);
					$allErrors = array_merge($allErrors, $errorMessages);
				case '2.1-rc2':
				case '2.1':
				case '2.1.1':
				case '2.1.2':
					//$errorMessages = $this->_applySQLUpdates('dp_to_w2p1.sql', $dbConn);
					//$allErrors = array_merge($allErrors, $errorMessages);

					$recordsUpdated = $this->_scrubDotProjectData($dbConn);

					//$errorMessages = $this->_applySQLUpdates('dp_to_w2p2.sql', $dbConn);
					//$allErrors = array_merge($allErrors, $errorMessages);

					//$errorMessages = $this->upgradeSystem($dbConn);
					//$allErrors = array_merge($allErrors, $errorMessages);

					break;
				default:
					$allErrors[] = "Unfortunately, we can't determine which version of dotProject you're using.  To be safe, we're not going to do anything.";
					$allErrors[] = "If you are using dotProject 1.x, please use their methods to upgrade to dotProject v2.x before you go any further.";
			}
			
			return $allErrors;
		}
		private function _scrubDotProjectData($dbConn) {			
			/*
			 * While this seems like a good place to use the core classes, it's
			 * really not.  With all of the dependencies, it just gets to be a huge
			 * pain and ends up loading all kinds of unnecessary stuff.
			 */
			$recordsUpdated = 0;

			$sql = "SELECT * FROM sysvals WHERE sysval_value like '%|%' ORDER BY sysval_id ASC";
			$res = $dbConn->Execute($sql);
			if ($res->RecordCount() > 0) {
				while (!$res->EOF) {
					$fields = $res->fields;
					
					$sysvalId = $fields['sysval_id'];
					$sysvalKeyId = $fields['sysval_key_id'];
					$sysvalTitle = $fields['sysval_title'];
					$values = explode("\n", $fields['sysval_value']); 
					foreach ($values as $syskey) {
						$sysvalValId = substr($syskey, 0, strpos($syskey, '|'));
						$sysvalValue = substr(trim(' '.$syskey.' '), strpos($syskey, '|') + 1);
						$sql = "INSERT INTO sysvals (sysval_key_id, sysval_title, sysval_value, sysval_value_id) " .
								"VALUES ($sysvalKeyId, '$sysvalTitle', '$sysvalValue', $sysvalValId)";
						$dbConn->Execute($sql);
					}
					$recordsUpdated++;
					$sql = "DELETE FROM sysvals WHERE sysval_id = $sysvalId";
					$dbConn->Execute($sql);
					$res->MoveNext();
				}
			}
			return $recordsUpdated;
		}
	}
?>