<?php
/**
 * This class helps us build simple list table for the various modules. Using
 *   this ensures we have similar layouts and styles across the board. You can
 *   always hardcode your own.
 *
 * @package web2project\output
 * @author  D. Keith Casey, Jr. <contrib@caseysoftware.com>
 */

class w2p_Output_ListTable extends w2p_Output_HTMLHelper
{
    protected $_AppUI = null;
    protected $module = '';

    protected $_fieldKeys = array();
    protected $_fieldNames = array();

    public $cellCount = 0;
    protected $_before = array();
    protected $_after  = array();
    protected $_divider = '';

    public function __construct($AppUI)
    {
        $this->_AppUI = $AppUI;

        parent::__construct($AppUI);
    }

    public function startTable(string $id = '', string $classes = 'tbl list')
    {
        $_id = ('' == $id) ? '' : 'id="' . $id . '"';
        $_classes = 'class="' . $classes . '"';

        return "<table {$_id} {$_classes}>";
    }

    public function buildHeader($fields = array(), $sortable = false, $m = '')
    {
        $this->_fieldKeys = array_keys($fields);
        $this->_fieldNames = array_values($fields);
        $this->module = $m;

        $cells = '';
        foreach ($this->_fieldNames as $index => $name) {
            $link = ($sortable) ? '<a href="?m=' . $this->module . '&orderby=' . $this->_fieldKeys[$index] . '" class="hdr">' : '';
            $link .= $this->_AppUI->_($name);
            $link .= ($sortable) ? '</a>' : '';
            $cells .= '<th>' . $link . '</th>';
        }

        $this->cellCount = count($this->_before) + count($fields) + count($this->_after);

        return '<thead><tr>' . str_repeat('<th></th>', count($this->_before)) .
                $cells . str_repeat('<th></th>', count($this->_after)) . '</tr></thead>';
    }

    public function buildRows($allRows, $customLookups = array())
    {
        $body = '';

        if (count($allRows) > 0) {
            $_init = '';
            foreach ($allRows as $row) {
                // group rows under a heading only when addDividers() was called
                if ('' != $this->_divider) {
                    if ($_init != $row[$this->_divider]) {
                        $body .= $this->_addDivider($row);
                    }
                    $_init = $row[$this->_divider];
                }
                $body .= $this->buildRow($row, $customLookups);
            }
        } else {
            $body .= $this->buildEmptyRow();
        }

        return '<tbody>' . $body . '</tbody>';
    }

    public function buildRow($rowData, $customLookups = array())
    {
        $this->stageRowData($rowData);

        $row = '<tr>';
        $row .= $this->_buildCells($this->_before);
        foreach ($this->_fieldKeys as $column) {
            $row .= $this->createCell($column, $rowData[$column] ?? '', $customLookups);
        }
        $row .= $this->_buildCells($this->_after);
        $row .= '</tr>';

        return $row;
    }

    public function addDividers($key = '')
    {
        $this->_divider = $key;
    }
    /**
     * The heading row printed above each group of rows by addDividers().
     * Any key ending in _project (file_project, link_project, risk_project,
     * ...) groups by project: the heading is the project's name in its
     * colour, from the row's project_name and project_color_identifier.
     */
    protected function _addDivider($row)
    {
        $key = (string) $this->_divider;
        if ('_project' !== substr($key, -strlen('_project'))) {
            return '';
        }

        $style = 'text-align: left; border: outset 2px #eeeeee;';
        $project_id = (int) ($row[$key] ?? 0);
        if ($project_id) {
            $color = (string) ($row['project_color_identifier'] ?? '');
            $style .= 'background-color:#' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . ';color:' . bestColor($color);
            $link = './index.php?m=projects&a=view&project_id=' . $project_id;
            $name = $row['project_name'] ?? '';
        } else {
            $link = './index.php?m=projects';
            $name = $this->_AppUI->_('Not attached to a project');
        }
        $content = '<a href="' . $link . '">' . htmlspecialchars((string) $name, ENT_QUOTES, 'UTF-8', false) . '</a>';

        return '<tr><td colspan="' . $this->cellCount . '" style="' . $style . '">' . $content . '</td></tr>';
    }

    public function addBefore($type, $value = '')
    {
        $this->_before[$type] = $value;
    }

    public function addAfter($type, $value = '')
    {
        $this->_after[$type] = $value;
    }

    protected function _buildCells($array = array())
    {
        $cells = '';

        /**
         * Note: We can't refactor the actual td/class stuff out to the return statement because we may have multiple
         *   inserted cells processed together.. and we need them to remain separate cells.
         */
        foreach ($array as $type => $value) {
            switch($type) {
                case 'edit':
                    // @note This module determination *only* works if you've followed our naming conventions.
                    $pieces = explode('_', $value);
                    $module    = w2p_pluralize($pieces[0]);
                    $contents  = '<td class="_'.$type.'">';
                    $contents .= '<a href="./index.php?m='.$module.'&a=addedit&' . $value . '=' . $this->tableRowData[$value] .'">' .
                        w2PshowImage('icons/stock_edit-16.png', '16', '16') . '</a>';
                    $contents .= '</td>';
                    break;
                case 'select':
                    $contents  = '<td class="_'.$type.'">';
                    $contents .= '<input type="checkbox" value="' . $this->tableRowData[$value] . '" name="' . $value . '[]" />';
                    $contents .= '</td>';
                    break;
                case 'log':
                    $pieces = explode('_', $value);
                    $module    = w2p_pluralize($pieces[0]);
                    $contents  = '<td class="_'.$type.'">';
                    $contents .= '<a href="./index.php?m='.$module.'&a=view&tab=1&' . $value . '=' . $this->tableRowData[$value] .'">' .
                        w2PshowImage('icons/edit_add.png', '16', '16') . '</a>';
                    $contents .= '</td>';
                    break;
                case 'pin':
                    $pinned = !empty($this->tableRowData['task_pinned']);
                    $image = $pinned ? 'pin.gif' : 'unpin.gif';
                    $pin  = $pinned ? -1 : 1;
                    $pieces = explode('_', $value);
                    $module    = w2p_pluralize($pieces[0]);
                    $contents  = '<td class="_'.$type.'">';
                    $contents .= '<a href="./index.php?m='.$module.'&pin=' . $pin . '&' . $value . '=' . $this->tableRowData[$value] .'">' .
                        w2PshowImage('icons/' . $image, '16', '16') . '</a>';
                    $contents .= '</td>';
                    break;
                case 'url':
                    $contents  = '<td class="_'.$type.'">';
                    $url = w2p_safe_url($this->tableRowData[$value]);
                    if ('' != $url) {
                        $contents .= '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8', false) . '" target="_blank">' .
                            w2PshowImage('forward.png', '16', '16') . '</a>';
                    }
                    $contents .= '</td>';
                    break;
                case 'watch':
                    $contents  = '<td class="_'.$type.'">';
                    $contents .= '<input type="checkbox" name="forum_' .
                        $this->tableRowData[$value] . '"' .
                        ($this->tableRowData['watch_user'] ? 'checked="checked"' : '') . ' />';
                    $contents .= '</td>';
                    break;
                default:
                    $contents = '<td></td>';
            }
            $cells .= $contents;
        }

        return $cells;
    }

    public function buildEmptyRow()
    {
        $row = '<tr><td colspan="'.$this->cellCount.'">' .
                $this->_AppUI->_('No data available') . '</td></tr>';

        return $row;
    }

    public function endTable()
    {
        return '</table>';
    }
}