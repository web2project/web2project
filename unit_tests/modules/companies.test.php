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
require_once W2P_BASE_DIR . '/classes/ui.class.php';
require_once W2P_BASE_DIR . '/classes/query.class.php';

/*
 * Need this to test actions that require permissions.
 */
$AppUI  = new CAppUI;
$_POST['login'] = 'login';
$_REQUEST['login'] = 'sql';
$AppUI->login('admin', 'passwd');

require_once W2P_BASE_DIR . '/classes/permissions.class.php';
require_once W2P_BASE_DIR . '/includes/session.php';
require_once W2P_BASE_DIR . '/classes/CustomFields.class.php';
require_once W2P_BASE_DIR . '/modules/companies/companies.class.php';
require_once W2P_BASE_DIR . '/modules/projects/projects.class.php';
require_once W2P_BASE_DIR . '/modules/departments/departments.class.php';
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Extensions/Database/TestCase.php';

/**
 * CompaniesTest Class.
 * 
 * Class to test the companies class
 * @author Trevor Morse<trevor.morse@gmail.com>
 * @package web2project
 * @subpackage unit_tests
 */
class Companies_Test extends PHPUnit_Extensions_Database_TestCase 
{
    
    protected $backupGlobals = FALSE;
        
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
    	return dirname(dirname(__FILE__)).'/db_files/';
    }

    /**
     * Tests the Attributes of a new Companies object.
     */
    public function testNewCompanyAttributes() 
    {        
        $company = new CCompany();
        
        $this->assertType('CCompany', $company);
        $this->assertObjectHasAttribute('company_id',           $company);
        $this->assertObjectHasAttribute('company_name',         $company);
        $this->assertObjectHasAttribute('company_phone1',       $company);
        $this->assertObjectHasAttribute('company_phone2',       $company);
        $this->assertObjectHasAttribute('company_fax',          $company);
        $this->assertObjectHasAttribute('company_address1',     $company);
        $this->assertObjectHasAttribute('company_address2',     $company);
        $this->assertObjectHasAttribute('company_city',         $company);
        $this->assertObjectHasAttribute('company_state',        $company);
        $this->assertObjectHasAttribute('company_zip',          $company);
        $this->assertObjectHasAttribute('company_country',      $company);
        $this->assertObjectHasAttribute('company_email',        $company);
        $this->assertObjectHasAttribute('company_primary_url',  $company);
        $this->assertObjectHasAttribute('company_owner',        $company);
        $this->assertObjectHasAttribute('company_description',  $company);
        $this->assertObjectHasAttribute('company_type',         $company);
        $this->assertObjectHasAttribute('company_custom',       $company);
        $this->assertObjectHasAttribute('_tbl_prefix',          $company);   
        $this->assertObjectHasAttribute('_tbl',                 $company);
        $this->assertObjectHasAttribute('_tbl_key',             $company);
        $this->assertObjectHasAttribute('_error',               $company);
        $this->assertObjectHasAttribute('_query',               $company);
    }
    
    /**
     * Tests the Attribute Values of a new Company object.
     */
    public function testNewCompanyAttributeValues() 
    {        
        $company = new CCompany();
        $this->assertType('CCompany', $company);
        $this->assertNull($company->company_id);
        $this->assertNull($company->company_name);
        $this->assertNull($company->company_phone1);
        $this->assertNull($company->company_phone2);
        $this->assertNull($company->company_fax);
        $this->assertNull($company->company_address1);
        $this->assertNull($company->company_address2);
        $this->assertNull($company->company_city);
        $this->assertNull($company->company_state);
        $this->assertNull($company->company_zip);
        $this->assertNull($company->company_country);
        $this->assertNull($company->company_email);
        $this->assertNull($company->company_primary_url);
        $this->assertNull($company->company_owner);
        $this->assertNull($company->company_description);
        $this->assertNull($company->company_type);
        $this->assertNull($company->company_custom);
        $this->assertEquals('',             $company->_tbl_prefix); 
        $this->assertEquals('companies',    $company->_tbl);
        $this->assertEquals('company_id',   $company->_tbl_key);
        $this->assertEquals('',             $company->_error);
        $this->assertType('DBQuery',        $company->_query);
    }
    
    /**
     * Tests that the proper error message is returned when a company
     * is attempted to be created without an id.
     */
    public function testCreateCompanyNoID() 
    {        
        $company = new CCompany();

        $post_array = array(
            'dosql'                 => 'do_company_aed',
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
            'company_primary_url'   => 'web2project.org',
            'company_owner'         => 1,
            'company_type'          => 2,
            'company_description'   => 'This is a company.'
        );
        $company->bind($post_array);
        $msg = $company->store();
        
        /**
         * Verify we got the proper error message
         */
        $this->AssertEquals('CCompany::store-check failed company id is NULL', $msg);
        
        /**
         * Verify that company id was not set
         */
        $this->assertNull($company->company_id);
    }
    
