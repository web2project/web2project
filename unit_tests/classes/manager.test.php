<?php
/**
 * Necessary global variables 
 */
global $db;
global $ADODB_FETCH_MODE;
global $w2p_performance_dbtime;
global $w2p_performance_old_dbqueries;
global $AppUI;

require_once '../base.php';
require_once W2P_BASE_DIR . '/includes/config.php';
require_once W2P_BASE_DIR . '/includes/main_functions.php';
require_once W2P_BASE_DIR . '/includes/db_adodb.php';
require_once W2P_BASE_DIR . '/includes/db_adodb.php';
require_once W2P_BASE_DIR . '/classes/ui.class.php';
require_once W2P_BASE_DIR . '/classes/query.class.php';
require_once W2P_BASE_DIR . '/install/manager.class.php';

require_once 'PHPUnit/Framework.php';

/**
 * UpgradeManagerTest Class.
 * 
 * Class to test the upgrade manager class
 * @author D. Keith Casey, Jr<caseydk@users.sourceforge.net>
 * @package web2project
 * @subpackage unit_tests
 */
class UpgradeManager_Test extends PHPUnit_Framework_TestCase 
{
	public function testManagerSetup()
	{
		$manager = new UpgradeManager();
		$manager->getActionRequired();

		$this->assertEquals(W2P_BASE_DIR.'/includes', $manager->getConfigDir());
		$this->assertEquals(W2P_BASE_DIR.'/includes/config.php', $manager->getConfigFile());
		$this->assertEquals(W2P_BASE_DIR.'/files', $manager->getUploadDir());
		$this->assertEquals(W2P_BASE_DIR.'/locales/en', $manager->getLanguageDir());
		$this->assertEquals(W2P_BASE_DIR.'/files/temp', $manager->getTempDir());
	}

	public function testDatabaseConfiguration()
	{
		global $w2Pconfig;

		$manager = new UpgradeManager();
		$manager->getActionRequired();

		$this->assertTrue($manager->testDatabaseCredentials($w2Pconfig));
		$this->assertEquals($w2Pconfig, $manager->getConfigOptions());
	}

	public function testCreateConfigString()
	{
		global $w2Pconfig;

		$manager = new UpgradeManager();
		
		$configString = $manager->createConfigString($w2Pconfig);
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
		$manager = new UpgradeManager();

        $upload_max_filesize = str_replace('M', '', ini_get('upload_max_filesize'));
        $post_max_size = str_replace('M', '', ini_get('post_max_size'));
		$maxUpload = min($upload_max_filesize, $post_max_size).'M';
		$this->assertEquals($maxUpload, $manager->getMaxFileUpload());
	}

	/*
	 * Realistically, the install/upgrade paths are almost identical and
	 * even a system identified for "upgrade" will start with the first database
	 * update required even if it's the first (install) script.
	 */
	public function testInstallSystem()
	{
		global $w2Pconfig;

		$manager = new UpgradeManager();

		switch ($manager->getActionRequired()) {
			case 'install':
				$this->assertTrue($manager->testDatabaseCredentials($w2Pconfig));

				$errors = $manager->upgradeSystem();
				$this->assertEquals(0, count($errors));

				$updates = $manager->getUpdatesApplied();
				$this->assertGreaterThanOrEqual(5, count($updates));
				break;

			case 'upgrade':
				$this->assertTrue($manager->testDatabaseCredentials($w2Pconfig));

				$errors = $manager->upgradeSystem();
				$this->assertEquals(0, count($errors));

				$updates = $manager->getUpdatesApplied();
				$this->assertGreaterThanOrEqual(0, count($updates));				
				break;

			default:
				$this->fail('UpgradeManager action was not matched.');
		}
	}
	public function testConvertDotProject()
	{
		$this->markTestSkipped("Not a clue how to test this one...");
	}
}
