<?php /* $Id$ $URL$ */
##
## Global General Purpose Functions
##
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

define('SECONDS_PER_DAY', 60 * 60 * 24);

##
## Returns the best color based on a background color (x is cross-over)
##
function bestColor($bg, $lt = '#ffffff', $dk = '#000000') {
	// cross-over color = x
	$x = 128;
	$r = hexdec(substr($bg, 0, 2));
	$g = hexdec(substr($bg, 2, 2));
	$b = hexdec(substr($bg, 4, 2));

	if ($r < $x && $g < $x || $r < $x && $b < $x || $b < $x && $g < $x) {
		return $lt;
	} else {
		return $dk;
	}
}

##
## returns a select box based on an key,value array where selected is based on key
##
function arraySelect(&$arr, $select_name, $select_attribs, $selected, $translate = false) {
	global $AppUI;
	if (!is_array($arr)) {
		dprint(__file__, __line__, 0, 'arraySelect called with no array');
		return '';
	}
	reset($arr);
	$s = '<select id="' . $select_name . '" name="' . $select_name . '" ' . $select_attribs . '>';
	$did_selected = 0;
	foreach ($arr as $k => $v) {
		if ($translate) {
			$v = $AppUI->_($v);
			// This is supplied to allow some Hungarian characters to
			// be translated correctly. There are probably others.
			// As such a more general approach probably based upon an
			// array lookup for replacements would be a better approach. AJD.
			$v = str_replace('&#369;', 'ï¿½', $v);
			$v = str_replace('&#337;', 'ï¿½', $v);
		}
		$s .= '<option value="' . $k . '"' . ((($k == $selected && strcmp($k, $selected) == 0) && !$did_selected) ? ' selected="selected"' : '') . '>' . $v . '</option>';
		if (($k == $selected && strcmp($k, $selected) == 0)) {
			$did_selected = 1;
		}
	}
	$s .= '</select>';
	return $s;
}

##
## returns a select box based on an key,value array where selected is based on key
##
function arraySelectTree(&$arr, $select_name, $select_attribs, $selected, $translate = false) {
	global $AppUI;
	reset($arr);

	$children = array();
	// first pass - collect children
	foreach ($arr as $k => $v) {
		$id = $v[0];
		$pt = $v[2];
		$list = $children[$pt] ? $children[$pt] : array();
		array_push($list, $v);
		$children[$pt] = $list;
	}
	$list = tree_recurse($arr[0][2], '', array(), $children);
	return arraySelect($list, $select_name, $select_attribs, $selected, $translate);
}

function tree_recurse($id, $indent, $list, $children) {
	if ($children[$id]) {
		foreach ($children[$id] as $v) {
			$id = $v[0];
			$txt = $v[1];
			$pt = $v[2];
			$list[$id] = $indent . ' ' . $txt;
			$list = tree_recurse($id, $indent . '--', $list, $children);
		}
	}
	return $list;
}

/**
 **	Provide Projects Selectbox sorted by Companies
 **	@author gregorerhardt with special thanks to original author aramis
 **	@param 	int 		userID
 **	@param 	string 	HTML select box name identifier
 **	@param	string	HTML attributes
 **	@param	int			Proejct ID for preselection
 **	@param 	int			Project ID which will be excluded from the list 
 **									(e.g. in the tasks import list exclude the project to import into)
 **	@return	string 	HTML selectbox

 */

function projectSelectWithOptGroup($user_id, $select_name, $select_attribs, $selected, $excludeProjWithId = null) {
	global $AppUI;
	$q = new DBQuery();
	$q->addTable('projects', 'pr');
	$q->addQuery('pr.project_id, co.company_name, project_name');
	if (!empty($excludeProjWithId)) {
		$q->addWhere('pr.project_id <> ' . $excludeProjWithId);
	}
	$proj = new CProject();
	$proj->setAllowedSQL($user_id, $q, null, 'pr');
	$q->addOrder('co.company_name, project_name');
	$projects = $q->loadList();
	$s = '<select name="' . $select_name . '" ' . $select_attribs . '>';
	$s .= '<option value="0" ' . ($selected == 0 ? 'selected="selected"' : '') . ' >' . $AppUI->_('None') . '</option>';
	$current_company = '';
	foreach ($projects as $p) {
		if ($p['company_name'] != $current_company) {
			$current_company = $p['company_name'];
			$s .= '<optgroup label="' . $current_company . '" >' . $current_company . '</optgroup>';
		}
		$s .= '<option value="' . $p['project_id'] . '" ' . ($selected == $p['project_id'] ? 'selected="selected"' : '') . '>&nbsp;&nbsp;&nbsp;' . $p['project_name'] . '</option>';
	}
	$s .= '</select>';
	return $s;
}

##
## Merges arrays maintaining/overwriting shared numeric indicees
##
function arrayMerge($a1, $a2) {
	foreach ($a2 as $k => $v) {
		$a1[$k] = $v;
	}
	return $a1;
}

##
## breadCrumbs - show a separated list of crumbs
## array is in the form url => title
##
function breadCrumbs(&$arr) {
	global $AppUI;
	$crumbs = array();
	foreach ($arr as $k => $v) {
		$crumbs[] = '<a class="button" href="' . $k . '"><span>' . $AppUI->_($v) . '</span></a>';
	}
	return implode('</td><td align="left" nowrap="nowrap">', $crumbs);
}
##
## generate link for context help -- old version
##
function contextHelp($title, $link = '') {
	return w2PcontextHelp($title, $link);
}

function w2PcontextHelp($title, $link = '') {
	global $AppUI;
	return '<a href="#' . $link . '" onclick="javascript:window.open(\'?m=help&amp;dialog=1&amp;hid=' . $link . '\', \'contexthelp\', \'width=400, height=400, left=50, top=50, scrollbars=yes, resizable=yes\')">' . $AppUI->_($title) . '</a>';
}

/**
 * Retrieves a configuration setting.
 * @param $key string The name of a configuration setting
 * @param $default string The default value to return if the key not found.
 * @return The value of the setting, or the default value if not found.
 */
function w2PgetConfig($key, $default = null) {
	global $w2Pconfig;
	if (array_key_exists($key, $w2Pconfig)) {
		return $w2Pconfig[$key];
	} else {
		return $default;
	}
}

function w2PgetUsername($user) {
	$q = new DBQuery;
	$q->addTable('users');
	$q->addQuery('contact_first_name, contact_last_name');
	$q->addJoin('contacts', 'con', 'contact_id = user_contact', 'inner');
	$q->addWhere('user_username like \'' . $user . '\' OR user_id = ' . (int)$user);
	$r = $q->loadList();
	return $r[0]['contact_first_name'] . ' ' . $r[0]['contact_last_name'];
}

function w2PgetUsernameFromID($user) {
	$q = new DBQuery;
	$q->addTable('users');
	$q->addQuery('contact_first_name, contact_last_name');
	$q->addJoin('contacts', 'con', 'contact_id = user_contact', 'inner');
	$q->addWhere('user_id = ' . (int)$user);
	$r = $q->loadList();
	return $r[0]['contact_first_name'] . ' ' . $r[0]['contact_last_name'];
}

