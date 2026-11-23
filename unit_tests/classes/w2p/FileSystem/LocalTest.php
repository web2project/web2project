<?php
/**
 * Class for testing the local file backend's paths
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @category   w2p_FileSystem_Local
 * @package    web2project
 * @subpackage unit_tests
 * @license    Clear BSD
 * @link       http://www.web2project.net
 */

class w2p_FileSystem_LocalTest extends CommonSetup
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->obj = new w2p_FileSystem_Local();
    }

    public function testIsPlainNameAcceptsGeneratedNames()
    {
        $this->assertTrue(w2p_FileSystem_Local::isPlainName(uniqid(rand())));
        $this->assertTrue(w2p_FileSystem_Local::isPlainName('3292470586b043c054dd57'));
        $this->assertTrue(w2p_FileSystem_Local::isPlainName('report.pdf'));
        $this->assertTrue(w2p_FileSystem_Local::isPlainName(12345));
    }

    /**
     * GHSA-pw38-j7w8-hvpc: a stored name with a directory part reached any
     * file on the server.
     */
    public function testIsPlainNameRejectsPaths()
    {
        foreach (array('../../includes/config.php', '..', '.', '', 'a/b', 'a\\b',
            '/etc/passwd', "a\0b", null, array('x')) as $name) {
            $this->assertFalse(w2p_FileSystem_Local::isPlainName($name), var_export($name, true));
        }
    }

    public function testPath()
    {
        $this->assertEquals(W2P_BASE_DIR . '/files/3/abc', w2p_FileSystem_Local::path('3', 'abc'));
        $this->assertEquals(W2P_BASE_DIR . '/files/0/abc', w2p_FileSystem_Local::path('x', 'abc'));
        $this->assertFalse(w2p_FileSystem_Local::path(0, '../../includes/config.php'));
    }

    public function testReadAndExistsRefuseTraversal()
    {
        $this->assertFalse($this->obj->exists(0, '../../includes/config.php'));
        $this->assertFalse($this->obj->exists(0, '..'));

        ob_start();
        $result = $this->obj->read(0, '../../includes/config.php');
        $output = ob_get_clean();
        $this->assertFalse($result);
        $this->assertEquals('', $output);
    }

    public function testDuplicateRefusesTraversal()
    {
        $this->assertFalse($this->obj->duplicate(0, '../../includes/config.php', $this->_AppUI));
    }

    public function testDeleteLeavesFilesOutsideTheProjectDirectory()
    {
        $file = new CFile();
        $file->file_project = 0;
        $file->file_real_filename = '../../includes/config.php';

        $this->assertTrue($this->obj->delete($file));
        $this->assertFileExists(W2P_BASE_DIR . '/includes/config.php');
    }
}
