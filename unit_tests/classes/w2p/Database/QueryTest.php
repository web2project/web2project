<?php
/**
 * Tests for w2p_Database_Query.
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @category   w2p_Database_Query_Test
 * @package    web2project
 * @subpackage unit_tests
 * @license    Clear BSD
 * @link       http://www.web2project.net
 */

class w2p_Database_QueryTest extends CommonSetup
{
    /**
     * Whether $value LIKE quoteLike($term, $match), as MySQL evaluates it.
     */
    private function likeMatches(string $value, mixed $term, string $match = 'contains'): bool
    {
        // The query class needs a table; any one row will do.
        $q = new w2p_Database_Query();
        $q->addTable('users');
        $q->addQuery($q->quote($value) . ' LIKE ' . $q->quoteLike($term, $match));
        $q->setLimit(1);
        $result = $q->loadResult();

        return is_numeric($result) && 1 == (int) $result;
    }

    public function testQuoteLikeMatchesTheTermAnywhere(): void
    {
        $this->assertTrue($this->likeMatches('Site Operations', 'Oper'));
        $this->assertTrue($this->likeMatches('Site Operations', 'Site Operations'));
        $this->assertFalse($this->likeMatches('Site Operations', 'Estimating'));
    }

    public function testQuoteLikeStartsWith(): void
    {
        $this->assertTrue($this->likeMatches('dwhitfield', 'dw', 'starts'));
        $this->assertFalse($this->likeMatches('dwhitfield', 'whit', 'starts'));
    }

    /**
     * GHSA-98hj-h8rw-w56m: a quote in the term is part of the text searched
     *   for, not the end of the SQL string.
     */
    public function testQuoteLikeKeepsQuotesInsideTheString(): void
    {
        $this->assertTrue($this->likeMatches("O'Brien", "O'Bri"));
        $this->assertFalse($this->likeMatches('anything', "zz' OR 1=1 OR '"));
        $this->assertFalse($this->likeMatches('anything', 'zz" OR 1=1 OR "'));
        $this->assertFalse($this->likeMatches('anything', "zz') OR (1=1) OR ('"));
    }

    public function testQuoteLikeMatchesWildcardsLiterally(): void
    {
        $this->assertTrue($this->likeMatches('50% off', '50%'));
        $this->assertFalse($this->likeMatches('500 off', '50%'));
        $this->assertTrue($this->likeMatches('a_c', 'a_c'));
        $this->assertFalse($this->likeMatches('abc', 'a_c'));
        $this->assertTrue($this->likeMatches('C:\\temp', 'C:\\t'));
        $this->assertFalse($this->likeMatches('anything', '%'));
    }

    public function testQuoteLikeWithNonStrings(): void
    {
        $this->assertTrue($this->likeMatches('Task 42', 42));
        $this->assertTrue($this->likeMatches('anything', ''));
        $this->assertTrue($this->likeMatches('anything', null));
    }
}
