<?php
if (!defined('W2P_BASE_DIR')) {
	die('This file should not be called directly.');
}

// Message No Constants
define('UI_MSG_OK', 1);
define('UI_MSG_ALERT', 2);
define('UI_MSG_WARNING', 3);
define('UI_MSG_ERROR', 4);

// global variable holding the translation array
$GLOBALS['translate'] = array();

define('UI_CASE_MASK', 0x0F);
define('UI_CASE_UPPER', 1);
define('UI_CASE_LOWER', 2);
define('UI_CASE_UPPERFIRST', 3);

define('UI_OUTPUT_MASK', 0xF0);
define('UI_OUTPUT_HTML', 0);
define('UI_OUTPUT_JS', 0x10);
define('UI_OUTPUT_RAW', 0x20);

/**
 * The Application User Interface Class.
 *
 * @author Andrew Eddie <eddieajau@users.sourceforge.net>
 * @version $Revision$
 */

class w2p_Core_CAppUI {
	/**
 	@var array generic array for holding the state of anything */
	public $state = null;
	/**
 	@var int */
	public $user_id = null;
	/**
 	@var string */
	public $user_first_name = null;
	/**
 	@var string */
	public $user_last_name = null;
	/**
 	@var string */
	public $user_company = null;
	/**
 	@var int */
	public $user_department = null;
	/**
 	@var string */
	public $user_email = null;
	/**
 	@var int */
	public $user_type = null;
	/**
 	@var array */
	public $user_prefs = null;
	/**
 	@var int Unix time stamp */
	public $day_selected = null;

	// localisation
	/**
 	@var string */
	public $user_locale = null;
	/**
 	@var string */
	public $user_lang = null;
	/**
 	@var string */
	public $base_locale = 'en'; // do not change - the base 'keys' will always be in english

	/**
 	@var string Message string*/
	public $msg = '';
	/**
 	@var string */
	public $msgNo = '';
	/**
 	@var string Default page for a redirect call*/
	public $defaultRedirect = '';

	/**
 	@var array Configuration variable array*/
	public $cfg = null;

	/**
 	@var integer Version major */
	public $version_major = null;

	/**
 	@var integer Version minor */
	public $version_minor = null;

	/**
 	@var integer Version patch level */
	public $version_patch = null;

	/**
 	@var string Version string */
	public $version_string = null;

	/**
 	@var integer for register log ID */
	public $last_insert_id = null;

	/**
 	@var string */
	public $user_style = null;

	/**
 	@var integer */
	public $user_is_admin = null;

    public $long_date_format = null;

	protected $objStore = null;

	/**
	 * Holds an array of additional javascript files to be loaded
	 * in the footer of the page
	 *
	 * @var array
	 */
	protected $footerJavascriptFiles = array();

	/**

	 * CAppUI Constructor
	 */
	public function __construct() {
		$this->state = array();

		$this->user_id = -1;
		$this->user_first_name = '';
		$this->user_last_name = '';
		$this->user_company = 0;
		$this->user_department = 0;
		$this->user_type = 0;
		$this->user_is_admin = 0;

		// cfg['locale_warn'] is the only cfgVariable stored in session data (for security reasons)
		// this guarants the functionality of this->setWarning
		$this->cfg['locale_warn'] = w2PgetConfig('locale_warn');

		$this->project_id = 0;

		$this->defaultRedirect = '';
		// set up the default preferences
		$this->setUserLocale($this->base_locale);
		$this->user_prefs = array();
	}
	/**
	 * Used to load a php class file from the system classes directory
	 * @param string $name The class root file name (excluding .class.php)
	 * @return string The path to the include file
	 */
    public function getSystemClass($name = null) {
        trigger_error("CAppUI->getSystemClass() has been deprecated in v2.0 and will be removed in v3.0", E_USER_NOTICE );

		if ($name) {
			return W2P_BASE_DIR . '/classes/' . $name . '.class.php';
		}
	}

	/**
	 * Used to load a php class file from the lib directory
	 *
	 * @param string $name The class root file name (excluding .class.php)
	 * @return string The path to the include file
	 */
	public function getLibraryClass($name = null) {
		if ($name) {
			return W2P_BASE_DIR . '/lib/' . $name . '.php';
		}
	}

	/**
	 * Used to load a php class file from the module directory
	 * @param string $name The class root file name (excluding .class.php)
	 * @return string The path to the include file
	 */
	public function getModuleClass($name = null) {
		if ($name) {
			return W2P_BASE_DIR . '/modules/' . $name . '/' . $name . '.class.php';
		}
	}

    /**
    * Used to load a php class file from the module directory
    * @param string $name The class root file name (excluding .ajax.php)
    * @return string The path to the include file
    */
	public function getModuleAjax( $name=null ) {
		if ($name) {
			return W2P_BASE_DIR . '/modules/' . $name . '/' . $name . '.ajax.php';
		}
	}