function w2PgetUsers($module = '') {
	global $AppUI;
	$q = new DBQuery;
	$q->addTable('users');
	$q->addQuery('user_id, concat_ws(\' \', contact_first_name, contact_last_name) as name');
	$q->addJoin('contacts', 'con', 'contact_id = user_contact', 'inner');
	$q->addOrder('contact_first_name,contact_last_name');

	// get CCompany() to filter by company
	require_once ($AppUI->getModuleClass('companies'));
	$obj = new CCompany();
	$companies = $obj->getAllowedSQL($AppUI->user_id, 'company_id');
	$q->addJoin('companies', 'com', 'company_id = contact_company');
	if ($companies) {
		$q->addWhere('(' . implode(' OR ', $companies) . ' OR contact_company=\'\' OR contact_company IS NULL OR contact_company = 0)');
	}
	require_once ($AppUI->getModuleClass('departments'));
	$dpt = new CDepartment();
	$depts = $dpt->getAllowedSQL($AppUI->user_id, 'dept_id');
	$q->addJoin('departments', 'dep', 'dept_id = contact_department');
	if ($depts) {
		$q->addWhere('(' . implode(' OR ', $depts) . ' OR contact_department=0)');
	}
	//print_r($q->prepare());

	return arrayMerge(array(0 => $AppUI->_('All Users')), $q->loadHashList());
}

function w2PgetUsersList($stub = null, $where = null, $orderby = 'contact_first_name, contact_last_name') {
	global $AppUI;
	$q = new DBQuery;
	$q->addTable('users');
	$q->addQuery('DISTINCT(user_id), user_username, contact_last_name, contact_first_name,
		 contact_email, company_name, contact_company, dept_id, dept_name, CONCAT(contact_first_name,\' \',contact_last_name) contact_name, user_type');
	$q->addJoin('contacts', 'con', 'contact_id = user_contact', 'inner');
	if ($stub) {
		$q->addWhere('(UPPER(user_username) LIKE \'' . $stub . '%\' or UPPER(contact_first_name) LIKE \'' . $stub . '%\' OR UPPER(contact_last_name) LIKE \'' . $stub . '%\')');
	} elseif ($where) {
		$where = $q->quote('%' . $where . '%');
		$q->addWhere('(UPPER(user_username) LIKE ' . $where . ' OR UPPER(contact_first_name) LIKE ' . $where . ' OR UPPER(contact_last_name) LIKE ' . $where . ')');
	}

	$q->addGroup('user_id');
	$q->addOrder($orderby);

	// get CCompany() to filter by company
	require_once ($AppUI->getModuleClass('companies'));
	$obj = new CCompany();
	$companies = $obj->getAllowedSQL($AppUI->user_id, 'company_id');
	$q->addJoin('companies', 'com', 'company_id = contact_company');
	if ($companies) {
		$q->addWhere('(' . implode(' OR ', $companies) . ' OR contact_company=\'\' OR contact_company IS NULL OR contact_company = 0)');
	}
	require_once ($AppUI->getModuleClass('departments'));
	$dpt = new CDepartment();
	$depts = $dpt->getAllowedSQL($AppUI->user_id, 'dept_id');
	$q->addJoin('departments', 'dep', 'dept_id = contact_department');
	if ($depts) {
		$q->addWhere('(' . implode(' OR ', $depts) . ' OR contact_department=0)');
	}
	//print_r($q->prepare());

	return $q->loadList();
}

function w2PgetUsersHashList($stub = null, $where = null, $orderby = 'contact_first_name, contact_last_name') {
	global $AppUI;
	$q = new DBQuery;
	$q->addTable('users');
	$q->addQuery('DISTINCT(user_id), user_username, contact_last_name, contact_first_name,
		 contact_email, company_name, contact_company, dept_id, dept_name, CONCAT(contact_first_name,\' \',contact_last_name) contact_name, user_type');
	$q->addJoin('contacts', 'con', 'contact_id = user_contact', 'inner');
	if ($stub) {
		$q->addWhere('(UPPER(user_username) LIKE \'' . $stub . '%\' or UPPER(contact_first_name) LIKE \'' . $stub . '%\' OR UPPER(contact_last_name) LIKE \'' . $stub . '%\')');
	} elseif ($where) {
		$where = $q->quote('%' . $where . '%');
		$q->addWhere('(UPPER(user_username) LIKE ' . $where . ' OR UPPER(contact_first_name) LIKE ' . $where . ' OR UPPER(contact_last_name) LIKE ' . $where . ')');
	}

	$q->addGroup('user_id');
	$q->addOrder($orderby);

	// get CCompany() to filter by company
	require_once ($AppUI->getModuleClass('companies'));
	$obj = new CCompany();
	$companies = $obj->getAllowedSQL($AppUI->user_id, 'company_id');
	$q->addJoin('companies', 'com', 'company_id = contact_company');
	if ($companies) {
		$q->addWhere('(' . implode(' OR ', $companies) . ' OR contact_company=\'\' OR contact_company IS NULL OR contact_company = 0)');
	}
	require_once ($AppUI->getModuleClass('departments'));
	$dpt = new CDepartment();
	$depts = $dpt->getAllowedSQL($AppUI->user_id, 'dept_id');
	$q->addJoin('departments', 'dep', 'dept_id = contact_department');
	if ($depts) {
		$q->addWhere('(' . implode(' OR ', $depts) . ' OR contact_department=0)');
	}
	//print_r($q->prepare());

	return $q->loadHashList('user_id');
}

##
## displays the configuration array of a module for informational purposes
##
function w2PshowModuleConfig($config) {
	global $AppUI;
	$s = '<table cellspacing="2" cellpadding="2" border="0" class="std" width="50%">';
	$s .= '<tr><th colspan="2">' . $AppUI->_('Module Configuration') . '</th></tr>';
	foreach ($config as $k => $v) {
		$s .= '<tr><td width="50%">' . $AppUI->_($k) . '</td><td width="50%" class="hilite">' . $AppUI->_($v) . '</td></tr>';
	}
	$s .= '</table>';
	return ($s);
}

/**
 *	Function to recussively find an image in a number of places
 *	@param string The name of the image
 *	@param string Optional name of the current module
 */
function w2PfindImage($name, $module = null) {
	// uistyle must be declared globally
	global $uistyle;
	//print_r($name.' '.$module.' '.w2PgetConfig('host_style'));
	if ($module && file_exists(W2P_BASE_DIR . '/modules/' . $module . '/images/' . $name)) {
		return './modules/' . $module . '/images/' . $name;
	} elseif ($module && file_exists(W2P_BASE_DIR . '/style/' . $uistyle . '/images/modules/' . $module . '/' . $name)) {
		return './style/' . $uistyle . '/images/modules/' . $module . '/' . $name;
	} elseif (file_exists(W2P_BASE_DIR . '/style/' . $uistyle . '/images/icons/' . $name)) {
		return './style/' . $uistyle . '/images/icons/' . $name;
	} elseif (file_exists(W2P_BASE_DIR . '/style/' . $uistyle . '/images/obj/' . $name)) {
		return './style/' . $uistyle . '/images/obj/' . $name;
	} elseif (file_exists(W2P_BASE_DIR . '/style/' . $uistyle . '/images/' . $name)) {
		return './style/' . $uistyle . '/images/' . $name;
	} elseif ($module && file_exists(W2P_BASE_DIR . '/style/' . w2PgetConfig('host_style') . '/images/modules/' . $module . '/' . $name)) {
		return './style/' . w2PgetConfig('host_style') . '/images/modules/' . $module . '/' . $name;
	} elseif (file_exists(W2P_BASE_DIR . '/style/' . w2PgetConfig('host_style') . '/images/icons/' . $name)) {
		return './style/' . w2PgetConfig('host_style') . '/images/icons/' . $name;
	} elseif (file_exists(W2P_BASE_DIR . '/style/' . w2PgetConfig('host_style') . '/images/obj/' . $name)) {
		return './style/' . w2PgetConfig('host_style') . '/images/obj/' . $name;
	} else {
		return './style/' . w2PgetConfig('host_style') . '/images/' . $name;
	}
}

/**
 *	Workaround removed due to problems in Opera and other issues
 *	with IE6.
 *	Workaround to display png images with alpha-transparency in IE6.0
 *	@param string The name of the image
 *	@param string The image width
 *	@param string The image height
 *	@param string The alt text for the image
 */
function w2PshowImage($src, $wid = '', $hgt = '', $alt = '', $title = '', $module = null) {
	global $AppUI, $m;
	/*
	if (strpos( $src, '.png' ) > 0 && strpos( $_SERVER['HTTP_USER_AGENT'], 'MSIE 6.0' ) !== false) {
	return "<div style=\"height:{$hgt}px; width:{$wid}px; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='$src', sizingMethod='scale');\" ></div>";
	} else {
	*/
	if ($src == '') {
		return '';
	} elseif ($module) {
		$src = w2PfindImage($src, $module);
	} else {
		$src = w2PfindImage($src, $m);
	}

	if (!$alt && !$title) {
		$result = '';
	} elseif ($alt && $title) {
		$result = w2PtoolTip($alt, $title);
	} elseif ($alt && !$title) {
		$result = w2PtoolTip($m, $alt);
	} elseif (!$alt && $title) {
		$result = w2PtoolTip($m, $title);
	}
	$result .= '<img src="' . $src . '" alt="" ';
	if ($wid) {
		$result .= ' width="' . $wid . '"';
	}
	if ($hgt) {
		$result .= ' height="' . $hgt . '"';
	}
	$result .= ' border="0" />';
	if (!$alt && !$title) {
		//do nothing
	} elseif ($alt && $title) {
		$result .= w2PendTip();
	} elseif ($alt && !$title) {
		$result .= w2PendTip();
	} elseif (!$alt && $title) {
		$result .= w2PendTip();
	}

	return $result;
}

/**
 * function to return a default value if a variable is not set
 */
function defVal($var, $def) {
	return isset($var) ? $var : $def;
}

/**
 * Utility function to return a value from a named array or a specified default, and avoid poisoning the URL by denying:
 * 1) the use of spaces (for SQL and XSS injection)
 * 2) the use of <, ", [, ; and { (for XSS injection)
 */
function w2PgetParam(&$arr, $name, $def = null) {
	global $AppUI;
	if ((strpos($arr[$name], ' ') === false && strpos($arr[$name], '<') === false && strpos($arr[$name], '"') === false && strpos($arr[$name], '[') === false && strpos($arr[$name], ';') === false && strpos($arr[$name], '{') === false) || ($arr == $_POST)) {
		return isset($arr[$name]) ? $arr[$name] : $def;		
	} else {
		/*echo('<pre>');
		print_r(debug_backtrace());
		echo('</pre>');
		print_r($arr[$name]);die;*/
		//Hack attempt detected
		//return isset($arr[$name]) ? str_replace(' ','',$arr[$name]) : $def;
		$AppUI->setMsg('Poisoning attempt to the URL detected. Issue logged.', UI_MSG_ALERT);
		$AppUI->redirect('m=public&a=access_denied');
	}
}

/**
 * Alternative to protect from XSS attacks.
 */
function w2PgetCleanParam(&$arr, $name, $def = null) {
	$val = isset($arr[$name]) ? $arr[$name] : $def;
	if (empty($val)) {
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
			}
		}
	}
	return $val;
}

