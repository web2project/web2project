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
    /**
     * This is a simple MD5 Hash but should be replaced with something better as
     * soon as possible. I did it this way so that replacement is easier than
     * just finding and updating all the instances.
     *
     * @param type $password
     *
     * @return md5hash
     */
    public function hashPassword($password)
    {
        $hash = md5($password);

        return $hash;
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