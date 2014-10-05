<?php
/**
 * This class helps us build simple list table for the various modules. It's different from the normal list table in that we get
 *   horizontal project dividers for each set of items.
 *
 * @package     web2project\output\html
 * @author      D. Keith Casey, Jr. <contrib@caseysoftware.com>
 */

class w2p_Output_HTML_ProjectListTable extends w2p_Output_ListTable
{
    protected $project_id_name = 'project_id';

    public function setProjectIdName($project_id_name)
    {
        $this->project_id_name = $project_id_name;
    }

    public function buildRows($allRows, $customLookups = array())
    {
        $body = '';
        $project_id = -1;

        if (count($allRows) > 0) {
            foreach ($allRows as $row) {
                if ($project_id != $row[$this->project_id_name]) {
                    if ($project_id < 1) {
                        $href = './index.php?m=projects';
                        $project_name  = $this->_AppUI->_('Not attached to a project');
                        $project_color = 'f4efe3';
                    } else {
                        $project = new CProject();
                        $project->load($project_id);
                        $href = './index.php?m=projects&a=view&project_id=' . $project_id;
                        $project_name = $project->project_name;
                        $project_color = $project->project_color_identifier;
                    }
                    $style = 'background-color:#' . $project_color . ';color:' . bestColor($project_color);

                    $s = '<tr>';
                    $s .= '<td colspan="' . count($row) . '" style="' . $style . '">';
                    $s .= '<a href="' . $href . '">';
                    $s .= '<span style="' . $style . '">' . $project_name . '</span></a>';
                    $s .= '</td></tr>';

                    $body .= $s;
                }
                $body .= $this->buildRow($row, $customLookups);
                $project_id = $row[$this->project_id_name];
            }
        } else {
            $body .= $this->buildEmptyRow();
        }

        return $body;
    }
}