#
# add history entries for tracking changes
#

function addHistory($table, $id, $action = 'modify', $description = '', $project_id = 0) {
	global $AppUI;
	/*
	* TODO:
	* 1) description should be something like:
	* 		command(arg1, arg2...)
	*  The command should be as module_action
	*  for example:
	* 		forums_new('Forum Name', 'URL')
	*
	* This way, the history module will be able to display descriptions
	* using locale definitions:
	* 		"forums_new" -> "New forum '%s' was created" -> "Se ha creado un nuevo foro llamado '%s'"
	*
	* 2) project_id and module_id should be provided in order to filter history entries
	*
	*/
	if (!w2PgetConfig('log_changes')) {
		return;
	}
	$description = str_replace("'", "\'", $description);
	$q = new DBQuery;
	$q->addTable('modules');
	$q->addWhere('mod_name = \'History\' and mod_active = 1');
	$qid = $q->exec();

	if (!$qid || db_num_rows($qid) == 0) {
		$AppUI->setMsg('History module is not loaded, but your config file has requested that changes be logged.  You must either change the config file or install and activate the history module to log changes.', UI_MSG_ALERT);
		$q->clear();
		return;
	}

	$q->clear();
	$q->addTable('history');
	$q->addInsert('history_action', $action);
	$q->addInsert('history_item', $id);
	$q->addInsert('history_description', $description);
	$q->addInsert('history_user', $AppUI->user_id);
	$q->addInsert('history_date', 'now()', false, true);
	$q->addInsert('history_project', $project_id);
	$q->addInsert('history_table', $table);
	$q->exec();
	echo db_error();
	$q->clear();
}

##
## Looks up a value from the SYSVALS table
##
function w2PgetSysVal($title) {
	$q = new DBQuery;
	$q->addTable('sysvals');
	$q->addQuery('sysval_value_id, sysval_value');
	$q->addWhere('sysval_title = \'' . $title . '\'');
	$q->addOrder('sysval_value_id ASC');
	$rows = $q->loadList();
	$q->clear();

	$arr = array();
	// We use trim() to make sure a numeric that has spaces
	// is properly treated as a numeric
	$key_sort = SORT_NUMERIC;
	foreach ($rows as $key => $item) {
		if ($item) {
			$arr[trim($item['sysval_value_id'])] = trim($item['sysval_value']);
			if (!is_numeric(trim($item['sysval_value_id']))) {
				$key_sort = SORT_REGULAR;
			}
		}
	}
	ksort($arr, $key_sort);
	return $arr;
}

function w2PuserHasRole($name) {
	global $AppUI;
	$uid = $AppUI->user_id;
	$q = new DBQuery;
	$q->addTable('roles', 'r');
	$q->addTable('user_roles', 'ur');
	$q->addQuery('r.role_id');
	$q->addWhere('ur.user_id = ' . $uid . ' AND ur.role_id = r.role_id AND r.role_name = \'' . $name . '\'');
	return $q->loadResult();
}

function w2PformatDuration($x) {
	global $AppUI;
	$dur_day = floor($x / w2PgetConfig('daily_working_hours'));
	//$dur_hour = fmod($x, w2PgetConfig('daily_working_hours'));
	$dur_hour = $x - $dur_day * w2PgetConfig('daily_working_hours');
	$str = '';
	if ($dur_day > 1) {
		$str .= $dur_day . ' ' . $AppUI->_('days') . ' ';
	} elseif ($dur_day == 1) {
		$str .= $dur_day . ' ' . $AppUI->_('day') . ' ';
	}

	if ($dur_hour > 1) {
		$str .= $dur_hour . ' ' . $AppUI->_('hours');
	} elseif ($dur_hour > 0 and $dur_hour <= 1) {
		$str .= $dur_hour . ' ' . $AppUI->_('hour');
	}

	if ($str == '') {
		$str = $AppUI->_('n/a');
	}

	return $str;

}

/**
 */
function w2PsetMicroTime() {
	global $microTimeSet;
	list($usec, $sec) = explode(' ', microtime());
	$microTimeSet = (float)$usec + (float)$sec;
}

/**
 */
function w2PgetMicroDiff() {
	global $microTimeSet;
	$mt = $microTimeSet;
	w2PsetMicroTime();
	return sprintf('%.3f', $microTimeSet - $mt);
}

/**
 * Make text safe to output into double-quote enclosed attirbutes of an HTML tag
 */
