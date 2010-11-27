<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}
/*
* This file exists in order to identify individual functions which may not be
*   available in all versions of PHP.  Therefore, we have to wrap the
*   functionality in function_exists stuff and all that.  In the documentation
*   for each function, you must describe:
*
*    * the specific version of PHP or extension the regular function requires.
*
* During Minor releases, this file will grow only to shrink as Major releases
*   allow us to change minimum version for PHP compatibility.
*/

if (!defined('PHP_VERSION')) {
  define('PHP_VERSION', phpversion());
}


if (!function_exists('mb_strlen')) {
	/**
	 * mb_strlen()
	 * Alternative mb_strlen in case mb_string is not available
	 *
	 * @param mixed $str
	 * @return
	 */
	function mb_strlen($str) {
		global $locale_char_set;

		if (!$locale_char_set) {
			$locale_char_set = 'utf-8';
		}
		return $locale_char_set == 'utf-8' ? strlen(utf8_decode($str)) : strlen($str);
	}
}

if (!function_exists('mb_substr')) {
	/**
	 * mb_substr()
	 * Alternative mb_substr in case mb_string is not available
	 *
	 * @param mixed $str
	 * @param mixed $start
	 * @param mixed $length
	 * @return
	 */
	function mb_substr($str, $start, $length = null) {
		global $locale_char_set;

		if (!$locale_char_set) {
			$locale_char_set = 'utf-8';
		}
		if ($locale_char_set == 'utf-8') {
			return ($length === null) ?
				utf8_encode(substr(utf8_decode($str), $start)) :
				utf8_encode(substr(utf8_decode($str), $start, $length));
		} else {
			return ($length === null) ?
				substr($str, $start) :
				substr($str, $start, $length);
		}
	}
}

if (!function_exists('mb_strpos')) {
	/**
	 * mb_strpos()
	 * Alternative mb_strpos in case mb_string is not available
	 *
	 * @param mixed $str
	 * @return
	 */
	function mb_strpos($haystack, $needle, $offset = null) {
		global $locale_char_set;

		if (!$locale_char_set) {
			$locale_char_set = 'utf-8';
		}
		return $locale_char_set == 'utf-8' ? strpos(utf8_decode($haystack), utf8_decode($needle), $offset) : strpos($haystack, $needle, $offset);
	}
}

if(!function_exists('mb_str_replace')) {
    /**
     * mb_str_replace()
	 * Alternative mb_str_replace in case mb_string is not available
	 * (array aware from here http://www.php.net/manual/en/ref.mbstring.php)
     *
     * @param mixed $search : string or array of strings to be searched.
     * @param mixed $replace : string or array of the strings that will replace the searched string(s)
     * @param mixed $subject : string to be modified.
     * @return string with the replacements made
     */
    function mb_str_replace($search, $replace, $subject) {
        if(is_array($subject)) {
            $ret = array();
            foreach($subject as $key => $val) {
                $ret[$key] = mb_str_replace($search, $replace, $val);
            }
            return $ret;
        }

        foreach((array)$search as $key => $s) {
            if($s == '') {
                continue;
            }
            $r = !is_array($replace) ? $replace : (array_key_exists($key, $replace) ? $replace[$key] : '');
            $pos = mb_strpos($subject, $s);
            while($pos !== false) {
                $subject = mb_substr($subject, 0, $pos) . $r . mb_substr($subject, $pos + mb_strlen($s));
                $pos = mb_strpos($subject, $s, $pos + mb_strlen($r));
            }
        }
        return $subject;
    }
}

if(!function_exists('mb_trim')) {
	/**
	* mb_trim()
	* Alternative mb_trim in case mb_string solution is not available
	* (http://www.php.net/manual/en/ref.mbstring.php)
	*
	* Trim characters from either (or both) ends of a string in a way that is
	* multibyte-friendly.
	*
	* @param string
	* @param charlist list of characters to remove from the ends of this string.
	* @param boolean trim the left?
	* @param boolean trim the right?
	* @return String
	*/
	function mb_trim($string, $charlist='\\\\s', $ltrim=true, $rtrim=true) {
		$both_ends = $ltrim && $rtrim;

		$char_class_inner = preg_replace(
			array( '/[\^\-\]\\\]/S', '/\\\{4}/S' ),
			array( '\\\\\\0', '\\' ),
			$charlist
		);

		$work_horse = '[' . $char_class_inner . ']+';
		$ltrim && $left_pattern = '^' . $work_horse;
		$rtrim && $right_pattern = $work_horse . '$';

		if ($both_ends) {
			$pattern_middle = $left_pattern . '|' . $right_pattern;
		} elseif($ltrim) {
			$pattern_middle = $left_pattern;
		} else {
			$pattern_middle = $right_pattern;
		}
		return preg_replace("/$pattern_middle/usSD", '', $string);
	}
}