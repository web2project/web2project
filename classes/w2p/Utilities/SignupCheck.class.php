<?php
/**
 * Bot protection for the public signup form (newuser.php -> do_user_aed.php),
 *   without a third-party service or library:
 *
 * - a proof-of-work challenge: the form gets a random salt and
 *   sha256(salt . n) for a secret n <= MAX_NUMBER, and the browser has to find
 *   n (js/signupcheck.js, well under a second) before it can submit. Each
 *   attempt costs a script the same work, and the challenge is kept in the
 *   session and can only be used once.
 * - a honeypot field that people don't see but form-filling bots do.
 * - a minimum time between showing the form and submitting it.
 *
 * @package     web2project\utilities
 */
class w2p_Utilities_SignupCheck
{
    const SESSION_KEY = 'w2p_signup_check';
    const HONEYPOT_FIELD = 'website';
    const PROOF_FIELD = 'signup_proof';
    const MAX_NUMBER = 60000;
    const MIN_SECONDS = 3;
    const MAX_SECONDS = 3600;

    /**
     * Makes a new challenge for the form and remembers it in the session.
     *
     * @return array salt, challenge and max for the form's data attributes
     */
    public function issue()
    {
        $salt = bin2hex(random_bytes(12));
        $number = random_int(0, self::MAX_NUMBER);
        $challenge = hash('sha256', $salt . $number);

        $_SESSION[self::SESSION_KEY] = array(
            'salt' => $salt,
            'challenge' => $challenge,
            'max' => self::MAX_NUMBER,
            'issued' => time(),
        );

        return array('salt' => $salt, 'challenge' => $challenge, 'max' => self::MAX_NUMBER);
    }

    /**
     * The hidden fields the signup form needs.
     *
     * @return string HTML
     */
    public function renderFields()
    {
        $check = $this->issue();

        return '<input type="hidden" name="' . self::PROOF_FIELD . '" value=""'
            . ' data-salt="' . $check['salt'] . '" data-challenge="' . $check['challenge'] . '"'
            . ' data-max="' . $check['max'] . '" />'
            . '<div class="w2p-hp" aria-hidden="true"><label>Website'
            . ' <input type="text" name="' . self::HONEYPOT_FIELD . '" value="" tabindex="-1" autocomplete="off" />'
            . '</label></div>';
    }

    /**
     * Checks a submitted signup. The challenge is used up either way.
     *
     * @param array $post   the submitted form
     * @param int   $now    for tests
     *
     * @return string '' when it passes, otherwise 'expired' (no or old
     *   challenge: show the form again) or 'spam'
     */
    public function verify(array $post, $now = null)
    {
        $now = (null === $now) ? time() : (int) $now;
        $check = isset($_SESSION[self::SESSION_KEY]) ? $_SESSION[self::SESSION_KEY] : null;
        unset($_SESSION[self::SESSION_KEY]);

        if (!is_array($check) || !isset($check['salt'], $check['challenge'], $check['max'], $check['issued'])) {
            return 'expired';
        }
        if ($now - $check['issued'] > self::MAX_SECONDS) {
            return 'expired';
        }
        if (!empty($post[self::HONEYPOT_FIELD])) {
            return 'spam';
        }
        if ($now - $check['issued'] < self::MIN_SECONDS) {
            return 'spam';
        }
        $proof = isset($post[self::PROOF_FIELD]) ? $post[self::PROOF_FIELD] : '';
        if (!is_string($proof) || !ctype_digit($proof) || strlen($proof) > 10 || (int) $proof > $check['max']) {
            return 'spam';
        }
        if (!hash_equals($check['challenge'], hash('sha256', $check['salt'] . (int) $proof))) {
            return 'spam';
        }

        return '';
    }
}
