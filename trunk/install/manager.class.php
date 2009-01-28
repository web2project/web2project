<?php
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
		
		public function convertDotProject() {
			//TODO: get dP version
			//TODO: apply the changes for each step
			switch ($dpVersion) {
				case '1.0':
					echo $dpVersion;
					break;
				default:
					echo 'apply some changes';
			}
		}
	}
?>