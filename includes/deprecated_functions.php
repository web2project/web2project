<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
/*
* This file exists in order to identify individual functions which will be
*   deprecated in coming releases.  In the documentation for each function,
*   you must describe two things:
*
*    * the specific version of web2project where the behavior will change; and
*    * a reference to the new/proper way of performing the same functionality.
*
* During Minor releases, this file will grow only to shrink as Major releases
*   allow us to delete functions.
*
* WARNING: This file does not identify class-level method deprecations.
*   In order to find those, you'll have to explore the individual classes.
*/

/**
 * This function is now deprecated and will be removed.
 * In the interim it now does nothing.
 * TODO:  Remove for v3.0 - dkc 27 Nov 2010
 *
 * @deprecated
 */
function dpRealPath($file) {
	trigger_error("The dpRealPath function has been deprecated and will be removed in v3.0.", E_USER_NOTICE );
    return $file;
}

/**
 * Corrects the charset name if needed be
 * TODO:  Remove for v4.0 - dkc 08 May 2011
 *
 * @deprecated
 */
function w2PcheckCharset($charset) {
	trigger_error("The w2PcheckCharset function has been deprecated and will be removed in v4.0.", E_USER_NOTICE );
    return 'utf-8';
}

/*
* 	Convert string char (ref : Vbulletin #3987)
*
* @deprecated
*/
function strJpGraph($text) {
    global $locale_char_set;
    trigger_error("The strJpGraph function has been deprecated and will be removed in v4.0.", E_USER_NOTICE );
    if ( $locale_char_set=='utf-8' && function_exists("utf8_decode") ) {
        return utf8_decode($text);
    } else {
        return $text;
    }
}

/**
 * Casts the $a parameter to an integer
 * TODO:  Remove for v4.0 - caseydk 26 August 2011
 *
 * @deprecated
 */
function atoi($a) {
    trigger_error("The strJpGraph function has been deprecated and will be removed in v4.0. Please use int() instead.", E_USER_NOTICE );
    return $a + 0;
}

/*
 * This used to check if a $link was a URL. Since some users use local network resources,
 *   this was failing miserably and making our lives difficult.
 * TODO:  Remove for v4.0 - caseydk 01 September 2011
 *
 * @deprecated
*/
function w2p_check_url($link)
{
    trigger_error("The w2p_check_url function has been deprecated and will be removed in v4.0. There is no replacement.", E_USER_NOTICE );
    return true;
}
