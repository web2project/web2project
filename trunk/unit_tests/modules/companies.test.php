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

// Need this to test actions that require permissions.
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
/**
 * CompaniesTest Class.
 * 
 * Class to test the companies class
 * @author Trevor Morse<trevor.morse@gmail.com>
 * @package web2project
 * @subpackage unit_tests
 */
class Companies_Test extends PHPUnit_Framework_TestCase 
{
    
    protected $backupGlobals = FALSE;
    
    /**
     * Sets up the database for testing.
     */
    public function testSetupDB() 
    {        
        $file = dirname(___FILE___) . '/db_setup.sql';
        
        if (!file_exists($file)) {
            die("The file $file does not exist.");
        }
        
        $str = file_get_contents($file);
        
        if (!$str) {
            die("Unable to read the contents of $file.");
        }

        // split all the query's into an array
        $sql = explode(';', $str);
        $pdo = new PDO(w2PgetConfig('dbtype') . ':host=' . 
                       w2PgetConfig('dbhost') . ';dbname=' . 
                       w2PgetConfig('dbname'), 
                       w2PgetConfig('dbuser'), w2PgetConfig('dbpass'));
        
        foreach ($sql as $query) {
            
            if (!empty($query)) {
                try{
                    $result = $pdo->exec($query);
                } catch(PDOException $e) {
                    $pdo = null;
                    die($e->getMessage());
                }
            }
        }
        $pdo = null;
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
        
        // Verify we got the proper error message
        $this->AssertEquals('CCompany::store-check failed company id is NULL', $msg);
        
        // Verify that company id was not set
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
        $this->assertEquals('web2project@example.org', $company->company_email);
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
    public function testLoad() 
    {        
        $company_id = $this->createCompany();
        
        $company = new CCompany();
        $company->load($company_id);
        
        $this->assertEquals('CreatedCompany',           $company->company_name);
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
        $this->assertEquals(1,                          $company->company_type);
        $this->assertEquals('This is a company.' ,      $company->company_description);
    }
    
    /**
     * Tests loading the Company Object
     */
    public function testLoadFull() 
    {        
        $company_id = $this->createCompany();
        
        $company = new CCompany();
        $company->loadFull($company_id);
        
        $this->assertEquals('CreatedCompany',           $company->company_name);
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
        $this->assertEquals(1,                          $company->company_type);
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
        $company_id = $this->createCompany();
        
        $company = new CCompany();
        $company->load($company_id);
        
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
    }
    
    /**
     * Tests the delete of a company
     */
    public function testDeleteCompany() 
    {                
        $company_id = $this->createCompany();
        
        $company = new CCompany();
        $msg = $company->delete($company_id);
        
        $company = new CCompany();
        $result = $company->load($company_id);
        
        $this->assertFalse($result);
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
        $this->assertEquals(0,                             $companies[0]['countp']);
        $this->assertEquals(0,                             $companies[0]['inactive']);
        $this->assertEquals('Admin',                       $companies[0]['contact_first_name']);
        $this->assertEquals('Person',                      $companies[0]['contact_last_name']);
        $this->assertEquals(3,                             $companies[1]['company_id']);
        $this->assertEquals('CreatedCompany',              $companies[1]['company_name']);
        $this->assertEquals(1,                             $companies[1]['company_type']);
        $this->assertEquals('This is a company.',          $companies[1]['company_description']); 
        $this->assertEquals(0,                             $companies[1]['countp']);
        $this->assertEquals(0,                             $companies[1]['inactive']);
        $this->assertEquals('Admin',                       $companies[1]['contact_first_name']);
        $this->assertEquals('Person',                      $companies[1]['contact_last_name']);
        $this->assertEquals(1,                             $companies[2]['company_id']);
        $this->assertEquals('UnitTestCompany',             $companies[2]['company_name']);
        $this->assertEquals(2,                             $companies[2]['company_type']);
        $this->assertEquals('This is a company.',          $companies[2]['company_description']); 
        $this->assertEquals(0,                             $companies[2]['countp']);
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
        $this->assertEquals(2,                             count($companies));
        $this->assertEquals(2,                             $companies[0]['company_id']);
        $this->assertEquals('CreatedCompany',              $companies[0]['company_name']);
        $this->assertEquals(1,                             $companies[0]['company_type']);
        $this->assertEquals('This is a company.',          $companies[0]['company_description']); 
        $this->assertEquals(0,                             $companies[0]['countp']);
        $this->assertEquals(0,                             $companies[0]['inactive']);
        $this->assertEquals('Admin',                       $companies[0]['contact_first_name']);
        $this->assertEquals('Person',                      $companies[0]['contact_last_name']);
        $this->assertEquals(3,                             $companies[1]['company_id']);
        $this->assertEquals('CreatedCompany',              $companies[1]['company_name']);
        $this->assertEquals(1,                             $companies[1]['company_type']);
        $this->assertEquals('This is a company.',          $companies[1]['company_description']); 
        $this->assertEquals(0,                             $companies[1]['countp']);
        $this->assertEquals(0,                             $companies[1]['inactive']);
        $this->assertEquals('Admin',                       $companies[1]['contact_first_name']);
        $this->assertEquals('Person',                      $companies[1]['contact_last_name']);
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
        $this->assertEquals(0,                             $companies[0]['countp']);
        $this->assertEquals(0,                             $companies[0]['inactive']);
        $this->assertEquals('Admin',                       $companies[0]['contact_first_name']);
        $this->assertEquals('Person',                      $companies[0]['contact_last_name']);
        $this->assertEquals(3,                             $companies[1]['company_id']);
        $this->assertEquals('CreatedCompany',              $companies[1]['company_name']);
        $this->assertEquals(1,                             $companies[1]['company_type']);
        $this->assertEquals('This is a company.',          $companies[1]['company_description']); 
        $this->assertEquals(0,                             $companies[1]['countp']);
        $this->assertEquals(0,                             $companies[1]['inactive']);
        $this->assertEquals('Admin',                       $companies[1]['contact_first_name']);
        $this->assertEquals('Person',                      $companies[1]['contact_last_name']);
        $this->assertEquals(1,                             $companies[2]['company_id']);
        $this->assertEquals('UnitTestCompany',             $companies[2]['company_name']);
        $this->assertEquals(2,                             $companies[2]['company_type']);
        $this->assertEquals('This is a company.',          $companies[2]['company_description']); 
        $this->assertEquals(0,                             $companies[2]['countp']);
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

        // Create two companies, so we can verify that getProjects
        // only brings back projects for the proper company
        $company_id_1 = $this->createCompany();
        $company_id_2 = $this->createCompany();

        $project = new CProject();

        $post_array = array (
            'dosql'                     => 'do_project_aed',
            'project_id'                => 0,
            'project_creator'           => 1,
            'project_name'              => 'Test Project',
            'project_owner'             => 1,
            'project_company'           => $company_id_1,
            'project_priority'          => -1,
            'project_short_name'        =>'TP',
            'project_color_identifier'  => 'FFFFFF',
            'project_type'              => 0,
            'project_status'            => 0,
            'project_active'            => 1
        );
        $project->bind($post_array);
        $project->store();

        $post_array = array (
            'dosql'                     => 'do_project_aed',
            'project_id'                => 0,
            'project_creator'           => 1,
            'project_name'              => 'Test Project',
            'project_owner'             => 1,
            'project_company'           => $company_id_2,
            'project_priority'          => -1,
            'project_short_name'        =>'TP',
            'project_color_identifier'  => 'FFFFFF',
            'project_type'              => 0,
            'project_status'            => 0,
            'project_active'            => 1
        );
        $project->bind($post_array);
        $msg = $project->store();

        $projects = CCompany::getProjects($AppUI, $company_id_1);

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

        $company_id = $this->createCompany();

        $contacts = CCompany::getContacts($AppUI, $company_id);

        $this->markTestIncomplete('Cannot figure out why this does not return a result.');
    }
     
    /**
    * Tests loading list of Users for this company.
    */
    public function testGetUsers() 
    {
        global $AppUI;

        $company_id = $this->createCompany();

        $users = CCompany::getUsers($AppUI, $company_id);

        $this->markTestIncomplete('Cannot figure out how to add users to a company.');
    }
     
    /**
    * Tests loading list of Departments for this company.
    */
    public function testGetDepartments() 
    {
        global $AppUI;

        // Create two companies, so we can verify that getProjects
        // only brings back projects for the proper company
        $company_id_1 = $this->createCompany();
        $company_id_2 = $this->createCompany();

        $department = new CDepartment();

        $post_array = array (
            'dept_name'     => 'Department 1',
            'dosql'         => 'do_dept_aed',
            'dept_id'       => 0,
            'dept_company'  => $company_id_1
        );
        $department->bind($post_array);
        $department->store();

        $post_array = array (
            'dept_name'     => 'Department 1',
            'dosql'         => 'do_dept_aed',
            'dept_id'       => 0,
            'dept_company'  => $company_id_2
        );
        $department->bind($post_array);
        $department->store();

        $departments = CCompany::getDepartments($AppUI, $company_id_1);

        $this->assertType(PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $departments);
        $this->assertEquals(1,              count($departments));
        $this->assertEquals(1,              $departments[0]['dept_id']);
        $this->assertEquals(0,              $departments[0]['dept_parent']);
        $this->assertEquals(10,             $departments[0]['dept_company']);
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
     
    /**
    * Function to create a company for tests to use.
    * 
    * @return int
    */
    private function createCompany() 
    {        
        $company = new CCompany();

        $post_array = array(
            'dosql'                 => 'do_company_aed',
            'company_id'            => 0,
            'company_name'          => 'CreatedCompany',
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
            'company_type'          => 1,
            'company_description'   => 'This is a company.'
        );
        $company->bind($post_array);
        $msg = $company->store();

        return $company->company_id;
    }
}
