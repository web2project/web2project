<?php

/**
 * @package web2project\theme
 */

abstract class w2p_Theme_Base
{
    protected $_AppUI = null;
    protected $_m     = null;
    protected $_uistyle = 'web2project';
    /**
     * Scripts queued for the page footer. CAppUI::getTheme() builds a new theme
     * object on every call, so the queue is shared by all instances for the
     * request; an instance property would be lost before footer.php runs.
     *
     * @var array<int, string>
     */
    protected static $footerJavascriptFiles = array();

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

    /**
     * Opens the decorated box around the page content.
     *
     * The content area is laid out with CSS so there is nothing to render by
     * default. Themes which draw a frame around the content override this.
     */
    public function styleRenderBoxTop() {
        return '';
    }

    /**
     * Closes the decorated box around the page content.
     *
     * @param int $tab The current tab, -1 for the flat view
     */
    public function styleRenderBoxBottom($tab = 0) {
        return '';
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
        $base = W2P_BASE_URL;
        if (substr($base, -1) != '/') {
            $base .= '/';
        }
        if (strpos($pathTo, $base) === false) {
            $pathTo = $base . $pathTo;
        }
        // Compare the full URL, which is what the queue holds.
        if (!in_array($pathTo, static::$footerJavascriptFiles)) {
            static::$footerJavascriptFiles[] = $pathTo;
        }
    }

    public function loadFooterJS()
    {
        $s = '<script type="text/javascript">';
        $s .= '$(document).ready(function() {';
        // Attach tooltips to "span" elements
        $s .= '    $("span").tipTip({maxWidth: "600px;", delay: 200, fadeIn: 150, fadeOut: 150});';
        // Move the focus to the first textbox available, skipping the header's "Global Search..." box (#keyword)
        $s .= '    $("input[type=\'text\']:not(#keyword):first").focus();';
        $s .= '});';
        $s .= '</script>';

        if (!empty(static::$footerJavascriptFiles)) {
            while ($jsFile = array_shift(static::$footerJavascriptFiles)) {
                $s .= "<script type='text/javascript' src='" . $jsFile . "'></script>";
            }
        }

        return $s;
    }

    public function loadCalendarJS()
    {
        /** @var w2p_Core_CAppUI $AppUI */
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