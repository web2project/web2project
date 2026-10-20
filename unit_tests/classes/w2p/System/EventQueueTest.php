<?php
/**
 * Class for testing w2p_System_EventQueue rescheduling
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @category   w2p_System_EventQueue_Test
 * @package    web2project
 * @subpackage unit_tests
 * @license    Clear BSD
 * @link       http://www.web2project.net
 */

class w2p_System_EventQueueTest extends CommonSetup
{
    const DAY = 86400;

    private function row($start, $interval, $count)
    {
        return array(
            'queue_id' => 42,
            'queue_start' => $start,
            'queue_repeat_interval' => $interval,
            'queue_repeat_count' => $count,
        );
    }

    public function testOneOffEventIsDeleted()
    {
        $queue = new w2p_System_EventQueue();
        $fields = $this->row(1000, 0, 1);

        $queue->update_event($fields, 2000);

        $this->assertEquals(array(42), $queue->delete_list);
        $this->assertEquals(array(), $queue->update_list);
    }

    public function testRepeatingEventOnScheduleMovesOneInterval()
    {
        $queue = new w2p_System_EventQueue();
        $now = 1000000;
        $fields = $this->row($now - 60, self::DAY, 5);

        $queue->update_event($fields, $now);

        $this->assertEquals($now - 60 + self::DAY, $fields['queue_start']);
        $this->assertEquals(4, $fields['queue_repeat_count']);
        $this->assertEquals(array(), $queue->delete_list);
    }

    public function testRepeatingEventThatFellBehindSkipsMissedRuns()
    {
        $queue = new w2p_System_EventQueue();
        $now = 1000000;
        // Due ten days and one hour ago; the scanner was not running.
        $start = $now - 10 * self::DAY - 3600;
        $fields = $this->row($start, self::DAY, 30);

        $queue->update_event($fields, $now);

        // Next run is the first future slot at the same time of day,
        // so the next scan does not run it again.
        $this->assertGreaterThan($now, $fields['queue_start']);
        $this->assertLessThanOrEqual($now + self::DAY, $fields['queue_start']);
        $this->assertEquals(0, ($fields['queue_start'] - $start) % self::DAY);
        // Only the run that actually happened uses up a repeat.
        $this->assertEquals(29, $fields['queue_repeat_count']);
    }

    public function testNextStartExactlyNowIsPushedPastNow()
    {
        $queue = new w2p_System_EventQueue();
        $now = 1000000;
        $fields = $this->row($now - 3600, 3600, 3);

        $queue->update_event($fields, $now);

        $this->assertEquals($now + 3600, $fields['queue_start']);
    }

    public function testStringValuesFromTheDatabaseAreHandled()
    {
        $queue = new w2p_System_EventQueue();
        $now = 1000000;
        $fields = $this->row((string) ($now - 3 * 3600 - 5), '3600', '2');

        $queue->update_event($fields, $now);

        $this->assertSame($now + 3600 - 5, $fields['queue_start']);
        $this->assertEquals(1, $fields['queue_repeat_count']);
    }
}