	/**
	 * Determines the version.
	 * @return String value indicating the current web2project version
	 */
	public function getVersion() {
		global $w2Pconfig;
		if (!isset($this->version_major)) {
			include W2P_BASE_DIR . '/includes/version.php';
			$this->version_major = $w2p_version_major;
			$this->version_minor = $w2p_version_minor;
			$this->version_patch = $w2p_version_patch;
			$this->version_string = $this->version_major . '.' . $this->version_minor;
			if (isset($this->version_patch)) {
				$this->version_string .= '.' . $this->version_patch;
			}
			if (isset($w2p_version_prepatch)) {
				$this->version_string .= '-' . $w2p_version_prepatch;
			}
		}
		return $this->version_string;
	}

    /**
    *
    */
    public function getTZAwareTime() {
        $df = $this->getPref('FULLDATEFORMAT');

        /*
        * This try/catch is a nasty little hack to cover the issue where some
        *   timezones were set up incorrectly in the v1.3 release (caseydk's
        *   fault!). They've since been corrected for 2.0 but people upgrading
        *   will have a problem here without this.
        *                                            ~ caseydk June 2010
        *
        * TODO: This should be killed off in v2.2 or v2.3 or so..
        */
        try {
            $userTimezone = $this->getPref('TIMEZONE');
            $userTZ = new DateTimeZone($userTimezone);
        } catch (Exception $e) {
            global $AppUI;

            $timezoneOffset = $this->getPref('TIMEZONE');
			$timezoneOffset = ('' == $timezoneOffset) ? 'America/New_York' : $timezoneOffset;

            $q = new w2p_Database_Query();
            $q->addTable('sysvals');
            $q->addQuery('sysval_value');
            $q->addWhere("sysval_value_id = '$timezoneOffset'");
            $userTimezone = $q->loadResult();
            $userTimezone = (strlen($userTimezone) == 0) ? 'Europe/London' : $userTimezone;

            $userTZ = new DateTimeZone($userTimezone);
            echo '<span class="error"><strong>';
            echo '<a href="./index.php?m=system">'.$AppUI->_('Your system probably needs to be upgraded.').'</a>';
            echo '<br />';
            echo '<a href="./index.php?m=system&amp;a=addeditpref&amp;user_id='.$AppUI->user_id.'">'.$AppUI->_('Your user-defined timezone must be set immediately.').'</a>';
            echo '</strong></span><br />';
            echo '<span class="error"><strong>Your system must be upgraded immediately.</strong></span><br />';
        }

        $ts = new DateTime();
        $ts->setTimezone($userTZ);

        return $ts->format($df);
    }

    /**
    *
    */
    public function convertToSystemTZ($datetime = '', $format = 'Y-m-d H:i:s') {
        $userTZ = $this->getPref('TIMEZONE');
        $userTimezone = new DateTimeZone($userTZ);

        $systemTimezone = new DateTimeZone('Europe/London');

        $ts = new DateTime($datetime, $userTimezone);
        $ts->setTimezone($systemTimezone);

        return $ts->format($format);
    }

    /**
    *
    */
    public function formatTZAwareTime($datetime = '', $format = '') {
        $userTimezone = $this->getPref('TIMEZONE');
        $userTZ = new DateTimeZone($userTimezone);
        $systemTZ = new DateTimeZone('Europe/London');
        $ts = new DateTime($datetime, $systemTZ);
        $ts->setTimezone($userTZ);

        if ('' == $format) {
            $df = $this->getPref('FULLDATEFORMAT');
        } else {
            $df = $format;
            $ts  = new w2p_Utilities_Date($ts->format('Y-m-d H:i:s'));
        }

        return $ts->format($df);
    }

	/**
	 * Checks that the current user preferred style is valid/exists.
	 */
	public function checkStyle() {
		// check if default user's uistyle is installed
		$uistyle = $this->getPref('UISTYLE');

		if ($uistyle && !is_dir(W2P_BASE_DIR . '/style/' . $uistyle)) {
			// fall back to host_style if user style is not installed
			$this->setPref('UISTYLE', w2PgetConfig('host_style'));
		}
	}

	/**
	 * Utility function to read the 'directories' under 'path'
	 *
	 * This function is used to read the modules or locales installed on the file system.
	 * @param string The path to read.
	 * @return array A named array of the directories (the key and value are identical).
	 */
	public function readDirs($path) {
		$dirs = array();

		if (is_dir(W2P_BASE_DIR . '/' . $path)) {
			$d = dir(W2P_BASE_DIR . '/' . $path);
			while (false !== ($name = $d->read())) {
				if (is_dir(W2P_BASE_DIR . '/' . $path . '/' . $name) && $name != '.' && $name != '..' && $name != 'CVS' && $name != '.svn') {
					$dirs[$name] = $name;
				}
			}
			$d->close();
		}
		return $dirs;
	}

