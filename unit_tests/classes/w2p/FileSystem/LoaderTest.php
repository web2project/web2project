<?php
/**
 * Class for testing FileSystem Loader functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author      D. Keith Casey, Jr.<caseydk@users.sourceforge.net>
 * @category    w2p_FileSystem_Loader
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class w2p_FileSystem_LoaderTest extends CommonSetup
{
    protected function setUp()
    {
        parent::setUp();

        $this->obj = new w2p_FileSystem_Loader();
    }

    public function testReadDirsInvalidPath()
    {
        $dirs = $this->obj->readDirs('blah');

        $this->assertEquals(0,                count($dirs));
    }

    public function testReadDirs() {
        $dirs = $this->obj->readDirs('.');

        $this->assertTrue(array_key_exists('modules', $dirs));
        $this->assertTrue(array_key_exists('includes', $dirs));
        $this->assertGreaterThan(10, count($dirs));
    }

    public function testReadFiles() {
        $files1 = $this->obj->readFiles('..');

        $this->assertTrue(array_key_exists('COPYING', $files1));
        $this->assertTrue(array_key_exists('index.php', $files1));

        $files2 = $this->obj->readFiles('.', 'php');
        $this->assertFalse(array_key_exists('COPYING', $files2));
        $this->assertTrue(array_key_exists('index.php', $files2));

        //$this->assertGreaterThan(count($files2), count($files1));
    }

    /**
     * @todo Implement testCheckFileName().
     */
    public function testCheckFileName()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testMakeFileNameSafe() {
        $this->assertEquals('index.php', $this->obj->makeFileNameSafe('index.php'));
        $this->assertEquals('.', $this->obj->makeFileNameSafe('.'));
        $this->assertEquals('index.php', $this->obj->makeFileNameSafe('../index.php'));
        $this->assertEquals('index.php', $this->obj->makeFileNameSafe('..\index.php'));
    }
}
