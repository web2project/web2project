<?php

/**
 * @package     web2project\theme
 */

class w2p_Theme_TitleBlock {
    /**
    @var string The main title of the page */
    public $title = '';
    /**
    @var string The name of the icon used to the left of the title */
    public $icon = '';
    /**
    @var string The name of the module that this title block is displaying in */
    public $module = '';
    /**
    @var array An array of the table 'cells' to the right of the title block and for bread-crumbs */
    public $cells = null;

    protected $_AppUI = null;
    protected $_w2Pconfig = null;
    /**
     * The constructor
     *
     * Assigns the title, icon, module and help reference.  If the user does not
     * have permission to view the help module, then the context help icon is
     * not displayed.
     */
    public function __construct($title, $icon = '', $module = '') {
        global $AppUI;
        $this->_AppUI = $AppUI;
        global $w2Pconfig;
        $this->_w2Pconfig = $w2Pconfig;

        $this->title = $title;
        $this->icon = $icon;
        $this->module = $module;
        $this->cells1 = array();
        $this->cells2 = array();
        $this->crumbs = array();
        $this->showhelp = canView('help');
        $this->count = 0;
    }
    /**
     * Adds a table 'cell' beside the Title string
     *
     * Cells are added from left to right.
     */
    public function addCell($data = '', $attribs = '', $prefix = '', $suffix = '') {
        $this->cells1[] = array($attribs, $data, $prefix, $suffix);
    }

