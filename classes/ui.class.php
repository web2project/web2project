<?php /* $Id$ $URL$ */

class CAppUI extends w2p_Core_CAppUI {
    public function __construct()
    {
        parent::__construct();
        //trigger_error("CAppUI has been deprecated in v3.0 and will be removed by v4.0. Please use w2p_Core_CAppUI instead.", E_USER_NOTICE );
    }
}

/**
 * Tabbed box abstract class
 */
class CTabBox_core {
	/**
 	@var array */
	public $tabs = null;
	/**
 	@var int The active tab */
	public $active = null;
	/**
 	@var string The base URL query string to prefix tab links */
	public $baseHRef = null;
	/**
 	@var string The base path to prefix the include file */
	public $baseInc;
	/**

	 * the active tab, and the selected tab **/
	public $javascript = null;

	/**
	 * Constructor
	 * @param string The base URL query string to prefix tab links
	 * @param string The base path to prefix the include file
	 * @param int The active tab
	 * @param string Optional javascript method to be used to execute tabs.
	 *	Must support 2 arguments, currently active tab, new tab to activate.
	 */
	public function __construct($baseHRef = '', $baseInc = '', $active = 0, $javascript = null) {
		$this->tabs = array();
		$this->active = $active;
		$this->baseHRef = ($baseHRef ? $baseHRef . '&amp;' : '?');
		$this->javascript = $javascript;
		$this->baseInc = $baseInc;
	}
	/**
	 * Gets the name of a tab
	 * @return string
	 */
	public function getTabName($idx) {
		return $this->tabs[$idx][1];
	}
	/**
	 * Adds a tab to the object
	 * @param string File to include
	 * @param The display title/name of the tab
	 */
	public function add($file, $title, $translated = false, $key = null) {
		$t = array($file, $title, $translated);
		if (isset($key)) {
			$this->tabs[$key] = $t;
		} else {
			$this->tabs[] = $t;
		}
	}

	public function isTabbed() {
		global $AppUI;
		if ($this->active < 0 || $AppUI->getPref('TABVIEW') == 2) {
			return false;
		}
		return true;
	}

	/**
	 * Displays the tabbed box
	 *
	 * This function may be overridden
	 *
	 * @param string Can't remember whether this was useful
	 */
	public function show($extra = '', $js_tabs = false) {
		global $AppUI, $currentTabId, $currentTabName;
		$this->loadExtras($m, $a);
		reset($this->tabs);
		$s = '';
		// tabbed / flat view options
		if ($AppUI->getPref('TABVIEW') == 0) {
			$s .= '<table border="0" cellpadding="2" cellspacing="0" width="100%"><tr><td nowrap="nowrap">';
			$s .= '<a class="crumb" href="' . $this->baseHRef . 'tab=0"><span>' . $AppUI->_('tabbed') . '</span></a> ';
			$s .= '<a class="crumb" href="' . $this->baseHRef . 'tab=-1"><span>' . $AppUI->_('flat') . '</span></a>';
			$s .= '</td>' . $extra . '</tr></table>';
			echo $s;
		} else {
			if ($extra) {
				echo '<table border="0" cellpadding="2" cellspacing="0" width="100%"><tr>' . $extra . '</tr></table>';
			} else {
				echo '<img src="' . w2PfindImage('shim.gif') . '" height="10" width="1" alt="" />';
			}
		}

		if ($this->active < 0 || $AppUI->getPref('TABVIEW') == 2) {
			// flat view, active = -1
			echo '<table border="0" cellpadding="2" cellspacing="0" width="100%">';
			foreach ($this->tabs as $k => $v) {
				echo '<tr><td><strong>' . ($v[2] ? $v[1] : $AppUI->_($v[1])) . '</strong></td></tr>';
				echo '<tr><td>';
				$currentTabId = $k;
				$currentTabName = $v[1];
				include $this->baseInc . $v[0] . '.php';
				echo '</td></tr>';
			}
			echo '</table>';
		} else {
			// tabbed view
			$s = '<table width="100%" border="0" cellpadding="3" cellspacing="0"><tr>';
			if (count($this->tabs) - 1 < $this->active) {
				//Last selected tab is not available in this view. eg. Child tasks
				$this->active = 0;
			}
			foreach ($this->tabs as $k => $v) {
				$class = ($k == $this->active) ? 'tabon' : 'taboff';
				$s .= '<td width="1%" nowrap="nowrap" class="tabsp"><img src="' . w2PfindImage('shim.gif') . '" height="1" width="1" alt="" /></td>';
				$s .= '<td id="toptab_' . $k . '" width="1%" nowrap="nowrap"';
				if ($js_tabs) {
					$s .= ' class="' . $class . '"';
				}
				$s .= '><a href="';
				if ($this->javascript) {
					$s .= 'javascript:' . $this->javascript . '(' . $this->active . ', ' . $k . ')';
				} elseif ($js_tabs) {
					$s .= 'javascript:show_tab(' . $k . ')';
				} else {
					$s .= $this->baseHRef . "tab=$k";
				}
				$s .= '">' . ($v[2] ? $v[1] : $AppUI->_($v[1])) . '</a></td>';
			}
			$s .= '<td nowrap="nowrap" class="tabsp">&nbsp;</td></tr>';
			$s .= '<tr><td width="100%" colspan="' . (count($this->tabs) * 2 + 1) . '" class="tabox">';
			echo $s;
			//Will be null if the previous selection tab is not available in the new window eg. Children tasks
			if ($this->baseInc . $this->tabs[$this->active][0] != '') {
				$currentTabId = $this->active;
				$currentTabName = $this->tabs[$this->active][1];
				if (!$js_tabs) {
					require $this->baseInc . $this->tabs[$this->active][0] . '.php';
				}
			}
			if ($js_tabs) {
				foreach ($this->tabs as $k => $v) {
					echo '<div class="tab" id="tab_' . $k . '">';
					require $this->baseInc . $v[0] . '.php';
					echo '</div>';
				}
			}
			echo '</td></tr></table>';
		}
	}