function w2PformSafe($txt, $deslash = false) {
	global $locale_char_set;

	if (!$locale_char_set) {
		$locale_char_set = 'utf-8';
	}

	if (is_object($txt)) {
		foreach (get_object_vars($txt) as $k => $v) {
			if ($deslash) {
				$obj->$k = htmlspecialchars(stripslashes($v), ENT_COMPAT, $locale_char_set);
			} else {
				$obj->$k = htmlspecialchars($v, ENT_COMPAT, $locale_char_set);
			}
		}
	} elseif (is_array($txt)) {
		foreach ($txt as $k => $v) {
			if ($deslash) {
				$txt[$k] = htmlspecialchars(stripslashes($v), ENT_COMPAT, $locale_char_set);
			} else {
				$txt[$k] = htmlspecialchars($v, ENT_COMPAT, $locale_char_set);
			}
		}
	} else {
		if ($deslash) {
			$txt = htmlspecialchars(stripslashes($txt), ENT_COMPAT, $locale_char_set);
		} else {
			$txt = htmlspecialchars($txt, ENT_COMPAT, $locale_char_set);
		}
	}
	return $txt;
}

function convert2days($durn, $units) {
	switch ($units) {
		case 0:
		case 1:
			return $durn / w2PgetConfig('daily_working_hours');
			break;
		case 24:
			return $durn;
	}
}

function formatTime($uts) {
	global $AppUI;
	$date = new CDate();
	$date->setDate($uts, DATE_FORMAT_UNIXTIME);
	return $date->format($AppUI->getPref('SHDATEFORMAT'));
}

/**
 * This function is necessary because Windows likes to
 * write their own standards.  Nothing that depends on locales
 * can be trusted in Windows.
 */
function formatCurrency($number, $format) {
	global $AppUI, $locale_char_set;

	if (!$format) {
		$format = $AppUI->getPref('SHCURRFORMAT');
	}
	// If the requested locale doesn't work, don't fail,
	// revert to the system default.
	if ($locale_char_set != 'utf-8' || !setlocale(LC_MONETARY, $format . '.UTF8')) {
		if (!setlocale(LC_MONETARY, $format)) {
			setlocale(LC_MONETARY, '');
		}
	}

	// Technically this should be acheivable with the following, however
	// it seems that some versions of PHP will set this incorrectly
	// and you end up with everything using locale C.
	// setlocale(LC_MONETARY, $format . '.UTF8', $format, '');

	if (function_exists('money_format')) {
		return money_format('%i', $number);
	}

	// NOTE: This is called if money format doesn't exist.
	// Money_format only exists on non-windows 4.3.x sites.
	// This uses localeconv to get the information required
	// to format the money.  It tries to set reasonable defaults.
	$mondat = localeconv();
	if (!isset($mondat['int_frac_digits']) || $mondat['int_frac_digits'] > 100) {
		$mondat['int_frac_digits'] = 2;
	}
	if (!isset($mondat['int_curr_symbol'])) {
		$mondat['int_curr_symbol'] = '';
	}
	if (!isset($mondat['mon_decimal_point'])) {
		$mondat['mon_decimal_point'] = '.';
	}
	if (!isset($mondat['mon_thousands_sep'])) {
		$mondat['mon_thousands_sep'] = ',';
	}
	$numeric_portion = number_format(abs($number), $mondat['int_frac_digits'], $mondat['mon_decimal_point'], $mondat['mon_thousands_sep']);
	// Not sure, but most countries don't put the sign in if it is positive.
	$letter = 'p';
	$currency_prefix = '';
	$currency_suffix = '';
	$prefix = '';
	$suffix = '';
	if ($number < 0) {
		$sign = $mondat['negative_sign'];
		$letter = 'n';
		switch ($mondat['n_sign_posn']) {
			case 0:
				$prefix = '(';
				$suffix = ')';
				break;
			case 1:
				$prefix = $sign;
				break;
			case 2:
				$suffix = $sign;
				break;
			case 3:
				$currency_prefix = $sign;
				break;
			case 4:
				$currency_suffix = $sign;
				break;
		}
	}
	$currency .= $currency_prefix . $mondat['int_curr_symbol'] . $currency_suffix;
	$space = '';
	if ($mondat[$letter . '_sep_by_space']) {
		$space = ' ';
	}
	if ($mondat[$letter . '_cs_precedes']) {
		$result = $currency . $space . $numeric_portion;
	} else {
		$result = $numeric_portion . $space . $currency;
	}
	return $result;
}

function format_backtrace($bt, $file, $line, $msg) {
	echo '<pre>';
	echo 'ERROR: ' . $file . '(' . $line . ') : ' . $msg . "\n";
	echo 'Backtrace:' . "\n";
	foreach ($bt as $level => $frame) {
		echo $level . ' ' . $frame['file'] . ':' . $frame['line'] . ' ' . $frame['function'] . '(';
		$in = false;
		foreach ($frame['args'] as $arg) {
			if ($in) {
				echo ',';
			} else {
				$in = true;
			}
			echo var_export($arg, true);
		}
		echo ")\n";
	}
}

function dprint($file, $line, $level, $msg) {
	$max_level = 0;
	$max_level = (int)w2PgetConfig('debug');
	$display_debug = w2PgetConfig('display_debug', false);
	if ($level <= $max_level) {
		error_log($file . '(' . $line . '): ' . $msg);
		if ($display_debug) {
			echo $file . '(' . $line . '): ' . $msg . ' <br />';
		}
		if ($level == 0 && $max_level > 0 && version_compare(phpversion(), '4.3.0') >= 0) {
			format_backtrace(debug_backtrace(), $file, $line, $msg);
		}
	}
}

/**
 * Return a list of modules that are associated with tabs for this
 * page.  This can be used to find post handlers, for instance.
 */
function findTabModules($module, $file = null) {
	$modlist = array();
	if (!isset($_SESSION['all_tabs']) || !isset($_SESSION['all_tabs'][$module])) {
		return $modlist;
	}

	if (isset($file)) {
		if (isset($_SESSION['all_tabs'][$module][$file]) && is_array($_SESSION['all_tabs'][$module][$file])) {
			$tabs_array = &$_SESSION['all_tabs'][$module][$file];
		} else {
			return $modlist;
		}
	} else {
		$tabs_array = &$_SESSION['all_tabs'][$module];
	}
	foreach ($tabs_array as $tab) {
		if (isset($tab['module'])) {
			$modlist[] = $tab['module'];
		}
	}
	return array_unique($modlist);
}

/**
 * Return a list of modules that are associated with crumbs for this
 * page.  This can be used to find post handlers, for instance.
 */
function findCrumbModules($module, $file = null) {
	$modlist = array();
	if (!isset($_SESSION['all_crumbs']) || !isset($_SESSION['all_crumbs'][$module])) {
		return $modlist;
	}

	if (isset($file)) {
		if (isset($_SESSION['all_crumbs'][$module][$file]) && is_array($_SESSION['all_crumbs'][$module][$file])) {
			$crumbs_array = &$_SESSION['all_crumbs'][$module][$file];
		} else {
			return $modlist;
		}
	} else {
		$crumbs_array = &$_SESSION['all_crumbs'][$module];
	}
	foreach ($crumbs_array as $crumb) {
		if (isset($crumb['module'])) {
			$modlist[] = $crumb['module'];
		}
	}
	return array_unique($modlist);
}

/**
 * @return void
 * @param mixed $var
 * @param char $title
 * @desc Show an estructure (array/object) formatted
 */
function showFVar(&$var, $title = '') {
	echo '<h1>' . $title . '</h1>';
	echo '<pre>';
	print_r($var);
	echo '</pre>';
}

function getUsersArray() {
	return w2PgetUsersHashList();

}

