<?php

/**
 * @package     web2project\theme
 */

class w2p_Theme_Base
{
    protected $_AppUI = null;
    protected $_m     = null;
    protected $_uistyle = 'web2project';

    public function __construct($AppUI, $m = '') {
        $this->_AppUI = $AppUI;
        $this->_m = $m;
    }

    public function buildHeaderNavigation($rootTag = '', $innerTag = '', $dividingToken = '') {
        $s = '';
        $nav = $this->_AppUI->getMenuModules();

        $s .= ($rootTag != '') ? "<$rootTag id=\"headerNav\">" : '';
        $links = array();
        foreach ($nav as $module) {
            if (canAccess($module['mod_directory'])) {
                $link = ($innerTag != '') ? "<$innerTag>" : '';
                $class = ($this->_m == $module['mod_directory']) ? ' class="module"' : '';
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

    public function messageHandler($reset = true)
    {
        return $this->_AppUI->getMsg($reset);
    }

    public function styleRenderBoxTop() {
        global $currentInfoTabId;
        if ($currentInfoTabId) {
            return '';
        }

        $ret = '<table width="100%" cellspacing="0" cellpadding="0" border="0">';
        $ret .= '<tbody>';
        $ret .= '<tr>';
        $ret .= '	<td valign="bottom" height="17" style="background:url(./style/' . $this->_uistyle . '/images/box_left_corner.jpg);" align="left">';
        $ret .= '		<img width="19" height="17" alt="" src="./style/' . $this->_uistyle . '/images/box_left_corner.jpg"/>';
        $ret .= '	</td>';
        $ret .= '	<td valign="bottom" width="100%" style="background:url(./style/' . $this->_uistyle . '/images/box_top.jpg);" align="left">';
        $ret .= '		<img width="19" height="17" alt="" src="./style/' . $this->_uistyle . '/images/box_top.jpg"/>';
        $ret .= '	</td>';
        $ret .= '	<td valign="bottom" style="background:url(./style/' . $this->_uistyle . '/images/box_right_corner.jpg);" align="right">';
        $ret .= '		<img width="19" height="17" alt="" src="./style/' . $this->_uistyle . '/images/box_right_corner.jpg"/>';
        $ret .= '	</td>';
        $ret .= '</tr>';
        $ret .= '</tbody>';
        $ret .= '</table>';
        return $ret;
    }

    public function styleRenderBoxBottom($tab = 0) {
        if (-1 == $tab) {
            return '';
        }

        $ret = '<table width="100%" cellspacing="0" cellpadding="0" border="0">';
        $ret .= '<tbody>';
        $ret .= '<tr>';
        $ret .= '	<td valign="top" height="35" style="background:url(./style/' . $this->_uistyle . '/images/shadow_bttm_left_corner.jpg) no-repeat;" align="left">';
        $ret .= '		<img width="19" height="35" alt="" src="./style/' . $this->_uistyle . '/images/shadow_bttm_left_corner.jpg"/>';
        $ret .= '	</td>';
        $ret .= '	<td valign="top" width="100%" style="background: repeat-x url(./style/' . $this->_uistyle . '/images/shadow_bottom.jpg);" align="left">';
        $ret .= '		<img width="19" height="35" alt="" src="./style/' . $this->_uistyle . '/images/shadow_bottom.jpg"/>';
        $ret .= '	</td>';
        $ret .= '	<td valign="top" style="background:url(./style/' . $this->_uistyle . '/images/shadow_bttm_right_corner.jpg) no-repeat;" align="right">';
        $ret .= '		<img width="19" height="35" alt="" src="./style/' . $this->_uistyle . '/images/shadow_bttm_right_corner.jpg"/>';
        $ret .= '	</td>';
        $ret .= '</tr>';
        $ret .= '</tbody>';
        $ret .= '</table>';
        return $ret;
    }
}