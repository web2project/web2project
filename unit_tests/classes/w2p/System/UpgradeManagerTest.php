<?php
/**
 * Class for testing UpgradeManager functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author      D. Keith Casey, Jr.<caseydk@users.sourceforge.net>
 * @category    w2p_System_UpgradeManager
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class w2p_System_UpgradeManagerTest extends CommonSetup
{
    public function setUp()
    {
        parent::setUp();

        $this->obj = new w2p_System_UpgradeManager();
    }

    public function testManagerSetup()
    {
        $this->obj->getActionRequired();

        $this->assertEquals(W2P_BASE_DIR.'/includes', $this->obj->getConfigDir());
        $this->assertEquals(W2P_BASE_DIR.'/includes/config.php', $this->obj->getConfigFile());
        $this->assertEquals(W2P_BASE_DIR.'/files', $this->obj->getUploadDir());
        $this->assertEquals(W2P_BASE_DIR.'/locales/en', $this->obj->getLanguageDir());
        $this->assertEquals(W2P_BASE_DIR.'/files/temp', $this->obj->getTempDir());
    }

    public function testDatabaseConfiguration()
    {
        global $w2Pconfig;

        $this->obj->getActionRequired();

        $this->assertTrue($this->obj->testDatabaseCredentials($w2Pconfig));
        $this->assertEquals($w2Pconfig, $this->obj->getConfigOptions());
    }

    public function testCreateConfigString()
    {
        global $w2Pconfig;

        $configString = $this->obj->createConfigString($w2Pconfig);
        $this->assertRegExp('/dbtype/', $configString);
        $this->assertRegExp('/'.$w2Pconfig['dbtype'].'/', $configString);
        $this->assertRegExp('/dbhost/', $configString);
        $this->assertRegExp('/'.$w2Pconfig['dbhost'].'/', $configString);
        $this->assertRegExp('/dbname/', $configString);
        $this->assertRegExp('/'.$w2Pconfig['dbname'].'/', $configString);
        $this->assertRegExp('/dbuser/', $configString);
        $this->assertRegExp('/'.$w2Pconfig['dbuser'].'/', $configString);
        $this->assertRegExp('/dbpass/', $configString);
        $this->assertRegExp('/'.$w2Pconfig['dbpass'].'/', $configString);
    }

    public function testGetMaxFileUpload()
    {
        $upload_max_filesize = str_replace('M', '', ini_get('upload_max_filesize'));
        $post_max_size = str_replace('M', '', ini_get('post_max_size'));
        $maxUpload = min($upload_max_filesize, $post_max_size).'M';
        $this->assertEquals($maxUpload, $this->obj->getMaxFileUpload());
    }

    public function testGetConfigDir()
    {
        $this->obj->getActionRequired();
        $this->assertEquals(W2P_BASE_DIR . '/includes', $this->obj->getConfigDir());
    }

    public function testGetConfigFile()
    {
        $this->obj->getActionRequired();
        $this->assertEquals(W2P_BASE_DIR . '/includes/config.php', $this->obj->getConfigFile());

    }

    public function testGetUploadDir()
    {
        $this->obj->getActionRequired();
        $this->assertEquals(W2P_BASE_DIR . '/files', $this->obj->getUploadDir());
    }

    public function testGetLanguageDir()
    {
        $this->obj->getActionRequired();
        $this->assertEquals(W2P_BASE_DIR . '/locales/en', $this->obj->getLanguageDir());
    }

    public function testGetTempDir()
    {
        $this->obj->getActionRequired();
        $this->assertEquals(W2P_BASE_DIR . '/files/temp', $this->obj->getTempDir());
    }
}