	public function loadExtras($module, $file = null) {
		global $AppUI;
		if (!isset($_SESSION['all_tabs']) || !isset($_SESSION['all_tabs'][$module])) {
			return false;
		}

		if ($file) {
			if (isset($_SESSION['all_tabs'][$module][$file]) && is_array($_SESSION['all_tabs'][$module][$file])) {
				$tab_array = &$_SESSION['all_tabs'][$module][$file];
			} else {
				return false;
			}
		} else {
			$tab_array = &$_SESSION['all_tabs'][$module];
		}
		$tab_count = 0;
		foreach ($tab_array as $tab_elem) {
			if (isset($tab_elem['module']) && $AppUI->isActiveModule($tab_elem['module'])) {
				$tab_count++;
				$this->add($tab_elem['file'], $tab_elem['name']);
			}
		}
		return $tab_count;
	}

	public function findTabModule($tab) {
		global $AppUI, $m, $a;

		if (!isset($_SESSION['all_tabs']) || !isset($_SESSION['all_tabs'][$m])) {
			return false;
		}

		if (isset($a)) {
			if (isset($_SESSION['all_tabs'][$m][$a]) && is_array($_SESSION['all_tabs'][$m][$a])) {
				$tab_array = &$_SESSION['all_tabs'][$m][$a];
			} else {
				$tab_array = &$_SESSION['all_tabs'][$m];
			}
		} else {
			$tab_array = &$_SESSION['all_tabs'][$m];
		}

		list($file, $name) = $this->tabs[$tab];
		foreach ($tab_array as $tab_elem) {
			if (isset($tab_elem['name']) && $tab_elem['name'] == $name && $tab_elem['file'] == $file) {
				return $tab_elem['module'];
			}
		}
		return false;
	}
}

/**
 * CInfoTabBox
 * This class is used to do second level tabs or subtabs aligned to the right by default
 * @package
 * @author Pedro Azevedo
 * @copyright 2007
 * @version $Rev$
 * @access public
 */
