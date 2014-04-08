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
     * This is a simple MD5 Hash but should be replaced with something better as
     * soon as possible. I did it this way so that replacement is easier than
     * just finding and updating all the instances.
     *
     * @param string $password
     * @param string $salt unused but available @since 3.0
     *
     * @return md5hash
     */
    public function hashPassword($password, $salt = '')
    {
        $hash = md5($password . $salt);

        return $hash;
    }

    public function verify($username, $password, $hash)
    {
        $length = strlen($hash);

        switch ($length) {
            case '32':
                $q = $this->_query;
                $q->addTable('users');
                $q->addUpdate('user_password', password_hash($password, PASSWORD_BCRYPT));
                $q->addWhere("user_username = '$username'");
                $q->exec();
                return ($hash == $this->hashPassword($password));
            default:
                return password_verify($password, $hash);
        }
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