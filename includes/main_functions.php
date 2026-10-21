<?php

require_once W2P_BASE_DIR . '/includes/backcompat_functions.php';
require_once W2P_BASE_DIR . '/includes/cleanup_functions.php';
require_once W2P_BASE_DIR . '/classes/w2p/web2project.php';
require_once W2P_BASE_DIR . '/vendor/autoload.php';

spl_autoload_register('web2project_autoload');
spl_autoload_register('w2p_old_autoload');

/**
 * For all intents and purposes, this autoloader should be considered
 *  deprecated. As we move forward, we'll continue to simplify and clean this
 *  up so that it should eventually be nothing except the module autoloader.
 */
function w2p_old_autoload($class_name)
{
    $name = strtolower($class_name);
    switch ($name) {
        /*
         * These are our library helper libraries. They're included here to simplify usage.
         */
        case 'captcha':
            require_once W2P_BASE_DIR . '/lib/captcha/Captcha.class.php';
            break;
        case 'contact_vcard_build':
            require_once W2P_BASE_DIR . '/lib/PEAR/Contact_Vcard_Build.php';
            break;
        case 'contact_vcard_parse':
            require_once W2P_BASE_DIR . '/lib/PEAR/Contact_Vcard_Parse.php';
            break;
        case 'date':
        case 'date_calc':
        case 'date_human':
        case 'date_span':
        case 'date_timezone':
        case 'html_bbcodeparser_filter':
        case 'html_bbcodeparser_filter_basic':
        case 'html_bbcodeparser_filter_email':
        case 'html_bbcodeparser_filter_extended':
        case 'html_bbcodeparser_filter_links':
        case 'html_bbcodeparser_filter_lists':
            $filename = str_replace('_', '/', $class_name) . '.php';
            require_once W2P_BASE_DIR . '/lib/PEAR/' . $filename;
            break;
        case 'html_bbcodeparser':
            require_once W2P_BASE_DIR . '/lib/PEAR/BBCodeParser.php';
            break;
        case 'cezpdf':
            require_once W2P_BASE_DIR . '/lib/ezpdf/class.ezpdf.php';
            break;
        case 'cpdf':
            require_once W2P_BASE_DIR . '/lib/ezpdf/class.pdf.php';
            break;
        case 'gacl':
            require_once W2P_BASE_DIR . '/lib/phpgacl/gacl.class.php';
            break;
        case 'gacl_api':
            require_once W2P_BASE_DIR . '/lib/phpgacl/gacl_api.class.php';
            break;
        case 'ganttgraph':
            require_once W2P_BASE_DIR . '/lib/jpgraph/src/jpgraph.php';
            require_once W2P_BASE_DIR . '/lib/jpgraph/src/jpgraph_gantt.php';
            break;
        case 'phpmailer':
            require_once W2P_BASE_DIR . '/lib/PHPMailer/class.phpmailer.php';
            break;
        case 'xajax':
            require_once W2P_BASE_DIR . '/lib/xajax/xajax_core/xajax.inc.php';
            break;
        case 'xajaxresponse':
            require_once W2P_BASE_DIR . '/lib/xajax/xajax_core/xajaxResponse.inc.php';
            break;

        default:
            if ($name[0] == 'c') {
                $name = substr($name, 1);
            }
            $pieces = (strpos($name, '_') === false) ?
                    array($name, $name) : explode('_', $name);

            /*
             * I switched the order of the path resolution on the modules. The
             *   vast majority of module names/structures fall into this
             *   category, so we'll have marginally faster resolution.
             */
            $plural_pieces = array_map('w2p_pluralize', $pieces);
            if ('systems' == $plural_pieces[0]) {
                $plural_pieces[0] = 'system';
            }
            $path = implode('/', $plural_pieces);
            if (file_exists(W2P_BASE_DIR . '/modules/' . $path . '.class.php')) {
                require_once W2P_BASE_DIR . '/modules/' . $path . '.class.php';

                return;
            }

            $path = implode('/', $pieces);
            if (file_exists(W2P_BASE_DIR . '/modules/' . $path . '.class.php')) {
                require_once W2P_BASE_DIR . '/modules/' . $path . '.class.php';

                return;
            }

            break;
    }
}

