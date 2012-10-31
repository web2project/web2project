<?php /* $Id$ $URL$ */

/**
 *	@package web2project
 *	@subpackage theme
 *	@version $Revision$
 */

/**
 * Title box abstract class
 */

class w2p_Theme_TitleBlock {
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
		global $AppUI, $a, $m, $w2Pconfig;
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
		global $m, $a;

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