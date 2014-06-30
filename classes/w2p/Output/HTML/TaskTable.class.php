<?php
/**
 * This class helps us build simple task table for the various modules. Using this ensures we have similar layouts and
 *   styles across the board. You can always hardcode your own.
 *
 * @package     web2project\output\html
 * @author      D. Keith Casey, Jr. <contrib@caseysoftware.com>
 */

class w2p_Output_HTML_TaskTable extends w2p_Output_ListTable
{
    public function buildRow($rowData, $customLookups = array())
    {
        $this->stageRowData($rowData);
        $class = w2pFindTaskComplete($rowData['task_start_date'], $rowData['task_end_date'], $rowData['task_percent_complete']);

        $row = '<tr class="'.$class.'">';
        $row .= $this->_buildBeforeCells();
        foreach ($this->_fieldKeys as $column) {
            if ('task_name' == $column ) {
                if ($rowData['depth'] > 1) {
                    $prefix = str_repeat('&nbsp;', $rowData['depth']) . '<img src="' . w2PfindImage('corner-dots.gif') . '" />';
                }
                if ($rowData['children'] > 0) {
                    $prefix = str_repeat('&nbsp;', $rowData['depth'] * 1.5 ) . '<img src="' . w2PfindImage('icons/collapse.gif') . '" />&nbsp;';
                }

                $rowData[$column] = $prefix . $rowData[$column];
            }
            $row .= $this->createCell($column, $rowData[$column], $customLookups);
        }
        $row .= '</tr>';

        return $row;
    }
}