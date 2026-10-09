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
}

/**
 * The tabs are rendered as Bootstrap nav-tabs by w2p_Theme_TabBox itself.
 */
class CTabBox extends w2p_Theme_TabBox
{

}
