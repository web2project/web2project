<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class for testing w2p_Controllers_Base_Test functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author      Trevor Morse <trevor.morse@gmail.com>
 * @category    w2p_Controllers_Base
 * @package     web2project
 * @subpackage  unit_tests
 * @copyright   2007-2012 The web2Project Development Team <w2p-developers@web2project.net>
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

// NOTE: This path is relative to Phing's build.xml, not this test.
include_once 'CommonSetup.php';

class w2p_Controllers_Base_Test extends CommonSetup
{

    /**
     * An AppUI object for validation
     *
     * @param CAppUI
     * @access private
     */
    private $appUI;

    /**
     * Create an AppUI before running tests
     */
    protected function setUp()
    {
        parent::setUp();
        $this->appUI = new w2p_Core_CAppUI();
        $_POST['login'] = 'login';
        $_REQUEST['login'] = 'sql';
        $this->appUI->login('admin', 'passwd');
    }

    /**
     * Tests that a new base controller object has the proper attributes
     */
    public function testNewBaseAttributes()
    {
        $base_controller = new w2p_Controllers_Base(new CLink(), false, 'prefix', '/success', '/failure');

        $this->assertInstanceOf('w2p_Controllers_Base',     $base_controller);
        $this->assertObjectHasAttribute('delete',           $base_controller);
        $this->assertObjectHasAttribute('successPath',      $base_controller);
        $this->assertObjectHasAttribute('errorPath',        $base_controller);
        $this->assertObjectHasAttribute('accessDeniedPath', $base_controller);
        $this->assertObjectHasAttribute('object',           $base_controller);
        $this->assertObjectHasAttribute('success',          $base_controller);
        $this->assertObjectHasAttribute('resultPath',       $base_controller);
        $this->assertObjectHasAttribute('resultMessage',    $base_controller);
        $this->assertInstanceOf('CLink',                    $base_controller->object);
    }

    /**
     * @todo Implement testSetAccessDeniedPath().
     */
    public function testSetAccessDeniedPath() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testProcess().
     */
    public function testProcess() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }
}