function getUsersCombo($default_user_id = 0, $first_option = 'All users') {
	global $AppUI;

	$parsed = '<select name="user_id" class="text">';
	if ($first_option != '') {
		$parsed .= '<option value="0" ' . (!$default_user_id ? 'selected="selected"' : '') . '>' . $AppUI->_($first_option) . '</option>';
	}
	foreach (getUsersArray() as $user_id => $user) {
		$selected = $user_id == $default_user_id ? ' selected="selected"' : '';
		$parsed .= '<option value="' . $user_id . '"' . $selected . '>' . $user['contact_first_name'] . ' ' . $user['contact_last_name'] . '</option>';
	}
	$parsed .= '</select>';
	return $parsed;
}

/**
 * Function to format hours into useful numbers.
 * Supplied by GrahamJB.
 */
function formatHours($hours) {
	global $AppUI;

	$hours = (int)$hours;
	$working_hours = w2PgetConfig('daily_working_hours');

	if ($hours < $working_hours) {
		if ($hours == 1) {
			return '1 ' . $AppUI->_('hour');
		} else {
			return $hours . ' ' . $AppUI->_('hours');
		}
	}

	$hoursPart = $hours % $working_hours;
	$daysPart = (int)($hours / $working_hours);
	if ($hoursPart == 0) {
		if ($daysPart == 1) {
			return '1 ' . $AppUI->_('day');
		} else {
			return $daysPart . ' ' . $AppUI->_('days');
		}
	}

	if ($daysPart == 1) {
		return '1 ' . $AppUI->_('day') . ' ' . $hoursPart . ' ' . $AppUI->_('hr');
	} else {
		return $daysPart . ' ' . $AppUI->_('days') . ' ' . $hoursPart . ' ' . $AppUI->_('hr');
	}
}

/**
 * PHP doesn't come with a signum function
 */
function w2Psgn($x) {
	return $x ? ($x > 0 ? 1 : -1) : 0;
}

/**
 * This function is now deprecated and will be removed.
 * In the interim it now does nothing.
 */
function dpRealPath($file) {
	return $file;
}

/*
** Create the Required Fields (From Sysvals) JavaScript Code
** For instance implemented in projects and tasks addedit.php
** @param array required field array from SysVals
*/
function w2PrequiredFields($requiredFields) {
	global $AppUI, $m;
	$buffer = 'var foc=false;';

	if (!empty($requiredFields)) {
		foreach ($requiredFields as $rf => $comparator) {
			$buffer .= 'if (' . $rf . html_entity_decode($comparator, ENT_QUOTES) . ') {';
			$buffer .= 'msg += "\n' . $AppUI->_('required_field_' . $rf, UI_OUTPUT_JS) . '";';

			/* MSIE cannot handle the focus command for some disabled or hidden fields like the start/end date fields
			** Another workaround would be to check whether the field is disabled, 
			** but then one would for instance need to use end_date instead of project_end_date in the projects addedit site.
			** As this cannot be guaranteed since these fields are grabbed from a user-specifiable 
			** System Value it's IMHO more safe to disable the focus for MSIE.
			*/
			$r = strstr($rf, '.');
			$buffer .= 'if((foc==false) && (navigator.userAgent.indexOf(\'MSIE\')== -1)) {';
			$buffer .= 'f.' . substr($r, 1, strpos($r, '.', 1) - 1) . '.focus();';
			$buffer .= 'foc=true;}}';
		}
	}
	return $buffer;
}

/*
* Make function htmlspecialchar_decode for older PHP versions
*/
if (!function_exists('htmlspecialchars_decode')) {
	function htmlspecialchars_decode($str) {
		return strtr($str, array_flip(get_html_translation_table(HTML_SPECIALCHARS)));
	}
}

/**
 * Return the number of bytes represented by a PHP.INI value
 */
function w2PgetBytes($str) {
	$val = $str;
	if (preg_match('/^([0-9]+)([kmg])?$/i', $str, $match)) {
		if (!empty($match[2])) {
			switch (strtolower($match[2])) {
				case 'k':
					$val = $match[1] * 1024;
					break;
				case 'm':
					$val = $match[1] * 1024 * 1024;
					break;
				case 'g':
					$val = $match[1] * 1024 * 1024 * 1024;
					break;
			}
		}
	}
	return $val;
}

/**
 * Check for a memory limit, if we can't generate it then we fail.
 * @param int $min minimum amount of memory needed
 * @param bool $revert revert back to original config after test.
 * @return bool true if we have the minimum amount of RAM and if we can modify RAM
 */
function w2PcheckMem($min = 0, $revert = false) {
	// First of all check if we have the minimum memory requirement.
	$want = w2PgetBytes($GLOBALS['w2Pconfig']['reset_memory_limit']);
	$have = ini_get('memory_limit');
	// Try upping the memory limit based on our config
	ini_set('memory_limit', $GLOBALS['w2Pconfig']['reset_memory_limit']);
	$now = w2PgetBytes(ini_get('memory_limit'));
	// Revert, if necessary, back to the original after testing.
	if ($revert) {
		ini_set('memory_limit', $have);
	}
	if ($now < $want || $now < $min) {
		return false;
	} else {
		return true;
	}
}

/**
 * decode HTML entities
 */
function w2PHTMLDecode($txt) {
	global $locale_char_set;

	if (!$locale_char_set) {
		$locale_char_set = 'utf-8';
	}

	if (is_object($txt)) {
		foreach (get_object_vars($txt) as $k => $v) {
			$obj->$k = html_entity_decode($v, ENT_COMPAT);
		}
	} else
		if (is_array($txt)) {
			foreach ($txt as $k => $v) {
				$txt[$k] = html_entity_decode($v, ENT_COMPAT);
			}
		} else {
			$txt = html_entity_decode($txt, ENT_COMPAT);
		}
		return $txt;
}

function w2PtoolTip($header = '', $tip = '', $raw = false, $id = '') {
	global $AppUI;
	if ($raw) {
		$starttip = '<span id="' . $id . '" title="' . nl2br($AppUI->_($header)) . '::' . nl2br($AppUI->_($tip)) . '">';
	} else {
		$starttip = '<span id="' . $id . '" title="' . nl2br(ucwords(strtolower($AppUI->_($header)))) . '::' . nl2br(strtolower($AppUI->_($tip))) . '">';
	}
	return $starttip;
}

function w2PendTip() {
	$endtip = '</span>';
	return $endtip;
}

