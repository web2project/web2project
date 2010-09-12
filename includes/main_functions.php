<?php /* $Id$ $URL$ */
##
## Global General Purpose Functions
##
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

define('SECONDS_PER_DAY', 86400);

require_once W2P_BASE_DIR . '/includes/backcompat_functions.php';
require_once W2P_BASE_DIR . '/includes/deprecated_functions.php';

/*
 * TODO: Personally, I'm already hating this autoloader... while it's great in
 * concept, we don't have anything that resembles a real class naming convention
 * so this ends up being nasty and getting nastier.  Hopefully, we can clean
 * these things up for v2.0
 */
spl_autoload_register('w2p_autoload');

function w2p_autoload($class_name) {
    global $AppUI;
    $name = $class_name;

    if (false !== strpos($name, 'w2p_')) {
        $name = str_replace('_', DIRECTORY_SEPARATOR, $name);
        $classpath = W2P_BASE_DIR . '/classes/'.$name.'.class.php';
        require_once $classpath;
        return;
    }

    $name = strtolower($class_name);
    switch ($name) {
        case 'libmail':
            require_once W2P_BASE_DIR . '/classes/mail.class.php';
            break;
        case 'w2pacl':
            require_once W2P_BASE_DIR . '/classes/permissions.class.php';
            break;
        case 'cappui':
            require_once W2P_BASE_DIR . '/classes/ui.class.php';
            break;

        /*
         * The following are all wirings for module classes that don't follow
         * our naming conventions.
         */
        case 'cevent':
            require_once W2P_BASE_DIR.'/modules/calendar/calendar.class.php';
            break;
        case 'cuser':
            require_once W2P_BASE_DIR.'/modules/admin/admin.class.php';
            break;
        case 'cfilefolder':
            require_once W2P_BASE_DIR.'/modules/files/filefolder.class.php';
            break;
        case 'ctasklog':
            require_once W2P_BASE_DIR.'/modules/tasks/tasklogs.class.php';
            break;
        case 'cforummessage':
            require_once W2P_BASE_DIR.'/modules/forums/forummessage.class.php';
            break;
        case 'ctasklog':
            require_once W2P_BASE_DIR.'/modules/tasks/tasklogs.class.php';
            break;
        case 'cprojectdesigneroptions':
            require_once W2P_BASE_DIR.'/modules/projectdesigner/projectdesigner.class.php';
            break;
        case 'csyskey':
            require_once W2P_BASE_DIR.'/modules/system/syskeys/syskeys.class.php';
            break;
        case 'cprojectdesigneroptions':
            require_once W2P_BASE_DIR.'/modules/projectdesigner/projectdesigner.class.php';
            break;
        case 'cprojectdesigneroptions':
            require_once W2P_BASE_DIR.'/modules/projectdesigner/projectdesigner.class.php';
            break;

        default:
            if (file_exists(W2P_BASE_DIR.'/classes/'.$name.'.class.php')) {
                require_once W2P_BASE_DIR.'/classes/'.$name.'.class.php';
                return;
            }

            if ($name[0] == 'c') {
                $name = substr($name, 1);
                if (substr($name, -1) == 'y') {
                    $name = substr($name, 0, -1).'ies';
                } elseif (in_array($name, array('system'))) {
                    //do nothing
                } else {
                    $name .= 's';
                }
            }
            if (file_exists(W2P_BASE_DIR.'/modules/'.$name.'/'.$name.'.class.php')) {
                require_once W2P_BASE_DIR.'/modules/'.$name.'/'.$name.'.class.php';
                return;
            }

            $name = substr($name, 0, -1);
            if (file_exists(W2P_BASE_DIR.'/modules/'.$name.'/'.$name.'.class.php')) {
                require_once W2P_BASE_DIR.'/modules/'.$name.'/'.$name.'.class.php';
                return;
            }
            break;
    }
}

