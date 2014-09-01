<?php
/**
 * Class for testing Web2project\Utilities\Paginator functionality
 *
 * @author      Keith Casey <contrib@caseysoftware.com>
 * @package     web2project
 * @subpackage  unit_tests
 * @license     Clear BSD
 * @link        http://www.web2project.net
 */

class Web2project_Utilities_PaginatorTest extends CommonSetup
{
    protected $paginator = null;

    protected function setUp()
    {
        parent::setUp();

        for($i=65; $i<91; $i++)
        {
            $items[] = chr($i);
        }

        $this->paginator = new w2p_Utilities_Paginator($items, 10);
    }

    public function testGetItemsOnPage()
    {
        $page1 = $this->paginator->getItemsOnPage();
        $this->assertEquals(10, count($page1));

        $page3 = $this->paginator->getItemsOnPage(3);
        $this->assertEquals(6,  count($page3));

        $page2 = $this->paginator->getItemsOnPage(2);
        $this->assertTrue(isset($page2[13]));
        $this->assertEquals('T', $page2[19]);
    }

    public function testBuildNavigation()
    {
        $navHTML = $this->paginator->buildNavigation(new w2p_Core_CAppUI(), 'fake', 0);
        $this->assertGreaterThan(0, strpos($navHTML, '26 Record(s) 3 Page(s)'));
    }
}