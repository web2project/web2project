<?php
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
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class w2p_Controllers_BaseTest extends CommonSetup
{
    protected function setUp()
    {
      parent::setUp();

      $this->link    = new CLink();
      $this->link->overrideDatabase($this->mockDB);

      $this->obj = new w2p_Controllers_Base($this->link, false, 'prefix', '/success', '/failure');

      $GLOBALS['acl'] = new w2p_Mocks_Permissions();

      $this->post_data = array(
          'dosql'             => 'do_link_aed',
          'link_id'           => 0,
          'link_name'         => 'web2project homepage',
          'link_project'      => 0,
          'link_task'         => 0,
          'link_url'          => 'http://web2project.net',
          'link_parent'       => '0',
          'link_description'  => 'This is web2project',
          'link_owner'        => 1,
          'link_date'         => '2009-01-01',
          'link_icon'         => '',
          'link_category'     => 0
      );
    }
    /**
     * Tests that a new base controller object has the proper attributes
     */
    public function testNewBaseAttributes()
    {
        $this->assertInstanceOf('w2p_Controllers_Base',     $this->obj);
        $this->assertObjectHasAttribute('delete',           $this->obj);
        $this->assertObjectHasAttribute('successPath',      $this->obj);
        $this->assertObjectHasAttribute('errorPath',        $this->obj);
        $this->assertObjectHasAttribute('object',           $this->obj);
        $this->assertObjectHasAttribute('success',          $this->obj);
        $this->assertObjectHasAttribute('resultPath',       $this->obj);
        $this->assertInstanceOf('CLink',                    $this->obj->object);
    }

    /**
     * Testing process() with first a well-formed POST and then a damaged POST.
     */
    public function testProcess()
    {
        $AppUI = $this->obj->process($this->_AppUI, $this->post_data);
        $this->assertEquals('/success',   $this->obj->resultPath);

        unset($this->post_data['link_url']);
        $this->obj->object = new CLink();
        $AppUI = $this->obj->process($this->_AppUI, $this->post_data);
        $this->assertEquals('/failure',   $this->obj->resultPath);
    }
}