	/**
	 * Utility function to read the 'files' under 'path'
	 * @param string The path to read.
	 * @param string A regular expression to filter by.
	 * @return array A named array of the files (the key and value are identical).
	 */
	public function readFiles($path, $filter = '.') {
		$files = array();

		if (is_dir($path) && ($handle = opendir($path))) {
			while (false !== ($file = readdir($handle))) {
				if ($file != '.' && $file != '..' && preg_match('/' . $filter . '/', $file)) {
					$files[$file] = $file;
				}
			}
			closedir($handle);
		}
		return $files;
	}

	/**
	 * Utility function to check whether a file name is 'safe'
	 *
	 * Prevents from access to relative directories (eg ../../dealyfile.php);
	 * @param string The file name.
	 * @return array A named array of the files (the key and value are identical).
	 */
	public function checkFileName($file) {
		global $AppUI;

		// define bad characters and their replacement
		$bad_chars = ";/\\";
		$bad_replace = '....'; // Needs the same number of chars as $bad_chars

		// check whether the filename contained bad characters
		if (strpos(strtr($file, $bad_chars, $bad_replace), '.') !== false) {
			$AppUI->redirect('m=public&a=access_denied');
		} else {
			return $file;
		}

	}

	/**
	 * Utility function to make a file name 'safe'
	 *
	 * Strips out mallicious insertion of relative directories (eg ../../dealyfile.php);
	 * @param string The file name.
	 * @return array A named array of the files (the key and value are identical).
	 */
	public function makeFileNameSafe($file) {
		$file = str_replace('../', '', $file);
		$file = str_replace('..\\', '', $file);
		return $file;
	}

	/**
	 * Sets the user locale.
	 *
	 * Looks in the user preferences first.  If this value has not been set by the user it uses the system default set in config.php.
	 * @param string Locale abbreviation corresponding to the sub-directory name in the locales directory (usually the abbreviated language code).
	 */
	public function setUserLocale($loc = '', $set = true) {
		global $locale_char_set;

		$LANGUAGES = $this->loadLanguages();

		if (!$loc) {
			$loc = $this->user_prefs['LOCALE'] ? $this->user_prefs['LOCALE'] : w2PgetConfig('host_locale');
		}

		if (isset($LANGUAGES[$loc])) {
			$lang = $LANGUAGES[$loc];
        } else {
			// Need to try and find the language the user is using, find the first one
			// that has this as the language part
			if (strlen($loc) > 2) {
				list($l, $c) = explode('_', $loc);
				$loc = $this->findLanguage($l, $c);
			} else {
				$loc = $this->findLanguage($loc);
			}
			$lang = $LANGUAGES[$loc];
		}
		list($base_locale, $english_string, $native_string, $default_language, $lcs) = $lang;
		if (!isset($lcs)) {
			$lcs = (isset($locale_char_set)) ? $locale_char_set : 'utf-8';
        }
		$user_lang = array($loc . '.' . $lcs, $default_language, $loc, $base_locale);

		if ($set) {
			$this->user_locale = $base_locale;
			$this->user_lang = $user_lang;
			$locale_char_set = $lcs;
		} else {
			return $user_lang;
		}
	}

	public function findLanguage($language, $country = false) {
		$LANGUAGES = $this->loadLanguages();
		$language = strtolower($language);
		if ($country) {
			$country = strtoupper($country);
			// Try constructing the code again
			$code = $language . '_' . $country;
			if (isset($LANGUAGES[$code])) {
				return $code;
			}
		}

		// Just use the country code and try and find it in the
		// languages list.
		$first_entry = null;
		foreach ($LANGUAGES as $lang => $info) {
			list($l, $c) = explode('_', $lang);
			if ($l == $language) {
				if (!$first_entry) {
					$first_entry = $lang;
				}
				if ($country && $c == $country) {
					return $lang;
				}
			}
		}
		return $first_entry;
	}

	/**
	 * Load the known language codes for loaded locales
	 *
	 */
	public function loadLanguages() {
		if (isset($_SESSION['LANGUAGES'])) {
			$LANGUAGES = &$_SESSION['LANGUAGES'];
		} else {
			$LANGUAGES = array();
			$langs = $this->readDirs('locales');
			foreach ($langs as $lang) {
				if (file_exists(W2P_BASE_DIR . '/locales/' . $lang . '/lang.php')) {
					include_once W2P_BASE_DIR . '/locales/' . $lang . '/lang.php';
				}
			}
			$_SESSION['LANGUAGES'] = &$LANGUAGES;
		}
		return $LANGUAGES;
	}

