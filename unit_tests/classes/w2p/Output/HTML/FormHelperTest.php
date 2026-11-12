<?php
/**
 * Class for testing HTML FormHelper functionality
 *
 * Many of the tests are quite similar with the only difference being the field
 *   names being tested. The duplication is on purpose because the formatting is
 *   vitally important to various parts of the system. If the formatting on a
 *   field changes, we need to know about it.
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author     D. Keith Casey, Jr.<contrib@caseysoftware.com>
 * @category   w2p_Output_HTMLHelper
 * @package    web2project
 * @subpackage unit_tests
 * @license    Clear BSD
 * @link       http://www.web2project.net
 */

class w2p_Output_HTML_FormHelperTest extends CommonSetup
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->obj = new w2p_Output_HTML_FormHelper($this->_AppUI);

        // The company the 'company' field links to (the test used to expect
        // whatever company 1 was in the database).
        $this->deleteCompany();
        $q = new w2p_Database_Query();
        $q->addTable('companies');
        $q->addInsert('company_id', 99951);
        $q->addInsert('company_name', 'UnitTest <Company>');
        $q->addInsert('company_owner', 1);
        $q->exec();
    }

    protected function tearDown(): void
    {
        $this->deleteCompany();
        parent::tearDown();
    }

    protected function deleteCompany()
    {
        $q = new w2p_Database_Query();
        $q->setDelete('companies');
        $q->addWhere('company_id = 99951');
        $q->exec();
    }

    public function testAddLabel()
    {
        $this->assertEquals('<label>Project:</label>', $this->obj->addLabel('Project'));
    }

    public function testAddCancelButton()
    {
        $this->assertEquals('<input type="button" value="back" class="cancel button btn btn-danger" onclick="javascript:history.back(-1);" />', $this->obj->addCancelButton());
    }

    public function testAddSaveButton()
    {
        $this->assertEquals('<input type="button" value="save" class="save button btn btn-primary" onclick="submitIt()" />', $this->obj->addSaveButton());
    }

    public function testAddNonce()
    {
        $this->assertGreaterThan(0, strpos($this->obj->addNonce(), '__nonce'));
    }

    public function testAddField()
    {
        $options = array();
        $values  = array();

        $output = $this->obj->addField('description', 'test');
        $this->assertEquals('<textarea name="description" class="description">test</textarea>', $output);

        $output = $this->obj->addField('birthday', '2014-02-01');
        // @todo $this->assertEquals('<input type="text" class="text birthday" name="birthday" value="2014-02-01" />', $output);

        $output = $this->obj->addField('task_end_date', '2014-02-01');
        $this->assertGreaterThan(0, strpos($output, 'value="20140201"'));
        $this->assertGreaterThan(0, strpos($output, 'value="01/Feb/2014"'));
        $this->assertGreaterThan(0, strpos($output, "return showCalendar('end_date'"));

        $output = $this->obj->addField('private', 'fieldvalue');
        // @todo $this->assertEquals('<input type="checkbox" value="1" class="text private" name="private" />', $output);

        $output = $this->obj->addField('type', 0, array(), array(0 => 'monkey', 2 => 'dog'));
        $this->assertGreaterThan(0, strpos($output, '<option value="0" selected="selected">monkey</option>'));

        $output = $this->obj->addField('url', 'http://google.com', $options, $values);
        $this->assertGreaterThan(0, strpos($output, 'class="text url"'));
        $this->assertGreaterThan(0, strpos($output, 'value="http://google.com"'));
        $this->assertGreaterThan(0, strpos($output, 'onclick="testURL()"'));

        $output = $this->obj->addField('company', '99951');
        $this->assertEquals('<a href="?m=companies&a=view&company_id=99951">UnitTest &lt;Company&gt;</a>', $output);

        $output = $this->obj->addField('other', 'fieldvalue', $options, $values);
        // @todo $this->assertEquals('<input type="text" xx="text other" name="other" value="fieldvalue" />', $output);
    }
}
