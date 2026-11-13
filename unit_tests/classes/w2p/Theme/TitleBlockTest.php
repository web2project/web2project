<?php
/**
 * Tests for w2p_Theme_TitleBlock.
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @category   w2p_Theme_TitleBlock_Test
 * @package    web2project
 * @subpackage unit_tests
 * @license    Clear BSD
 * @link       http://www.web2project.net
 */

class w2p_Theme_TitleBlockTest extends CommonSetup
{
    /**
     * The HTML of the search box built for $search.
     */
    private function searchCell(mixed $search): string
    {
        $titleBlock = new w2p_Theme_TitleBlock('Departments', 'icon.png', 'departments');
        $titleBlock->addSearchCell($search);

        return $titleBlock->cells1[0][1];
    }

    /**
     * The search box gets the term as typed and escapes it, so quotes and
     *   ampersands are shown as typed and can't end the value attribute.
     */
    public function testAddSearchCellEscapesTheTerm(): void
    {
        $html = $this->searchCell('" autofocus onfocus="alert(1)');
        $this->assertStringContainsString('value="&quot; autofocus onfocus=&quot;alert(1)"', $html);
        $this->assertStringNotContainsString('" autofocus', $html);

        $html = $this->searchCell("O'Brien & Sons");
        $this->assertStringContainsString('value="O&#039;Brien &amp; Sons"', $html);
    }

    public function testAddSearchCellWithNonStrings(): void
    {
        $this->assertStringContainsString('value=""', $this->searchCell(array('x')));
        $this->assertStringContainsString('value=""', $this->searchCell(null));
        $this->assertStringContainsString('value="42"', $this->searchCell(42));
    }
}