	/**
	 * Translate string to the local language [same form as the gettext abbreviation]
	 *
	 * This is the order of precedence:
	 * <ul>
	 * <li>If the key exists in the lang array, return the value of the key
	 * <li>If no key exists and the base lang is the same as the local lang, just return the string
	 * <li>If this is not the base lang, then return string with a red star appended to show
	 * that a translation is required.
	 * </ul>
	 * @param string The string to translate
	 * @param int Option flags, can be case handling or'd with output styles
	 * @return string
	 */
	public function _($str, $flags = 0) {
		if (is_array($str)) {
			$translated = array();
			foreach ($str as $s) {
				$translated[] = $this->__($s, $flags);
			}
			return implode(' ', $translated);
		} else {
			return $this->__($str, $flags);
		}
	}

	public function __($str, $flags = 0) {
		$str = trim($str);
		if (empty($str)) {
			return '';
		}
		$x = isset($GLOBALS['translate'][$str]) ? $GLOBALS['translate'][$str] : '';

		if ($x) {
			$str = $x;
		} elseif (w2PgetConfig('locale_warn')) {
			if ($this->base_locale != $this->user_locale || ($this->base_locale == $this->user_locale && !in_array($str, $GLOBALS['translate']))) {
				$str .= w2PgetConfig('locale_alert');
			}
		}
		switch ($flags & UI_CASE_MASK) {
			case UI_CASE_UPPER:
				$str = strtoupper($str);
				break;
			case UI_CASE_LOWER:
				$str = strtolower($str);
				break;
			case UI_CASE_UPPERFIRST:
				$str = ucwords($str);
				break;
		}
		/* Altered to support multiple styles of output, to fix
		* bugs where the same output style cannot be used succesfully
		* for both javascript and HTML output.
		* PLEASE NOTE: The default is currently UI_OUTPUT_HTML,
		* which is different to the previous version (which was
		* effectively UI_OUTPUT_RAW).  If this causes problems,
		* and they are localised, then use UI_OUTPUT_RAW in the
		* offending call.  If they are widespread, change the
		* default to UI_OUTPUT_RAW and use the other options
		* where appropriate.
		* AJD - 2004-12-10
		*/
		global $locale_char_set;

		if (!$locale_char_set) {
			$locale_char_set = 'utf-8';
		}

		switch ($flags & UI_OUTPUT_MASK) {
			case UI_OUTPUT_HTML:
				$str = htmlspecialchars(stripslashes($str), ENT_COMPAT, $locale_char_set);
				$str = nl2br($str);
				break;
			case UI_OUTPUT_JS:
				$str = addslashes(stripslashes($str)); //, ENT_COMPAT, $locale_char_set);
				break;
			case UI_OUTPUT_RAW:
				$str = stripslashes($str);
				break;
		}
		return $str;
	}
	/**
	 * Set the display of warning for untranslated strings
	 * @param string
	 */
	public function setWarning($state = true) {
		$temp = $this->cfg['locale_warn'];
		$this->cfg['locale_warn'] = $state;
		return $temp;
	}
	/**
	 * Save the url query string
	 *
	 * Also saves one level of history.  This is useful for returning from a delete
	 * operation where the record more not now exist.  Returning to a view page
	 * would be a nonsense in this case.
	 * @param string If not set then the current url query string is used
	 */
	public function savePlace($query = '') {
		$query = ($query == '') ? $_SERVER['QUERY_STRING'] : $query;
		$saved = (isset($this->state['SAVEDPLACE'])) ? $this->state['SAVEDPLACE'] : '';

		if ($query != $saved) {
			$this->state['SAVEDPLACE-1'] = $saved;
			$this->state['SAVEDPLACE'] = $query;
		}
	}
	/**
	 * Resets the internal variable
	 */
	public function resetPlace() {
		$this->state['SAVEDPLACE'] = '';
	}
	/**
	 * Get the saved place (usually one that could contain an edit button)
	 * @return string
	 */
	public function getPlace() {
		return $this->state['SAVEDPLACE'];
	}

	/**
	 * Provides a way to temporary store an object from call to call.
	 *
	 * Primarily useful for holding an object after a failed validation check
	 * without it actually being saved.
	 *
	 * @param object The item to be temporarily stored
	 *
	 *
	 */
	public function holdObject($obj) {
	  $this->objStore = $obj;
	}

	public function restoreObject() {
	  $obj = $this->objStore;
	  $this->objStore = null;
	  return $obj;
	}

