<?php

/**
 * @package     web2project\theme
 */

abstract class w2p_Theme_TabBox {
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
    /** the active tab, and the selected tab **/
    public $javascript = null;

    protected $_AppUI = null;
    protected $_uistyle = 'web2project';

    protected $currentTabId = null;
    protected $currentTabName = null;
    protected $m = null;
    protected $a = null;

    /**
     * Constructor
     * @param string The base URL query string to prefix tab links
     * @param string The base path to prefix the include file
     * @param int The active tab
     * @param string Optional javascript method to be used to execute tabs.
     *    Must support 2 arguments, currently active tab, new tab to activate.
     */
    public function __construct($baseHRef = '', $baseInc = '', $active = 0, $javascript = null) {
        global $AppUI, $currentTabId, $currentTabName, $m, $a;

        $this->_AppUI = $AppUI;
        $this->currentTabId = $currentTabId;
        $this->currentTabName = $currentTabName;
        $this->m = $m;
        $this->a = $a;

        $this->tabs = array();
        $this->active = $active;
        $this->baseHRef = ($baseHRef ? $baseHRef . '&amp;' : '?');
        $this->javascript = $javascript;
        $this->baseInc = $baseInc;

        $this->_uistyle = $this->_AppUI->getPref('UISTYLE') ?
                $this->_AppUI->getPref('UISTYLE') : w2PgetConfig('host_style');
        if (!$this->_uistyle) {
            $this->_uistyle = 'web2project';
        }
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
        if ($this->active < 0 || $this->_AppUI->getPref('TABVIEW') == 2) {
            return false;
        }
        return true;
    }

    public function show($extra = '', $js_tabs = false, $alignment = 'left', $opt_flat = true) {
        $this->loadExtras($this->m, $this->a);

        if (($this->a == 'addedit' || $this->a == 'view' || $this->a == 'viewuser')) {
            echo $this->_AppUI->getTheme()->styleRenderBoxBottom();
        }

        reset($this->tabs);
        $s = '';
        // tabbed / flat view options
        if ($this->_AppUI->getPref('TABVIEW') != 0) {
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
                $this->currentTabId = $k;
                $this->currentTabName = $v[1];
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
                $s .= '<td valign="middle"><img src="./style/' . $this->_uistyle . '/images/bar_top_' . $sel . 'left.gif" id="lefttab_' . $k . '" /></td>';
                $s .= '<td id="toptab_' . $k . '" valign="middle" nowrap="nowrap" class="' . $class . '">&nbsp;<a href="';
                if ($this->javascript) {
                    $s .= 'javascript:' . $this->javascript . '(' . $this->active . ', ' . $k . ')';
                } elseif ($js_tabs) {
                    $s .= 'javascript:show_tab(' . $k . ')';
                } else {
                    $s .= $this->baseHRef . 'tab=' . $k;
                }
                $s .= '">' . ($v[2] ? $v[1] : $this->_AppUI->_($v[1])) . '</a>&nbsp;</td>';
                $s .= '<td valign="middle" ><img id="righttab_' . $k . '" src="./style/' . $this->_uistyle . '/images/bar_top_' . $sel . 'right.gif" /></td>';
                $s .= '<td class="tabsp"><img src="' . w2PfindImage('shim.gif') . '" alt=""/></td>';
            }
            $s .= '</tr></table></td></tr>';

            //round the right top of the tab box
            $s .= '<tr><td>';
            $s .= '<table width="100%" cellspacing="0" cellpadding="0" border="0">';
            $s .= '<tbody>';
            $s .= '<tr>';
            $s .= '    <td valign="bottom" width="100%" background="./style/' . $this->_uistyle . '/images/tabbox_top.jpg" align="left">';
            $s .= '        <img src="./style/' . $this->_uistyle . '/images/tabbox_top.jpg" alt=""/>';
            $s .= '    </td>';
            $s .= '</tr>';
            $s .= '</tbody>';
            $s .= '</table>';
            $s .= '</td></tr>';

            $s .= '<tr><td width="100%" colspan="' . (count($this->tabs) * 4 + 1) . '" class="tabox">';
            echo $s;
            //Will be null if the previous selection tab is not available in the new window eg. Children tasks
            if (isset($this->tabs[$this->active][0]) && $this->tabs[$this->active][0] != '') {
                $this->currentTabId = $this->active;
                $this->currentTabName = $this->tabs[$this->active][1];
                if (!$js_tabs) {
                    require $this->baseInc . $this->tabs[$this->active][0] . '.php';
                }
            }
            if ($js_tabs) {
                foreach ($this->tabs as $k => $v) {
                    echo '<div class="tab" id="tab_' . $k . '">';
                    $this->currentTabId = $k;
                    $this->currentTabName = $v[1];
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

    public function loadExtras($module, $file = null) {
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
        $modules = $this->_AppUI->getActiveModules();
        $tab_count = 0;
        foreach ($tab_array as $tab_elem) {
            if (isset($tab_elem['module']) && isset($modules[$tab_elem['module']])) {
                $tab_count++;
                $this->add($tab_elem['file'], $tab_elem['name']);
            }
        }
        return $tab_count;
    }

    public function findTabModule($tab) {
        global $m, $a;

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