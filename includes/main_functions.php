<?php
##
## Global General Purpose Functions
##
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

define('SECONDS_PER_DAY', 86400);

require_once W2P_BASE_DIR . '/includes/backcompat_functions.php';
require_once W2P_BASE_DIR . '/includes/deprecated_functions.php';
require_once W2P_BASE_DIR . '/includes/cleanup_functions.php';
require_once W2P_BASE_DIR . '/lib/adodb/adodb.inc.php';
require_once W2P_BASE_DIR . '/classes/w2p/web2project.php';

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
        case 'cappui':                  // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'ccalendar':               // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'cdate':                   // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'cfilefolder':             // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'cforummessage':           // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'cinfotabbox':             // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'cmonthcalendar':          // Deprecated as of v3.0, TODO: remove this in v4.0
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
        case 'w2pacl':                  // Deprecated as of v3.0, TODO: remove this in v4.0
        case 'w2pajaxresponse':         // Deprecated as of v3.0, TODO: remove this in v4.0
            require_once W2P_BASE_DIR . '/classes/deprecated.class.php';
            break;

        /*
         * The following are all wirings for module classes that don't follow
         * our naming conventions.
         */
        case 'cevent':
            // Deprecated as of v3.0, TODO: remove this in v4.0
            require_once W2P_BASE_DIR . '/modules/calendar/calendar.class.php';
            break;
        case 'cadmin_user':
        case 'cuser':
            // Deprecated as of v3.0, TODO: remove this in v4.0
            require_once W2P_BASE_DIR . '/modules/admin/users.class.php';
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
 * @param $key string The name of a configuration setting
 * @param $default string The default value to return if the key not found.
 * @return The value of the setting, or the default value if not found.
 */
