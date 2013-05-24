<?php
/**
 * This class helps us build simple list table for the various modules. Using
 *   this ensures we have similar layouts and styles across the board. You can
 *   always hardcode your own.
 *
 * @package     web2project\output
 * @author      D. Keith Casey, Jr. <contrib@caseysoftware.com>
 */

class w2p_Output_ListTable extends w2p_Output_HTMLHelper
{
    protected $_AppUI = null;

    protected $_fieldKeys = array();
    protected $_fieldNames = array();

    public function __construct($AppUI)
    {
        $this->_AppUI = $AppUI;

        parent::__construct($AppUI);
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
            $link .= $this->_AppUI->_($name);
            $link .= ($sortable) ? '</a>' : '';
            $cells .= '<th>' . $link . '</th>';
        }

        return '<tr>' . $cells . '</tr>';
    }

    public function buildRows($allRows, $customLookups = array())
    {
        $body = '';

        if (count($allRows) > 0) {
            foreach ($allRows as $row) {
                $body .= $this->buildRow($row, $customLookups);
            }
        } else {
            $body .= $this->buildEmptyRow();
        }

        return $body;
    }

    public function buildRow($rowData, $customLookups = array())
    {
        $row = '<tr>';
        $this->stageRowData($rowData);
        foreach ($this->_fieldKeys as $column) {
            $row .= $this->createCell($column, $rowData[$column], $customLookups);
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