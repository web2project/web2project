<?php
/**
 * This is the core of the authentication system. All other Authenticators
 *  should extend it.
 *
 * @package web2project\authenticators
 */
/**
 * This class just collects the common functionality from across the
 *  Authenticators. It will tend to grow as we support more auth options.
 *
 * @author Keith Casey <caseydk@users.sourceforge.net>
 *
 * @package  web2project\authenticators
 * @abstract
 */
abstract class w2p_Authenticators_Base
{
    protected $AppUI = null;
    protected $w2Pconfig = null;
    protected $query = null;
    protected $user_id = null;

    public function __construct() {
        /** @var w2p_Core_CAppUI $AppUI */
        global $AppUI;
        global $w2Pconfig;

        $this->AppUI = $AppUI;
        $this->w2Pconfig = $w2Pconfig;
        $this->query = new w2p_Database_Query;
    }

    public function hashPassword($password, $salt = '')
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);

        return $hash;
    }

    /**
     * This generates a new temporary password in order to send it to the user.
     *   It should be considered temporary because we could be sent via email.
     *
     * @return string
     */
    public function createNewPassword()
    {
        // 17 characters from an alphabet without look-alikes (no i, l, o, I,
        // L, O), picked with a cryptographically secure generator. rand()
        // seeded from the clock made these passwords guessable.
        $alphabet = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ0123456789';
        $max = strlen($alphabet) - 1;

        $newPassword = '';
        for ($i = 0; $i < 17; $i++) {
            $newPassword .= $alphabet[random_int(0, $max)];
        }

        return $newPassword;
    }

    /**
     * This just returns the userId and will need to be overridden only rarely.
     *
     * @return int
     */
    public function userId()
    {
        return (int) $this->user_id;
    }
}