/*
*	Authenticator Factory
*
*/

function &getAuth($auth_mode) {
	switch ($auth_mode) {
		case 'ldap':
			$auth = new w2p_Authenticators_LDAP();
			return $auth;
			break;
		case 'pn':
			$auth = new w2p_Authenticators_PostNuke();
			return $auth;
			break;
		default:
			$auth = new w2p_Authenticators_SQL();
			return $auth;
			break;
	}
}

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
		$list = isset($children[$pt]) ? $children[$pt] : array();
		array_push($list, $v);
		$children[$pt] = $list;
	}
	$list = tree_recurse($arr[0][2], '', array(), $children);
	return arraySelect($list, $select_name, $select_attribs, $selected, $translate);
}

function tree_recurse($id, $indent, $list, $children) {
	if (isset($children[$id])) {
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
	if (isset($w2Pconfig[$key])) {
		return $w2Pconfig[$key];
	} else {
		return $default;
	}
}

function w2PgetUsername($username) {
	return CContact::getContactByUsername($username);
}

function w2PgetUsernameFromID($userId) {
	return CContact::getContactByUserid($userId);
}

function w2PgetUsers() {
	global $AppUI;

	$q = new DBQuery;
	$q->addTable('users');
	$q->addQuery('user_id, concat_ws(\' \', contact_first_name, contact_last_name) as name');
	$q->addJoin('contacts', 'con', 'con.contact_id = user_contact', 'inner');
	$q->addOrder('contact_first_name,contact_last_name');

	$obj = new CCompany();
	$companies = $obj->getAllowedSQL($AppUI->user_id, 'company_id');
	$q->addJoin('companies', 'com', 'company_id = contact_company');
	if ($companies) {
		$q->addWhere('(' . implode(' OR ', $companies) . ' OR contact_company=\'\' OR contact_company IS NULL OR contact_company = 0)');
	}

	if ($AppUI->isActiveModule('departments')) {
		$dpt = new CDepartment();
		$depts = $dpt->getAllowedSQL($AppUI->user_id, 'dept_id');
		$q->addJoin('departments', 'dep', 'dept_id = contact_department');
		if ($depts) {
			$q->addWhere('(' . implode(' OR ', $depts) . ' OR contact_department=0)');
		}
	}

	return $q->loadHashList();
}

function w2PgetUsersList($stub = null, $where = null, $orderby = 'contact_first_name, contact_last_name') {
	global $AppUI;
	$q = new DBQuery;
	$q->addTable('users');
	$q->addQuery('DISTINCT(user_id), user_username, contact_last_name, contact_first_name,
		 company_name, contact_company, dept_id, dept_name, CONCAT(contact_first_name,\' \',contact_last_name) contact_name, user_type');
	$q->addJoin('contacts', 'con', 'con.contact_id = user_contact', 'inner');
    $q->leftJoin('contacts_methods', 'cm', 'cm.contact_id = con.contact_id');
    $q->addWhere("cm.method_name = 'email_primary'");
    $q->addQuery('cm.method_value AS contact_email');
	if ($stub) {
		$q->addWhere('(UPPER(user_username) LIKE \'' . $stub . '%\' or UPPER(contact_first_name) LIKE \'' . $stub . '%\' OR UPPER(contact_last_name) LIKE \'' . $stub . '%\')');
	} elseif ($where) {
		$where = $q->quote('%' . $where . '%');
		$q->addWhere('(UPPER(user_username) LIKE ' . $where . ' OR UPPER(contact_first_name) LIKE ' . $where . ' OR UPPER(contact_last_name) LIKE ' . $where . ')');
	}

	$q->addGroup('user_id');
	$q->addOrder($orderby);

	// get CCompany() to filter by company
	$obj = new CCompany();
	$companies = $obj->getAllowedSQL($AppUI->user_id, 'company_id');
	$q->addJoin('companies', 'com', 'company_id = contact_company');
	if ($companies) {
		$q->addWhere('(' . implode(' OR ', $companies) . ' OR contact_company=\'\' OR contact_company IS NULL OR contact_company = 0)');
	}
	$dpt = new CDepartment();
	$depts = $dpt->getAllowedSQL($AppUI->user_id, 'dept_id');
	$q->addJoin('departments', 'dep', 'dept_id = contact_department');
	if ($depts) {
		$q->addWhere('(' . implode(' OR ', $depts) . ' OR contact_department=0)');
	}

	return $q->loadList();
}

function w2PgetUsersHashList($stub = null, $where = null, $orderby = 'contact_first_name, contact_last_name') {
	global $AppUI;
	$q = new DBQuery;
	$q->addTable('users');
	$q->addQuery('DISTINCT(user_id), user_username, contact_last_name, contact_first_name,
		 company_name, contact_company, dept_id, dept_name, CONCAT(contact_first_name,\' \',contact_last_name) contact_name, user_type');
	$q->addJoin('contacts', 'con', 'con.contact_id = user_contact', 'inner');
	if ($stub) {
		$q->addWhere('(UPPER(user_username) LIKE \'' . $stub . '%\' or UPPER(contact_first_name) LIKE \'' . $stub . '%\' OR UPPER(contact_last_name) LIKE \'' . $stub . '%\')');
	} elseif ($where) {
		$where = $q->quote('%' . $where . '%');
		$q->addWhere('(UPPER(user_username) LIKE ' . $where . ' OR UPPER(contact_first_name) LIKE ' . $where . ' OR UPPER(contact_last_name) LIKE ' . $where . ')');
	}
    $q->leftJoin('contacts_methods', 'cm', 'cm.contact_id = con.contact_id');
    $q->addWhere("cm.method_name = 'email_primary'");
    $q->addQuery('cm.method_value AS contact_email');

	$q->addGroup('user_id');
	$q->addOrder($orderby);

	// get CCompany() to filter by company
	$obj = new CCompany();
	$companies = $obj->getAllowedSQL($AppUI->user_id, 'company_id');
	$q->addJoin('companies', 'com', 'company_id = contact_company');
	if ($companies) {
		$q->addWhere('(' . implode(' OR ', $companies) . ' OR contact_company=\'\' OR contact_company IS NULL OR contact_company = 0)');
	}
	$dpt = new CDepartment();
	$depts = $dpt->getAllowedSQL($AppUI->user_id, 'dept_id');
	$q->addJoin('departments', 'dep', 'dept_id = contact_department');
	if ($depts) {
		$q->addWhere('(' . implode(' OR ', $depts) . ' OR contact_department=0)');
	}

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
	} elseif (file_exists(W2P_BASE_DIR . '/style/web2project/images/obj/' . $name)) {
		return './style/web2project/images/obj/' . $name;
	} else {
		return './style/web2project/images/' . $name;
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

// ****************************************************************************
// Page numbering variables
// Pablo Roca (pabloroca@Xmvps.org) (Remove the X)
// 19 August 2003
//
// $tab             - file category
// $page            - actual page to show
// $xpg_pagesize    - max rows per page
// $xpg_min         - initial record in the SELECT LIMIT
// $xpg_totalrecs   - total rows selected
// $xpg_sqlrecs     - total rows from SELECT LIMIT
// $xpg_total_pages - total pages
// $xpg_next_page   - next pagenumber
// $xpg_prev_page   - previous pagenumber
// $xpg_break       - stop showing page numbered list?
// $xpg_sqlcount    - SELECT for the COUNT total
// $xpg_sqlquery    - SELECT for the SELECT LIMIT
// $xpg_result      - pointer to results from SELECT LIMIT

function buildPaginationNav($AppUI, $m, $tab, $xpg_totalrecs, $xpg_pagesize, $page) {
  $xpg_total_pages = ($xpg_totalrecs > $xpg_pagesize) ? ceil($xpg_totalrecs / $xpg_pagesize) : 0;

  $xpg_break = false;
  $xpg_prev_page = $xpg_next_page = 0;

  $s = '<table width="100%" cellspacing="0" cellpadding="0" border="0"><tr>';

  if ($xpg_totalrecs > $xpg_pagesize) {
    $xpg_prev_page = $page - 1;
    $xpg_next_page = $page + 1;
    // left buttoms
    if ($xpg_prev_page > 0) {
      $s .= '<td align="left" width="15%"><a href="./index.php?m=' . $m . '&amp;tab=' . $tab . '&amp;page=1"><img src="' . w2PfindImage('navfirst.gif') . '" border="0" Alt="First Page"></a>&nbsp;&nbsp;';
      $s .= '<a href="./index.php?m=' . $m . '&amp;tab=' . $tab . '&amp;page=' . $xpg_prev_page . '"><img src="' . w2PfindImage('navleft.gif') . '" border="0" Alt="Previous page (' . $xpg_prev_page . ')"></a></td>';
    } else {
      $s .= '<td width="15%">&nbsp;</td>';
    }

    // central text (files, total pages, ...)
    $s .= '<td align="center" width="70%">';
    $s .= $xpg_totalrecs . ' ' . $AppUI->_('Record(s)') . ' ' . $xpg_total_pages . ' ' . $AppUI->_('Page(s)');

    // Page numbered list, up to 30 pages
    $s .= ' [ ';

    for ($n = $page > 16 ? $page - 16 : 1; $n <= $xpg_total_pages; $n++) {
      if ($n == $page) {
        $s .= '<b>' . $n . '</b></a>';
      } else {
        $s .= '<a href="./index.php?m=' . $m . '&amp;tab=' . $tab . '&amp;page=' . $n . '">' . $n . '</a>';
      }
      if ($n >= 30 + $page - 15) {
        $xpg_break = true;
        break;
      } elseif ($n < $xpg_total_pages) {
        $s .= ' | ';
      }
    }

    if (!isset($xpg_break)) { // are we supposed to break ?
      if ($n == $page) {
        $s .= '<' . $n . '</a>';
      } else {
        $s .= '<a href="./index.php?m=' . $m . '&amp;tab=' . $tab . '&amp;page=' . $xpg_total_pages . '">' . $n . '</a>';
      }
    }
    $s .= ' ] ';
    $s .= '</td>';
    // right buttoms
    if ($xpg_next_page <= $xpg_total_pages) {
      $s .= '<td align="right" width="15%"><a href="./index.php?m=' . $m . '&amp;tab=' . $tab . '&amp;page=' . $xpg_next_page . '"><img src="' . w2PfindImage('navright.gif') . '" border="0" Alt="Next Page (' . $xpg_next_page . ')"></a>&nbsp;&nbsp;';
      $s .= '<a href="./index.php?m=' . $m . '&amp;tab=' . $tab . '&amp;page=' . $xpg_total_pages . '"><img src="' . w2PfindImage('navlast.gif') . '" border="0" Alt="Last Page"></a></td>';
    } else {
      $s .= '<td width="15%">&nbsp;</td></tr>';
    }
  }
  $s .= '</table>';
  return $s;
}

function buildHeaderNavigation($AppUI, $rootTag = '', $innerTag = '', $dividingToken = '') {
	$s = '';
  $nav = $AppUI->getMenuModules();
  $perms = $AppUI->acl();

  $s .= ($rootTag != '') ? "<$rootTag id=\"headerNav\">" : '';
  $links = array();
  foreach ($nav as $module) {
  	if (canAccess($module['mod_directory'])) {
  		$link = ($innerTag != '') ? "<$innerTag>" : '';
      $link .= '<a href="?m=' . $module['mod_directory'] . '">' . $AppUI->_($module['mod_ui_name']) . '</a>';
      $link .= ($innerTag != '') ? "</$innerTag>" : '';
      $links[] = $link;
  	}
  }
  $s .= implode($dividingToken, $links);
  $s .= ($rootTag != '') ? "</$rootTag>" : '';

  return $s;
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
				$AppUI->redirect('m=public&a=access_denied');
			}
	} else {
		return $def;
	}
}

/**
 * Alternative to protect from XSS attacks.
 */
function w2PgetCleanParam(&$arr, $name, $def = null) {
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
	if (!w2PgetConfig('log_changes') || !$AppUI->isActiveModule('history')) {
		return;
	}

	$q = new DBQuery;
	$q->addTable('history');
	$q->addInsert('history_action', $action);
	$q->addInsert('history_item', $id);
	$q->addInsert('history_description', $description);
	$q->addInsert('history_user', $AppUI->user_id);
	$q->addInsert('history_date', "'".$q->dbfnNowWithTZ()."'", false, true);
	$q->addInsert('history_project', $project_id);
	$q->addInsert('history_table', $table);
	$q->exec();
	//echo db_error();
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

function w2PsetExecutionConditions($w2Pconfig) {

	$memoryLimt = ($w2Pconfig['reset_memory_limit'] != '') ? $w2Pconfig['reset_memory_limit'] : '64M';
	ini_set('max_execution_time', 180);
	ini_set('memory_limit', $memoryLimt);
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

function file_size($size) {
	if ($size > 1024 * 1024 * 1024)
		return round($size / 1024 / 1024 / 1024, 2) . ' Gb';
	if ($size > 1024 * 1024)
		return round($size / 1024 / 1024, 2) . ' Mb';
	if ($size > 1024)
		return round($size / 1024, 2) . ' Kb';
	return $size . ' B';
}

function filterCurrency($number) {

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
		echo $level . ' ' . $frame['file'] . ':' . $frame['line'] . ' ' . $frame['function'] . "()\n";
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
		if ($level == 0 && $max_level > 0) {
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
	} else {
		if (is_array($txt)) {
            foreach ($txt as $k => $v) {
                $txt[$k] = (is_array($v)) ? $v : html_entity_decode($v, ENT_COMPAT);
			}
		} else {
			$txt = html_entity_decode($txt, ENT_COMPAT);
		}
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

function w2p_url($link, $text = '')
{
  $result = '';

  if ($link != '') {
    if (strpos($link, 'http') === false) {
        $link = 'http://'.$link;
    }
    $text = ('' != $text) ? $text : $link;
    $result = '<a href="'.$link.'" target="_new">'.$text.'</a>';
  }
  return $result;
}
/*
* This function is basically a verbatim copy of Example 4-12 (pg 160) from
*   Nathan A Good's "Regular Expression Recipes" from Apress.
*/
function w2p_check_url($link)
{
    $result = false;
    $link = strtolower($link);
    if (strpos($link, 'http') === false) {
        $link = 'http://'.$link;
    }

    $urlPieces = parse_url($link);
    //if (preg_match("/^(?:[a-z0-9][-a-z0-9]+\.)+[a-z]{2,4}$/i", $urlPieces['host'])) {
        $result = true;
    //}
    return $result;
}

function w2p_email($email, $name = '')
{
  $result = '';

  if ($email != '') {
    $name = ('' != $name) ? $name : $email;
    $result = '<a href="mailto:'.$email.'">'.$name.'</a>';
  }
  return $result;
}
/*
* This function is basically a verbatim copy of Example 4-11 (pg 157) from
*   Nathan A Good's "Regular Expression Recipes" from Apress.
*/
function w2p_check_email($email)
{
  $result = false;
  if (preg_match("/^[-\w.]+@([a-z0-9][-a-z0-9]+\.)+[a-z]{2,4}$/i", $email)) {
    $result = true;
  }
  return $result;
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