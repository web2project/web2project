<?php /* $Id$ $URL$ */

/**
 *	@package    web2project
 *	@subpackage unit_tests
 *	@version    $Revision$
 *  @license	Clear BSD
 *  @author     Keith casey
 */

class w2p_Mocks_Permissions_Test extends PHPUnit_Framework_TestCase
{
    protected $perms = null;

    protected function setUp()
    {
        parent::setUp();
        $this->perms = new w2p_Mocks_Permissions();
    }

    public function testW2Pacl_nuclear()
    {
        $results = $this->perms->w2Pacl_nuclear(1, 'anymodule', new stdClass());

        $this->assertEquals(2,         count($results));
        $this->assertEquals(1,         $results['access']);
        $this->assertEquals('checked', $results['acl_id']);
    }
}