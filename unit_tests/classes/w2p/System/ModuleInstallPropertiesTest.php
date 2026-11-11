<?php
/**
 * Module install/remove without PHP 8.2+ dynamic-property deprecations or
 * undefined variables (#563).
 *
 * @package    web2project
 * @subpackage unit_tests
 * @license    Clear BSD
 */

class w2p_System_ModuleInstallPropertiesTest extends CommonSetup
{
    public function testPropertiesAreDeclared()
    {
        $this->assertTrue(property_exists('w2p_System_Module', 'mod_admin'));
        $this->assertTrue(property_exists('w2p_Extensions_Permissions', '_debug_msg'));

        // mod_admin is not a column of modules, so it must stay out of
        // the values insertObject()/updateObject() write
        $this->assertArrayNotHasKey('mod_admin', get_object_vars(new w2p_System_Module()));
    }

    public function testAddGroupItemWithUnknownGroupReturnsFalse()
    {
        $perms = new w2p_Extensions_Permissions();

        $this->assertFalse($perms->addGroupItem('no-such-module', 'no-such-group'));
    }
}