function w2Phtmlspecialchars($string) {
	$trans_tbl = array();
	$trans_tbl['"'] = '&quot;';
	$trans_tbl["'"] = '&#39;';
	$trans_tbl['\\'] = '&#92;';
	$trans_tbl['`'] = '&#96;';
	$trans_tbl['~'] = '&#126;';
	$trans_tbl['¡'] = '&iexcl;';
	$trans_tbl['¢'] = '&cent;';
	$trans_tbl['£'] = '&pound;';
	$trans_tbl['¤'] = '&curren;';
	$trans_tbl['¥'] = '&yen;';
	$trans_tbl['¦'] = '&brvbar;';
	$trans_tbl['§'] = '&sect;';
	$trans_tbl['¨'] = '&uml;';
	$trans_tbl['©'] = '&copy;';
	$trans_tbl['ª'] = '&ordf;';
	$trans_tbl['«'] = '&laquo;';
	$trans_tbl['¬'] = '&not;';
	$trans_tbl['­'] = '&shy;';
	$trans_tbl['®'] = '&reg;';
	$trans_tbl['¯'] = '&macr;';
	$trans_tbl['°'] = '&deg;';
	$trans_tbl['±'] = '&plusmn;';
	$trans_tbl['²'] = '&sup2;';
	$trans_tbl['³'] = '&sup3;';
	$trans_tbl['´'] = '&acute;';
	$trans_tbl['µ'] = '&micro;';
	$trans_tbl['¶'] = '&para;';
	$trans_tbl['·'] = '&middot;';
	$trans_tbl['¸'] = '&cedil;';
	$trans_tbl['¹'] = '&sup1;';
	$trans_tbl['º'] = '&ordm;';
	$trans_tbl['»'] = '&raquo;';
	$trans_tbl['¼'] = '&frac14;';
	$trans_tbl['½'] = '&frac12;';
	$trans_tbl['¾'] = '&frac34;';
	$trans_tbl['¿'] = '&iquest;';
	$trans_tbl['À'] = '&Agrave;';
	$trans_tbl['Á'] = '&Aacute;';
	$trans_tbl['Â'] = '&Acirc;';
	$trans_tbl['Ã'] = '&Atilde;';
	$trans_tbl['Ä'] = '&Auml;';
	$trans_tbl['Å'] = '&Aring;';
	$trans_tbl['Æ'] = '&AElig;';
	$trans_tbl['Ç'] = '&Ccedil;';
	$trans_tbl['È'] = '&Egrave;';
	$trans_tbl['É'] = '&Eacute;';
	$trans_tbl['Ê'] = '&Ecirc;';
	$trans_tbl['Ë'] = '&Euml;';
	$trans_tbl['Ì'] = '&Igrave;';
	$trans_tbl['Í'] = '&Iacute;';
	$trans_tbl['Î'] = '&Icirc;';
	$trans_tbl['Ï'] = '&Iuml;';
	$trans_tbl['Ð'] = '&ETH;';
	$trans_tbl['Ñ'] = '&Ntilde;';
	$trans_tbl['Ò'] = '&Ograve;';
	$trans_tbl['Ó'] = '&Oacute;';
	$trans_tbl['Ô'] = '&Ocirc;';
	$trans_tbl['Õ'] = '&Otilde;';
	$trans_tbl['Ö'] = '&Ouml;';
	$trans_tbl['×'] = '&times;';
	$trans_tbl['Ø'] = '&Oslash;';
	$trans_tbl['Ù'] = '&Ugrave;';
	$trans_tbl['Ú'] = '&Uacute;';
	$trans_tbl['Û'] = '&Ucirc;';
	$trans_tbl['Ü'] = '&Uuml;';
	$trans_tbl['Ý'] = '&Yacute;';
	$trans_tbl['Þ'] = '&THORN;';
	$trans_tbl['ß'] = '&szlig;';
	$trans_tbl['ß'] = '&szlig;';
	$trans_tbl['à'] = '&agrave;';
	$trans_tbl['á'] = '&aacute;';
	$trans_tbl['â'] = '&acirc;';
	$trans_tbl['ã'] = '&atilde;';
	$trans_tbl['ä'] = '&auml;';
	$trans_tbl['å'] = '&aring;';
	$trans_tbl['æ'] = '&aelig;';
	$trans_tbl['ç'] = '&ccedil;';
	$trans_tbl['è'] = '&egrave;';
	$trans_tbl['é'] = '&eacute;';
	$trans_tbl['ê'] = '&ecirc;';
	$trans_tbl['ë'] = '&euml;';
	$trans_tbl['ì'] = '&igrave;';
	$trans_tbl['í'] = '&iacute;';
	$trans_tbl['î'] = '&icirc;';
	$trans_tbl['ï'] = '&iuml;';
	$trans_tbl['ð'] = '&eth;';
	$trans_tbl['ñ'] = '&ntilde;';
	$trans_tbl['ò'] = '&ograve;';
	$trans_tbl['ó'] = '&oacute;';
	$trans_tbl['ô'] = '&ocirc;';
	$trans_tbl['õ'] = '&otilde;';
	$trans_tbl['ö'] = '&ouml;';
	$trans_tbl['÷'] = '&divide;';
	$trans_tbl['ø'] = '&oslash;';
	$trans_tbl['ù'] = '&ugrave;';
	$trans_tbl['ú'] = '&uacute;';
	$trans_tbl['û'] = '&ucirc;';
	$trans_tbl['ü'] = '&uuml;';
	$trans_tbl['ý'] = '&yacute;';
	$trans_tbl['þ'] = '&thorn;';
	$trans_tbl['ÿ'] = '&yuml;';
	$trans_tbl['Œ'] = '&OElig;';
	$trans_tbl['œ'] = '&oelig;';
	$trans_tbl['Š'] = '&Scaron;';
	$trans_tbl['š'] = '&scaron;';
	$trans_tbl['Ÿ'] = '&Yuml;';
	$trans_tbl['ƒ'] = '&fnof;';
	$trans_tbl['ˆ'] = '&circ;';
	$trans_tbl['˜'] = '&tilde;';
	$trans_tbl['Α'] = '&Alpha;';
	$trans_tbl['Β'] = '&Beta;';
	$trans_tbl['Γ'] = '&Gamma;';
	$trans_tbl['Δ'] = '&Delta;';
	$trans_tbl['Ε'] = '&Epsilon;';
	$trans_tbl['Ζ'] = '&Zeta;';
	$trans_tbl['Η'] = '&Eta;';
	$trans_tbl['Θ'] = '&Theta;';
	$trans_tbl['Ι'] = '&Iota;';
	$trans_tbl['Κ'] = '&Kappa;';
	$trans_tbl['Λ'] = '&Lambda;';
	$trans_tbl['Μ'] = '&Mu;';
	$trans_tbl['Ν'] = '&Nu;';
	$trans_tbl['Ξ'] = '&Xi;';
	$trans_tbl['Ο'] = '&Omicron;';
	$trans_tbl['Π'] = '&Pi;';
	$trans_tbl['Ρ'] = '&Rho;';
	$trans_tbl['Σ'] = '&Sigma;';
	$trans_tbl['Τ'] = '&Tau;';
	$trans_tbl['Υ'] = '&Upsilon;';
	$trans_tbl['Φ'] = '&Phi;';
	$trans_tbl['Χ'] = '&Chi;';
	$trans_tbl['Ψ'] = '&Psi;';
	$trans_tbl['Ω'] = '&Omega;';
	$trans_tbl['α'] = '&alpha;';
	$trans_tbl['β'] = '&beta;';
	$trans_tbl['γ'] = '&gamma;';
	$trans_tbl['δ'] = '&delta;';
	$trans_tbl['ε'] = '&epsilon;';
	$trans_tbl['ζ'] = '&zeta;';
	$trans_tbl['η'] = '&eta;';
	$trans_tbl['θ'] = '&theta;';
	$trans_tbl['ι'] = '&iota;';
	$trans_tbl['κ'] = '&kappa;';
	$trans_tbl['λ'] = '&lambda;';
	$trans_tbl['μ'] = '&mu;';
	$trans_tbl['ν'] = '&nu;';
	$trans_tbl['ξ'] = '&xi;';
	$trans_tbl['ο'] = '&omicron;';
	$trans_tbl['π'] = '&pi;';
	$trans_tbl['ρ'] = '&rho;';
	$trans_tbl['ς'] = '&sigmaf;';
	$trans_tbl['σ'] = '&sigma;';
	$trans_tbl['τ'] = '&tau;';
	$trans_tbl['υ'] = '&upsilon;';
	$trans_tbl['φ'] = '&phi;';
	$trans_tbl['χ'] = '&chi;';
	$trans_tbl['ψ'] = '&psi;';
	$trans_tbl['ω'] = '&omega;';
	$trans_tbl['‌'] = '&zwnj;';
	$trans_tbl['‍'] = '&zwj;';
	$trans_tbl['‎'] = '&lrm;';
	$trans_tbl['‏'] = '&rlm;';
	$trans_tbl['–'] = '&ndash;';
	$trans_tbl['—'] = '&mdash;';
	$trans_tbl['‘'] = '&lsquo;';
	$trans_tbl['’'] = '&rsquo;';
	$trans_tbl['‚'] = '&sbquo;';
	$trans_tbl['“'] = '&ldquo;';
	$trans_tbl['”'] = '&rdquo;';
	$trans_tbl['„'] = '&bdquo;';
	$trans_tbl['†'] = '&dagger;';
	$trans_tbl['‡'] = '&Dagger;';
	$trans_tbl['•'] = '&bull;';
	$trans_tbl['…'] = '&hellip;';
	$trans_tbl['‰'] = '&permil;';
	$trans_tbl['´'] = '&prime;';
	$trans_tbl['″'] = '&Prime;';
	$trans_tbl['‹'] = '&lsaquo;';
	$trans_tbl['›'] = '&rsaquo;';
	$trans_tbl['‾'] = '&oline;';
	$trans_tbl['⁄'] = '&frasl;';
	$trans_tbl['€'] = '&euro;';
	$trans_tbl['™'] = '&trade;';
	$trans_tbl['←'] = '&larr;';
	$trans_tbl['↑'] = '&uarr;';
	$trans_tbl['→'] = '&rarr;';
	$trans_tbl['↓'] = '&darr;';
	$trans_tbl['↔'] = '&harr;';
	$trans_tbl['∂'] = '&part;';
	$trans_tbl['∏'] = '&prod;';
	$trans_tbl['∑'] = '&sum;';
	$trans_tbl['−'] = '&minus;';
	$trans_tbl['√'] = '&radic;';
	$trans_tbl['∞'] = '&infin;';
	$trans_tbl['∩'] = '&cap;';
	$trans_tbl['∫'] = '&int;';
	$trans_tbl['≈'] = '&asymp;';
	$trans_tbl['≠'] = '&ne;';
	$trans_tbl['≡'] = '&equiv;';
	$trans_tbl['≤'] = '&le;';
	$trans_tbl['≥'] = '&ge;';
	$trans_tbl['◊'] = '&loz;';
	$trans_tbl['♠'] = '&spades;';
	$trans_tbl['♣'] = '&clubs;';
	$trans_tbl['♥'] = '&hearts;';
	$trans_tbl['♦'] = '&diams;';
	return strtr($string, $trans_tbl);
}

