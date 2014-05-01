<?php

require_once W2P_BASE_DIR . '/includes/backcompat_functions.php';
require_once W2P_BASE_DIR . '/includes/deprecated_functions.php';
require_once W2P_BASE_DIR . '/includes/cleanup_functions.php';
require_once W2P_BASE_DIR . '/lib/adodb/adodb.inc.php';
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
        case 'bcode':                   // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'budgets':                 // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'cadmin_user':             // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'cappui':                  // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'ccalendar':               // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'cconfig':                 // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'cdate':                   // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'cfilefolder':             // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'cforummessage':           // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'cinfotabbox':             // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'cmonthcalendar':          // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'cpreferences':            // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'cprojectdesigneroptions': // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'crole':                   // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'csyskey':                 // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'csysval':                 // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'ctabbox_core':            // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'ctasklog':                // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'ctitleblock':             // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'ctitleblock_core':        // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'customfields':            // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'cw2pobject':              // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'dbquery':                 // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'libmail':                 // Deprecated as of v2.3, TODO: remove this in v4.0
        case 'smartsearch':             // Deprecated as of v2.3, TODO: remove this in v4.0
        case 'w2pacl':                  // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'w2pajaxresponse':         // Deprecated as of v3.0, TODO: remove this in v4.0
            require_once W2P_BASE_DIR . '/classes/deprecated.class.php';
            break;

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
 * @param type $a1
 * @param type $a2
 * @return type
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
 * @param $key          string The name of a configuration setting
 * @param null $default string The default value to return if the key not found.
 * @return null         The value of the setting, or the default value if not found.
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
 *  default, and avoid poisoning the URL by denying:
 * 1) the use of spaces (for SQL and XSS injection)
 * 2) the use of <, ", [, ; and { (for XSS injection)
 */
function w2PgetParam(&$arr, $name, $def = null)
{
    $key = preg_replace("/[^A-Za-z0-9_]/", "", $name);

    if (isset($arr[$key])) {
        if (is_array($arr[$key])) {
            $_result = $arr[$key];
            foreach($_result as $_key => $_value) {
                $_result[$_key] = preg_replace("/<>'\"\[\]{}:;/", "", $_value);
            }
            $result  = $_result;
        } else {
            $_result = strip_tags($arr[$key]);
            $result  = preg_replace("/<>\`'\"\[\]{}():;/", "", $_result);
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
            break;
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

function w2p_url($link, $text = '')
{
    $result = '';

    if ($link != '') {
        if (strpos($link, 'http') === false) {
            $link = 'http://' . $link;
        }
        $text = ('' != $text) ? $text : $link;
        $result = '<a href="' . $link . '" target="_new">' . $text . '</a>';
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
        //$result = html_entity_decode($result);
    }

    return $result;
}

function notifyNewExternalUser($emailAddress, $username, $logname, $logpwd, $emailUtility = null)
{
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
 * @param $auth_mode
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