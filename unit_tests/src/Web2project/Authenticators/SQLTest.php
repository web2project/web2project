<?php
/**
 * Class for testing \Web2project\Authenticators\SQL functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @category    \Web2project\Authenticators
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class Web2project_Authenticators_SQL extends CommonSetup
{
    protected function setUp()
    {
        parent::setUp();

        $query = new w2p_Mocks_Query();
        $query->stageResult(123);

        $this->obj = new w2p_Authenticators_SQL($query);
    }

    public function testAuthenticate()
    {
        $user_id = $this->obj->authenticate('username', 'password');

        $this->assertEquals(123, $user_id);
    }

    public function testHashPassword()
    {
        $password = 'monkey';

        $this->assertEquals('d0763edaa9d9bd2a9516280e9044d885', $this->obj->hashPassword($password));
    }

    public function testCreateNewPassword()
    {
        $password = $this->obj->createNewPassword();

        $this->assertEquals(11, strlen($password));
    }

    public function testUserId()
    {
        $this->obj->user_id = 1;

        $this->assertEquals(1, $this->obj->userId());
    }
}