/**
 * Merges arrays maintaining/overwriting shared numeric indicees
 *
 * @param mixed $a1
 * @param mixed $a2
 *
 * @return mixed
 */
function arrayMerge($a1, $a2)
{
    if (is_array($a1) && !is_array($a2)) {
        return $a1;
    }
    if (is_array($a2) && !is_array($a1)) {
        return $a2;
    }
    foreach ($a2 as $k => $v) {
        $a1[$k] = $v;
    }

    return $a1;
}

/**
 * Retrieves a configuration setting.
 *
 * @param string          $key The name of a configuration setting
 * @param mixed $default The default value to return if the key not found.
 *
 * @return mixed The value of the setting, or the default value if not found.
 */
function w2PgetConfig($key, $default = null)
{
    global $w2Pconfig;

    if (isset($w2Pconfig[$key])) {
        return $w2Pconfig[$key];
    } else {
        return $default;
    }
}

/**
 * Utility function to return a value from a named array or a specified
 *  default.
 *
 * A string value has its HTML tags removed (strip_tags()); nothing else is
 *  filtered, and array values are returned as they are. The result is NOT
 *  safe for SQL or HTML: cast it ((int)), quote it (w2p_Database_Query::quote())
 *  or escape it (w2PformSafe()/htmlspecialchars()) where it is used.
 *
 *  Earlier versions also ran preg_replace() with a pattern meant to remove
 *  quotes, brackets and semicolons. It wasn't a character class, so it only
 *  matched that exact sequence and never removed anything.
 *
 * @param array<mixed> $arr  Source array, usually $_GET or $_POST
 * @param string       $name Key to read
 * @param mixed        $def  Default when the key is missing
 *
 * @return mixed
 */
function w2PgetParam(&$arr, $name, $def = null)
{
    $key = preg_replace("/[^A-Za-z0-9_]/", "", $name);

    if (isset($arr[$key])) {
        if (is_array($arr[$key])) {
            $result = $arr[$key];
        } else {
            $result = strip_tags($arr[$key]);
        }
    } else {
        $result = $def;
    }

    return $result;
}

function convert2days($durn, $units)
{
    switch ($units) {
        case 0:
        case 1:
            return $durn / w2PgetConfig('daily_working_hours');
        case 24:
            return $durn;
    }
}

function filterCurrency($number)
{

    if (substr($number, -3, 1) == ',') {
        // This is the European format, so convert it to the US decimal format.
        $number = str_replace('.', '', $number);
        $number = str_replace(',', '.', $number);
    } else {
        // This is the US format, so just make sure it's clean.
        $number = str_replace(',', '', $number);
    }

    return $number;
}

function w2pFindTaskComplete($start_date, $end_date, $percent)
{
    $start = strtotime($start_date);
    $end   = strtotime($end_date);
    $now   = time();

    if ($percent >= 100) { return 'done'; }
    if ($now < $start) { return ''; }
    if ($now > $end) { return 'late'; }
    if ($now > $start && $percent > 0) { return 'active'; }
    if ($now > $start && $percent == 0) { return 'notstarted'; }
}

/**
 * PHP doesn't come with a signum function
 */
function w2Psgn($x)
{
    return $x ? ($x > 0 ? 1 : -1) : 0;
}

/**
 * Makes a user-entered URL safe to use as a link target.
 *
 * Only http, https, ftp and mailto links are allowed. Text without a scheme
 *  (example.com/page) gets http://. Anything else, such as javascript: or
 *  data: URLs, returns ''.
 *
 * The result is not HTML-escaped; escape it when printing it.
 *
 * @param string $url
 *
 * @return string
 */
