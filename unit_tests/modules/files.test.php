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
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Extensions/Database/TestCase.php';

/**
 * FilesTest Class.
 * 
 * Class to test the files class
 * @author D. Keith Casey, Jr.<caseydk@users.sourceforge.net>
 * @package web2project
 * @subpackage unit_tests
 */
class Files_Test extends PHPUnit_Extensions_Database_TestCase 
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
        return $this->createXMLDataSet($this->getDataSetPath().'filesSeed.xml');
    }
    protected function getDataSetPath()
    {
      return dirname(dirname(__FILE__)).'/db_files/';
    }
    
    /**
     * Tests the Attributes of a new Files object.
     */
    public function testNewFilesAttributes() 
    {
        $file = new CFile();
        
        $this->assertType('CFile',                                  $file);
        $this->assertObjectHasAttribute('file_id',                  $file);
        $this->assertObjectHasAttribute('file_real_filename',       $file);
        $this->assertObjectHasAttribute('file_project',             $file);
        $this->assertObjectHasAttribute('file_task',                $file);
        $this->assertObjectHasAttribute('file_name',                $file);
        $this->assertObjectHasAttribute('file_parent',              $file);
        $this->assertObjectHasAttribute('file_description',         $file);
        $this->assertObjectHasAttribute('file_type',                $file);
        $this->assertObjectHasAttribute('file_owner',               $file);
        $this->assertObjectHasAttribute('file_date',                $file);
        $this->assertObjectHasAttribute('file_size',                $file);
        $this->assertObjectHasAttribute('file_version',             $file);
        $this->assertObjectHasAttribute('file_icon',                $file);
        $this->assertObjectHasAttribute('file_category',            $file);
        $this->assertObjectHasAttribute('file_checkout',            $file);
        $this->assertObjectHasAttribute('file_co_reason',           $file);
        $this->assertObjectHasAttribute('file_folder',              $file);
        $this->assertObjectHasAttribute('file_indexed',             $file);
        $this->assertObjectHasAttribute('_tbl_prefix',              $file);
        $this->assertObjectHasAttribute('_tbl',                     $file);
        $this->assertObjectHasAttribute('_tbl_key',                 $file);
        $this->assertObjectHasAttribute('_error',                   $file);
        $this->assertObjectHasAttribute('_query',                   $file);
    }
    
    /**
     * Tests the Attribute Values of a new File object.
     */
    public function testNewFilesAttributeValues()
    {
        $file = new CFile();
        
        $this->assertType('CFile', $file);
        $this->assertNull($file->file_id);
        $this->assertNull($file->file_real_filename);
        $this->assertNull($file->file_project);
        $this->assertNull($file->file_task);
        $this->assertNull($file->file_name);
        $this->assertNull($file->file_parent);
        $this->assertNull($file->file_description);
        $this->assertNull($file->file_type);
        $this->assertNull($file->file_owner);
        $this->assertNull($file->file_date);
        $this->assertNull($file->file_size);
        $this->assertNull($file->file_version);
        $this->assertNull($file->file_icon);
        $this->assertNull($file->file_category);
        $this->assertNull($file->file_checkout);
        $this->assertNull($file->file_co_reason);
        $this->assertNull($file->file_folder);
        $this->assertNull($file->file_indexed);
        $this->assertEquals('',         $file->_tbl_prefix);
        $this->assertEquals('files',    $file->_tbl);
        $this->assertEquals('file_id',  $file->_tbl_key);
        $this->assertEquals('',         $file->_errors);
        $this->assertType('DBQuery',    $file->_query);
    }
}