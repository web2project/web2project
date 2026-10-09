<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}

/**
 * The Bootstrap 5.3 theme.
 *
 * Bootstrap itself is bundled in style/bootstrap/css and style/bootstrap/js so
 * the theme works without any external network access. Images which are not
 * provided by this theme fall back to the ones in style/web2project/images.
 *
 * @package web2project\theme
 */
class style_bootstrap extends w2p_Theme_Base
{
    protected $_uistyle = 'bootstrap';

    /**
     * Builds the module navigation as Bootstrap navbar items.
     *
     * The parameters are accepted for compatibility with the base class but
     * the markup is always a <ul class="navbar-nav">.
     */
    public function buildHeaderNavigation($rootTag = '', $innerTag = '', $dividingToken = '')
    {
        $links = array();
        foreach ($this->_AppUI->getMenuModules() as $module) {
            if (canAccess($module['mod_directory'])) {
                $active = ($this->_m == $module['mod_directory']);
                $links[] = '<li class="nav-item"><a href="?m=' . $module['mod_directory'] . '" class="nav-link' .
                    ($active ? ' active" aria-current="page' : '') . '">' .
                    $this->_AppUI->_($module['mod_ui_name']) . '</a></li>';
            }
        }

        return '<ul class="navbar-nav me-auto" id="headerNav">' . implode('', $links) . '</ul>';
    }

    /**
     * Messages are rendered as dismissible Bootstrap alerts.
     */
    public function messageHandler($reset = true)
    {
        $msg = $this->_AppUI->getMsg($reset);

        return $msg ? '<div class="alert alert-light border py-2 w2p-message">' . $msg . '</div>' : '';
    }

    /**
     * The content area is a plain Bootstrap container so the decorative box
     *   top and bottom of the table based themes are not needed.
     */
    public function styleRenderBoxTop()
    {
        return '';
    }

    public function styleRenderBoxBottom($tab = 0)
    {
        return '';
    }
}

/**
 * Renders the module tabs as Bootstrap nav-tabs.
 */
class CTabBox extends w2p_Theme_TabBox
{
    public function show($extra = '', $js_tabs = false, $alignment = 'left', $opt_flat = true)
    {
        $this->loadExtras($this->m, $this->a);

        reset($this->tabs);
        if ($extra) {
            echo '<div class="d-flex flex-wrap gap-2 align-items-center mb-2 w2p-tab-extra">' . $extra . '</div>';
        }

        if ($this->active < 0 || $this->_AppUI->getPref('TABVIEW') == 2) {
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
}
