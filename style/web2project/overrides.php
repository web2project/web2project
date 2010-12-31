<?php /* $Id$ $URL$ */

class CTitleBlock extends CTitleBlock_core {
}

##
##  This overrides the show function of the CTabBox_core function
##
class CTabBox extends CTabBox_core {
	function show($extra = '', $js_tabs = false, $alignment = 'left', $opt_flat = true) {
		global $AppUI, $w2Pconfig, $currentTabId, $currentTabName, $m, $a;
		$this->loadExtras($m, $a);
		$uistyle = $AppUI->getPref('UISTYLE') ? $AppUI->getPref('UISTYLE') : $w2Pconfig['host_style'];
		if (!$uistyle) {
			$uistyle = 'web2project';
		}

		if (($a == 'addedit' || $a == 'view' || $a == 'viewuser') && function_exists('styleRenderBoxBottom')) {
			echo styleRenderBoxBottom();
		}

		reset($this->tabs);
		$s = '';
		// tabbed / flat view options
		if ($AppUI->getPref('TABVIEW') == 0) {
			if ($opt_flat) {
				$s .= '<table border="0" cellpadding="2" cellspacing="0" width="100%">';
				$s .= '<tr>';
				$s .= '<td width="54" nowrap="nowrap">';
				$s .= '<a class="button" href="' . $this->baseHRef . 'tab=0"><span>' . $AppUI->_('tabbed') . '</span></a>';
				$s .= '</td>';
				$s .= '<td nowrap="nowrap">';
				$s .= '<a class="button" href="' . $this->baseHRef . 'tab=-1"><span>' . $AppUI->_('flat') . '</span></a>';
				$s .= '</td>' . $extra . '</tr></table>';
				echo $s;
			}
		} else {
			if ($extra) {
				echo '<table border="0" cellpadding="2" cellspacing="0" width="100%"><tr>' . $extra . '</tr>' . '</table>';
			}
		}

		if ($this->active < 0 || $AppUI->getPref('TABVIEW') == 2) {
			// flat view, active = -1
			echo '<table border="0" cellpadding="2" cellspacing="0" width="100%">';
			foreach ($this->tabs as $k => $v) {
				echo '<tr><td><strong>' . ($v[2] ? $v[1] : $AppUI->_($v[1])) . '</strong></td></tr><tr><td>';
				$currentTabId = $k;
				$currentTabName = $v[1];
				include $this->baseInc . $v[0] . '.php';
				echo '</td></tr>';
			}
			echo '</table>';
		} else {
			// tabbed view
			$s = '<table width="100%" border="0" cellpadding="0" cellspacing="0">';
			$s .= '<tr><td><table align="' . $alignment . '" border="0" cellpadding="0" cellspacing="0"><tr>';

			if (count($this->tabs) - 1 < $this->active) {
				//Last selected tab is not available in this view. eg. Child tasks
				$this->active = 0;
			}
			foreach ($this->tabs as $k => $v) {
				$class = ($k == $this->active) ? 'tabon' : 'taboff';
				$sel = ($k == $this->active) ? 'Selected' : '';
				$s .= '<td valign="middle"><img src="./style/' . $uistyle . '/images/bar_top_' . $sel . 'left.gif" id="lefttab_' . $k . '" border="0" alt="" /></td>';
				$s .= '<td id="toptab_' . $k . '" valign="middle" nowrap="nowrap" class="' . $class . '">&nbsp;<a href="';
				if ($this->javascript) {
					$s .= 'javascript:' . $this->javascript . '(' . $this->active . ', ' . $k . ')';
				} elseif ($js_tabs) {
					$s .= 'javascript:show_tab(' . $k . ')';
				} else {
					$s .= $this->baseHRef . 'tab=' . $k;
				}
				$s .= '">' . ($v[2] ? $v[1] : $AppUI->_($v[1])) . '</a>&nbsp;</td>';
				$s .= '<td valign="middle" ><img id="righttab_' . $k . '" src="./style/' . $uistyle . '/images/bar_top_' . $sel . 'right.gif" border="0" alt="" /></td>';
				$s .= '<td class="tabsp"><img src="' . w2PfindImage('shim.gif') . '" alt=""/></td>';
			}
			$s .= '</tr></table></td></tr>';

			//round the right top of the tab box
			$s .= '<tr><td>';
			$s .= '<table width="100%" cellspacing="0" cellpadding="0" border="0">';
			$s .= '<tbody>';
			$s .= '<tr>';
			$s .= '	<td valign="bottom" width="100%" background="./style/' . $uistyle . '/images/tabbox_top.jpg" align="left">';
			$s .= '		<img src="./style/' . $uistyle . '/images/tabbox_top.jpg" alt=""/>';
			$s .= '	</td>';
			$s .= '</tr>';
			$s .= '</tbody>';
			$s .= '</table>';
			$s .= '</td></tr>';

			$s .= '<tr><td width="100%" colspan="' . (count($this->tabs) * 4 + 1) . '" class="tabox">';
			echo $s;
			//Will be null if the previous selection tab is not available in the new window eg. Children tasks
			if ($this->tabs[$this->active][0] != '') {
				$currentTabId = $this->active;
				$currentTabName = $this->tabs[$this->active][1];
				if (!$js_tabs) {
					require $this->baseInc . $this->tabs[$this->active][0] . '.php';
				}
			}
			if ($js_tabs) {
				foreach ($this->tabs as $k => $v) {
					echo '<div class="tab" id="tab_' . $k . '">';
					$currentTabId = $k;
					$currentTabName = $v[1];
					require $this->baseInc . $v[0] . '.php';
					echo '</div>';
					echo '<script language="javascript" type="text/javascript">
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

function styleRenderBoxTop() {
	global $AppUI, $currentInfoTabId;
	$uistyle = $AppUI->getPref('UISTYLE') ? $AppUI->getPref('UISTYLE') : w2PgetConfig('host_style');
	if (!$uistyle) {
		$uistyle = 'web2project';
	}
	if ($currentInfoTabId) {
		return '';
	}
	$ret = '<table width="100%" cellspacing="0" cellpadding="0" border="0">';
	$ret .= '<tbody>';
	$ret .= '<tr>';
	$ret .= '	<td valign="bottom" height="17" style="background:url(./style/' . $uistyle . '/images/box_left_corner.jpg);" align="left">';
	$ret .= '		<img width="19" height="17" alt="" src="./style/' . $uistyle . '/images/box_left_corner.jpg"/>';
	$ret .= '	</td>';
	$ret .= '	<td valign="bottom" width="100%" style="background:url(./style/' . $uistyle . '/images/box_top.jpg);" align="left">';
	$ret .= '		<img width="19" height="17" alt="" src="./style/' . $uistyle . '/images/box_top.jpg"/>';
	$ret .= '	</td>';
	$ret .= '	<td valign="bottom" style="background:url(./style/' . $uistyle . '/images/box_right_corner.jpg);" align="right">';
	$ret .= '		<img width="19" height="17" alt="" src="./style/' . $uistyle . '/images/box_right_corner.jpg"/>';
	$ret .= '	</td>';
	$ret .= '</tr>';
	$ret .= '</tbody>';
	$ret .= '</table>';
	return $ret;
}

function styleRenderBoxBottom() {
	global $AppUI, $currentInfoTabId;
	$uistyle = $AppUI->getPref('UISTYLE') ? $AppUI->getPref('UISTYLE') : w2PgetConfig('host_style');
	if (!$uistyle) {
		$uistyle = 'web2project';
	}
	$ret = '<table width="100%" cellspacing="0" cellpadding="0" border="0">';
	$ret .= '<tbody>';
	$ret .= '<tr>';
	$ret .= '	<td valign="top" height="35" style="background:url(./style/' . $uistyle . '/images/shadow_bttm_left_corner.jpg) no-repeat;" align="left">';
	$ret .= '		<img width="19" height="35" alt="" src="./style/' . $uistyle . '/images/shadow_bttm_left_corner.jpg"/>';
	$ret .= '	</td>';
	$ret .= '	<td valign="top" width="100%" style="background: repeat-x url(./style/' . $uistyle . '/images/shadow_bottom.jpg);" align="left">';
	$ret .= '		<img width="19" height="35" alt="" src="./style/' . $uistyle . '/images/shadow_bottom.jpg"/>';
	$ret .= '	</td>';
	$ret .= '	<td valign="top" style="background:url(./style/' . $uistyle . '/images/shadow_bttm_right_corner.jpg) no-repeat;" align="right">';
	$ret .= '		<img width="19" height="35" alt="" src="./style/' . $uistyle . '/images/shadow_bttm_right_corner.jpg"/>';
	$ret .= '	</td>';
	$ret .= '</tr>';
	$ret .= '</tbody>';
	$ret .= '</table>';
	return $ret;
}