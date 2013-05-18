<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

class style_web2project extends w2p_Theme_Base
{

}

/**
 * This overrides the show function of the CTabBox_core function
 */
class CTabBox extends w2p_Theme_TabBox {
	public function show($extra = '', $js_tabs = false, $alignment = 'left', $opt_flat = true) {
		global $currentTabId, $currentTabName, $m, $a;
		$this->loadExtras($m, $a);

		if (($a == 'addedit' || $a == 'view' || $a == 'viewuser') && function_exists('styleRenderBoxBottom')) {
			echo styleRenderBoxBottom();
		}

		reset($this->tabs);
		$s = '';
		// tabbed / flat view options
		if ($this->_AppUI->getPref('TABVIEW') == 0) {
			if ($opt_flat) {
				$s .= '<table border="0" cellpadding="2" cellspacing="0" width="100%">';
				$s .= '<tr>';
				$s .= '<td width="54" nowrap="nowrap">';
				$s .= '<a class="button" href="' . $this->baseHRef . 'tab=0"><div>' . $this->_AppUI->_('tabbed') . '</div></a>';
				$s .= '</td>';
				$s .= '<td nowrap="nowrap">';
				$s .= '<a class="button" href="' . $this->baseHRef . 'tab=-1"><div>' . $this->_AppUI->_('flat') . '</div></a>';
				$s .= '</td>' . $extra . '</tr></table>';
				echo $s;
			}
		} else {
			if ($extra) {
				echo '<table border="0" cellpadding="2" cellspacing="0" width="100%"><tr>' . $extra . '</tr>' . '</table>';
			}
		}

		if ($this->active < 0 || $this->_AppUI->getPref('TABVIEW') == 2) {
			// flat view, active = -1
			echo '<table border="0" cellpadding="2" cellspacing="0" width="100%">';
			foreach ($this->tabs as $k => $v) {
				echo '<tr><td><strong>' . ($v[2] ? $v[1] : $this->_AppUI->_($v[1])) . '</strong></td></tr>';
                echo '<tr><td>';
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
				$s .= '<td valign="middle"><img src="./style/' . $this->_uistyle . '/images/bar_top_' . $sel . 'left.gif" id="lefttab_' . $k . '" border="0" alt="" /></td>';
				$s .= '<td id="toptab_' . $k . '" valign="middle" nowrap="nowrap" class="' . $class . '">&nbsp;<a href="';
				if ($this->javascript) {
					$s .= 'javascript:' . $this->javascript . '(' . $this->active . ', ' . $k . ')';
				} elseif ($js_tabs) {
					$s .= 'javascript:show_tab(' . $k . ')';
				} else {
					$s .= $this->baseHRef . 'tab=' . $k;
				}
				$s .= '">' . ($v[2] ? $v[1] : $this->_AppUI->_($v[1])) . '</a>&nbsp;</td>';
				$s .= '<td valign="middle" ><img id="righttab_' . $k . '" src="./style/' . $this->_uistyle . '/images/bar_top_' . $sel . 'right.gif" border="0" alt="" /></td>';
				$s .= '<td class="tabsp"><img src="' . w2PfindImage('shim.gif') . '" alt=""/></td>';
			}
			$s .= '</tr></table></td></tr>';

			//round the right top of the tab box
			$s .= '<tr><td>';
			$s .= '<table width="100%" cellspacing="0" cellpadding="0" border="0">';
			$s .= '<tbody>';
			$s .= '<tr>';
			$s .= '	<td valign="bottom" width="100%" background="./style/' . $this->_uistyle . '/images/tabbox_top.jpg" align="left">';
			$s .= '		<img src="./style/' . $this->_uistyle . '/images/tabbox_top.jpg" alt=""/>';
			$s .= '	</td>';
			$s .= '</tr>';
			$s .= '</tbody>';
			$s .= '</table>';
			$s .= '</td></tr>';

			$s .= '<tr><td width="100%" colspan="' . (count($this->tabs) * 4 + 1) . '" class="tabox">';
			echo $s;
			//Will be null if the previous selection tab is not available in the new window eg. Children tasks
			if (isset($this->tabs[$this->active][0]) && $this->tabs[$this->active][0] != '') {
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
    global $AppUI;
    $theme = new style_web2project($AppUI);
    return $theme->styleRenderBoxTop();
}

function styleRenderBoxBottom() {
    global $AppUI;
    $theme = new style_web2project($AppUI);
    return $theme->styleRenderBoxBottom();
}