<?php

/**
 * @package web2project\theme
 */

class w2p_Theme_TabBox {
    /**
     * @var array
     */
    public $tabs = null;
    /**
     * @var int The active tab
     */
    public $active = null;
    /**
     * @var string The base URL query string to prefix tab links
     */
    public $baseHRef = null;
    /**
     * @var string The base path to prefix the include file
     */
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
     *
     * @param string $baseHRef The base URL query string to prefix tab links
     * @param string $baseInc The base path to prefix the include file
     * @param int $active The active tab
     * @param string $javascript Optional javascript method to be used to execute tabs.
     *    Must support 2 arguments, currently active tab, new tab to activate.
     */
    public function __construct($baseHRef = '', $baseInc = '', $active = 0, $javascript = null) {
        /** @var w2p_Core_CAppUI $AppUI */
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
     *
     * @return string
     */
    public function getTabName($idx) {
        return $this->tabs[$idx][1];
    }
    /**
     * Adds a tab to the object
     *
     * @param string $file File to include
     * @param string $title The display title/name of the tab
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
     * Displays the tabbed box as a list of tabs (nav-tabs) followed by the
     * content of the active tab, or every tab one after the other in the
     * flat view.
     *
     * This function may be overridden by a theme.
     *
     * @param string $extra     Markup displayed above the tabs
     * @param bool   $js_tabs   Render every tab and switch between them client side
     * @param string $alignment 'left' or 'right' alignment of the tabs
     */
    public function show($extra = '', $js_tabs = false, $alignment = 'left') {
        $this->loadExtras($this->m, $this->a);

        reset($this->tabs);
        if ($extra) {
            echo '<div class="d-flex flex-wrap gap-2 align-items-center mb-2 w2p-tab-extra">' . $extra . '</div>';
        }

        if (!$this->isTabbed()) {
            // flat view, active = -1
            echo '<div class="w2p-tabs-flat">';
            foreach ($this->tabs as $k => $v) {
                echo '<section class="mb-4">';
                echo '<h2 class="h5 border-bottom pb-1">' . ($v[2] ? $v[1] : $this->_AppUI->_($v[1])) . '</h2>';
                $this->currentTabId = $k;
                $this->currentTabName = $v[1];
                include $this->baseInc . $v[0] . '.php';
                echo '</section>';
            }
            echo '</div>';
            return;
        }

        if (count($this->tabs) - 1 < $this->active) {
            //Last selected tab is not available in this view. eg. Child tasks
            $this->active = 0;
        }

        $s = '<ul class="nav nav-tabs w2p-tabs' . ('right' == $alignment ? ' justify-content-end' : '') . '"' .
            ($js_tabs ? ' role="tablist"' : '') . '>';
        foreach ($this->tabs as $k => $v) {
            $active = ($k == $this->active);
            $title = ($v[2] ? $v[1] : $this->_AppUI->_($v[1]));
            $s .= '<li class="nav-item"' . ($js_tabs ? ' role="presentation"' : '') . '>';
            if ($js_tabs) {
                $s .= '<button type="button" id="toptab_' . $k . '" class="nav-link' . ($active ? ' active' : '') .
                    '" data-bs-toggle="tab" data-bs-target="#tab_' . $k . '" role="tab" aria-controls="tab_' . $k .
                    '" aria-selected="' . ($active ? 'true' : 'false') . '">' . $title . '</button>';
            } else {
                $href = $this->javascript ?
                    'javascript:' . $this->javascript . '(' . $this->active . ', ' . $k . ')' :
                    $this->baseHRef . 'tab=' . $k;
                $s .= '<a id="toptab_' . $k . '" class="nav-link' . ($active ? ' active" aria-current="page' : '') .
                    '" href="' . $href . '">' . $title . '</a>';
            }
            $s .= '</li>';
        }
        $s .= '</ul>';
        $s .= '<div class="tab-content border border-top-0 rounded-bottom p-2 mb-3 w2p-tab-content">';
        echo $s;

        if ($js_tabs) {
            foreach ($this->tabs as $k => $v) {
                $active = ($k == $this->active);
                echo '<div class="tab-pane fade' . ($active ? ' show active' : '') . '" id="tab_' . $k .
                    '" role="tabpanel" aria-labelledby="toptab_' . $k . '">';
                $this->currentTabId = $k;
                $this->currentTabName = $v[1];
                require $this->baseInc . $v[0] . '.php';
                echo '</div>';
            }
        } elseif (isset($this->tabs[$this->active][0]) && $this->tabs[$this->active][0] != '') {
            //Will be null if the previous selection tab is not available in the new window eg. Children tasks
            $this->currentTabId = $this->active;
            $this->currentTabName = $this->tabs[$this->active][1];
            require $this->baseInc . $this->tabs[$this->active][0] . '.php';
        }
        echo '</div>';
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