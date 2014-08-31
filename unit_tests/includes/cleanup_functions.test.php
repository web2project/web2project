<?php
/**
 * Class for testing cleanup_functions functionality
 *
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author      D. Keith Casey, Jr.<caseydk@users.sourceforge.net>
 * @category    main_functions
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class Cleanup_Functions_Test extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        parent::setUp();

        global $AppUI;
        $this->_AppUI  = $AppUI;
    }

    /**
     * Tests generating options for a department selection list.
     */
    public function testGetDepartmentSelectionListIDOnly()
    {
        global $departments_count;
        $departments_count = 0;

        $options = getDepartmentSelectionList(1);

        if ($this->_AppUI->isActiveModule('departments')) {
            $this->assertEquals('<option value="1">Department 1</option>', $options);
        } else {
            $this->assertEquals('', $options);
        }
    }

    /**
     * Tests generating options for a department selection list with some checked
     */
    public function testGetDepartmentSelectionListCheckedArray()
    {
        global $departments_count;
        $departments_count = 0;
        $checked = array(1);

        $options = getDepartmentSelectionList(1, $checked);

        if ($this->_AppUI->isActiveModule('departments')) {
            $this->assertEquals('<option value="1" selected="selected">Department 1</option>', $options);
        } else {
            $this->assertEquals('', $options);
        }
    }

    /**
     * Tests generating options for a department selection list with a dept parent passed
     */
    public function testGetDepartmentSelectionListDeptParent()
    {
        global $departments_count;
        $departments_count = 0;

        $options = getDepartmentSelectionList(1, array(), 1);

        $this->assertEquals('', $options);
    }

    /**
     * Tests generating options for a department selection list with
     * set spaces in front to the option
     */
    public function testGetDepartmentSelectionListSpaces()
    {
        global $departments_count;
        $departments_count = 0;

        $options = getDepartmentSelectionList(1, array(), 0, 1);

        if ($this->_AppUI->isActiveModule('departments')) {
            $this->assertEquals('<option value="1">&nbsp;Department 1</option>', $options);
        } else {
            $this->assertEquals('', $options);
        }

        $options = getDepartmentSelectionList(1, array(), 0, 5);

        if ($this->_AppUI->isActiveModule('departments')) {
            $this->assertEquals('<option value="1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Department 1</option>', $options);
        } else {
            $this->assertEquals('', $options);
        }
    }
}
