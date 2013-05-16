<?php

/**
 * @package     web2project\theme
 */

class w2p_Theme_Base
{
    protected $_AppUI = null;

    public function __construct($AppUI) {
        $this->_AppUI = $AppUI;
    }

    public function buildHeaderNavigation($rootTag = '', $innerTag = '', $dividingToken = '', $m = '') {
        $s = '';
        $nav = $this->_AppUI->getMenuModules();

        $s .= ($rootTag != '') ? "<$rootTag id=\"headerNav\">" : '';
        $links = array();
        foreach ($nav as $module) {
            if (canAccess($module['mod_directory'])) {
                $link = ($innerTag != '') ? "<$innerTag>" : '';
                $class = ($m == $module['mod_directory']) ? ' class="module"' : '';
                $link .= '<a href="?m=' . $module['mod_directory'] . '"'.$class.'>' .
                        $this->_AppUI->_($module['mod_ui_name']) . '</a>';
                $link .= ($innerTag != '') ? "</$innerTag>" : '';
                $links[] = $link;
            }
        }
        $s .= implode($dividingToken, $links);
        $s .= ($rootTag != '') ? "</$rootTag>" : '';

        return $s;
    }
}