function w2PgetConfig($key, $default = null)
{
    global $w2Pconfig;

    if (isset($w2Pconfig[$key])) {
        return $w2Pconfig[$key];
    } else {
//TODO: This block had to be removed because if the w2pgetConfig was called before
//  we had a valid database object, creating the w2p_Core_Config object below would
//  call its parent - w2p_Core_BaseObject - which would try to get an w2p_Core_AppUI
//  which would in turn get back to here.. nasty loop.
//
//        if (!is_null($default)) {
//            $obj = new w2p_Core_Config();
//            $obj->overrideDatabase($dbConn);
//            $obj->config_name = $key;
//            $obj->config_value = $default;
//            $obj->store();
//        }
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
    global $AppUI;

    if (isset($arr[$name])) {
        if ((is_array($arr[$name])) || (strpos($arr[$name], ' ') === false
                && strpos($arr[$name], '<') === false && strpos($arr[$name], '"') === false
                && strpos($arr[$name], '[') === false && strpos($arr[$name], ';') === false
                && strpos($arr[$name], '{') === false) || ($arr == $_POST)) {
            return isset($arr[$name]) ? $arr[$name] : $def;
        } else {
            //Hack attempt detected
            //return isset($arr[$name]) ? str_replace(' ','',$arr[$name]) : $def;
            $AppUI->setMsg('Poisoning attempt to the URL detected. Issue logged.', UI_MSG_ALERT);
            $AppUI->redirect(ACCESS_DENIED);
        }
    } else {
        return $def;
    }
}

/**
 * Alternative to protect from XSS attacks.
 */
function w2PgetCleanParam(&$arr, $name, $def = null)
{
    $val = isset($arr[$name]) ? $arr[$name] : $def;
    if (!is_null($val)) {
        return $val;
    }

    // Code from http://quickwired.com/kallahar/smallprojects/php_xss_filter_function.php
    // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
    // this prevents some character re-spacing such as <java\0script>
    // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
    $val = preg_replace('/([\x00-\x08][\x0b-\x0c][\x0e-\x20])/', '', $val);

    // straight replacements, the user should never need these since they're normal characters
    // this prevents like <IMG SRC=&#X40&#X61&#X76&#X61&#X73&#X63&#X72&#X69&#X70&#X74&#X3A&#X61&#X6C&#X65&#X72&#X74&#X28&#X27&#X58&#X53&#X53&#X27&#X29>
    $search = 'abcdefghijklmnopqrstuvwxyz';
    $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $search .= '1234567890!@#$%^&*()';
    $search .= '~`";:?+/={}[]-_|\'\\';
    for ($i = 0, $i_cmp = strlen($search); $i < $i_cmp; $i++) {
        // ;? matches the ;, which is optional
        // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
        // &#x0040 @ search for the hex values
        $val = preg_replace('/(&#[x|X]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val); // with a ;
        // &#00064 @ 0{0,7} matches '0' zero to seven times
        $val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val); // with a ;
    }

    // now the only remaining whitespace attacks are \t, \n, and \r
    $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
    $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout',
        'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
    $ra = array_merge($ra1, $ra2);

    $found = true; // keep replacing as long as the previous round replaced something
    while ($found == true) {
        $val_before = $val;
        for ($i = 0, $i_cmp = sizeof($ra); $i < $i_cmp; $i++) {
            $pattern = '/';
            for ($j = 0, $j_cmp = strlen($ra[$i]); $j < $j_cmp; $j++) {
                if ($j > 0) {
                    $pattern .= '(';
                    $pattern .= '(&#[x|X]0{0,8}([9][a][b]);?)?';
                    $pattern .= '|(&#0{0,8}([9][10][13]);?)?';
                    $pattern .= ')?';
                }
                $pattern .= $ra[$i][$j];
            }
            $pattern .= '/i';
            $replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2); // add in <> to nerf the tag
            $val = (in_array($arr[$name], $ra)) ? preg_replace($pattern, $replacement, $val) : $val; // filter out the hex tags
            if ($val_before == $val) {
                // no replacements were made, so exit the loop
                $found = false;
                break;
            }
        }
    }
    return $val;
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

function w2pFindTaskComplete($start_date, $end_date, $percent) {
    $start = strtotime($start_date);
    $end   = strtotime($end_date);
    $now   = time();

    if ($percent >= 100) { return 'done'; }
    if ($now < $start)   { return ''; }
    if ($now > $end)     { return 'late'; }
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
        $result = htmlentities($result, ENT_QUOTES, 'UTF-8');

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

function notifyNewExternalUser($emailAddress, $username, $logname, 
        $logpwd, $emailUtility = null) {

    global $AppUI;
	$mail = (!is_null($emailUtility)) ? $emailUtility : new w2p_Utilities_Mail();
	if ($mail->ValidEmail($emailAddress)) {
//TODO: why aren't we actually using this $email variable?
        if ($mail->ValidEmail($AppUI->user_email)) {
			$email = $AppUI->user_email;
		} else {
//TODO: this email should be set to something sane
            $email = 'web2project@web2project.net';
		}
		$mail->To($emailAddress);
        $emailManager = new w2p_Output_EmailManager($AppUI);
        $body = $emailManager->notifyNewExternalUser($logname, $logpwd);
		$mail->Subject('New Account Created');
        $mail->Body($body);
		$mail->Send();
	}
}

function notifyNewUser($emailAddress, $username, $emailUtility = null) {
	global $AppUI;
	$mail = (!is_null($emailUtility)) ? $emailUtility : new w2p_Utilities_Mail();
	if ($mail->ValidEmail($emailAddress)) {
//TODO: why aren't we actually using this $email variable?
        if ($mail->ValidEmail($AppUI->user_email)) {
			$email = $AppUI->user_email;
		} else {
//TODO: this email should be set to something sane
            return false;
		}

		$mail->To($emailAddress);
        $emailManager = new w2p_Output_EmailManager($AppUI);
        $body = $emailManager->getNotifyNewUser($username);
        $mail->Subject('New Account Created');
		$mail->Body($body);
		$mail->Send();
	}
}