<?php
/**
 * This is the core of the authentication system. All other Authenticators
 *  should extend it.
 *
 * @package     web2project\authenticators
 */
/**
 * This class just collects the common functionality from across the
 *  Authenticators. It will tend to grow as we support more auth options.
 * 
 * @author      Keith Casey <caseydk@users.sourceforge.net>
 *
 * @package     web2project\authenticators
 * @abstract
 */
abstract class w2p_Authenticators_Base
{
    protected $AppUI = null;
    protected $w2Pconfig = null;
    protected $query = null;
    protected $user_id = null;

    public function __construct() {
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
        $newPassword = '';
        $salt = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ0123456789';
        srand((double)microtime() * 1000000);

        $i = 0;
        while ($i <= 16) {
            $num = rand() % strlen($salt);
            $tmp = substr($salt, $num, 1);
            $newPassword = $newPassword . $tmp;
            $i++;
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