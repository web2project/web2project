<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class for testing companies functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version. Please see the LICENSE file in root of site
 * for further details
 *
 * @category    Companies
 * @package     web2project
 * @subpackage  unit_tests
 * @author      Trevor Morse <trevor.morse@gmail.com>
 * @copyright   2007-2010 The web2Project Development Team <w2p-developers@web2project.net>
 * @link        http://www.web2project.net
 */

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

/*
 * Need this to test actions that require permissions.
 */
$AppUI  = new CAppUI;
$_POST['login'] = 'login';
$_REQUEST['login'] = 'sql';
$AppUI->login('admin', 'passwd');
/*
 * Need this to not get the annoying timezone warnings in tests.
 */
$defaultTZ = w2PgetConfig('system_timezone', 'Europe/London');
$defaultTZ = ('' == $defaultTZ) ? 'Europe/London' : $defaultTZ;
date_default_timezone_set($defaultTZ);

require_once W2P_BASE_DIR . '/includes/session.php';
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Extensions/Database/TestCase.php';

/**
 * This class tests functionality for Companies
 *
 * @author      Trevor Morse <trevor.morse@gmail.com>
 * @category    Companies
 * @package     web2project
 * @subpackage  unit_tests
 * @copyright   2007-2010 The web2Project Development Team <w2p-developers@web2project.net>
 * @link        http://www.web2project.net
 */
class Companies_Test extends PHPUnit_Extensions_Database_TestCase
{

    protected $backupGlobals = FALSE;
	protected $obj = null;
	protected $post_data = array();

    /**
     * Return database connection for tests
     */
    protected function getConnection()
    {
        $pdo = new PDO(w2PgetConfig('dbtype') . ':host=' .
                       w2PgetConfig('dbhost') . ';dbname=' .
                       w2PgetConfig('dbname'),
                       w2PgetConfig('dbuser'), w2PgetConfig('dbpass'));
        return $this->createDefaultDBConnection($pdo, w2PgetConfig('dbname'));
    }

    /**
     * Set up default dataset for testing
     */
    protected function getDataSet()
    {
        return $this->createXMLDataSet($this->getDataSetPath().'companiesSeed.xml');
    }
    protected function getDataSetPath()
    {
    	return dirname(dirname(__FILE__)).'/db_files/companies/';
    }

	public function setUp()
	{
		parent::setUp();

		$this->obj = new CCompany();
		$this->post_data = array (
			'dosql'                 => 'do_company_aed',
			'company_id'            => 0,
			'company_name'          => 'UnitTestCompany',
			'company_email'         => 'web2project@example.org',
			'company_phone1'        => '1.999.999.9999',
			'company_phone2'        => '1.999.999.9998',
			'company_fax'           => '1.999.999.9997',
			'company_address1'      => 'Address 1',
			'company_address2'      => 'Address 2',
			'company_city'          => 'City',
			'company_state'         => 'CA',
			'company_zip'           => '90210',
			'company_country'       => 'US',
			'company_primary_url'   => 'web2project.net',
			'company_owner'         => 1,
			'company_type'          => 2,
			'company_description'   => 'This is a company.'

		);
	}

	public function tearDown()
	{
		parent::tearDown();

		unset($this->obj, $this->post_data);
	}

    /**
     * Tests the Attributes of a new Companies object.
     */
    public function testNewCompanyAttributes()
    {
        $this->assertInstanceOf('CCompany', $this->obj);
        $this->assertObjectHasAttribute('company_id',           $this->obj);
        $this->assertObjectHasAttribute('company_name',         $this->obj);
        $this->assertObjectHasAttribute('company_phone1',       $this->obj);
        $this->assertObjectHasAttribute('company_phone2',       $this->obj);
        $this->assertObjectHasAttribute('company_fax',          $this->obj);
        $this->assertObjectHasAttribute('company_address1',     $this->obj);
        $this->assertObjectHasAttribute('company_address2',     $this->obj);
        $this->assertObjectHasAttribute('company_city',         $this->obj);
        $this->assertObjectHasAttribute('company_state',        $this->obj);
        $this->assertObjectHasAttribute('company_zip',          $this->obj);
        $this->assertObjectHasAttribute('company_country',      $this->obj);
        $this->assertObjectHasAttribute('company_email',        $this->obj);
        $this->assertObjectHasAttribute('company_primary_url',  $this->obj);
        $this->assertObjectHasAttribute('company_owner',        $this->obj);
        $this->assertObjectHasAttribute('company_description',  $this->obj);
        $this->assertObjectHasAttribute('company_type',         $this->obj);
        $this->assertObjectHasAttribute('company_custom',       $this->obj);
    }

