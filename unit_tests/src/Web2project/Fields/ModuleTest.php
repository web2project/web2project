<?php
/**
 * Class for testing Web2project\Field\Module functionality
 *
 * @author      Keith Casey <contrib@caseysoftware.com>
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

// NOTE: This path is relative to Phing's build.xml, not this test.
include_once 'unit_tests/CommonSetup.php';

class Web2project_Fields_ModuleTest extends CommonSetup
{
    protected function setUp()
    {
        parent::setUp();

        $this->obj = new \Web2project\Fields\Module();
    }

    public function testViewWithContact()
    {
        $contact = new CContact();
        $contact->contact_display_name = 'monkey';
        $this->obj->setObject($contact, 'user');

        $output = $this->obj->view(5);
        $this->assertEquals('<a href="?m=users&a=view&user_id=5">monkey</a>', $output);
    }

    public function testViewWithCompany()
    {
        $company = new CCompany();
        $company->company_name = 'CaseySoftware';
        $this->obj->setObject($company, 'company');

        $output = $this->obj->view(5);
        $this->assertEquals('<a href="?m=companies&a=view&company_id=5">CaseySoftware</a>', $output);
    }
}