	/**
	 * Redirects the browser to a new page.
	 *
	 * Mostly used in conjunction with the savePlace method. It is generally used
	 * to prevent nasties from doing a browser refresh after a db update.  The
	 * method deliberately does not use javascript to effect the redirect.
	 *
	 * @param string The URL query string to append to the URL
	 * @param string A marker for a historic 'place, only -1 or an empty string is valid.
	 */
	public function redirect($params = '', $hist = '') {
		$session_id = SID;

		session_write_close();
		// are the params empty
		if (!$params) {
			// has a place been saved
			$params = !empty($this->state['SAVEDPLACE' . $hist]) ? $this->state['SAVEDPLACE' . $hist] : $this->defaultRedirect;
		}
		// Fix to handle cookieless sessions
		if ($session_id != '') {
			if (!$params) {
				$params = $session_id;
			} else {
				$params .= '&' . $session_id;
			}
		}
		ob_implicit_flush(); // Ensure any buffering is disabled.
		header('Location: index.php?' . $params);
		exit(); // stop the PHP execution
	}
	/**
	 * Set the page message.
	 *
	 * The page message is displayed above the title block and then again
	 * at the end of the page.
	 *
	 * IMPORTANT: Please note that append should not be used, since for some
	 * languagues atomic-wise translation doesn't work. Append should be
	 * deprecated.
	 *
	 * @param mixed The (untranslated) message
	 * @param int The type of message
	 * @param boolean If true, $msg is appended to the current string otherwise
	 * the existing message is overwritten with $msg.
	 */
	public function setMsg($msg, $msgNo = 0, $append = false) {
        $this->msgNo = $msgNo;

        if (is_array($msg)) {
            $tmp_msg = '';
            foreach ($msg as $value) {
                $tmp_msg .= $this->_($value, UI_OUTPUT_RAW) . '<br />';
            }
            $tmp_msg = trim($tmp_msg, '<br />');
            $this->msg = ($append) ? $this->msg . ' ' . $tmp_msg : $tmp_msg;
        } else {
            $msg = $this->_($msg, UI_OUTPUT_RAW);
            $this->msg = ($append) ? $this->msg . ' ' . $msg : $msg;
        }
	}
	/**
	 * Display the formatted message and icon
	 * @param boolean If true the current message state is cleared.
	 */
	public function getMsg($reset = true) {
		$img = '';
		$class = '';
		$msg = $this->msg;

		switch ($this->msgNo) {
			case UI_MSG_OK:
				$img = w2PshowImage('stock_ok-16.png', 16, 16, '');
				$class = 'message';
				break;
			case UI_MSG_ALERT:
				$img = w2PshowImage('rc-gui-status-downgr.png', 16, 16, '');
				$class = 'message';
				break;
			case UI_MSG_WARNING:
				$img = w2PshowImage('rc-gui-status-downgr.png', 16, 16, '');
				$class = 'warning';
				break;
			case UI_MSG_ERROR:
				$img = w2PshowImage('stock_cancel-16.png', 16, 16, '');
				$class = 'error';
				break;
			default:
				$class = 'message';
				break;
		}
		if ($reset) {
			$this->msg = '';
			$this->msgNo = 0;
		}
		return $msg ? '<table cellspacing="0" cellpadding="1" border="0"><tr>' . '<td>' . $img . '</td>' . '<td class="' . $class . '">' . $msg . '</td>' . '</tr></table>' : '';
	}
	/**
	 * Set the value of a temporary state variable.
	 *
	 * The state is only held for the duration of a session.  It is not stored in the database.
	 * Also do not set the value if it is unset.
	 * @param string The label or key of the state variable
	 * @param mixed Value to assign to the label/key
	 */
	public function setState($label, $value = null) {
		if (isset($value)) {
			$this->state[$label] = $value;
		}
	}
	/**
	 * Get the value of a temporary state variable.
	 * If a default value is supplied and no value is found, set the default.
	 * @return mixed
	 */
	public function getState($label, $default_value = null) {
		if (array_key_exists($label, $this->state)) {
			return $this->state[$label];
		} else {
			if (isset($default_value)) {
				$this->setState($label, $default_value);
				return $default_value;
			} else {
				return null;
			}
    }
	}

  public function processIntState($label, $valueArray = array(), $name = '', $default_value = 0) {
    if(isset($valueArray)) {
    	if (isset($valueArray[$name])) {
    		$this->setState($label, (int) $valueArray[$name]);
    	} else {
    		if ($this->getState($label) === null) {
    			$this->setState($label, (int) $default_value);
    		}
    	}
    } else {
    	$this->setState($label, (int) $default_value);
    }
    return $this->getState($label);
  }

