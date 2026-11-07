<?php
/**
 * Tests for w2p_Utilities_Mail.
 *
 * LICENSE: This source file is subject to Clear BSD License. Please see the
 *   LICENSE file in root of site for further details
 *
 * @package    web2project
 * @subpackage unit_tests
 * @license    Clear BSD
 * @link       http://www.web2project.net
 */

class w2p_Utilities_MailTest extends CommonSetup
{
    /**
     * Returns the encoded Subject header for a subject.
     */
    private function subjectHeader($subject)
    {
        $mail = new w2p_Utilities_Mail();
        $mail->From('a@example.com');
        $mail->To('c@example.com');
        $mail->Subject($subject);

        $this->assertSame(1, preg_match('/^Subject: (.*(?:\r?\n[ \t].*)*)/m', $mail->CreateHeader(), $matches));

        return trim($matches[1]);
    }

    /**
     * A mostly-ASCII subject with a few accented characters is Q-encoded,
     * not sent empty (the bundled PHPMailer's EncodeQ() used the removed
     * preg_replace() /e modifier).
     */
    public function testSubjectWithAFewAccentsIsNotEmpty()
    {
        $header = $this->subjectHeader('Ümlaut Corp');

        $this->assertNotSame('=?utf-8?Q??=', $header);
        $this->assertSame('Subject: [web2Project] Ümlaut Corp', iconv_mime_decode('Subject: ' . $header, 0, 'UTF-8'));
    }

    /**
     * Characters the Q encoding reserves (=, ?, _) survive the round trip.
     */
    public function testSubjectWithReservedCharacters()
    {
        $header = $this->subjectHeader('Task "Q3 = done?" für_alle');

        $this->assertSame('Subject: [web2Project] Task "Q3 = done?" für_alle', iconv_mime_decode('Subject: ' . $header, 0, 'UTF-8'));
    }

    public function testEncodeQPositions()
    {
        $mail = new w2p_Utilities_Mail();

        $this->assertSame('J=C3=BCrgen_=28Ops=29', $mail->EncodeQ('Jürgen (Ops)', 'phrase'));
        $this->assertSame('a=28b=29=22c=22_=C3=BC', $mail->EncodeQ('a(b)"c" ü', 'comment'));
        $this->assertSame('a=3Db=3F_c=5Fd_=C3=A9', $mail->EncodeQ("a=b? c_d\r\n é", 'text'));
    }
}