    /**
     * Tests the Attribute Values of a new Company object.
     */
    public function testNewCompanyAttributeValues()
    {
        $this->assertInstanceOf('CCompany', $this->obj);
        $this->assertEquals(0, $this->obj->company_id);
        $this->assertNull($this->obj->company_name);
        $this->assertNull($this->obj->company_phone1);
        $this->assertNull($this->obj->company_phone2);
        $this->assertNull($this->obj->company_fax);
        $this->assertNull($this->obj->company_address1);
        $this->assertNull($this->obj->company_address2);
        $this->assertNull($this->obj->company_city);
        $this->assertNull($this->obj->company_state);
        $this->assertNull($this->obj->company_zip);
        $this->assertNull($this->obj->company_country);
        $this->assertNull($this->obj->company_email);
        $this->assertNull($this->obj->company_primary_url);
        $this->assertNull($this->obj->company_owner);
        $this->assertNull($this->obj->company_description);
        $this->assertNull($this->obj->company_type);
        $this->assertNull($this->obj->company_custom);
    }

    /**
     * Tests that the proper error message is returned when a company
     * is attempted to be created without an owner.
     */
    public function testCreateCompanyNoOwner()
    {
		global $AppUI;

		unset($this->post_data['company_owner']);
		$this->obj->bind($this->post_data);
		$errorArray = $this->obj->store($AppUI);

		/**
		 * Verify we got the proper error message
		 */
		$this->assertArrayHasKey('company_owner', $errorArray);

		/**
		 * Verify that company id was not set
		 */
		$this->assertEquals(0, $this->obj->company_id);
    }

	/**
     * Tests that the proper error message is returned when a company
     * is attempted to be created without a name.
     */
    public function testCreateCompanyNoName()
    {
		global $AppUI;

		unset($this->post_data['company_name']);
		$this->obj->bind($this->post_data);
		$errorArray = $this->obj->store($AppUI);

		/**
		 * Verify we got the proper error message
		 */
		$this->assertArrayHasKey('company_name', $errorArray);

		/**
		 * Verify that company id was not set
		 */
		$this->assertEquals(0, $this->obj->company_id);
    }

    /**
     * Tests the proper creation of a company
     */
    public function testCreateCompany()
    {
        global $AppUI;

        $this->obj->bind($this->post_data);
        $result = $this->obj->store($AppUI);

        $this->assertTrue($result);
        $this->assertEquals('UnitTestCompany',          $this->obj->company_name);
        $this->assertEquals('web2project@example.org',  $this->obj->company_email);
        $this->assertEquals('1.999.999.9999',           $this->obj->company_phone1);
        $this->assertEquals('1.999.999.9998',           $this->obj->company_phone2);
        $this->assertEquals('1.999.999.9997',           $this->obj->company_fax);
        $this->assertEquals('Address 1',                $this->obj->company_address1);
        $this->assertEquals('Address 2',                $this->obj->company_address2);
        $this->assertEquals('City',                     $this->obj->company_city);
        $this->assertEquals('CA',                       $this->obj->company_state);
        $this->assertEquals('90210',                    $this->obj->company_zip);
        $this->assertEquals('US',                       $this->obj->company_country);
        $this->assertEquals('web2project.net',          $this->obj->company_primary_url);
        $this->assertEquals(1,                          $this->obj->company_owner);
        $this->assertEquals(2,                          $this->obj->company_type);
        $this->assertEquals('This is a company.' ,      $this->obj->company_description);

        $xml_dataset = $this->createXMLDataSet($this->getDataSetPath().'companiesTestCreateCompany.xml');
        $this->assertTablesEqual($xml_dataset->getTable('companies'), $this->getConnection()->createDataSet()->getTable('companies'));
    }

