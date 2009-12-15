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

// Need this to test actions that require permissions.
$AppUI  = new CAppUI;
$_POST['login'] = 'login';
$_REQUEST['login'] = 'sql';
$AppUI->login('admin', 'passwd');

require_once W2P_BASE_DIR . '/includes/session.php';
require_once 'PHPUnit/Framework.php';
/**
 * W2pObjectTest Class.
 *
 * Class to test the date include
 * @author D. Keith Casey, Jr.
 * @package web2project
 * @subpackage unit_tests
 */
class CW2pObject_Test extends PHPUnit_Framework_TestCase
{
  protected $backupGlobals = FALSE;
  protected $obj = null;
  protected $post_data = array();

  protected function setUp()
  {
    parent::setUp();

    $this->obj = new CW2pObject('fake', 'fake_id');

    $this->obj->name = 'web2project homepage';
    $this->obj->link = 'http://web2project.net';
    $this->obj->email = 'test@test.com';
  }

  /**
   * Tests the proper creation of a link
   */
  public function testURL()
  {
    global $AppUI;

    $target = '<a href="'.$this->obj->link.'" target="_new">'.$this->obj->link.'</a>';
    $linkText = $this->obj->url('link');
    $this->assertEquals($target, $linkText);

    $target = '';
    $linkText = $this->obj->url('empty_param');
    $this->assertEquals($target, $linkText);

    $target = '<a href="'.$this->obj->link.'" target="_new">'.$this->obj->name.'</a>';
    $linkText = $this->obj->url('link', $this->obj->name);
    $this->assertEquals($target, $linkText);
  }

  /**
   * Tests the proper creation of an email link
   */
  public function testEmail()
  {
    global $AppUI;

    $target = '<a href="mailto:'.$this->obj->email.'">'.$this->obj->email.'</a>';
    $linkText = $this->obj->email('email');
    $this->assertEquals($target, $linkText);

    $target = '';
    $linkText = $this->obj->email('empty_param');
    $this->assertEquals($target, $linkText);

    $target = '<a href="mailto:'.$this->obj->email.'">'.$this->obj->name.'</a>';
    $linkText = $this->obj->email('email', $this->obj->name);
    $this->assertEquals($target, $linkText);
  }
}