	public function checkPrefState($label, $value, $prefname, $default_value = null) {
		// Check if we currently have it set
		if (isset($value)) {
			$result = $value;
			$this->state[$label] = $value;
		} else
			if (array_key_exists($label, $this->state)) {
				$result = $this->state[$label];
			} else
				if (($pref = $this->getPref($prefname)) !== null) {
					$this->state[$label] = $pref;
					$result = $pref;
				} else
					if (isset($default_value)) {
						$this->state[$label] = $default_value;
						$result = $default_value;
					} else {
						$result = null;
					}
					return $result;
	}
	/**
	 * Login function
	 *
	 * A number of things are done in this method to prevent illegal entry:
	 * <ul>
	 * <li>The username and password are trimmed and escaped to prevent malicious
	 *     SQL being executed
	 * </ul>
	 * The schema previously used the MySQL PASSWORD function for encryption.  This
	 * Method has been deprecated in favour of PHP's MD5() function for database independance.
	 * The check_legacy_password option is no longer valid
	 *
	 * Upon a successful username and password match, several fields from the user
	 * table are loaded in this object for convenient reference.  The style, locales
	 * and preferences are also loaded at this time.
	 *
	 * @param string The user login name
	 * @param string The user password
	 * @return boolean True if successful, false if not
	 */
	public function login($username, $password) {
		$auth_method = w2PgetConfig('auth_method', 'sql');
		if ($_POST['login'] != 'login' && $_POST['login'] != $this->_('login', UI_OUTPUT_RAW) && $_REQUEST['login'] != $auth_method) {
			die('You have chosen to log in using an unsupported or disabled login method');
		}
		$auth = &getauth($auth_method);

		$username = trim(db_escape($username));
		$password = trim($password);

		if (!$auth->authenticate($username, $password)) {
			return false;
		}

		$user_id = $auth->userId($username);
		$username = $auth->username; // Some authentication schemes may collect username in various ways.
		// Now that the password has been checked, see if they are allowed to
		// access the system
		if (!isset($GLOBALS['acl'])) {
			$GLOBALS['acl'] = new w2p_Extensions_Permissions();
		}
		if (!$GLOBALS['acl']->checkLogin($user_id)) {
			dprint(__file__, __line__, 1, 'Permission check failed');
			return false;
		}

		$q = new w2p_Database_Query;
		$q->addTable('users');
		$q->addQuery('user_id, contact_first_name as user_first_name, contact_last_name as user_last_name, contact_company as user_company, contact_department as user_department, user_type');
		$q->addJoin('contacts', 'con', 'con.contact_id = user_contact', 'inner');

        /* Begin Hack */
        /*
         * This is a particularly annoying hack but I don't know of a better
         *   way to resolve #457. In v2.0, there was a refactoring to allow for
         *   muliple contact methods which resulted in the contact_email being
         *   removed from the contacts table. If the user is upgrading from
         *   v1.x and they try to log in before applying the database, crash.
         *   Info: http://bugs.web2project.net/view.php?id=457
         * This hack was deprecated in dbVersion 26 for v2.2 in December 2010.
         */

        $qTest = new w2p_Database_Query();
        $qTest->addTable('w2pversion');
        $qTest->addQuery('max(db_version)');
        $dbVersion = $qTest->loadResult();
        if ($dbVersion >= 21 && $dbVersion < 26) {
            $q->leftJoin('contacts_methods', 'cm', 'cm.contact_id = con.contact_id');
            $q->addWhere("cm.method_name = 'email_primary'");
            $q->addQuery('cm.method_value AS user_email');
        } else {
            $q->addQuery('contact_email AS user_email');
        }
        /* End Hack */

		$q->addWhere('user_id = ' . (int)$user_id . ' AND user_username = \'' . $username . '\'');
		$sql = $q->prepare();

		$q->loadObject($this);
		dprint(__file__, __line__, 7, 'Login SQL: ' . $sql);

		if (!$this) {
			dprint(__file__, __line__, 1, 'Failed to load user information');
			return false;
		}

		// load the user preferences
		$this->loadPrefs($this->user_id);
		$this->setUserLocale();
		$this->checkStyle();

		// Let's see if this user has admin privileges
		if (canView('admin')) {
			$this->user_is_admin = 1;
		}
		return true;
	}
	/************************************************************************************************************************
	/**
	*@Function for regiser log in dotprojet table "user_access_log"
	*/
	public function registerLogin() {
		$q = new w2p_Database_Query;
		$q->addTable('user_access_log');
		$q->addInsert('user_id', '' . $this->user_id);
		$q->addInsert('date_time_in', "'".$q->dbfnNowWithTZ()."'", false, true);
		$q->addInsert('user_ip', $_SERVER['REMOTE_ADDR']);
		$q->exec();
		$this->last_insert_id = db_insert_id();
		$q->clear();
	}

	/**
	 *@Function for register log out in web2project table "user_acces_log"
	 */
	public function registerLogout($user_id) {
		$q = new w2p_Database_Query;
		$q->addTable('user_access_log');
		$q->addUpdate('date_time_out', "'".$q->dbfnNowWithTZ()."'", false, true);
		$q->addWhere('user_id = ' . (int)$user_id . ' AND (date_time_out = \'0000-00-00 00:00:00\' OR ISNULL(date_time_out)) ');
		if ($user_id > 0) {
			$q->exec();
			$q->clear();
		}
	}