/**
     * Tests that the proper error message is returned when a company
     * is attempted to be created without a name.
     */
    public function testCreateCompanyNoName() 
    {        
        $company = new CCompany();

        $post_array = array(
            'dosql'                 => 'do_company_aed',
            'company_id'            => 0,
            'company_name'          => '',
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
            'company_primary_url'   => 'web2project.org',
            'company_owner'         => 1,
            'company_type'          => 2,
            'company_description'   => 'This is a company.'
        );
        $company->bind($post_array);
        $msg = $company->store();
        
        /**
         * Verify we got the proper error message
         */
        $this->AssertEquals('CCompany::store-check failed company name is NULL', $msg);
        
        /**
         * Verify that company id was not set
         */
        $this->assertNull($company->company_id);
    }
    
    /**
     * Tests the proper creation of a company
     */
    public function testCreateCompany() 
    {        
        $company = new CCompany();

        $post_array = array(
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
        $company->bind($post_array);
        $msg = $company->store();
        
        $this->assertEquals('', $msg);
        $this->assertEquals('UnitTestCompany',          $company->company_name);
        $this->assertEquals('web2project@example.org',  $company->company_email);
        $this->assertEquals('1.999.999.9999',           $company->company_phone1);
        $this->assertEquals('1.999.999.9998',           $company->company_phone2);
        $this->assertEquals('1.999.999.9997',           $company->company_fax);
        $this->assertEquals('Address 1',                $company->company_address1);
        $this->assertEquals('Address 2',                $company->company_address2);
        $this->assertEquals('City',                     $company->company_city);
        $this->assertEquals('CA',                       $company->company_state);
        $this->assertEquals('90210',                    $company->company_zip);
        $this->assertEquals('US',                       $company->company_country);
        $this->assertEquals('web2project.net',          $company->company_primary_url);
        $this->assertEquals(1,                          $company->company_owner);
        $this->assertEquals(2,                          $company->company_type);
        $this->assertEquals('This is a company.' ,      $company->company_description);
        
        $xml_dataset = $this->createXMLDataSet(dirname(__FILE__).'/../db_files/testCreateCompany.xml');        
        $this->assertTablesEqual($xml_dataset->getTable('companies'), $this->getConnection()->createDataSet()->getTable('companies'));
    }
    
    /** 
     * Tests loading the Company Object
     */
    public function testLoad() 
    {        
        $company = new CCompany();
        $company->load(1);
        
        $this->assertEquals('UnitTestCompany',          $company->company_name);
        $this->assertEquals('web2project@example.org',  $company->company_email);
        $this->assertEquals('1.999.999.9999',           $company->company_phone1);
        $this->assertEquals('1.999.999.9998',           $company->company_phone2);
        $this->assertEquals('1.999.999.9997',           $company->company_fax);
        $this->assertEquals('Address 1',                $company->company_address1);
        $this->assertEquals('Address 2',                $company->company_address2);
        $this->assertEquals('City',                     $company->company_city);
        $this->assertEquals('CA',                       $company->company_state);
        $this->assertEquals('90210',                    $company->company_zip);
        $this->assertEquals('US',                       $company->company_country);
        $this->assertEquals('web2project.net',          $company->company_primary_url);
        $this->assertEquals(1,                          $company->company_owner);
        $this->assertEquals(2,                          $company->company_type);
        $this->assertEquals('This is a company.' ,      $company->company_description);
    }
    
    /**
     * Tests loading the Company Object
     */
    public function testLoadFull() 
    {        
        $company = new CCompany();
        $company->loadFull(1);
        
        $this->assertEquals('UnitTestCompany',          $company->company_name);
        $this->assertEquals('web2project@example.org',  $company->company_email);
        $this->assertEquals('1.999.999.9999',           $company->company_phone1);
        $this->assertEquals('1.999.999.9998',           $company->company_phone2);
        $this->assertEquals('1.999.999.9997',           $company->company_fax);
        $this->assertEquals('Address 1',                $company->company_address1);
        $this->assertEquals('Address 2',                $company->company_address2);
        $this->assertEquals('City',                     $company->company_city);
        $this->assertEquals('CA',                       $company->company_state);
        $this->assertEquals('90210',                    $company->company_zip);
        $this->assertEquals('US',                       $company->company_country);
        $this->assertEquals('web2project.net',          $company->company_primary_url);
        $this->assertEquals(1,                          $company->company_owner);
        $this->assertEquals(2,                          $company->company_type);
        $this->assertEquals('This is a company.' ,      $company->company_description);
        $this->assertEquals(0,                          $company->company_module);
        $this->assertEquals(0,                          $company->company_private);
        $this->assertEquals('Admin',                    $company->contact_first_name);
        $this->assertEquals('Person',                   $company->contact_last_name);
    }
    
    /**
     * Tests the update of a company
     */
    public function testUpdateCompany() 
    {       
        $company = new CCompany();
        $company->load(1);
        
        $post_array = array(
            'dosql'                 => 'do_company_aed',
            'company_id'            => $company_id,
            'company_name'          => 'UpdatedCompany',
            'company_email'         => 'updated@example.org',
            'company_phone1'        => '1.777.999.9999',
            'company_phone2'        => '1.777.999.9998',
            'company_fax'           => '1.777.999.9997',
            'company_address1'      => 'Updated Address 1',
            'company_address2'      => 'Updated Address 2',
            'company_city'          => 'Updated City',
            'company_state'         => 'NS',
            'company_zip'           => 'A2A 2B2',
            'company_country'       => 'CA',
            'company_primary_url'   => 'ut.web2project.net',
            'company_owner'         => 1,
            'company_type'          => 2,
            'company_description'   => 'This is an updated company.'
        );
        
        $company->bind($post_array);
        $company->store();
        
        $this->assertEquals('UpdatedCompany',               $company->company_name);
        $this->assertEquals('updated@example.org',          $company->company_email);
        $this->assertEquals('1.777.999.9999',               $company->company_phone1);
        $this->assertEquals('1.777.999.9998',               $company->company_phone2);
        $this->assertEquals('1.777.999.9997',               $company->company_fax);
        $this->assertEquals('Updated Address 1',            $company->company_address1);
        $this->assertEquals('Updated Address 2',            $company->company_address2);
        $this->assertEquals('Updated City',                 $company->company_city);
        $this->assertEquals('NS',                           $company->company_state);
        $this->assertEquals('A2A 2B2',                      $company->company_zip);
        $this->assertEquals('CA',                           $company->company_country);
        $this->assertEquals('ut.web2project.net',           $company->company_primary_url);
        $this->assertEquals(1,                              $company->company_owner);
        $this->assertEquals(2,                              $company->company_type);
        $this->assertEquals('This is an updated company.',  $company->company_description);
        
        $xml_dataset = $this->createXMLDataSet($this->getDataSetPath().'testUpdateCompany.xml');        
        $this->assertTablesEqual($xml_dataset->getTable('companies'), $this->getConnection()->createDataSet()->getTable('companies'));
    }
    
    /**
     * Tests the delete of a company
     */
    public function testDeleteCompany() 
    {              
        $company = new CCompany();
        $msg = $company->delete(1);
        $this->assertEquals('noDeleteRecord: Projects, Departments', $msg);
        
        $msg = $company->delete(3);      
        $this->assertEquals('', $msg);
        
        $result = $company->load(3);
        $this->assertFalse($result);
        
        $xml_dataset = $this->createXMLDataSet($this->getDataSetPath().'testDeleteCompany.xml');        
        $this->assertTablesEqual($xml_dataset->getTable('companies'), $this->getConnection()->createDataSet()->getTable('companies'));
    }
    
    /** 
     * Tests loading list of companies with no criteria
     */
    public function testGetCompanyListNoCriteria() 
    {         
        global $AppUI;
        
        $company = new CCompany();

        $companies = $company->getCompanyList($AppUI);

        $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $companies);
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

        $company = new CCompany();

        $results = $company->getCompanyList($AppUI, 3);

        $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $results);
        $this->assertEquals(0, count($results));
    }
     
    /** 
    * Tests loading list of companies by Type
    */
    public function testGetCompanyListByType() 
    {
        global $AppUI;

        $company = new CCompany();

        $companies = $company->getCompanyList($AppUI, 1);

        $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $companies);
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

        $company = new CCompany();

        $results = $company->getCompanyList($AppUI, -1, 'This is a company');

        $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $results);
        $this->assertEquals(0, count($results));
    }
     
    /** 
    * Tests loading list of companies by owner id
    */
    public function testGetCompanyListByOwnerIDNoMatch() 
    {
        global $AppUI;

        $company = new CCompany();

        $results = $company->getCompanyList($AppUI, -1, '', 2);

        $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $results);
        $this->assertEquals(0, count($results));
    }
     
    /** 
    * Tests loading list of companies by owner id
    */
    public function testGetCompanyListByOwnerID() 
    {
        global $AppUI;

        $company = new CCompany();

        $companies = $company->getCompanyList($AppUI, -1, '', 1);

        $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $companies);
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

        $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $projects);
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
        $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $contacts);
        $this->assertEquals(2, 							count($contacts));
        $this->assertEquals(1,                          $contacts[1]['contact_id']);
        $this->assertEquals('Admin',                    $contacts[1]['contact_first_name']);
        $this->assertEquals('Person',                   $contacts[1]['contact_last_name']);
        $this->assertEquals('',                         $contacts[1]['contact_order_by']);
        $this->assertEquals('President',                $contacts[1]['contact_title']);
        $this->assertEquals('1983-07-22',               $contacts[1]['contact_birthday']);
        $this->assertEquals('President',                $contacts[1]['contact_job']);
        $this->assertEquals(1,                          $contacts[1]['contact_company']);
        $this->assertEquals(0,                          $contacts[1]['contact_department']);
        $this->assertEquals('person',                   $contacts[1]['contact_type']);
        $this->assertEquals('contact1@example.org',     $contacts[1]['contact_email']);
        $this->assertEquals('contact1_2@example.org',   $contacts[1]['contact_email2']);
        $this->assertEquals('1.example.org',            $contacts[1]['contact_url']);
        $this->assertEquals('1.999.999.9999',           $contacts[1]['contact_phone']);
        $this->assertEquals('1.999.999.9998',           $contacts[1]['contact_phone2']);
        $this->assertEquals('1.999.999.9997',           $contacts[1]['contact_fax']);
        $this->assertEquals('1.999.999.9996',           $contacts[1]['contact_mobile']);
        $this->assertEquals('c1 address 1',             $contacts[1]['contact_address1']);
        $this->assertEquals('c1 address 2',             $contacts[1]['contact_address2']);
        $this->assertEquals('c1 city',                  $contacts[1]['contact_city']);
        $this->assertEquals('CA',                       $contacts[1]['contact_state']);
        $this->assertEquals('90210',                    $contacts[1]['contact_zip']);
        $this->assertEquals('US',                       $contacts[1]['contact_country']);
        $this->assertEquals('c1jabber',                 $contacts[1]['contact_jabber']);
        $this->assertEquals('c1icq',                    $contacts[1]['contact_icq']);
        $this->assertEquals('c1msn',                    $contacts[1]['contact_msn']);
        $this->assertEquals('c1yahoo',                  $contacts[1]['contact_yahoo']);
        $this->assertEquals('c1aol',                    $contacts[1]['contact_aol']);
        $this->assertEquals('c1s notes.',               $contacts[1]['contact_notes']);
        $this->assertEquals(0,                          $contacts[1]['contact_project']);
        $this->assertEquals('obj/contact',              $contacts[1]['contact_icon']);
        $this->assertEquals(0,                          $contacts[1]['contact_owner']);
        $this->assertEquals(0,                          $contacts[1]['contact_private']);
        $this->assertEquals('',                         $contacts[1]['contact_updatekey']);
        $this->assertEquals('2009-01-01 11:11:11',      $contacts[1]['contact_lastupdate']);
        $this->assertEquals('2008-12-12 11:11:11',      $contacts[1]['contact_updateasked']);
        $this->assertEquals('c1skype',                  $contacts[1]['contact_skype']);
        $this->assertEquals('c1google',                 $contacts[1]['contact_google']);
        $this->assertEquals('',                         $contacts[1]['dept_name']);
        $this->assertEquals(1,                          $contacts[1]['0']);
        $this->assertEquals('Admin',                    $contacts[1]['1']);
        $this->assertEquals('Person',                   $contacts[1]['2']);
        $this->assertEquals('',                         $contacts[1]['3']);
        $this->assertEquals('President',                $contacts[1]['4']);
        $this->assertEquals('1983-07-22',               $contacts[1]['5']);
        $this->assertEquals('President',                $contacts[1]['6']);
        $this->assertEquals(1,                          $contacts[1]['7']);
        $this->assertEquals(0,                          $contacts[1]['8']);
        $this->assertEquals('person',                   $contacts[1]['9']);
        $this->assertEquals('contact1@example.org',     $contacts[1]['10']);
        $this->assertEquals('contact1_2@example.org',   $contacts[1]['11']);
        $this->assertEquals('1.example.org',            $contacts[1]['12']);
        $this->assertEquals('1.999.999.9999',           $contacts[1]['13']);
        $this->assertEquals('1.999.999.9998',           $contacts[1]['14']);
        $this->assertEquals('1.999.999.9997',           $contacts[1]['15']);
        $this->assertEquals('1.999.999.9996',           $contacts[1]['16']);
        $this->assertEquals('c1 address 1',             $contacts[1]['17']);
        $this->assertEquals('c1 address 2',             $contacts[1]['18']);
        $this->assertEquals('c1 city',                  $contacts[1]['19']);
        $this->assertEquals('CA',                       $contacts[1]['20']);
        $this->assertEquals('90210',                    $contacts[1]['21']);
        $this->assertEquals('US',                       $contacts[1]['22']);
        $this->assertEquals('c1jabber',                 $contacts[1]['23']);
        $this->assertEquals('c1icq',                    $contacts[1]['24']);
        $this->assertEquals('c1msn',                    $contacts[1]['25']);
        $this->assertEquals('c1yahoo',                  $contacts[1]['26']);
        $this->assertEquals('c1aol',                    $contacts[1]['27']);
        $this->assertEquals('c1s notes.',               $contacts[1]['28']);
        $this->assertEquals(0,                          $contacts[1]['29']);
        $this->assertEquals('obj/contact',              $contacts[1]['30']);
        $this->assertEquals(0,                          $contacts[1]['31']);
        $this->assertEquals(0,                          $contacts[1]['32']);
        $this->assertEquals('',                         $contacts[1]['33']);
        $this->assertEquals('2009-01-01 11:11:11',      $contacts[1]['34']);
        $this->assertEquals('2008-12-12 11:11:11',      $contacts[1]['35']);
        $this->assertEquals('c1skype',                  $contacts[1]['36']);
        $this->assertEquals('c1google',                 $contacts[1]['37']);
        $this->assertEquals('',                         $contacts[1]['38']);
        $this->assertEquals(2,                          $contacts[2]['contact_id']);
        $this->assertEquals('Contact',                  $contacts[2]['contact_first_name']);
        $this->assertEquals('Number 1',                 $contacts[2]['contact_last_name']);
        $this->assertEquals('',                         $contacts[2]['contact_order_by']);
        $this->assertEquals('Vice President',           $contacts[2]['contact_title']);
        $this->assertEquals('1973-07-22',               $contacts[2]['contact_birthday']);
        $this->assertEquals('Vice President',           $contacts[2]['contact_job']);
        $this->assertEquals(1,                          $contacts[2]['contact_company']);
        $this->assertEquals(0,                          $contacts[2]['contact_department']);
        $this->assertEquals('person',                   $contacts[2]['contact_type']);
        $this->assertEquals('contact2@example.org',     $contacts[2]['contact_email']);
        $this->assertEquals('contact2_2@example.org',   $contacts[2]['contact_email2']);
        $this->assertEquals('2.example.org',            $contacts[2]['contact_url']);
        $this->assertEquals('1.888.888.8888',           $contacts[2]['contact_phone']);
        $this->assertEquals('1.888.888.8887',           $contacts[2]['contact_phone2']);
        $this->assertEquals('1.888.888.8886',           $contacts[2]['contact_fax']);
        $this->assertEquals('1.888.888.8885',           $contacts[2]['contact_mobile']);
        $this->assertEquals('c2 address 1',             $contacts[2]['contact_address1']);
        $this->assertEquals('c2 address 2',             $contacts[2]['contact_address2']);
        $this->assertEquals('c2 city',                  $contacts[2]['contact_city']);
        $this->assertEquals('CA',                       $contacts[2]['contact_state']);
        $this->assertEquals('90211',                    $contacts[2]['contact_zip']);
        $this->assertEquals('US',                       $contacts[2]['contact_country']);
        $this->assertEquals('c2jabber',                 $contacts[2]['contact_jabber']);
        $this->assertEquals('c2icq',                    $contacts[2]['contact_icq']);
        $this->assertEquals('c2msn',                    $contacts[2]['contact_msn']);
        $this->assertEquals('c2yahoo',                  $contacts[2]['contact_yahoo']);
        $this->assertEquals('c2aol',                    $contacts[2]['contact_aol']);
        $this->assertEquals('c2s notes.',               $contacts[2]['contact_notes']);
        $this->assertEquals(0,                          $contacts[2]['contact_project']);
        $this->assertEquals('obj/contact',              $contacts[2]['contact_icon']);
        $this->assertEquals(0,                          $contacts[2]['contact_owner']);
        $this->assertEquals(0,                          $contacts[2]['contact_private']);
        $this->assertEquals('',                         $contacts[2]['contact_updatekey']);
        $this->assertEquals('2008-01-01 11:11:11',      $contacts[2]['contact_lastupdate']);
        $this->assertEquals('2007-12-12 11:11:11',      $contacts[2]['contact_updateasked']);
        $this->assertEquals('c2skype',                  $contacts[2]['contact_skype']);
        $this->assertEquals('c2google',                 $contacts[2]['contact_google']);
        $this->assertEquals('',                         $contacts[2]['dept_name']);
        $this->assertEquals(2,                          $contacts[2]['0']);
        $this->assertEquals('Contact',                  $contacts[2]['1']);
        $this->assertEquals('Number 1',                 $contacts[2]['2']);
        $this->assertEquals('',                         $contacts[2]['3']);
        $this->assertEquals('Vice President',           $contacts[2]['4']);
        $this->assertEquals('1973-07-22',               $contacts[2]['5']);
        $this->assertEquals('Vice President',           $contacts[2]['6']);
        $this->assertEquals(1,                          $contacts[2]['7']);
        $this->assertEquals(0,                          $contacts[2]['8']);
        $this->assertEquals('person',                   $contacts[2]['9']);
        $this->assertEquals('contact2@example.org',     $contacts[2]['10']);
        $this->assertEquals('contact2_2@example.org',   $contacts[2]['11']);
        $this->assertEquals('2.example.org',            $contacts[2]['12']);
        $this->assertEquals('1.888.888.8888',           $contacts[2]['13']);
        $this->assertEquals('1.888.888.8887',           $contacts[2]['14']);
        $this->assertEquals('1.888.888.8886',           $contacts[2]['15']);
        $this->assertEquals('1.888.888.8885',           $contacts[2]['16']);
        $this->assertEquals('c2 address 1',             $contacts[2]['17']);
        $this->assertEquals('c2 address 2',             $contacts[2]['18']);
        $this->assertEquals('c2 city',                  $contacts[2]['19']);
        $this->assertEquals('CA',                       $contacts[2]['20']);
        $this->assertEquals('90211',                    $contacts[2]['21']);
        $this->assertEquals('US',                       $contacts[2]['22']);
        $this->assertEquals('c2jabber',                 $contacts[2]['23']);
        $this->assertEquals('c2icq',                    $contacts[2]['24']);
        $this->assertEquals('c2msn',                    $contacts[2]['25']);
        $this->assertEquals('c2yahoo',                  $contacts[2]['26']);
        $this->assertEquals('c2aol',                    $contacts[2]['27']);
        $this->assertEquals('c2s notes.',               $contacts[2]['28']);
        $this->assertEquals(0,                          $contacts[2]['29']);
        $this->assertEquals('obj/contact',              $contacts[2]['30']);
        $this->assertEquals(0,                          $contacts[2]['31']);
        $this->assertEquals(0,                          $contacts[2]['32']);
        $this->assertEquals('',                         $contacts[2]['33']);
        $this->assertEquals('2008-01-01 11:11:11',      $contacts[2]['34']);
        $this->assertEquals('2007-12-12 11:11:11',      $contacts[2]['35']);
        $this->assertEquals('c2skype',                  $contacts[2]['36']);
        $this->assertEquals('c2google',                 $contacts[2]['37']);
        $this->assertEquals('',                         $contacts[2]['38']);
        
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
        $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $users);
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

        $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $departments);
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
