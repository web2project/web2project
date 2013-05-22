<?php
/**
 * This class helps us build simple list table for the various modules. Using
 *   this ensures we have similar layouts and styles across the board. You can
 *   always hardcode your own.
 *
 * @package     web2project\output
 * @author      D. Keith Casey, Jr. <contrib@caseysoftware.com>
 */

class w2p_Output_ListTable
{
    protected $_AppUI = null;
    protected $_m     = null;

    protected $_fieldKeys = array();
    protected $_fieldNames = array();

    public function __construct($AppUI, $m)
    {
        $this->_AppUI = $AppUI;
        $this->_m = $m;
    }

    public function startTable($classes = 'tbl list')
    {
        return '<table class="' . $classes . '">';
    }

    public function buildHeader($fields = array(), $sortable = false, $m = '')
    {
        $this->_fieldKeys = array_keys($fields);
        $this->_fieldNames = array_values($fields);

        $cells = '';
        foreach ($this->_fieldNames as $index => $name) {
            $link = ($sortable) ? '<a href="?m=' . $m . '&orderby=' . $this->_fieldKeys[$index] . '" class="hdr">' : '';
            $link .= $this->_AppUI->_($this->_fieldNames[$index]);
            $link .= ($sortable) ? '</a>' : '';
            $cells .= '<th>' . $link . '</th>';
        }

        return '<tr>' . $cells . '</tr>';
    }

    public function buildRow($data, $htmlHelper, $customLookups)
    {
        $row = '<tr>';
        $htmlHelper->stageRowData($data);
        foreach ($this->_fieldKeys as $index => $column) {
            $row .= $htmlHelper->createCell($this->_fieldKeys[$index], $data[$this->_fieldKeys[$index]], $customLookups);
        }
        $row .= '</tr>';

        return $row;
    }

    public function buildEmptyRow()
    {
        $row = '<tr><td colspan="'.count($this->_fieldNames).'">' .
                $this->_AppUI->_('No data available') . '</td></tr>';

        return $row;
    }

    public function endTable()
    {
        return '</table>';
    }
}