	/**
	 *@Function for update table user_acces_log in field date_time_lost_action
	 */
	public function updateLastAction($last_insert_id) {
		$q = new w2p_Database_Query;
		$q->addTable('user_access_log');
		$q->addUpdate('date_time_last_action', "'".$q->dbfnNowWithTZ()."'", false, true);
		$q->addWhere('user_access_log_id = ' . $last_insert_id);
		if ($last_insert_id > 0) {
			$q->exec();
			$q->clear();
		}
	}
	/************************************************************************************************************************
	/**
	* @deprecated
	*/
	public function logout() {
	}

	/**
	 * Checks whether there is any user logged in.
	 */
	public function doLogin() {
		return ($this->user_id < 0) ? true : false;
	}

	/**
	 * Gets the value of the specified user preference
	 * @param string Name of the preference
	 */
	public function getPref($name) {
		return isset($this->user_prefs[$name]) ? $this->user_prefs[$name] : '';
	}

	/**
	 * Sets the value of a user preference specified by name
	 * @param string Name of the preference
	 * @param mixed The value of the preference
	 */
	public function setPref($name, $val) {
		$this->user_prefs[$name] = $val;
	}

	/**
	 * Loads the stored user preferences from the database into the internal
	 * preferences variable.
	 * @param int User id number
	 */
	public function loadPrefs($uid = 0) {
		$q = new w2p_Database_Query;
		$q->addTable('user_preferences');
		$q->addQuery('pref_name, pref_value');
		$q->addWhere('pref_user = ' . (int)$uid);
		$prefs = $q->loadHashList();
		$this->user_prefs = array_merge($this->user_prefs, $prefs);

        $df = $this->getPref('SHDATEFORMAT');
        $df .= ' ' . $this->getPref('TIMEFORMAT');
        $cf = $df;
        $cal_df = $cf;
        $cal_df = str_replace('%S', '%s', $cal_df);
        $cal_df = str_replace('%M', '%i', $cal_df);
        $cal_df = str_replace('%p', '%a', $cal_df);
        $cal_df = str_replace('%I', '%h', $cal_df);
        $cal_df = str_replace('%b', '%M', $cal_df);
        $cal_df = str_replace('%', '', $cal_df);
        $df = $cal_df;
        $this->user_prefs['FULLDATEFORMAT'] = $cal_df;
	}

	// --- Module connectors
	/**
	 * Gets a list of the installed modules
	 * @return array Named array list in the form 'module directory'=>'module name'
	 */
	public function getInstalledModules() {
		$q = new w2p_Database_Query;
		$q->addTable('modules');
		$q->addQuery('mod_directory, mod_ui_name');
		$q->addOrder('mod_directory');
		return $q->loadHashList();
	}
	/**
	 * Gets a list of the active modules
	 * @return array Named array list in the form 'module directory'=>'module name'
	 */
	public function getActiveModules() {
		$q = new w2p_Database_Query;
		$q->addTable('modules');
		$q->addQuery('mod_directory, mod_ui_name');
		$q->addWhere('mod_active = 1');
		$q->addOrder('mod_directory');
		return $q->loadHashList();
	}

	/**
	 * Gets a list of the modules that should appear in the menu
	 * @return array Named array list in the form
	 * ['module directory', 'module name', 'module_icon']
	 */
	public function getMenuModules() {
		$q = new w2p_Database_Query;
		$q->addTable('modules');
		$q->addQuery('mod_directory, mod_ui_name, mod_ui_icon');
		$q->addWhere("mod_active > 0 AND mod_ui_active > 0 AND mod_directory <> 'public'");
		$q->addWhere("mod_type <> 'utility'");
		$q->addOrder('mod_ui_order');
		return $q->loadList();
	}

	/**
	 * Gets a list of the active modules
	 * @return array Named array list in the form 'module directory'=>'module name'
	 */
	public function getLoadableModuleList() {
		$q = new w2p_Database_Query;
		$q->addTable('modules', 'm');
		$q->addQuery('mod_directory, mod_main_class, mod_version');
		$q->addWhere('mod_active = 1');
		$q->addWhere("mod_main_class <> ''");
		$q->addOrder('mod_ui_order');
		return $q->loadList();
	}

	public function getPermissionableModuleList() {
		$q = new w2p_Database_Query;
		$q->addTable('modules', 'm');
		$q->addQuery('mod_id, mod_name, permissions_item_table, permissions_item_field, permissions_item_label');
		$q->addWhere('permissions_item_table is not null');
		$q->addWhere("permissions_item_table <> ''");
		return $q->loadHashList('mod_name');
	}

	public function isActiveModule($module) {
		$q = new w2p_Database_Query;
		$q->addTable('modules');
		$q->addQuery('mod_active');
		$q->addWhere("mod_directory = '$module'");
		return $q->loadResult();
	}

