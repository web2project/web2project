<?php
/**
 * The Application User Interface Class.
 *
 * @package     web2project\core
 * @author      Andrew Eddie <eddieajau@users.sourceforge.net>
 *
 * @todo    refactor to w2p/System/AppUI
 */

class w2p_Core_CAppUI
{

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
    public $user_display_name = null;

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
      @var string Message string */
    public $msg = '';

    /**
      @var string */
    public $msgNo = '';

    /**
      @var string Default page for a redirect call */
    public $defaultRedirect = '';

    /**
      @var array Configuration variable array */
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
    public function __construct()
    {
        $this->state = array();

        $this->user_id = -1;
        $this->user_first_name = '';
        $this->user_last_name = '';
        $this->user_company = 0;
        $this->user_department = 0;
        $this->user_type = 0;

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
    public function getSystemClass($name = null)
    {
        trigger_error("CAppUI->getSystemClass() has been deprecated in v2.0 and will be removed in v3.0", E_USER_NOTICE);

        if ($name) {
            return W2P_BASE_DIR . '/classes/' . $name . '.class.php';
        }
    }

    /** @deprecated */
    public function getLibraryClass($name = null)
    {
        trigger_error("CAppUI->getLibraryClass() has been deprecated in v3.1 and will be removed in v4.0", E_USER_NOTICE);

        if ($name) {
            return W2P_BASE_DIR . '/lib/' . $name . '.php';
        }
    }

    /** @deprecated */
    public function getModuleClass($name = null)
    {
        trigger_error("CAppUI->getModuleClass() has been deprecated in v3.1 and will be removed in v4.0", E_USER_NOTICE);

        if ($name) {
            return W2P_BASE_DIR . '/modules/' . $name . '/' . $name . '.class.php';
        }
    }

    /**
     * Used to load a php class file from the module directory
     * @param string $name The class root file name (excluding .ajax.php)
     * @return string The path to the include file
     */
    public function getModuleAjax($name = null)
    {
        if ($name) {
            return W2P_BASE_DIR . '/modules/' . $name . '/' . $name . '.ajax.php';
        }
    }

    /**
     * Determines the version.
     * @return String value indicating the current web2project version
     */
    public function getVersion()
    {
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

    /** @deprecated */
    public function getTZAwareTime()
    {
        return $this->formatTZAwareTime();
    }

    /**
     *
     */
    public function convertToSystemTZ($datetime = '', $format = 'Y-m-d H:i:s')
    {
        $userTZ = $this->getPref('TIMEZONE');
        $userTimezone = new DateTimeZone($userTZ);

        $systemTimezone = new DateTimeZone('UTC');

        $ts = new DateTime($datetime, $userTimezone);
        $ts->setTimezone($systemTimezone);

        return $ts->format($format);
    }

    /**
     * This converts the date from the GMT/UTC value stored in the database to the
     *   user-specific timezone specified by the user.
     */
    public function formatTZAwareTime($datetime = '', $format = '')
    {
        $userTimezone = $this->getPref('TIMEZONE');
        $userTimezone = ('' == $userTimezone) ? 'UTC' : $userTimezone;
        $userTZ = new DateTimeZone($userTimezone);
        $systemTZ = new DateTimeZone('UTC');
        $ts = new DateTime($datetime, $systemTZ);
        $ts->setTimezone($userTZ);

        if ('' == $format) {
            $df = $this->getPref('FULLDATEFORMAT');
        } else {
            $df = $format;
            $ts = new w2p_Utilities_Date($ts->format('Y-m-d H:i:s'));
        }

        return $ts->format($df);
    }

    public function setStyle()
    {
        // check if default user's uistyle is installed
        $uistyle = $this->getPref('UISTYLE');

        if ($uistyle && !is_dir(W2P_BASE_DIR . '/style/' . $uistyle)) {
            // fall back to host_style if user style is not installed
            $this->setPref('UISTYLE', w2PgetConfig('host_style'));
        }
    }

    /**
     * This creates and returns a theme object (which extends w2p_Theme_Base)
     *  which is then used for all templating, etc.
     *
     * @return w2p_Theme_Base object
     */
    public function getTheme()
    {
        $this->setStyle();
        $uistyle = ('' == $this->getPref('UISTYLE')) ? 'web2project' : $this->getPref('UISTYLE');
        $uiClass = 'style_' . str_replace('-', '', $uistyle);

        $theme = new $uiClass($this);

        return $theme;
    }

    /**
     * Checks that the current user preferred style is valid/exists.
     *
     * @deprecated
     */
    public function checkStyle()
    {
        trigger_error("AppUI->checkStyle() has been deprecated in v3.0 and will be removed by v4.0. Please use AppUI->setStyle() instead.", E_USER_NOTICE);

        $this->setStyle();
    }

    /** @deprecated */
    public function readDirs($path)
    {
        trigger_error("AppUI->readDirs() has been deprecated in v3.0 and will be removed by v4.0. Please use w2p_FileSystem_Loader->readDirs() instead.", E_USER_NOTICE);

        $loader = new w2p_FileSystem_Loader();
        return $loader->readDirs($path);
    }

    /** @deprecated */
    public function readFiles($path, $filter = '.')
    {
        trigger_error("AppUI->readFiles() has been deprecated in v3.0 and will be removed by v4.0. Please use w2p_FileSystem_Loader->readFiles() instead.", E_USER_NOTICE);

        $loader = new w2p_FileSystem_Loader();
        return $loader->readFiles($path, $filter);
    }

    /** @deprecated */
    public function checkFileName($file)
    {
        trigger_error("AppUI->checkFileName() has been deprecated in v3.0 and will be removed by v4.0. Please use w2p_FileSystem_Loader->checkFileName() instead.", E_USER_NOTICE);

        $loader = new w2p_FileSystem_Loader();
        return $loader->checkFileName($file);
    }

    /** @deprecated */
    public function makeFileNameSafe($file)
    {
        trigger_error("AppUI->makeFileNameSafe() has been deprecated in v3.0 and will be removed by v4.0. Please use w2p_FileSystem_Loader->makeFileNameSafe() instead.", E_USER_NOTICE);

        $loader = new w2p_FileSystem_Loader();
        return $loader->makeFileNameSafe($file);
    }

    /**
     * Sets the user locale.
     *
     * Looks in the user preferences first.  If this value has not been set by the user it uses the system default set in config.php.
     * @param string Locale abbreviation corresponding to the sub-directory name in the locales directory (usually the abbreviated language code).
     */
    public function setUserLocale($loc = '', $set = true)
    {
        global $locale_char_set;

        $LANGUAGES = $this->loadLanguages();

        if (!$loc) {
            $loc = in_array('LOCALE', $this->user_prefs) ? $this->user_prefs['LOCALE'] : w2PgetConfig('host_locale');
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
        list($base_locale, $notUsed, $notUsed2, $default_language, $lcs) = $lang;
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

    public function findLanguage($language, $country = false)
    {
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
        foreach ($LANGUAGES as $lang => $notUsed) {
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
    public function loadLanguages()
    {
        if (isset($_SESSION['LANGUAGES'])) {
            $LANGUAGES = &$_SESSION['LANGUAGES'];
        } else {
            $LANGUAGES = array();
            $loader = new w2p_FileSystem_Loader();
            $langs = $loader->readDirs('locales');
            foreach ($langs as $lang) {
                if (file_exists(W2P_BASE_DIR . '/locales/' . $lang . '/lang.php')) {
                    include W2P_BASE_DIR . '/locales/' . $lang . '/lang.php';
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
    public function _($str, $flags = 0)
    {
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

    public function __($str, $flags = 0)
    {
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
    public function setWarning($state = true)
    {
        $temp = $this->cfg['locale_warn'];
        $this->cfg['locale_warn'] = $state;
        return $temp;
    }

    /** @deprecated */
    public function savePlace($query = '')
    {
        trigger_error("AppUI->savePlace() has been deprecated in v3.2 and will be removed by v5.0. Please route the user explicitly.", E_USER_NOTICE);
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
    public function resetPlace()
    {
        $this->state['SAVEDPLACE'] = '';
    }

    /**
     * Get the saved place (usually one that could contain an edit button)
     * @return string
     */
    public function getPlace()
    {
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
    public function holdObject($obj)
    {
        $this->objStore = $obj;
    }

    public function restoreObject()
    {
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
    public function redirect($params = '', $hist = '')
    {
        $session_id = session_id();

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
    public function setMsg($msg, $msgNo = 0, $append = false)
    {
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
    public function getMsg($reset = true)
    {
        $msg = $this->msg;
        $class = 'message';

        switch ($this->msgNo) {
            case UI_MSG_ALERT:
                $img = w2PshowImage('rc-gui-status-downgr.png');
                break;
            case UI_MSG_WARNING:
                $img = w2PshowImage('rc-gui-status-downgr.png');
                $class = 'warning';
                break;
            case UI_MSG_ERROR:
                $img = w2PshowImage('stock_cancel-16.png');
                $class = 'error';
                break;
            case UI_MSG_OK:
            default:
                $img = w2PshowImage('stock_ok-16.png');
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
    public function setState($label, $value = null)
    {
        if (isset($value)) {
            $this->state[$label] = $value;
        }
    }

    /**
     * Get the value of a temporary state variable.
     * If a default value is supplied and no value is found, set the default.
     * @return mixed
     */
    public function getState($label, $default_value = null)
    {
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

    public function processIntState($label, $valueArray = array(), $name = '', $default_value = 0)
    {
        if (isset($valueArray)) {
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

    public function checkPrefState($label, $value, $prefname, $default_value = null)
    {
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
    public function login($username, $password)
    {
        $auth_method = w2PgetConfig('auth_method', 'sql');
        if ($_POST['login'] != 'login' && $_POST['login'] != $this->_('login', UI_OUTPUT_RAW) && $_REQUEST['login'] != $auth_method) {
            die('You have chosen to log in using an unsupported or disabled login method');
        }
        $auth = &getauth($auth_method);

        $username = preg_replace("/[^A-Za-z0-9._@-]/", "", $username);
        $username = trim($username);
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
        $q->addQuery('user_id, contact_first_name as user_first_name, ' .
            'contact_last_name as user_last_name, contact_display_name as user_display_name, ' .
            'contact_company as user_company, contact_department as user_department, user_type');
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
        }
        /* End Hack */

        $q->addWhere('user_id = ' . (int) $user_id . ' AND user_username = \'' . $username . '\'');
        $q->loadObject($this);

        if (!$this) {
            dprint(__file__, __line__, 1, 'Failed to load user information');
            return false;
        }

        // load the user preferences
        $this->loadPrefs($this->user_id);
        $this->setUserLocale();
        $this->setStyle();

        return true;
    }

    /*     * **********************************************************************************************************************
      /**
     * @Function for regiser log in dotprojet table "user_access_log"
     */

    public function registerLogin()
    {
        $q = new w2p_Database_Query;
        $q->addTable('user_access_log');
        $q->addInsert('user_id', '' . $this->user_id);
        $q->addInsert('date_time_in', "'" . $q->dbfnNowWithTZ() . "'", false, true);
        $q->addInsert('user_ip', $_SERVER['REMOTE_ADDR']);
        $q->exec();
    }

    /**
     * @Function for register log out in web2project table "user_acces_log"
     */
    public function registerLogout($user_id)
    {
        if ($user_id > 0) {
            $q = new w2p_Database_Query;
            $q->addTable('user_access_log');
            $q->addUpdate('date_time_out', "'" . $q->dbfnNowWithTZ() . "'", false, true);
            $q->addWhere('user_id = ' . (int) $user_id . ' AND date_time_out IS NULL');
            $q->exec();
        }
    }

    /**
     * @Function for update table user_acces_log in field date_time_lost_action
     */
    public function updateLastAction($last_insert_id)
    {
        if ($last_insert_id > 0) {
            $q = new w2p_Database_Query;
            $q->addTable('user_access_log');
            $q->addUpdate('date_time_last_action', "'" . $q->dbfnNowWithTZ() . "'", false, true);
            $q->addWhere('user_access_log_id = ' . $last_insert_id);
            $q->exec();
        }
    }

    /**
     * @deprecated
     */
    public function logout()
    {
        trigger_error("The AppUI->logout() method has been deprecated in 2.0 and will be removed in v4.0. Please use CCompany->projects() instead.", E_USER_NOTICE );
    }

    /**
     * Checks whether there is any user logged in.
     */
    public function doLogin()
    {
        return ($this->user_id < 0) ? true : false;
    }

    /**
     * Gets the value of the specified user preference
     * @param string Name of the preference
     */
    public function getPref($name)
    {
        return isset($this->user_prefs[$name]) ? $this->user_prefs[$name] : '';
    }

    /**
     * Sets the value of a user preference specified by name
     * @param string Name of the preference
     * @param mixed The value of the preference
     */
    public function setPref($name, $val)
    {
        $this->user_prefs[$name] = $val;
    }

    /**
     * Loads the stored user preferences from the database into the internal
     * preferences variable.
     * @param int User id number
     * @param bool If false (default) then sture internally, if true return pref array
     */
    public function loadPrefs($uid = 0, $return = false)
    {
        // Temp pref object to store result in

        $q = new w2p_Database_Query;
        $q->addTable('user_preferences');
        $q->addQuery('pref_name, pref_value');
        $q->addWhere('pref_user = ' . (int) $uid);
        $prefs = $q->loadHashList();

        $df = $this->getPref('SHDATEFORMAT');
        $df .= ' ' . $this->getPref('TIMEFORMAT');
        $prefs['DISPLAYFORMAT'] = $df;

        $cal_df = $df;
        $cal_df = str_replace('%S', '%s', $cal_df);
        $cal_df = str_replace('%M', '%i', $cal_df);
        $cal_df = str_replace('%p', '%a', $cal_df);
        $cal_df = str_replace('%I', '%h', $cal_df);
        $cal_df = str_replace('%b', '%M', $cal_df);
        $cal_df = str_replace('%', '', $cal_df);
        $prefs['FULLDATEFORMAT'] = $cal_df;

        if($return) {
            return $prefs;
        }
        else {
            $this->user_prefs = is_array($this->user_prefs) ? $this->user_prefs : array();
            $this->user_prefs = array_merge($this->user_prefs, $prefs);
        }
    }

    // --- Module connectors
    /**
     * Gets a list of the installed modules
     * @return array Named array list in the form 'module directory'=>'module name'
     */
    public function getInstalledModules()
    {
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
    public function getActiveModules()
    {
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
    public function getMenuModules()
    {
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
    public function getLoadableModuleList()
    {
        $q = new w2p_Database_Query;
        $q->addTable('modules', 'm');
        $q->addQuery('mod_directory, mod_main_class, mod_version');
        $q->addWhere('mod_active = 1');
        $q->addWhere("mod_main_class <> ''");
        $q->addOrder('mod_ui_order');
        return $q->loadList();
    }

    public function getPermissionableModuleList()
    {
        $q = new w2p_Database_Query;
        $q->addTable('modules', 'm');
        $q->addQuery('mod_id, mod_name, permissions_item_table, permissions_item_field, permissions_item_label');
        $q->addWhere('permissions_item_table is not null');
        $q->addWhere("permissions_item_table <> ''");
        return $q->loadHashList('mod_name');
    }

    public function isActiveModule($module)
    {
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
    public function &acl()
    {
        if (!isset($GLOBALS['acl'])) {
            $GLOBALS['acl'] = new w2p_Extensions_Permissions();
        }
        return $GLOBALS['acl'];
    }

    /** @deprecated */
    public function loadHeaderJS()
    {
        trigger_error("CAppUI->loadHeaderJS() has been deprecated in v3.2 and will be removed in v5.0", E_USER_NOTICE);

        $this->getTheme()->loadHeaderJS();
    }

    /** @deprecated */
    public function getModuleJS($module, $file = null, $load_all = false)
    {
        trigger_error("CAppUI->getModuleJS() has been deprecated in v3.2 and will be removed in v5.0", E_USER_NOTICE);

        $this->getTheme()->getModuleJS($module, $file, $load_all);
    }

    /** @deprecated */
    public function addFooterJavascriptFile($pathTo)
    {
        trigger_error("CAppUI->addFooterJavascriptFile() has been deprecated in v3.2 and will be removed in v5.0", E_USER_NOTICE);

        $this->getTheme()->addFooterJavascriptFile($pathTo);
    }

    /** @deprecated */
    public function loadFooterJS()
    {
        trigger_error("CAppUI->loadFooterJS() has been deprecated in v3.2 and will be removed in v5.0", E_USER_NOTICE);

        return $this->getTheme()->loadFooterJS();
    }

    /** @deprecated */
    public function loadCalendarJS()
    {
        trigger_error("CAppUI->loadCalendarJS() has been deprecated in v3.2 and will be removed in v5.0", E_USER_NOTICE);

        $this->getTheme()->loadCalendarJS();
    }
}
