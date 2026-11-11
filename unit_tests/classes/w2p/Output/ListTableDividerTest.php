<?php
/**
 * addDividers() groups by any *_project key (#560).
 *
 * @package    web2project
 * @subpackage unit_tests
 * @license    Clear BSD
 */

class w2p_Output_ListTableDividerTest extends CommonSetup
{
    protected function rows($key)
    {
        return array(
            array($key => '0', 'project_name' => '', 'project_color_identifier' => '', 'x_count' => 1),
            array($key => '7', 'project_name' => 'Alpha <b>', 'project_color_identifier' => 'FFCC00', 'x_count' => 2),
            array($key => '7', 'project_name' => 'Alpha <b>', 'project_color_identifier' => 'FFCC00', 'x_count' => 3),
        );
    }

    public function testAnyProjectKeyGetsDividers()
    {
        foreach (array('file_project', 'link_project', 'risk_project') as $key) {
            $table = new w2p_Output_ListTable($this->_AppUI);
            $table->buildHeader(array('x_count' => 'Count'));
            $table->addDividers($key);
            $html = $table->buildRows($this->rows($key));

            $this->assertSame(2, substr_count($html, 'colspan="1"'), $key);
            $this->assertStringContainsString('<a href="./index.php?m=projects">Not attached to a project</a>', $html, $key);
            $this->assertStringContainsString('background-color:#FFCC00', $html, $key);
            $this->assertStringContainsString('<a href="./index.php?m=projects&a=view&project_id=7">Alpha &lt;b&gt;</a>', $html, $key);
        }
    }

    public function testOtherKeysGetNoDivider()
    {
        $table = new w2p_Output_ListTable($this->_AppUI);
        $table->buildHeader(array('x_count' => 'Count'));
        $table->addDividers('x_count');
        $html = $table->buildRows($this->rows('link_project'));

        $this->assertStringNotContainsString('colspan', $html);
    }
}
