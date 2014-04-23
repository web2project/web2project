<?php

/**
 * @package     web2project\theme
 */

class w2p_Theme_TabBox {
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

    /**
     * Displays the tabbed box
     *
     * This function may be overridden
     *
     * @param string Can't remember whether this was useful
     */
    public function show($extra = '', $js_tabs = false) {
        $this->loadExtras($notUsed, $notUsed2);

        
        
        
        
        reset($this->tabs);
        $s = '';
        // tabbed / flat view options
        if ($this->_AppUI->getPref('TABVIEW') == 0) {
            $s .= '<table border="0" cellpadding="2" cellspacing="0" width="100%"><tr><td nowrap="nowrap">';
            $s .= '<a class="crumb" href="' . $this->baseHRef . 'tab=0"><span>' . $this->_AppUI->_('tabbed') . '</span></a> ';
            $s .= '<a class="crumb" href="' . $this->baseHRef . 'tab=-1"><span>' . $this->_AppUI->_('flat') . '</span></a>';
            $s .= '</td>' . $extra . '</tr></table>';
            echo $s;
        } else {
            if ($extra) {
                echo '<table border="0" cellpadding="2" cellspacing="0" width="100%"><tr>' . $extra . '</tr></table>';
            } else {
                echo '<img src="' . w2PfindImage('shim.gif') . '" height="10" width="1" alt="" />';
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
                $s .= '">' . ($v[2] ? $v[1] : $this->_AppUI->_($v[1])) . '</a></td>';
            }
            $s .= '<td nowrap="nowrap" class="tabsp">&nbsp;</td></tr>';
            $s .= '<tr><td width="100%" colspan="' . (count($this->tabs) * 2 + 1) . '" class="tabox">';
            echo $s;
            //Will be null if the previous selection tab is not available in the new window eg. Children tasks
            if ($this->baseInc . $this->tabs[$this->active][0] != '') {
                $this->currentTabId = $this->active;
                $this->currentTabName = $this->tabs[$this->active][1];
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