class CInfoTabBox extends CTabBox_core {
	public function show($extra = '', $js_tabs = false, $alignment = 'left') {
		global $AppUI, $w2Pconfig, $currentInfoTabId, $currentInfoTabName, $m, $a;
		$uistyle = $AppUI->getPref('UISTYLE') ? $AppUI->getPref('UISTYLE') : $w2Pconfig['host_style'];
		if (!$uistyle) {
			$uistyle = 'web2project';
		}
		reset($this->tabs);
		$s = '';
		if ($extra) {
			echo '<table border="0" cellpadding="2" cellspacing="0" width="100%"><tr>' . $extra . '</tr></table>';
		}

		if ($this->active < 0 || $AppUI->getPref('TABVIEW') == 2) {
			// flat view, active = -1
			echo '<table border="0" cellpadding="2" cellspacing="0" width="100%">';
			foreach ($this->tabs as $k => $v) {
				echo '<tr><td><strong>' . ($v[2] ? $v[1] : $AppUI->_($v[1])) . '</strong></td></tr>';
				echo '<tr><td>';
				$currentInfoTabId = $k;
				$currentInfoTabName = $v[1];
				include $this->baseInc . $v[0] . '.php';
				echo '</td></tr>';
			}
			echo '</table>';
		} else {
			// tabbed view
			$s = '<table width="100%" border="0" cellpadding="0" cellspacing="0">';
			$s .= '<tr><td><table align="' . $alignment . '" border="0" cellpadding="0" cellspacing="0">';

			if (count($this->tabs) - 1 < $this->active) {
				//Last selected tab is not available in this view. eg. Child tasks
				$this->active = 0;
			}
			foreach ($this->tabs as $k => $v) {
				$class = ($k == $this->active) ? 'tabon' : 'taboff';
				$sel = ($k == $this->active) ? 'Selected' : '';
				$s .= '<td valign="middle"><img src="./style/' . $uistyle . '/bar_top_' . $sel . 'left.gif" id="lefttab_' . $k . '" border="0" alt="" /></td>';
				$s .= '<td id="toptab_' . $k . '" valign="middle" nowrap="nowrap"';
				$s .= ' class="' . $class . '"';
				$s .= '>&nbsp;<a href="';
				if ($this->javascript)
					$s .= 'javascript:' . $this->javascript . '(' . $this->active . ', ' . $k . ')';
				else
					if ($js_tabs) {
						$s .= 'javascript:show_tab(' . $k . ')';
					} else {
						if ($m == 'projectdesigner' && strpos($v[1], 'Invoices') === false) {
							$s .= $this->baseHRef . 'infotab_bil=' . $k . '#billings';
						} elseif ($m == 'projectdesigner') {
							$s .= $this->baseHRef . 'infotab_inv=' . $k . '#invoices';
						} else {
							$s .= $this->baseHRef . 'infotab=' . $k;
						}
					}
					$s .= '">' . ($v[2] ? $v[1] : $AppUI->_($v[1])) . '</a>&nbsp;</td>';
				$s .= '<td valign="middle" ><img id="righttab_' . $k . '" src="./style/' . $uistyle . '/bar_top_' . $sel . 'right.gif" border="0" alt="" /></td>';
				$s .= '<td class="tabsp"><img src="' . w2PfindImage('shim.gif') . '" alt=""/></td>';
			}
			$s .= '</table></td></tr>';
			$s .= '<tr><td width="100%" colspan="' . (count($this->tabs) * 4 + 1) . '" class="tabox">';
			echo $s;
			//Will be null if the previous selection tab is not available in the new window eg. Children tasks
			if ($this->tabs[$this->active][0] != '') {
				$currentInfoTabId = $this->active;
				$currentInfoTabName = $this->tabs[$this->active][1];
				if (!$js_tabs) {
					require $this->baseInc . $this->tabs[$this->active][0] . '.php';
				}
			}
			if ($js_tabs) {
				foreach ($this->tabs as $k => $v) {
					echo '<div class="tab" id="infotab_' . $k . '">';
					$currentInfoTabId = $k;
					$currentInfoTabName = $v[1];
					require $this->baseInc . $v[0] . '.php';
					echo '</div>';
					echo '<script language="JavaScript" type="text/javascript">
<!--
show_tab(' . $this->active . ');
//-->
</script>';

				}
			}
			echo '</td></tr></table>';
		}
	}
}
/**
 * Title box abstract class
 */
