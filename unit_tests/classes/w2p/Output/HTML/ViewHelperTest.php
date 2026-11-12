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

class w2p_Output_HTML_ViewHelperTest extends CommonSetup
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->obj = new w2p_Output_HTML_ViewHelper($this->_AppUI);

        // The user and contact the 'owner' field shows (the test used to
        // expect whatever user 2 was in the database).
        $this->deleteOwner();
        $q = new w2p_Database_Query();
        $q->addTable('contacts');
        $q->addInsert('contact_id', 99951);
        $q->addInsert('contact_first_name', 'Contact');
        $q->addInsert('contact_last_name', 'Number 1');
        $q->addInsert('contact_display_name', 'Contact Number 1');
        $q->exec();
        $q->clear();
        $q->addTable('users');
        $q->addInsert('user_id', 99951);
        $q->addInsert('user_contact', 99951);
        $q->addInsert('user_username', 'viewhelper-owner');
        $q->addInsert('user_password', '');
        $q->exec();
    }

    protected function tearDown(): void
    {
        $this->deleteOwner();
        parent::tearDown();
    }

    protected function deleteOwner()
    {
        $q = new w2p_Database_Query();
        $q->setDelete('users');
        $q->addWhere('user_id = 99951');
        $q->exec();
        $q->clear();
        $q->setDelete('contacts');
        $q->addWhere('contact_id = 99951');
        $q->exec();
    }

    public function testAddLabel()
    {
        $this->assertEquals('<label>Project:</label>', $this->obj->addLabel('Project'));
    }

    public function testAddField()
    {
        $output = $this->obj->addField('email', 'monkey@example.com');
        $this->assertEquals('<a href="mailto:monkey@example.com">monkey@example.com</a>', $output);

        $output = $this->obj->addField('url', 'http://google.com');
        $this->assertEquals('<a href="http://google.com" target="_new">http://google.com</a>', $output);

        $output = $this->obj->addField('owner', '99951');
        $this->assertEquals('<a href="?m=users&a=view&user_id=99951">Contact Number 1</a>', $output);

        $output = $this->obj->addField('description', 'test');
        $this->assertEquals('test', $output);

        $output = $this->obj->addField('description', 'test w/ a link: http://web2project.net');
        $this->assertEquals('test w/ a link: <a href="http://web2project.net" target="_blank">http://web2project.net</a>', $output);

        $output = $this->obj->addField('percent', '38.7');
        $this->assertEquals('39%', $output);
    }
}
