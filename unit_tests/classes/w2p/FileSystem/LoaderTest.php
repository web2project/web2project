<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

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

// NOTE: This path is relative to Phing's build.xml, not this test.
include_once 'unit_tests/CommonSetup.php';

class w2p_FileSystem_LoaderTest extends CommonSetup
{
    protected function setUp ()
    {
        parent::setUp();

        $this->obj = new w2p_FileSystem_Loader();
    }

    /**
     * Test reading directories from an invalid path
     *
     */
    public function testReadDirsInvalidPath()
    {
        $dirs = $this->obj->readDirs('blah');

        $this->assertEquals(0,				count($dirs));
    }

    /**
     * @todo Implement testReadDirs().
     */
    public function testReadDirs() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testReadFiles().
     */
    public function testReadFiles() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testCheckFileName().
     */
    public function testCheckFileName() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @todo Implement testMakeFileNameSafe().
     */
    public function testMakeFileNameSafe() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
}