function w2Phtml_entity_decode($string) {
	// replace numeric entities
	$string = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $string);
	$string = preg_replace('~&#([0-9]+);~e', 'chr(\\1)', $string);
	// replace literal entities
	$trans_tbl = get_html_translation_table(HTML_ENTITIES);
	$trans_tbl = array_flip($trans_tbl);
	$trans_tbl['&iexcl;'] = '¡';
	$trans_tbl['&cent;'] = '¢';
	$trans_tbl['&pound;'] = '£';
	$trans_tbl['&curren;'] = '¤';
	$trans_tbl['&yen;'] = '¥';
	$trans_tbl['&brvbar;'] = '¦';
	$trans_tbl['&sect;'] = '§';
	$trans_tbl['&uml;'] = '¨';
	$trans_tbl['&copy;'] = '©';
	$trans_tbl['&ordf;'] = 'ª';
	$trans_tbl['&laquo;'] = '«';
	$trans_tbl['&not;'] = '¬';
	$trans_tbl['&shy;'] = '­';
	$trans_tbl['&reg;'] = '®';
	$trans_tbl['&macr;'] = '¯';
	$trans_tbl['&deg;'] = '°';
	$trans_tbl['&plusmn;'] = '±';
	$trans_tbl['&sup2;'] = '²';
	$trans_tbl['&sup3;'] = '³';
	$trans_tbl['&acute;'] = '´';
	$trans_tbl['&micro;'] = 'µ';
	$trans_tbl['&para;'] = '¶';
	$trans_tbl['&middot;'] = '·';
	$trans_tbl['&cedil;'] = '¸';
	$trans_tbl['&sup1;'] = '¹';
	$trans_tbl['&ordm;'] = 'º';
	$trans_tbl['&raquo;'] = '»';
	$trans_tbl['&frac14;'] = '¼';
	$trans_tbl['&frac12;'] = '½';
	$trans_tbl['&frac34;'] = '¾';
	$trans_tbl['&iquest;'] = '¿';
	$trans_tbl['&Agrave;'] = 'À';
	$trans_tbl['&Aacute;'] = 'Á';
	$trans_tbl['&Acirc;'] = 'Â';
	$trans_tbl['&Atilde;'] = 'Ã';
	$trans_tbl['&Auml;'] = 'Ä';
	$trans_tbl['&Aring;'] = 'Å';
	$trans_tbl['&AElig;'] = 'Æ';
	$trans_tbl['&Ccedil;'] = 'Ç';
	$trans_tbl['&Egrave;'] = 'È';
	$trans_tbl['&Eacute;'] = 'É';
	$trans_tbl['&Ecirc;'] = 'Ê';
	$trans_tbl['&Euml;'] = 'Ë';
	$trans_tbl['&Igrave;'] = 'Ì';
	$trans_tbl['&Iacute;'] = 'Í';
	$trans_tbl['&Icirc;'] = 'Î';
	$trans_tbl['&Iuml;'] = 'Ï';
	$trans_tbl['&ETH;'] = 'Ð';
	$trans_tbl['&Ntilde;'] = 'Ñ';
	$trans_tbl['&Ograve;'] = 'Ò';
	$trans_tbl['&Oacute;'] = 'Ó';
	$trans_tbl['&Ocirc;'] = 'Ô';
	$trans_tbl['&Otilde;'] = 'Õ';
	$trans_tbl['&Ouml;'] = 'Ö';
	$trans_tbl['&times;'] = '×';
	$trans_tbl['&Oslash;'] = 'Ø';
	$trans_tbl['&Ugrave;'] = 'Ù';
	$trans_tbl['&Uacute;'] = 'Ú';
	$trans_tbl['&Ucirc;'] = 'Û';
	$trans_tbl['&Uuml;'] = 'Ü';
	$trans_tbl['&Yacute;'] = 'Ý';
	$trans_tbl['&THORN;'] = 'Þ';
	$trans_tbl['&szlig;'] = 'ß';
	$trans_tbl['&szlig;'] = 'ß';
	$trans_tbl['&agrave;'] = 'à';
	$trans_tbl['&aacute;'] = 'á';
	$trans_tbl['&acirc;'] = 'â';
	$trans_tbl['&atilde;'] = 'ã';
	$trans_tbl['&auml;'] = 'ä';
	$trans_tbl['&aring;'] = 'å';
	$trans_tbl['&aelig;'] = 'æ';
	$trans_tbl['&ccedil;'] = 'ç';
	$trans_tbl['&egrave;'] = 'è';
	$trans_tbl['&eacute;'] = 'é';
	$trans_tbl['&ecirc;'] = 'ê';
	$trans_tbl['&euml;'] = 'ë';
	$trans_tbl['&igrave;'] = 'ì';
	$trans_tbl['&iacute;'] = 'í';
	$trans_tbl['&icirc;'] = 'î';
	$trans_tbl['&iuml;'] = 'ï';
	$trans_tbl['&eth;'] = 'ð';
	$trans_tbl['&ntilde;'] = 'ñ';
	$trans_tbl['&ograve;'] = 'ò';
	$trans_tbl['&oacute;'] = 'ó';
	$trans_tbl['&ocirc;'] = 'ô';
	$trans_tbl['&otilde;'] = 'õ';
	$trans_tbl['&ouml;'] = 'ö';
	$trans_tbl['&divide;'] = '÷';
	$trans_tbl['&oslash;'] = 'ø';
	$trans_tbl['&ugrave;'] = 'ù';
	$trans_tbl['&uacute;'] = 'ú';
	$trans_tbl['&ucirc;'] = 'û';
	$trans_tbl['&uuml;'] = 'ü';
	$trans_tbl['&yacute;'] = 'ý';
	$trans_tbl['&thorn;'] = 'þ';
	$trans_tbl['&yuml;'] = 'ÿ';
	$trans_tbl['&OElig;'] = 'Œ';
	$trans_tbl['&oelig;'] = 'œ';
	$trans_tbl['&Scaron;'] = 'Š';
	$trans_tbl['&scaron;'] = 'š';
	$trans_tbl['&Yuml;'] = 'Ÿ';
	$trans_tbl['&fnof;'] = 'ƒ';
	$trans_tbl['&circ;'] = 'ˆ';
	$trans_tbl['&tilde;'] = '˜';
	$trans_tbl['&Alpha;'] = 'Α';
	$trans_tbl['&Beta;'] = 'Β';
	$trans_tbl['&Gamma;'] = 'Γ';
	$trans_tbl['&Delta;'] = 'Δ';
	$trans_tbl['&Epsilon;'] = 'Ε';
	$trans_tbl['&Zeta;'] = 'Ζ';
	$trans_tbl['&Eta;'] = 'Η';
	$trans_tbl['&Theta;'] = 'Θ';
	$trans_tbl['&Iota;'] = 'Ι';
	$trans_tbl['&Kappa;'] = 'Κ';
	$trans_tbl['&Lambda;'] = 'Λ';
	$trans_tbl['&Mu;'] = 'Μ';
	$trans_tbl['&Nu;'] = 'Ν';
	$trans_tbl['&Xi;'] = 'Ξ';
	$trans_tbl['&Omicron;'] = 'Ο';
	$trans_tbl['&Pi;'] = 'Π';
	$trans_tbl['&Rho;'] = 'Ρ';
	$trans_tbl['&Sigma;'] = 'Σ';
	$trans_tbl['&Tau;'] = 'Τ';
	$trans_tbl['&Upsilon;'] = 'Υ';
	$trans_tbl['&Phi;'] = 'Φ';
	$trans_tbl['&Chi;'] = 'Χ';
	$trans_tbl['&Psi;'] = 'Ψ';
	$trans_tbl['&Omega;'] = 'Ω';
	$trans_tbl['&alpha;'] = 'α';
	$trans_tbl['&beta;'] = 'β';
	$trans_tbl['&gamma;'] = 'γ';
	$trans_tbl['&delta;'] = 'δ';
	$trans_tbl['&epsilon;'] = 'ε';
	$trans_tbl['&zeta;'] = 'ζ';
	$trans_tbl['&eta;'] = 'η';
	$trans_tbl['&theta;'] = 'θ';
	$trans_tbl['&iota;'] = 'ι';
	$trans_tbl['&kappa;'] = 'κ';
	$trans_tbl['&lambda;'] = 'λ';
	$trans_tbl['&mu;'] = 'μ';
	$trans_tbl['&nu;'] = 'ν';
	$trans_tbl['&xi;'] = 'ξ';
	$trans_tbl['&omicron;'] = 'ο';
	$trans_tbl['&pi;'] = 'π';
	$trans_tbl['&rho;'] = 'ρ';
	$trans_tbl['&sigmaf;'] = 'ς';
	$trans_tbl['&sigma;'] = 'σ';
	$trans_tbl['&tau;'] = 'τ';
	$trans_tbl['&upsilon;'] = 'υ';
	$trans_tbl['&phi;'] = 'φ';
	$trans_tbl['&chi;'] = 'χ';
	$trans_tbl['&psi;'] = 'ψ';
	$trans_tbl['&omega;'] = 'ω';
	$trans_tbl['&zwnj;'] = '‌';
	$trans_tbl['&zwj;'] = '‍';
	$trans_tbl['&lrm;'] = '‎';
	$trans_tbl['&rlm;'] = '‏';
	$trans_tbl['&ndash;'] = '–';
	$trans_tbl['&mdash;'] = '—';
	$trans_tbl['&lsquo;'] = '‘';
	$trans_tbl['&rsquo;'] = '’';
	$trans_tbl['&sbquo;'] = '‚';
	$trans_tbl['&ldquo;'] = '“';
	$trans_tbl['&rdquo;'] = '”';
	$trans_tbl['&bdquo;'] = '„';
	$trans_tbl['&dagger;'] = '†';
	$trans_tbl['&Dagger;'] = '‡';
	$trans_tbl['&bull;'] = '•';
	$trans_tbl['&hellip;'] = '…';
	$trans_tbl['&permil;'] = '‰';
	$trans_tbl['&prime;'] = "′";
	$trans_tbl['&Prime;'] = '″';
	$trans_tbl['&lsaquo;'] = '‹';
	$trans_tbl['&rsaquo;'] = '›';
	$trans_tbl['&oline;'] = '‾';
	$trans_tbl['&frasl;'] = '⁄';
	$trans_tbl['&euro;'] = '€';
	$trans_tbl['&trade;'] = '™';
	$trans_tbl['&larr;'] = '←';
	$trans_tbl['&uarr;'] = '↑';
	$trans_tbl['&rarr;'] = '→';
	$trans_tbl['&darr;'] = '↓';
	$trans_tbl['&harr;'] = '↔';
	$trans_tbl['&part;'] = '∂';
	$trans_tbl['&prod;'] = '∏';
	$trans_tbl['&sum;'] = '∑';
	$trans_tbl['&minus;'] = '−';
	$trans_tbl['&radic;'] = '√';
	$trans_tbl['&infin;'] = '∞';
	$trans_tbl['&cap;'] = '∩';
	$trans_tbl['&int;'] = '∫';
	$trans_tbl['&asymp;'] = '≈';
	$trans_tbl['&ne;'] = '≠';
	$trans_tbl['&equiv;'] = '≡';
	$trans_tbl['&le;'] = '≤';
	$trans_tbl['&ge;'] = '≥';
	$trans_tbl['&loz;'] = '◊';
	$trans_tbl['&spades;'] = '♠';
	$trans_tbl['&clubs;'] = '♣';
	$trans_tbl['&hearts;'] = '♥';
	$trans_tbl['&diams;'] = '♦';
	$trans_tbl['&nbsp;'] = ' ';

	return strtr($string, $trans_tbl);
}

/**
 *    Corrects the charset name if needed be
 *
 *    @param string $charset the charset string to be checked
 *    @access public
 */
function w2PcheckCharset($charset) {
	if (!(strpos($charset, 'iso') === false)) {
		if (strpos($charset, 'iso-') === false) {
			return str_replace('iso', 'iso-', $charset);
		}
	}
	return $charset;
}

/**
 *    Write debugging to debug.log file
 *
 *    @param string $s the debug message
 *    @param string $t the header of the message
 *    @param string $f the script filename
 *    @param string $l the script line 
 *    @access public
 */
$debug_file = W2P_BASE_DIR . '/files/debug.log';
function w2PwriteDebug($s, $t = '', $f = '?', $l = '?') {
	global $debug, $debug_file;
	if ($debug && ($fp = fopen($debug_file, "at"))) {
		fputs($fp, "Debug message from file [$f], line [$l], at: " . strftime('%H:%S'));
		if ($t) {
			fputs($fp, "\n * * $t * *\n");
		}
		fputs($fp, "\n$s\n\n");
		fclose($fp);
	}
}
?>