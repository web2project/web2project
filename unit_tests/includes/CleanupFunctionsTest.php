<?php
/**
 * Class for testing cleanup_functions functionality
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @author     D. Keith Casey, Jr.<caseydk@users.sourceforge.net>
 * @category   main_functions
 * @package    web2project
 * @subpackage unit_tests
 * @license    Clear BSD
 * @link       http://www.web2project.net
 */

class CleanupFunctionsTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp(): void
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

    /**
     * arraySelect() takes its list by value, so an expression can be passed,
     * and compares keys as strings (#554).
     */
    public function testArraySelectAcceptsExpressionsAndNullSelected()
    {
        $html = arraySelect(array_merge(array('' => 'None'), array(5 => 'Five')), 'x', '', null);
        $this->assertStringContainsString('<option value="" selected="selected">None</option>', $html);
        $this->assertStringContainsString('<option value="0">Five</option>', $html);

        $html = arraySelect(array(0 => 'Zero', 1 => 'One', '1a' => 'Odd'), 'x', '', '1');
        $this->assertStringContainsString('<option value="1" selected="selected">One</option>', $html);
        $this->assertStringNotContainsString('<option value="0" selected', $html);
        $this->assertStringNotContainsString('<option value="1a" selected', $html);
    }

    /**
     * The Users list's letter filter (stub) and the preferences key are
     * quoted, not pasted into the SQL.
     */
    public function testUserStubAndPreferenceNameAreQuoted()
    {
        $sql = getUsers("A' OR '1'='1")->prepare();
        $this->assertStringContainsString("LIKE 'A\\' OR \\'1\\'=\\'1%'", $sql);

        $users = w2PgetUsersList("zz' OR '1'='1");
        $this->assertSame(array(), $users);

        $pref = new w2p_System_Preferences();
        $pref->pref_user = 0;
        $pref->pref_name = "NOSUCHPREF' OR '1'='1";
        $this->assertNull($pref->delete());

        $q = new w2p_Database_Query();
        $q->addTable('user_preferences');
        $q->addQuery('COUNT(*)');
        $q->addWhere('pref_user = 0');
        $this->assertGreaterThan(0, (int) $q->loadResult());
    }

    /**
     * Tooltips are plain text in a title attribute: markup in the tip
     * becomes lines, names stay literal and everything is escaped.
     */
    public function testToolTipIsPlainEscapedText()
    {
        $tip = '<dl><dt>Type</dt><dd>Meeting</dd><dt>Note</dt><dd>' . htmlspecialchars('a "quoted" <b>note</b>') . '</dd></dl>';
        $html = w2PtoolTip('O\'Brien <img src=x onerror=alert(1)>', $tip, true);

        $this->assertSame('<span title="O&#039;Brien &lt;img src=x onerror=alert(1)&gt;&#10;Type: Meeting&#10;Note: a &quot;quoted&quot; &lt;b&gt;note&lt;/b&gt;">', $html);
        $this->assertSame('<span id="x" title="Edit This&#10;click to edit">', w2PtoolTip('edit this', 'Click to edit', false, 'x'));
        // one line, so it can sit inside a JavaScript string
        $this->assertStringNotContainsString("\n", w2PtoolTip('add tasks panel', 'click here to add a description<br />click again to collapse it.'));
    }

    /**
     * Forum titles and bodies are escaped on output, whether the row holds
     * plain text or entity-encoded text (#533).
     */
    public function testForumTextAndBodyAreEscaped()
    {
        $bbparser = new HTML_BBCodeParser();

        $raw = "<script>alert(1)</script> Café & co\n[b]bold[/b] [url=http://example.com/?a=1&b=2]link[/url] <img src=x onerror=alert(2)>";
        $encoded = htmlentities($raw, ENT_QUOTES, 'UTF-8');

        foreach (array($raw, $encoded) as $text) {
            $html = w2PforumBody($text, $bbparser);
            $this->assertStringNotContainsString('<script', $html);
            $this->assertStringNotContainsString('<img', $html);
            $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
            $this->assertStringContainsString('Café &amp; co<br />', $html);
            $this->assertStringContainsString('<strong>bold</strong>', $html);
            $this->assertStringContainsString('<a href="http://example.com/?a=1&amp;b=2">link</a>', $html);
        }
        $this->assertSame(w2PforumBody($raw, $bbparser), w2PforumBody($encoded, $bbparser));
        $this->assertStringNotContainsString('<br />', w2PforumBody($raw, $bbparser, false));

        $this->assertSame('Re: &lt;b&gt; &amp; &quot;co&quot;', w2PforumText('Re: <b> & "co"'));
        $this->assertSame('Re: &lt;b&gt; &amp; &quot;co&quot;', w2PforumText('Re: &lt;b&gt; &amp; &quot;co&quot;'));
    }
}
