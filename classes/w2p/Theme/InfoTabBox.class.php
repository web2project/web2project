<?php

/**
 * This class is used to do second level tabs or subtabs aligned to the right by default
 *
 * @package web2project\theme
 * @author  Pedro Azevedo
 */

class w2p_Theme_InfoTabBox extends w2p_Theme_TabBox {

    public function show($extra = '', $js_tabs = false, $alignment = 'left') {
        /** @var w2p_Core_CAppUI $AppUI */
        global $AppUI, $currentInfoTabId, $currentInfoTabName, $m;

        reset($this->tabs);
        if ($extra) {
            echo '<div class="d-flex flex-wrap gap-2 align-items-center mb-2 w2p-tab-extra">' . $extra . '</div>';
        }

        if ($this->active < 0 || $AppUI->getPref('TABVIEW') == 2) {
            // flat view, active = -1
            echo '<div class="w2p-tabs-flat">';
            foreach ($this->tabs as $k => $v) {
                echo '<section class="mb-3">';
                echo '<h3 class="h6 border-bottom pb-1">' . ($v[2] ? $v[1] : $AppUI->_($v[1])) . '</h3>';
                $currentInfoTabId = $k;
                $currentInfoTabName = $v[1];
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

        $s = '<ul class="nav nav-tabs w2p-infotabs' . ('right' == $alignment ? ' justify-content-end' : '') . '">';
        foreach ($this->tabs as $k => $v) {
            $active = ($k == $this->active);
            $title = ($v[2] ? $v[1] : $AppUI->_($v[1]));
            if ($js_tabs) {
                $s .= '<li class="nav-item"><button type="button" id="infotoptab_' . $k . '" class="nav-link' . ($active ? ' active' : '') .
                    '" data-bs-toggle="tab" data-bs-target="#infotab_' . $k . '">' . $title . '</button></li>';
                continue;
            }
            if ($this->javascript) {
                $href = 'javascript:' . $this->javascript . '(' . $this->active . ', ' . $k . ')';
            } elseif ($m == 'projectdesigner' && strpos($v[1], 'Invoices') === false) {
                $href = $this->baseHRef . 'infotab_bil=' . $k . '#billings';
            } elseif ($m == 'projectdesigner') {
                $href = $this->baseHRef . 'infotab_inv=' . $k . '#invoices';
            } else {
                $href = $this->baseHRef . 'infotab=' . $k;
            }
            $s .= '<li class="nav-item"><a id="infotoptab_' . $k . '" class="nav-link' . ($active ? ' active" aria-current="page' : '') . '" href="' . $href . '">' .
                $title . '</a></li>';
        }
        $s .= '</ul>';
        $s .= '<div class="tab-content border border-top-0 rounded-bottom p-2 mb-3 w2p-infotab-content">';
        echo $s;

        //Will be null if the previous selection tab is not available in the new window eg. Children tasks
        if (!$js_tabs && $this->tabs[$this->active][0] != '') {
            $currentInfoTabId = $this->active;
            $currentInfoTabName = $this->tabs[$this->active][1];
            require $this->baseInc . $this->tabs[$this->active][0] . '.php';
        }
        if ($js_tabs) {
            foreach ($this->tabs as $k => $v) {
                echo '<div class="tab-pane fade' . ($k == $this->active ? ' show active' : '') . '" id="infotab_' . $k . '">';
                $currentInfoTabId = $k;
                $currentInfoTabName = $v[1];
                require $this->baseInc . $v[0] . '.php';
                echo '</div>';
            }
        }
        echo '</div>';
    }
}