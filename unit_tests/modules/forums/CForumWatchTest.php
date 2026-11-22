<?php
/**
 * Tests for the forum read checks behind watches and watch mail
 * (GHSA-qp43-fcwc-p3j3).
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @category   CForum
 * @package    web2project
 * @subpackage unit_tests
 * @license    Clear BSD
 * @link       http://www.web2project.net
 */

class CForumWatchTest extends CommonSetup
{
    public function testNothingToFilterAllowsNothing(): void
    {
        $forum = new CForum();
        $forum->overrideDatabase($this->mockDB);

        $this->assertSame(array(), $forum->filterViewable(1, array()));
        $this->assertSame(array(), $forum->filterViewable(1, array(0, '', 'x')));
        $this->assertSame(array(), $forum->filterViewableTopics(1, array()));
    }

    public function testNoUserAllowsNothing(): void
    {
        // A watch row for user 0 is the "watch all" marker, not a reader.
        $forum = new CForum();
        $forum->overrideDatabase($this->mockDB);

        $this->assertSame(array(), $forum->filterViewable(0, array(1, 2)));
        $this->assertSame(array(), $forum->filterViewableTopics(0, array(1, 2)));
    }
}
