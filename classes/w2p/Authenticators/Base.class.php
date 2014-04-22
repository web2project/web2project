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
    protected $_AppUI = null;
    protected $_w2Pconfig = null;

    public function __construct() {
        global $AppUI;
        global $w2Pconfig;

        $this->_AppUI = $AppUI;
        $this->_w2Pconfig = $w2Pconfig;
        $this->_query = new w2p_Database_Query;
    }

    /**
     * @deprecated @since 3.2
     */
    public function hashPassword($password, $salt = '')
    {
        $hash = md5($password . $salt);

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
        while ($i <= 10) {
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