function w2p_safe_url($url)
{
    // Browsers ignore control characters and whitespace inside a scheme
    // ("java\tscript:"), so drop them before looking at it.
    // Stored values are often HTML-encoded (bind() runs htmlentities()), and
    // the browser decodes entities in an href, so check the decoded text.
    $url = trim(preg_replace('/[\x00-\x20\x7f]+/', ' ', (string) $url));
    if ('' == $url) {
        return '';
    }
    $squashed = preg_replace('/[\x00-\x20\x7f]+/', '', html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

    if (preg_match('/^([a-z][a-z0-9+.\-]*):/i', $squashed, $matches)) {
        if (!in_array(strtolower($matches[1]), array('http', 'https', 'ftp', 'mailto'))) {
            // Could still be host:port without a scheme (example.com:8080).
            if (!preg_match('/^[a-z0-9.\-]+:\d+(\/|$)/i', $squashed)) {
                return '';
            }
            return 'http://' . $squashed;
        }
        return $url;
    }
    if (0 === strpos($squashed, '//')) {
        return 'http:' . $squashed;
    }

    return 'http://' . ltrim($url, '/');
}

function w2p_url($link, $text = '')
{
    $result = '';

    $link = w2p_safe_url($link);
    if ($link != '') {
        $text = ('' != $text) ? $text : $link;
        // Don't double-encode values that were stored HTML-encoded.
        $result = '<a href="' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8', false) . '" target="_new">' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8', false) . '</a>';
    }

    return $result;
}

function w2p_email($email, $name = '')
{
    $result = '';

    if ($email != '') {
        $name = ('' != $name) ? $name : $email;
        $result = '<a href="mailto:' . $email . '">' . $name . '</a>';
    }

    return $result;
}

function w2p_check_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function w2p_textarea($content)
{
    $result = '';

    if ($content != '') {
        $result = $content;
        $result = htmlentities($result, ENT_QUOTES |8|ENT_IGNORE, 'UTF-8',false);

        /*
         * Thanks to Alison Gianotto for two regular expressions to make our
         *   links all linky.  This code is based on her work here:
         *   http://www.snipe.net/2009/09/php-twitter-clickable-links
         */
        $result = preg_replace("#(^|[\n ])([\w]+?://[\w]+[^ \"\n\r\t< ]*)#", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $result);
        $result = preg_replace("#(^|[\n ])((www|ftp)\.[^ \"\t\n\r< ]*)#", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $result);
        $result = nl2br($result);
    }

    return $result;
}

function notifyNewExternalUser($emailAddress, $username, $logname, $logpwd, $emailUtility = null)
{
    /** @var w2p_Core_CAppUI $AppUI */
    global $AppUI;
    $emailManager = new w2p_Output_EmailManager($AppUI);
    $body = $emailManager->notifyNewExternalUser($logname, $logpwd);

    $mail = (!is_null($emailUtility)) ? $emailUtility : new w2p_Utilities_Mail();
    $mail->To($emailAddress);
    $mail->Subject('New Account Created');
    $mail->Body($body);
    return $mail->Send();
}

function notifyNewUser($emailAddress, $username, $emailUtility = null)
{
    /** @var w2p_Core_CAppUI $AppUI */
    global $AppUI;
    $emailManager = new w2p_Output_EmailManager($AppUI);
    $body = $emailManager->getNotifyNewUser($username);

    $mail = (!is_null($emailUtility)) ? $emailUtility : new w2p_Utilities_Mail();
    $mail->To($emailAddress);
    $mail->Subject('New Account Created');
    $mail->Body($body);
    return $mail->Send();
}

/**
 * Authenticator Factory
 *
 * @param mixed $auth_mode
 *
 * @return w2p_Authenticators_Base
 */
function &getAuth($auth_mode) {
    switch ($auth_mode) {
        case 'ldap':
            $auth = new w2p_Authenticators_LDAP();
            break;
        case 'pn':
            $auth = new w2p_Authenticators_PostNuke();
            break;
        default:
            $auth = new w2p_Authenticators_SQL();
    }
    return $auth;
}