class CTitleBlock_core {
	/**
 	@var string The main title of the page */
	public $title = '';
	/**
 	@var string The name of the icon used to the left of the title */
	public $icon = '';
	/**
 	@var string The name of the module that this title block is displaying in */
	public $module = '';
	/**
 	@var array An array of the table 'cells' to the right of the title block and for bread-crumbs */
	public $cells = null;
	/**
 	@var string The reference for the context help system */
	public $helpref = '';
	/**
	 * The constructor
	 *
	 * Assigns the title, icon, module and help reference.  If the user does not
	 * have permission to view the help module, then the context help icon is
	 * not displayed.
	 */
	public function __construct($title, $icon = '', $module = '', $helpref = '') {
		$this->title = $title;
		$this->icon = $icon;
		$this->module = $module;
		$this->helpref = $helpref;
		$this->cells1 = array();
		$this->cells2 = array();
		$this->crumbs = array();
		$this->showhelp = canView('help');
	}
	/**
	 * Adds a table 'cell' beside the Title string
	 *
	 * Cells are added from left to right.
	 */
	public function addCell($data = '', $attribs = '', $prefix = '', $suffix = '') {
		$this->cells1[] = array($attribs, $data, $prefix, $suffix);
	}
	/**
	 * Adds a table 'cell' to left-aligned bread-crumbs
	 *
	 * Cells are added from left to right.
	 */
	public function addCrumb($link, $label, $icon = '') {
		$this->crumbs[$link] = array($label, $icon);
	}
	/**
	 * Adds a table 'cell' to the right-aligned bread-crumbs
	 *
	 * Cells are added from left to right.
	 */
	public function addCrumbRight($data = '', $attribs = '', $prefix = '', $suffix = '') {
		$this->cells2[] = array($attribs, $data, $prefix, $suffix);
	}
	/**
	 * Creates a standarised, right-aligned delete bread-crumb and icon.
	 */
	public function addCrumbDelete($title, $canDelete = '', $msg = '') {
		global $AppUI;
		$this->addCrumbRight('<table cellspacing="0" cellpadding="0" border="0"><tr><td>' . '<a class="delete" href="javascript:delIt()" title="' . ($canDelete ? '' : $msg) . '"><span>' . $AppUI->_($title) . '</span></a>' . '</td></tr></table>');
	}
	/**
	 * The drawing function
	 */
	public function show() {
		global $AppUI, $a, $m, $tab, $infotab;
		$this->loadExtraCrumbs($m, $a);
		$uistyle = $AppUI->getPref('UISTYLE') ? $AppUI->getPref('UISTYLE') : $w2Pconfig['host_style'];
		if (!$uistyle) {
			$uistyle = 'web2project';
		}
		$s = '<table width="100%" border="0" cellpadding="1" cellspacing="1"><tr>';
		if ($this->icon) {
			$s .= '<td width="42">';
			$s .= w2PshowImage($this->icon, '', '', '', '', $this->module);
			$s .= '</td>';
		}
		$s .= '<td align="left" width="100%" nowrap="nowrap"><h1>' . $AppUI->_($this->title) . '</h1></td>';
		foreach ($this->cells1 as $c) {
			$s .= $c[2] ? $c[2] : '';
			$s .= '<td align="right" nowrap="nowrap"' . ($c[0] ? (' ' . $c[0]) : '') . '>';
			$s .= $c[1] ? $c[1] : '&nbsp;';
			$s .= '</td>';
			$s .= $c[3] ? $c[3] : '';
		}
		$s .= '</tr></table>';

		if (count($this->crumbs) || count($this->cells2)) {
			$crumbs = array();
			$class = 'crumb';
			foreach ($this->crumbs as $k => $v) {
				$t = $v[1] ? '<img src="' . w2PfindImage($v[1], $this->module) . '" border="" alt="" />&nbsp;' : '';
				$t .= $AppUI->_($v[0]);
				$crumbs[] = '<li><a href="'.$k.'"><span>'.$t.'</span></a></li>';
			}
			$s .= '<table border="0" cellpadding="0" cellspacing="0" width="100%"><tr>';
			$s .= '<td height="20" nowrap="nowrap"><div class="'.$class.'"><ul>';
			$s .= implode('', $crumbs);
			$s .= '</ul></div></td>';

			foreach ($this->cells2 as $c) {
				$s .= $c[2] ? $c[2] : '';
				$s .= '<td align="right" nowrap="nowrap" ' . ($c[0] ? " $c[0]" : '') . '>';
				$s .= $c[1] ? $c[1] : '&nbsp;';
				$s .= '</td>';
				$s .= $c[3] ? $c[3] : '';
			}
			$s .= '</tr></table>';
		}
		echo '' . $s;
		if (($a != 'index' || $m == 'system' || $m == 'calendar' || $m == 'smartsearch') && !$AppUI->boxTopRendered && function_exists('styleRenderBoxTop')) {
			echo styleRenderBoxTop();
			$AppUI->boxTopRendered = true;
		}
	}

	public function loadExtraCrumbs($module, $file = null) {
		global $AppUI;
		if (!isset($_SESSION['all_crumbs']) || !isset($_SESSION['all_crumbs'][$module])) {
			return false;
		}

		if ($file) {
			if (isset($_SESSION['all_crumbs'][$module][$file]) && is_array($_SESSION['all_crumbs'][$module][$file])) {
				$crumb_array = &$_SESSION['all_crumbs'][$module][$file];
			} else {
				return false;
			}
		} else {
			$crumb_array = &$_SESSION['all_crumbs'][$module];
		}
		$crumb_count = 0;
		foreach ($crumb_array as $crumb_elem) {
			if (isset($crumb_elem['module']) && $AppUI->isActiveModule($crumb_elem['module'])) {
				$crumb_count++;
				include_once ($crumb_elem['file'] . '.php');
			}
		}
		return $crumb_count;
	}

	public function findCrumbModule($crumb) {
		global $AppUI, $m, $a;

		if (!isset($_SESSION['all_crumbs']) || !isset($_SESSION['all_crumbs'][$m])) {
			return false;
		}

		if (isset($a)) {
			if (isset($_SESSION['all_crumbs'][$m][$a]) && is_array($_SESSION['all_crumbs'][$m][$a])) {
				$crumb_array = &$_SESSION['all_crumbs'][$m][$a];
			} else {
				$crumb_array = &$_SESSION['all_crumbs'][$m];
			}
		} else {
			$crumb_array = &$_SESSION['all_crumbs'][$m];
		}

		list($file, $name) = $this->crumbs[$crumb];
		foreach ($crumb_array as $crumb_elem) {
			if (isset($crumb_elem['name']) && $crumb_elem['name'] == $name && $crumb_elem['file'] == $file) {
				return $crumb_elem['module'];
			}
		}
		return false;
	}
}