    /**
     * Tests loading the Company Object
     */
    public function testLoad()
    {
        $this->obj->load(1);

        $this->assertEquals('UnitTestCompany',          $this->obj->company_name);
        $this->assertEquals('web2project@example.org',  $this->obj->company_email);
        $this->assertEquals('1.999.999.9999',           $this->obj->company_phone1);
        $this->assertEquals('1.999.999.9998',           $this->obj->company_phone2);
        $this->assertEquals('1.999.999.9997',           $this->obj->company_fax);
        $this->assertEquals('Address 1',                $this->obj->company_address1);
        $this->assertEquals('Address 2',                $this->obj->company_address2);
        $this->assertEquals('City',                     $this->obj->company_city);
        $this->assertEquals('CA',                       $this->obj->company_state);
        $this->assertEquals('90210',                    $this->obj->company_zip);
        $this->assertEquals('US',                       $this->obj->company_country);
        $this->assertEquals('web2project.net',          $this->obj->company_primary_url);
        $this->assertEquals(1,                          $this->obj->company_owner);
        $this->assertEquals(2,                          $this->obj->company_type);
        $this->assertEquals('This is a company.' ,      $this->obj->company_description);
    }

    /**
     * Tests loading the Company Object
     */
    public function testLoadFull()
    {
        global $AppUI;

        $this->obj->loadFull($AppUI, 1);

        $this->assertEquals('UnitTestCompany',          $this->obj->company_name);
        $this->assertEquals('web2project@example.org',  $this->obj->company_email);
        $this->assertEquals('1.999.999.9999',           $this->obj->company_phone1);
        $this->assertEquals('1.999.999.9998',           $this->obj->company_phone2);
        $this->assertEquals('1.999.999.9997',           $this->obj->company_fax);
        $this->assertEquals('Address 1',                $this->obj->company_address1);
        $this->assertEquals('Address 2',                $this->obj->company_address2);
        $this->assertEquals('City',                     $this->obj->company_city);
        $this->assertEquals('CA',                       $this->obj->company_state);
        $this->assertEquals('90210',                    $this->obj->company_zip);
        $this->assertEquals('US',                       $this->obj->company_country);
        $this->assertEquals('web2project.net',          $this->obj->company_primary_url);
        $this->assertEquals(1,                          $this->obj->company_owner);
        $this->assertEquals(2,                          $this->obj->company_type);
        $this->assertEquals('This is a company.' ,      $this->obj->company_description);
        $this->assertEquals(0,                          $this->obj->company_module);
        $this->assertEquals(0,                          $this->obj->company_private);
        $this->assertEquals('Admin',                    $this->obj->contact_first_name);
        $this->assertEquals('Person',                   $this->obj->contact_last_name);
    }

    /**
     * Tests the update of a company
     */
    public function testUpdateCompany()
    {
        global $AppUI;
        $this->obj->load(1);

        $this->post_data['dosql']               = 'do_company_aed';
        $this->post_data['company_id']          = $this->obj->company_id;
        $this->post_data['company_name']        = 'UpdatedCompany';
        $this->post_data['company_email']       = 'updated@example.org';
        $this->post_data['company_phone1']      = '1.777.999.9999';
        $this->post_data['company_phone2']      = '1.777.999.9998';
        $this->post_data['company_fax']         = '1.777.999.9997';
        $this->post_data['company_address1']    = 'Updated Address 1';
        $this->post_data['company_address2']    = 'Updated Address 2';
        $this->post_data['company_city']        = 'Updated City';
        $this->post_data['company_state']       = 'NS';
        $this->post_data['company_zip']         = 'A2A 2B2';
        $this->post_data['company_country']     = 'CA';
        $this->post_data['company_primary_url']	= 'ut.web2project.net';
        $this->post_data['company_owner']       = 1;
        $this->post_data['company_type']        = 2;
        $this->post_data['company_description'] = 'This is an updated company.';

        $this->obj->bind($this->post_data);
        $this->obj->store($AppUI);

        $this->assertEquals('UpdatedCompany',               $this->obj->company_name);
        $this->assertEquals('updated@example.org',          $this->obj->company_email);
        $this->assertEquals('1.777.999.9999',               $this->obj->company_phone1);
        $this->assertEquals('1.777.999.9998',               $this->obj->company_phone2);
        $this->assertEquals('1.777.999.9997',               $this->obj->company_fax);
        $this->assertEquals('Updated Address 1',            $this->obj->company_address1);
        $this->assertEquals('Updated Address 2',            $this->obj->company_address2);
        $this->assertEquals('Updated City',                 $this->obj->company_city);
        $this->assertEquals('NS',                           $this->obj->company_state);
        $this->assertEquals('A2A 2B2',                      $this->obj->company_zip);
        $this->assertEquals('CA',                           $this->obj->company_country);
        $this->assertEquals('ut.web2project.net',           $this->obj->company_primary_url);
        $this->assertEquals(1,                              $this->obj->company_owner);
        $this->assertEquals(2,                              $this->obj->company_type);
        $this->assertEquals('This is an updated company.',  $this->obj->company_description);

        $xml_dataset = $this->createXMLDataSet($this->getDataSetPath().'companiesTestUpdateCompany.xml');
        $this->assertTablesEqual($xml_dataset->getTable('companies'), $this->getConnection()->createDataSet()->getTable('companies'));
    }

