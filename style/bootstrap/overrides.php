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
     * Bootstrap Icons class for each core module's navigation entry.
     *
     * @var array<string, string>
     */
    protected $_moduleIcons = array(
        'companies'       => 'bi-building',
        'projects'        => 'bi-kanban',
        'tasks'           => 'bi-list-check',
        'events'          => 'bi-calendar3',
        'contacts'        => 'bi-person-lines-fill',
        'files'           => 'bi-folder2-open',
        'links'           => 'bi-link-45deg',
        'forums'          => 'bi-chat-left-text',
        'departments'     => 'bi-diagram-3',
        'resources'       => 'bi-box-seam',
        'projectdesigner' => 'bi-pencil-square',
        'smartsearch'     => 'bi-search',
        'reports'         => 'bi-bar-chart-line',
        'history'         => 'bi-clock-history',
        'help'            => 'bi-question-circle',
        'users'           => 'bi-people',
        'system'          => 'bi-gear',
    );

    /**
     * The sidebar section each core module is listed under; other modules
     * go under "Modules".
     *
     * @var array<string, string>
     */
    protected $_moduleGroups = array(
        'companies'       => 'Work',
        'projects'        => 'Work',
        'tasks'           => 'Work',
        'events'          => 'Work',
        'contacts'        => 'Work',
        'files'           => 'Work',
        'links'           => 'Work',
        'forums'          => 'Work',
        'departments'     => 'Work',
        'resources'       => 'Work',
        'projectdesigner' => 'Work',
        'smartsearch'     => 'Tools',
        'reports'         => 'Tools',
        'history'         => 'Tools',
        'help'            => 'Tools',
        'users'           => 'Administration',
        'system'          => 'Administration',
    );

    /**
     * Returns the Bootstrap Icons class used for a module.
     *
     * @param string $module The module directory
     *
     * @return string
     */
    public function moduleIcon($module)
    {
        return isset($this->_moduleIcons[$module]) ? $this->_moduleIcons[$module] : 'bi-grid';
    }

    /**
     * Builds the module navigation for the sidebar, grouped into sections.
     *
     * The parameters are accepted for compatibility with the base class but
     * the markup is always the sidebar's <nav>.
     */
    public function buildHeaderNavigation($rootTag = '', $innerTag = '', $dividingToken = '')
    {
        global $m;

        // the theme object is usually created without the current module
        $current = $this->_m ? $this->_m : $m;
        $groups = array('Work' => array(), 'Tools' => array(), 'Modules' => array(), 'Administration' => array());
        foreach ($this->_AppUI->getMenuModules() as $module) {
            $dir = $module['mod_directory'];
            if (!canAccess($dir)) {
                continue;
            }
            $active = ($current == $dir);
            $name = $this->_AppUI->_($module['mod_ui_name']);
            $group = isset($this->_moduleGroups[$dir]) ? $this->_moduleGroups[$dir] : 'Modules';
            $groups[$group][] = '<li class="nav-item"><a href="?m=' . $dir . '" class="nav-link' .
                ($active ? ' active" aria-current="page' : '') . '" title="' . $name . '">' .
                '<i class="bi ' . $this->moduleIcon($dir) . '" aria-hidden="true"></i>' .
                '<span class="nav-text">' . $name . '</span></a></li>';
        }

        $s = '<nav class="w2p-sidebar-nav"><ul class="nav flex-column" id="headerNav">';
        foreach ($groups as $group => $links) {
            if (count($links)) {
                $s .= '<li class="nav-heading">' . $this->_AppUI->_($group) . '</li>' . implode('', $links);
            }
        }

        return $s . '</ul></nav>';
    }
}

/**
 * The tabs are rendered as Bootstrap nav-tabs by w2p_Theme_TabBox itself.
 */
class CTabBox extends w2p_Theme_TabBox
{

}
