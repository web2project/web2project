<?php

/**
 * @package     web2project\theme
 */

abstract class w2p_Theme_Base
{
    protected $_AppUI = null;
    protected $_m     = null;
    protected $_uistyle = 'web2project';
    protected $footerJavascriptFiles = array();

    public function __construct($AppUI, $m = '') {
        $this->_AppUI = $AppUI;
        $this->_m = $m;
    }

    public function __toString()
    {
        return $this->_uistyle;
    }

    public function resolveTemplate($template)
    {
        $filepath = W2P_BASE_DIR . '/style/' . $this->_uistyle . '/' . $template . '.php';
        if (!file_exists($filepath)) {
            $filepath = W2P_BASE_DIR . '/style/_common/' . $template . '.php';
        }

        return $filepath;
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
        $ret .= '    <td valign="bottom" height="17" style="background:url(./style/' . $this->_uistyle . '/images/box_left_corner.jpg);" align="left">';
        $ret .= '        <img width="19" height="17" alt="" src="./style/' . $this->_uistyle . '/images/box_left_corner.jpg"/>';
        $ret .= '    </td>';
        $ret .= '    <td valign="bottom" width="100%" style="background:url(./style/' . $this->_uistyle . '/images/box_top.jpg);" align="left">';
        $ret .= '        <img width="19" height="17" alt="" src="./style/' . $this->_uistyle . '/images/box_top.jpg"/>';
        $ret .= '    </td>';
        $ret .= '    <td valign="bottom" style="background:url(./style/' . $this->_uistyle . '/images/box_right_corner.jpg);" align="right">';
        $ret .= '        <img width="19" height="17" alt="" src="./style/' . $this->_uistyle . '/images/box_right_corner.jpg"/>';
        $ret .= '    </td>';
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
        $ret .= '    <td valign="top" height="35" style="background:url(./style/' . $this->_uistyle . '/images/shadow_bttm_left_corner.jpg) no-repeat;" align="left">';
        $ret .= '        <img width="19" height="35" alt="" src="./style/' . $this->_uistyle . '/images/shadow_bttm_left_corner.jpg"/>';
        $ret .= '    </td>';
        $ret .= '    <td valign="top" width="100%" style="background: repeat-x url(./style/' . $this->_uistyle . '/images/shadow_bottom.jpg);" align="left">';
        $ret .= '        <img width="19" height="35" alt="" src="./style/' . $this->_uistyle . '/images/shadow_bottom.jpg"/>';
        $ret .= '    </td>';
        $ret .= '    <td valign="top" style="background:url(./style/' . $this->_uistyle . '/images/shadow_bttm_right_corner.jpg) no-repeat;" align="right">';
        $ret .= '        <img width="19" height="35" alt="" src="./style/' . $this->_uistyle . '/images/shadow_bttm_right_corner.jpg"/>';
        $ret .= '    </td>';
        $ret .= '</tr>';
        $ret .= '</tbody>';
        $ret .= '</table>';
        return $ret;
    }

    /**
     * Find and add to output the file tags required to load module-specific
     * javascript.
     */
    public function loadHeaderJS()
    {
        global $m, $a;

        // load the js base.php
        include w2PgetConfig('root_dir') . '/js/base.php';

        // Search for the javascript files to load.
        if (!isset($m)) {
            return;
        }
        $root = W2P_BASE_DIR;
        if (substr($root, -1) != '/') {
            $root .= '/';
        }

        $base = W2P_BASE_URL;
        if (substr($base, -1) != '/') {
            $base .= '/';
        }
        // Load the basic javascript used by all modules.
        echo '<script type="text/javascript" src="' . $base . 'js/base.js"></script>';

        // additionally load jquery
        echo '<script type="text/javascript" src="' . $base . 'lib/jquery/jquery.js"></script>';
        echo '<script type="text/javascript" src="' . $base . 'lib/jquery/jquery.tipTip.js"></script>';

        $this->getModuleJS($m, $a, true);
    }

    public function getModuleJS($module, $file = null, $load_all = false)
    {
        $root = W2P_BASE_DIR;
        if (substr($root, -1) != '/') {
            $root .= '/';
        }
        $base = W2P_BASE_URL;
        if (substr($base, -1) != '/') {
            $base .= '/';
        }
        if ($load_all || !$file) {
            if (file_exists($root . 'modules/' . $module . '/' . $module . '.module.js')) {
                echo '<script type="text/javascript" src="' . $base . 'modules/' . $module . '/' . $module . '.module.js"></script>';
            }
        }
        if (isset($file) && file_exists($root . 'modules/' . $module . '/' . $file . '.js')) {
            echo '<script type="text/javascript" src="' . $base . 'modules/' . $module . '/' . $file . '.js"></script>';
        }
    }

    public function addFooterJavascriptFile($pathTo)
    {
        if (!in_array($pathTo, $this->footerJavascriptFiles)) {
            $base = W2P_BASE_URL;
            if (substr($base, -1) != '/') {
                $base .= '/';
            }
            if (strpos($pathTo, $base) === false) {
                $pathTo = $base . $pathTo;
            }
            $this->footerJavascriptFiles[] = $pathTo;
        }
    }

    public function loadFooterJS()
    {
        $s = '<script type="text/javascript">';
        $s .= '$(document).ready(function() {';
        // Attach tooltips to "span" elements
        $s .= '    $("span").tipTip({maxWidth: "600px;", delay: 200, fadeIn: 150, fadeOut: 150});';
        // Move the focus to the first textbox available, while avoiding the "Global Search..." textbox
        if (canAccess('smartsearch')) {
            $s .= '    $("input[type=\'text\']:eq(1)").focus();';
        } else {
            $s .= '    $("input[type=\'text\']:eq(0)").focus();';
        }
        $s .= '});';
        $s .= '</script>';

        if (is_array($this->footerJavascriptFiles) and !empty($this->footerJavascriptFiles)) {
            while ($jsFile = array_pop($this->footerJavascriptFiles)) {
                $s .= "<script type='text/javascript' src='" . $jsFile . "'></script>";
            }
        }

        return $s;
    }

    public function loadCalendarJS()
    {
        global $AppUI;

        $s = '<style type="text/css">@import url(' . W2P_BASE_URL . '/lib/jscalendar/skins/aqua/theme.css);</style>';
        $s .= '<script type="text/javascript" src="' . W2P_BASE_URL . '/js/calendar.js"></script>';
        $s .= '<script type="text/javascript" src="' . W2P_BASE_URL . '/lib/jscalendar/calendar.js"></script>';
        if (file_exists(w2PgetConfig('root_dir') . '/lib/jscalendar/lang/calendar-' . $AppUI->user_locale . '.js')) {
            $s .= '<script type="text/javascript" src="' . W2P_BASE_URL . '/lib/jscalendar/lang/calendar-' . $AppUI->user_locale . '.js"></script>';
        } else {
            $s .= '<script type="text/javascript" src="' . W2P_BASE_URL . '/lib/jscalendar/lang/calendar-en.js"></script>';
        }
        $s .= '<script type="text/javascript" src="' . W2P_BASE_URL . '/lib/jscalendar/calendar-setup.js"></script>';
        echo $s;
        include w2PgetConfig('root_dir') . '/js/calendar.php';
    }
}