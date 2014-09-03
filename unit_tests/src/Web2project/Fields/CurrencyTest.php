<?php
/**
 * Class for testing Web2project\Field\Currency functionality
 *
 * @author      Keith Casey <contrib@caseysoftware.com>
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class Web2project_Fields_CurrencyTest extends CommonSetup
{
    protected function setUp()
    {
        parent::setUp();

        $this->obj = new \Web2project\Fields\Currency();
    }

    public function testView()
    {
        $this->obj->setOptions(w2PgetConfig('currency_symbol'), $this->_AppUI->getPref('CURRENCYFORM'));
        $output = $this->obj->view('12345.67');

        $this->assertEquals('$USD12,345.67', $output);
    }
}