	/**
	 * Returns the global dpACL class or creates it as neccessary.
	 * @return object w2p_Extensions_Permissions
	 */
	public function &acl() {
		if (!isset($GLOBALS['acl'])) {
			$GLOBALS['acl'] = new w2p_Extensions_Permissions();
		}
		return $GLOBALS['acl'];
	}

	/**
	 * Find and add to output the file tags required to load module-specific
	 * javascript.
	 */
	public function loadHeaderJS() {
		global $m, $a;

		// load the js base.php
		include w2PgetConfig('root_dir') . '/js/base.php';

		// Search for the javascript files to load.
		if (!isset($m)) {
			return;
		}
		$root = W2P_BASE_DIR;
		if (substr($root, -1) != '/') {
			$root .= '/';
		}

		$base = W2P_BASE_URL;
		if (substr($base, -1) != '/') {
			$base .= '/';
		}
		// Load the basic javascript used by all modules.
		echo '<script type="text/javascript" src="'.$base.'js/base.js"></script>';

		// additionally load jquery
		echo '<script type="text/javascript" src="'.$base.'lib/jquery/jquery.js"></script>';
		echo '<script type="text/javascript" src="'.$base.'lib/jquery/jquery.tipTip.js"></script>';

		$this->getModuleJS($m, $a, true);
	}

	public function getModuleJS($module, $file = null, $load_all = false) {
		$root = W2P_BASE_DIR;
		if (substr($root, -1) != '/') {
			$root .= '/';
		}
		$base = W2P_BASE_URL;
		if (substr($base, -1) != '/') {
			$base .= '/';
		}
		if ($load_all || !$file) {
			if (file_exists($root . 'modules/' . $module . '/' . $module . '.module.js')) {
				echo '<script type="text/javascript" src="' . $base . 'modules/' . $module . '/' . $module . '.module.js"></script>';
			}
		}
		if (isset($file) && file_exists($root . 'modules/' . $module . '/' . $file . '.js')) {
			echo '<script type="text/javascript" src="' . $base . 'modules/' . $module . '/' . $file . '.js"></script>';
		}
	}

	public function addFooterJavascriptFile($pathTo) {
		if(!in_array($pathTo, $this->footerJavascriptFiles)) {
			$base = W2P_BASE_URL;
			if (substr($base, -1) != '/') {
				$base .= '/';
			}
			if(strpos($pathTo, $base) === false) {
				$pathTo = $base . $pathTo;
			}
			$this->footerJavascriptFiles[] = $pathTo;
		}
	}

	public function loadFooterJS() {
		$s = '<script type="text/javascript">';
		$s .= '$(document).ready(function() {';
        // Attach tooltips to "span" elements
		$s .= '	$("span").tipTip({maxWidth: "600px;", delay: 200, fadeIn: 150, fadeOut: 150});';
        // Move the focus to the first textbox available, while avoiding the "Global Search..." textbox
        if (canAccess('smartsearch')) {
            $s .= '	$("input[type=\'text\']:eq(1)").focus();';
        } else {
            $s .= '	$("input[type=\'text\']:eq(0)").focus();';
        }
		$s .= '});';
		$s .= '</script>';

		if(is_array($this->footerJavascriptFiles) and !empty($this->footerJavascriptFiles)) {
			while($jsFile = array_pop($this->footerJavascriptFiles)) {
				$s .= "<script type='text/javascript' src='" . $jsFile . "'></script>";
			}
		}

		echo $s;
	}

	public function loadCalendarJS() {
		global $AppUI;

		$s = '<style type="text/css">@import url(' . w2PgetConfig('base_url') . '/lib/jscalendar/skins/aqua/theme.css);</style>';
		$s .= '<script type="text/javascript" src="' . w2PgetConfig('base_url') . '/js/calendar.js"></script>';
		$s .= '<script type="text/javascript" src="' . w2PgetConfig('base_url') . '/lib/jscalendar/calendar.js"></script>';
		if (file_exists(w2PgetConfig('root_dir') . '/lib/jscalendar/lang/calendar-' . $AppUI->user_locale . '.js')) {
			$s .= '<script type="text/javascript" src="' . w2PgetConfig('base_url') . '/lib/jscalendar/lang/calendar-' . $AppUI->user_locale . '.js"></script>';
		} else {
			$s .= '<script type="text/javascript" src="' . w2PgetConfig('base_url') . '/lib/jscalendar/lang/calendar-en.js"></script>';
		}
		$s .= '<script type="text/javascript" src="' . w2PgetConfig('base_url') . '/lib/jscalendar/calendar-setup.js"></script>';
		echo $s;
		include w2PgetConfig('root_dir') . '/js/calendar.php';
	}
}