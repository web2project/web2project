<?php

/**
 * This class is used to do second level tabs or subtabs aligned to the right by default
 *
 * @package     web2project\theme
 * @author      Pedro Azevedo
 */

class w2p_Theme_InfoTabBox extends w2p_Theme_TabBox {

    public function show($extra = '', $js_tabs = false, $alignment = 'left') {
        global $AppUI, $w2Pconfig, $currentInfoTabId, $currentInfoTabName, $m;
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
                $s .= '<td valign="middle"><img src="./style/' . $uistyle . '/bar_top_' . $sel . 'left.gif" id="lefttab_' . $k . '" /></td>';
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
                $s .= '<td valign="middle" ><img id="righttab_' . $k . '" src="./style/' . $uistyle . '/bar_top_' . $sel . 'right.gif" /></td>';
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