<?php
/**
 * Tests for the signup form's bot protection (w2p_Utilities_SignupCheck).
 *
 * @package    web2project
 * @subpackage unit_tests
 * @license    Clear BSD
 * @link       http://www.web2project.net
 */

class w2p_Utilities_SignupCheckTest extends CommonSetup
{
    /** @var w2p_Utilities_SignupCheck */
    private $check;

    protected function setUp(): void
    {
        parent::setUp();
        $this->check = new w2p_Utilities_SignupCheck();
        unset($_SESSION[w2p_Utilities_SignupCheck::SESSION_KEY]);
    }

    /**
     * What the browser does: find n so that sha256(salt . n) == challenge.
     */
    private function solve(array $issued)
    {
        for ($n = 0; $n <= $issued['max']; $n++) {
            if (hash('sha256', $issued['salt'] . $n) === $issued['challenge']) {
                return (string) $n;
            }
        }
        $this->fail('challenge has no answer');
    }

    public function testSolvedChallengePasses()
    {
        $issued = $this->check->issue();
        $proof = $this->solve($issued);

        $this->assertSame('', $this->check->verify(array('signup_proof' => $proof, 'website' => ''), time() + 5));
    }

    public function testChallengeCanOnlyBeUsedOnce()
    {
        $issued = $this->check->issue();
        $proof = $this->solve($issued);
        $this->check->verify(array('signup_proof' => $proof), time() + 5);

        $this->assertSame('expired', $this->check->verify(array('signup_proof' => $proof), time() + 5));
    }

    public function testNoChallengeIsExpired()
    {
        $this->assertSame('expired', $this->check->verify(array('signup_proof' => '1'), time() + 5));
    }

    public function testWrongProofIsRejected()
    {
        $issued = $this->check->issue();
        $proof = (int) $this->solve($issued);
        $wrong = (string) (($proof + 1) % ($issued['max'] + 1));

        $this->assertSame('spam', $this->check->verify(array('signup_proof' => $wrong), time() + 5));
    }

    public function testMissingOrMalformedProofIsRejected()
    {
        foreach (array(array(), array('signup_proof' => ''), array('signup_proof' => '1e3'),
                     array('signup_proof' => array('1')), array('signup_proof' => '99999999999')) as $post) {
            $this->check->issue();
            $this->assertSame('spam', $this->check->verify($post, time() + 5));
        }
    }

    public function testHoneypotIsRejected()
    {
        $issued = $this->check->issue();
        $proof = $this->solve($issued);

        $this->assertSame('spam', $this->check->verify(array('signup_proof' => $proof, 'website' => 'http://spam.example'), time() + 5));
    }

    public function testTooFastIsRejected()
    {
        $issued = $this->check->issue();
        $proof = $this->solve($issued);

        $this->assertSame('spam', $this->check->verify(array('signup_proof' => $proof), time()));
    }

    public function testOldChallengeIsExpired()
    {
        $issued = $this->check->issue();
        $proof = $this->solve($issued);

        $this->assertSame('expired', $this->check->verify(array('signup_proof' => $proof),
            time() + w2p_Utilities_SignupCheck::MAX_SECONDS + 1));
    }

    public function testFormFieldsCarryTheChallenge()
    {
        $html = $this->check->renderFields();
        $issued = $_SESSION[w2p_Utilities_SignupCheck::SESSION_KEY];

        $this->assertStringContainsString('data-salt="' . $issued['salt'] . '"', $html);
        $this->assertStringContainsString('data-challenge="' . $issued['challenge'] . '"', $html);
        $this->assertStringContainsString('name="website"', $html);
    }
}