    /**
     * Tests the delete of a company
     */
    public function testDeleteCompany()
    {
        global $AppUI;

        $this->obj->company_id = 1;
        $msg = $this->obj->delete($AppUI);
        $this->assertEquals('noDeleteRecord: Projects, Departments', $msg);

        $this->obj->company_id = 3;
        $result = $this->obj->delete($AppUI);
        $this->assertTrue($result);

        $result = $this->obj->load(3);
        $this->assertFalse($result);

        $xml_dataset = $this->createXMLDataSet($this->getDataSetPath().'companiesTestDeleteCompany.xml');
        $this->assertTablesEqual($xml_dataset->getTable('companies'), $this->getConnection()->createDataSet()->getTable('companies'));
    }

    /**
     * Tests loading list of companies with no criteria
     */
    public function testGetCompanyListNoCriteria()
    {
        global $AppUI;

        $companies = $this->obj->getCompanyList($AppUI);

        $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $companies);
        $this->assertEquals(4,                             count($companies));
        $this->assertEquals(2,                             $companies[0]['company_id']);
        $this->assertEquals('CreatedCompany',              $companies[0]['company_name']);
        $this->assertEquals(1,                             $companies[0]['company_type']);
        $this->assertEquals('This is a company.',          $companies[0]['company_description']);
        $this->assertEquals(1,                             $companies[0]['countp']);
        $this->assertEquals(0,                             $companies[0]['inactive']);
        $this->assertEquals('Admin',                       $companies[0]['contact_first_name']);
        $this->assertEquals('Person',                      $companies[0]['contact_last_name']);
        $this->assertEquals(3,                             $companies[1]['company_id']);
        $this->assertEquals('CreatedCompany',              $companies[1]['company_name']);
        $this->assertEquals(2,                             $companies[1]['company_type']);
        $this->assertEquals('This is a company.',          $companies[1]['company_description']);
        $this->assertEquals(0,                             $companies[1]['countp']);
        $this->assertEquals(0,                             $companies[1]['inactive']);
        $this->assertEquals('Admin',                       $companies[1]['contact_first_name']);
        $this->assertEquals('Person',                      $companies[1]['contact_last_name']);
        $this->assertEquals(1,                             $companies[2]['company_id']);
        $this->assertEquals('UnitTestCompany',             $companies[2]['company_name']);
        $this->assertEquals(2,                             $companies[2]['company_type']);
        $this->assertEquals('This is a company.',          $companies[2]['company_description']);
        $this->assertEquals(1,                             $companies[2]['countp']);
        $this->assertEquals(0,                             $companies[2]['inactive']);
        $this->assertEquals('Admin',                       $companies[2]['contact_first_name']);
        $this->assertEquals('Person',                      $companies[2]['contact_last_name']);
        $this->assertEquals(4,                             $companies[3]['company_id']);
        $this->assertEquals('UpdatedCompany',              $companies[3]['company_name']);
        $this->assertEquals(2,                             $companies[3]['company_type']);
        $this->assertEquals('This is an updated company.', $companies[3]['company_description']);
        $this->assertEquals(0,                             $companies[3]['countp']);
        $this->assertEquals(0,                             $companies[3]['inactive']);
        $this->assertEquals('Admin',                       $companies[3]['contact_first_name']);
        $this->assertEquals('Person',                      $companies[3]['contact_last_name']);
    }

    /**
    * Tests loading list of companies by Type
    */
    public function testGetCompanyListByTypeNoMatch()
    {
        global $AppUI;

        $results = $this->obj->getCompanyList($AppUI, 3);

        $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $results);
        $this->assertEquals(0, count($results));
    }

    /**
    * Tests loading list of companies by Type
    */
    public function testGetCompanyListByType()
    {
        global $AppUI;

        $companies = $this->obj->getCompanyList($AppUI, 1);

        $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $companies);
        $this->assertEquals(1,                             count($companies));
        $this->assertEquals(2,                             $companies[0]['company_id']);
        $this->assertEquals('CreatedCompany',              $companies[0]['company_name']);
        $this->assertEquals(1,                             $companies[0]['company_type']);
        $this->assertEquals('This is a company.',          $companies[0]['company_description']);
        $this->assertEquals(1,                             $companies[0]['countp']);
        $this->assertEquals(0,                             $companies[0]['inactive']);
        $this->assertEquals('Admin',                       $companies[0]['contact_first_name']);
        $this->assertEquals('Person',                      $companies[0]['contact_last_name']);
    }

    /**
    * Tests loading list of companies by search string
    */
    public function testGetCompanyListByStringNoMatch()
    {
        global $AppUI;

        $results = $this->obj->getCompanyList($AppUI, -1, 'This is a company');

        $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $results);
        $this->assertEquals(0, count($results));
    }

    /**
    * Tests loading list of companies by owner id
    */
    public function testGetCompanyListByOwnerIDNoMatch()
    {
        global $AppUI;

        $results = $this->obj->getCompanyList($AppUI, -1, '', 2);

        $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $results);
        $this->assertEquals(0, count($results));
    }

    /**
    * Tests loading list of companies by owner id
    */
    public function testGetCompanyListByOwnerID()
    {
        global $AppUI;

        $companies = $this->obj->getCompanyList($AppUI, -1, '', 1);

        $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $companies);
        $this->assertEquals(4,                             count($companies));
        $this->assertEquals(2,                             $companies[0]['company_id']);
        $this->assertEquals('CreatedCompany',              $companies[0]['company_name']);
        $this->assertEquals(1,                             $companies[0]['company_type']);
        $this->assertEquals('This is a company.',          $companies[0]['company_description']);
        $this->assertEquals(1,                             $companies[0]['countp']);
        $this->assertEquals(0,                             $companies[0]['inactive']);
        $this->assertEquals('Admin',                       $companies[0]['contact_first_name']);
        $this->assertEquals('Person',                      $companies[0]['contact_last_name']);
        $this->assertEquals(3,                             $companies[1]['company_id']);
        $this->assertEquals('CreatedCompany',              $companies[1]['company_name']);
        $this->assertEquals(2,                             $companies[1]['company_type']);
        $this->assertEquals('This is a company.',          $companies[1]['company_description']);
        $this->assertEquals(0,                             $companies[1]['countp']);
        $this->assertEquals(0,                             $companies[1]['inactive']);
        $this->assertEquals('Admin',                       $companies[1]['contact_first_name']);
        $this->assertEquals('Person',                      $companies[1]['contact_last_name']);
        $this->assertEquals(1,                             $companies[2]['company_id']);
        $this->assertEquals('UnitTestCompany',             $companies[2]['company_name']);
        $this->assertEquals(2,                             $companies[2]['company_type']);
        $this->assertEquals('This is a company.',          $companies[2]['company_description']);
        $this->assertEquals(1,                             $companies[2]['countp']);
        $this->assertEquals(0,                             $companies[2]['inactive']);
        $this->assertEquals('Admin',                       $companies[2]['contact_first_name']);
        $this->assertEquals('Person',                      $companies[2]['contact_last_name']);
        $this->assertEquals(4,                             $companies[3]['company_id']);
        $this->assertEquals('UpdatedCompany',              $companies[3]['company_name']);
        $this->assertEquals(2,                             $companies[3]['company_type']);
        $this->assertEquals('This is an updated company.', $companies[3]['company_description']);
        $this->assertEquals(0,                             $companies[3]['countp']);
        $this->assertEquals(0,                             $companies[3]['inactive']);
        $this->assertEquals('Admin',                       $companies[3]['contact_first_name']);
        $this->assertEquals('Person',                      $companies[3]['contact_last_name']);
    }

    /**
    * Tests loading list of Projects for this company.
    */
    public function testGetProjects()
    {
        global $AppUI;

        $projects = CCompany::getProjects($AppUI, 1);

        $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $projects);
        $this->assertEquals(1,                 count($projects));
        $this->assertEquals(1,                 $projects[0]['project_id']);
        $this->assertEquals('Test Project',    $projects[0]['project_name']);
        $this->assertEquals('',                $projects[0]['project_start_date']);
        $this->assertEquals(0,                 $projects[0]['project_status']);
        $this->assertEquals('0.00',            $projects[0]['project_target_budget']);
        $this->assertEquals(-1,                $projects[0]['project_priority']);
        $this->assertEquals('Admin',           $projects[0]['contact_first_name']);
        $this->assertEquals('Person',          $projects[0]['contact_last_name']);
    }

    /**
    * Tests loading list of Contacts for this company.
    */
    public function testGetContacts()
    {
        global $AppUI;

        $contacts = CCompany::getContacts($AppUI, 1);

        /**
         * getContacts returns both an associative array as well as a indexed array
         * so we need to check both to make sure functionality depending on either does
         * not break.
         */
        $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $contacts);
        $this->assertEquals(2, 							count($contacts));
        $this->assertEquals(1,                          $contacts[1]['contact_id']);
        $this->assertEquals('Admin',                    $contacts[1]['contact_first_name']);
        $this->assertEquals('Person',                   $contacts[1]['contact_last_name']);
		$this->assertEquals('',                         $contacts[1]['contact_display_name']);
        $this->assertEquals('',                         $contacts[1]['contact_order_by']);
        $this->assertEquals('President',                $contacts[1]['contact_title']);
        $this->assertEquals('1983-07-22',               $contacts[1]['contact_birthday']);
        $this->assertEquals('President',                $contacts[1]['contact_job']);
        $this->assertEquals(1,                          $contacts[1]['contact_company']);
        $this->assertEquals(0,                          $contacts[1]['contact_department']);
        $this->assertEquals('person',                   $contacts[1]['contact_type']);
        $this->assertEquals('email1@example.com',       $contacts[1]['contact_email']);
        $this->assertEquals('703-555-1111',             $contacts[1]['contact_phone']);
        $this->assertEquals('c1 address 1',             $contacts[1]['contact_address1']);
        $this->assertEquals('c1 address 2',             $contacts[1]['contact_address2']);
        $this->assertEquals('c1 city',                  $contacts[1]['contact_city']);
        $this->assertEquals('CA',                       $contacts[1]['contact_state']);
        $this->assertEquals('90210',                    $contacts[1]['contact_zip']);
        $this->assertEquals('US',                       $contacts[1]['contact_country']);
        $this->assertEquals('c1s notes.',               $contacts[1]['contact_notes']);
        $this->assertEquals(0,                          $contacts[1]['contact_project']);
        $this->assertEquals('obj/contact',              $contacts[1]['contact_icon']);
        $this->assertEquals(0,                          $contacts[1]['contact_owner']);
        $this->assertEquals(0,                          $contacts[1]['contact_private']);
        $this->assertEquals('',                         $contacts[1]['contact_updatekey']);
        $this->assertEquals('2009-01-01 11:11:11',      $contacts[1]['contact_lastupdate']);
        $this->assertEquals('2008-12-12 11:11:11',      $contacts[1]['contact_updateasked']);
        $this->assertEquals('',                         $contacts[1]['dept_name']);
        $this->assertEquals(1,                          $contacts[1]['0']);
        $this->assertEquals('Admin',                    $contacts[1]['1']);
        $this->assertEquals('Person',                   $contacts[1]['2']);
        $this->assertEquals('',                         $contacts[1]['3']);
		$this->assertEquals('',                         $contacts[1]['4']);
        $this->assertEquals('President',                $contacts[1]['5']);
        $this->assertEquals('1983-07-22',               $contacts[1]['6']);
        $this->assertEquals('President',                $contacts[1]['7']);
        $this->assertEquals(1,                          $contacts[1]['8']);
        $this->assertEquals(0,                          $contacts[1]['9']);
        $this->assertEquals('person',                   $contacts[1]['10']);
        $this->assertEquals('email1@example.com',       $contacts[1]['11']);
        $this->assertEquals('703-555-1111',             $contacts[1]['12']);
        $this->assertEquals('c1 address 1',             $contacts[1]['13']);
        $this->assertEquals('c1 address 2',             $contacts[1]['14']);
        $this->assertEquals('c1 city',                  $contacts[1]['15']);
        $this->assertEquals('CA',                       $contacts[1]['16']);
        $this->assertEquals('90210',                    $contacts[1]['17']);
        $this->assertEquals('US',                       $contacts[1]['18']);
        $this->assertEquals('c1s notes.',               $contacts[1]['19']);
        $this->assertEquals(0,                          $contacts[1]['20']);
        $this->assertEquals('obj/contact',              $contacts[1]['21']);
        $this->assertEquals(0,                          $contacts[1]['22']);
        $this->assertEquals(0,                          $contacts[1]['23']);
        $this->assertEquals('',                         $contacts[1]['24']);
        $this->assertEquals('2009-01-01 11:11:11',      $contacts[1]['25']);
        $this->assertEquals('2008-12-12 11:11:11',      $contacts[1]['26']);
        $this->assertEquals('',                         $contacts[1]['27']);

        $this->assertEquals(2,                          $contacts[2]['contact_id']);
        $this->assertEquals('Contact',                  $contacts[2]['contact_first_name']);
        $this->assertEquals('Number 1',                 $contacts[2]['contact_last_name']);
		$this->assertEquals('',                         $contacts[2]['contact_display_name']);
        $this->assertEquals('',                         $contacts[2]['contact_order_by']);
        $this->assertEquals('Vice President',           $contacts[2]['contact_title']);
        $this->assertEquals('1973-07-22',               $contacts[2]['contact_birthday']);
        $this->assertEquals('Vice President',           $contacts[2]['contact_job']);
        $this->assertEquals(1,                          $contacts[2]['contact_company']);
        $this->assertEquals(0,                          $contacts[2]['contact_department']);
        $this->assertEquals('person',                   $contacts[2]['contact_type']);
        $this->assertEquals('email2@example.com',       $contacts[2]['contact_email']);
        $this->assertEquals('703-555-2222',             $contacts[2]['contact_phone']);
        $this->assertEquals('c2 address 1',             $contacts[2]['contact_address1']);
        $this->assertEquals('c2 address 2',             $contacts[2]['contact_address2']);
        $this->assertEquals('c2 city',                  $contacts[2]['contact_city']);
        $this->assertEquals('CA',                       $contacts[2]['contact_state']);
        $this->assertEquals('90211',                    $contacts[2]['contact_zip']);
        $this->assertEquals('US',                       $contacts[2]['contact_country']);
        $this->assertEquals('c2s notes.',               $contacts[2]['contact_notes']);
        $this->assertEquals(0,                          $contacts[2]['contact_project']);
        $this->assertEquals('obj/contact',              $contacts[2]['contact_icon']);
        $this->assertEquals(0,                          $contacts[2]['contact_owner']);
        $this->assertEquals(0,                          $contacts[2]['contact_private']);
        $this->assertEquals('',                         $contacts[2]['contact_updatekey']);
        $this->assertEquals('2008-01-01 11:11:11',      $contacts[2]['contact_lastupdate']);
        $this->assertEquals('2007-12-12 11:11:11',      $contacts[2]['contact_updateasked']);
        $this->assertEquals('',                         $contacts[2]['dept_name']);
        $this->assertEquals(2,                          $contacts[2]['0']);
        $this->assertEquals('Contact',                  $contacts[2]['1']);
        $this->assertEquals('Number 1',                 $contacts[2]['2']);
        $this->assertEquals('',                         $contacts[2]['3']);
		$this->assertEquals('',                         $contacts[2]['4']);
        $this->assertEquals('Vice President',           $contacts[2]['5']);
        $this->assertEquals('1973-07-22',               $contacts[2]['6']);
        $this->assertEquals('Vice President',           $contacts[2]['7']);
        $this->assertEquals(1,                          $contacts[2]['8']);
        $this->assertEquals(0,                          $contacts[2]['9']);
        $this->assertEquals('person',                   $contacts[2]['10']);
        $this->assertEquals('email2@example.com',       $contacts[2]['11']);
        $this->assertEquals('703-555-2222',             $contacts[2]['12']);
        $this->assertEquals('c2 address 1',             $contacts[2]['13']);
        $this->assertEquals('c2 address 2',             $contacts[2]['14']);
        $this->assertEquals('c2 city',                  $contacts[2]['15']);
        $this->assertEquals('CA',                       $contacts[2]['16']);
        $this->assertEquals('90211',                    $contacts[2]['17']);
        $this->assertEquals('US',                       $contacts[2]['18']);
        $this->assertEquals('c2s notes.',               $contacts[2]['19']);
        $this->assertEquals(0,                          $contacts[2]['20']);
        $this->assertEquals('obj/contact',              $contacts[2]['21']);
        $this->assertEquals(0,                          $contacts[2]['22']);
        $this->assertEquals(0,                          $contacts[2]['23']);
        $this->assertEquals('',                         $contacts[2]['24']);
        $this->assertEquals('2008-01-01 11:11:11',      $contacts[2]['25']);
        $this->assertEquals('2007-12-12 11:11:11',      $contacts[2]['26']);
        $this->assertEquals('',                         $contacts[2]['27']);
    }

    /**
    * Tests loading list of Users for this company.
    */
    public function testGetUsers()
    {
        global $AppUI;

        $users = CCompany::getUsers($AppUI, 2);

        /**
         * getUsers returns both an associative array as well as a indexed array
         * so we need to check both to make sure functionality depending on either does
         * not break.
         */
        $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $users);
        $this->assertEquals(2,                  count($users));
        $this->assertEquals(3,                  $users[3]['user_id']);
        $this->assertEquals('contact_number_2', $users[3]['user_username']);
        $this->assertEquals('Contact',          $users[3]['contact_first_name']);
        $this->assertEquals('Number 2',         $users[3]['contact_last_name']);
        $this->assertEquals(3,                  $users[3][0]);
        $this->assertEquals('contact_number_2', $users[3][1]);
        $this->assertEquals('Contact',          $users[3][2]);
        $this->assertEquals('Number 2',         $users[3][3]);
        $this->assertEquals(4,                  $users[4]['user_id']);
        $this->assertEquals('contact_number_3', $users[4]['user_username']);
        $this->assertEquals('Contact',          $users[4]['contact_first_name']);
        $this->assertEquals('Number 3',         $users[4]['contact_last_name']);
        $this->assertEquals(4,                  $users[4][0]);
        $this->assertEquals('contact_number_3', $users[4][1]);
        $this->assertEquals('Contact',          $users[4][2]);
        $this->assertEquals('Number 3',         $users[4][3]);
    }

    /**
    * Tests loading list of Departments for this company.
    */
    public function testGetDepartments()
    {
        global $AppUI;

        $departments = CCompany::getDepartments($AppUI, 1);

        $this->assertInternalType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $departments);
        $this->assertEquals(1,              count($departments));
        $this->assertEquals(1,              $departments[0]['dept_id']);
        $this->assertEquals(0,              $departments[0]['dept_parent']);
        $this->assertEquals(1,              $departments[0]['dept_company']);
        $this->assertEquals('Department 1', $departments[0]['dept_name']);
        $this->assertEquals('',             $departments[0]['dept_phone']);
        $this->assertEquals('',             $departments[0]['dept_fax']);
        $this->assertEquals('',             $departments[0]['dept_address1']);
        $this->assertEquals('',             $departments[0]['dept_address2']);
        $this->assertEquals('',             $departments[0]['dept_city']);
        $this->assertEquals('',             $departments[0]['dept_state']);
        $this->assertEquals('',             $departments[0]['dept_zip']);
        $this->assertEquals('',             $departments[0]['dept_url']);
        $this->assertEquals('',             $departments[0]['dept_desc']);
        $this->assertEquals(0,              $departments[0]['dept_owner']);
        $this->assertEquals('',             $departments[0]['dept_country']);
        $this->assertEquals('',             $departments[0]['dept_email']);
        $this->assertEquals(0,              $departments[0]['dept_type']);
        $this->assertEquals(0,              $departments[0]['dept_users']);
    }
}