    public function addSearchCell($search)
    {
        $this->addCell('<form name="searchform" action="?m=' . $this->module . '" method="post" accept-charset="utf-8">
                <input type="text" class="text" name="search_string" value="' . $search . '" /></form>');
        $this->addCell($this->_AppUI->_('Search') . ':');
    }

    public function addFilterCell($label, $field, $values, $value)
    {
        $form = 'filter' . $this->count;

        $this->addCell('<form action="?m=' . $this->module . '" method="post" name="'.$form.'" accept-charset="utf-8">' .
            arraySelect($values, $field, 'size="1" class="text" onChange="document.'.$form.'.submit();"', $value, false) .
            '</form>');
        $this->addCell($this->_AppUI->_($label) . ':');

        $this->count++;
    }

    public function addButton($label, $url)
    {
        $button = '<input type="submit" class="button btn btn-small dropdown-toggle" value="' . $this->_AppUI->_($label) . '">';
        $form = '<form action="' . $url . '" method="post" accept-charset="utf-8">' . $button . '</form>';

        $this->addCell($form);
    }

    /**
     * Adds a table 'cell' to left-aligned bread-crumbs
     *
     * Cells are added from left to right.
     */
    public function addCrumb($link, $label, $icon = '') {
        $this->crumbs[$link] = array($label, $icon);
    }

    /**
     * @param $m
     * @param $id
     * @param string $a
     */
    public function addViewLink($module, $key, $a = 'view')
    {
        if ($key) {
            $this->addCrumb('?m=' . w2p_pluralize($module) . '&a=' . $a .
                    '&' . $module . '_id=' . $key, 'view this ' . $module);
        }
    }

    /**
     * Adds a table 'cell' to the right-aligned bread-crumbs
     *
     * Cells are added from left to right.
     */
    public function addCrumbRight($data = '', $attribs = '', $prefix = '', $suffix = '') {
        $this->cells2[] = array($attribs, $data, $prefix, $suffix);
    }
    /**
     * Creates a standarised, right-aligned delete bread-crumb and icon.
     */
    public function addCrumbDelete($title, $canDelete = '', $msg = '') {
        $this->addCrumbRight('<a class="delete" href="javascript:delIt()" title="' . ($canDelete ? '' : $msg) . '"><div>' . $this->_AppUI->_($title) . '</div></a>');
    }
    /**
     * The drawing function
     */
    public function show() {
        global $a;
        $m = $this->module;
        $this->loadExtraCrumbs($m, $a);

        $s = '';

        $s .= '<div class="module-title">';
        if ($this->icon) {
            $s .= '<div class="icon">';
            $s .= w2PshowImage($this->icon, '', '', '', '', $this->module);
            $s .= '</div>';
        }
        $s .= '<h1>' . $this->_AppUI->_($this->title) . '</h1>';

        $s .= '<ul class="crumb-right">';
        foreach ($this->cells1 as $c) {
            if ('' == $c[1]) {
                continue;
            }
            $s .= $c[2] ? $c[2] : '';
            $s .= '<li class="right"' . ($c[0] ? (' ' . $c[0]) : '') . '>';
            $s .= $c[1] ? $c[1] : '&nbsp;';
            $s .= '</li>';
            $s .= $c[3] ? $c[3] : '';
        }
        $s .= '</ul>';
        $s .= '</div>';

        if (count($this->crumbs) || count($this->cells2)) {
            $crumbs = array();

            $s .= '<div class="module-nav">';
            foreach ($this->crumbs as $k => $v) {
                $t = $v[1] ? '<img src="' . w2PfindImage($v[1], $this->module) . '" />&nbsp;' : '';
                $t .= $this->_AppUI->_($v[0]);
                $crumbs[] = '<li><a href="'.$k.'"><div>'.$t.'</div></a></li>';
            }

            $s .= '<div class="crumb"><ul>' . implode('', $crumbs) . '</ul></div>';
            if (count($this->cells2)) {
                $s .= '<ul class="crumb-right">';
                foreach ($this->cells2 as $c) {
                    $s .= $c[2] ? $c[2] : '';
                    $s .= '<li ' . ($c[0] ? " $c[0]" : '') . '>';
                    $s .= $c[1] ? $c[1] : '&nbsp;';
                    $s .= '</li>';
                    $s .= $c[3] ? $c[3] : '';
                }
                $s .= '</ul>';
            }
            $s .= '</div>';
        }


        echo '' . $s;
        if (($a != 'index' || $m == 'system' || $m == 'events' || $m == 'smartsearch') && !$this->_AppUI->boxTopRendered) {
            echo $this->_AppUI->getTheme()->styleRenderBoxTop();
            $this->_AppUI->boxTopRendered = true;
        }
    }

    public function loadExtraCrumbs($module, $file = null) {
        if (!isset($_SESSION['all_crumbs']) || !isset($_SESSION['all_crumbs'][$module])) {
            return false;
        }

        if ($file) {
            if (isset($_SESSION['all_crumbs'][$module][$file]) && is_array($_SESSION['all_crumbs'][$module][$file])) {
                $crumb_array = &$_SESSION['all_crumbs'][$module][$file];
            } else {
                return false;
            }
        } else {
            $crumb_array = &$_SESSION['all_crumbs'][$module];
        }
        $crumb_count = 0;
        foreach ($crumb_array as $crumb_elem) {
            if (isset($crumb_elem['module']) && $this->_AppUI->isActiveModule($crumb_elem['module'])) {
                $crumb_count++;
                include $crumb_elem['file'] . '.php';
            }
        }
        return $crumb_count;
    }

    public function findCrumbModule($crumb) {
        global $m, $a;

        if (!isset($_SESSION['all_crumbs']) || !isset($_SESSION['all_crumbs'][$m])) {
            return false;
        }

        if (isset($a)) {
            if (isset($_SESSION['all_crumbs'][$m][$a]) && is_array($_SESSION['all_crumbs'][$m][$a])) {
                $crumb_array = &$_SESSION['all_crumbs'][$m][$a];
            } else {
                $crumb_array = &$_SESSION['all_crumbs'][$m];
            }
        } else {
            $crumb_array = &$_SESSION['all_crumbs'][$m];
        }

        list($file, $name) = $this->crumbs[$crumb];
        foreach ($crumb_array as $crumb_elem) {
            if (isset($crumb_elem['name']) && $crumb_elem['name'] == $name && $crumb_elem['file'] == $file) {
                return $crumb_elem['module'];
            }
        }
        return false;
    }
}