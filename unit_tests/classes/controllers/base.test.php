<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class for testing Date functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. Please see the LICENSE file in root of site
 * for further details
 *
 * @category    Date
 * @package     web2project
 * @subpackage  unit_tests
 * @author      D. Keith Casey, Jr.
 * @copyright   2007-2010 The web2Project Development Team <w2p-developers@web2project.net>
 * @link        http://www.web2project.net
 */

require_once '../base.php';
require_once W2P_BASE_DIR . '/includes/main_functions.php';

require_once 'PHPUnit/Framework.php';

/**
 * BaseTest Class.
 *
 * Class to test the base controller
 * @author Trevor Morse
 * @package web2project
 * @subpackage unit_tests
 */
class Base_Test extends PHPUnit_Framework_TestCase
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
        $this->appUI = new CAppUI;
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
}
