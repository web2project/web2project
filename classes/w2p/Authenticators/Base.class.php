<?php /* $Id$ $URL$ */

/**
 *	@package web2project
 *	@subpackage core
 *	@version $Revision$
 */

/**
 *	w2p_Authenticator_Base Abstract Class.
 *
 *	Parent class for all authenticators
 *	@author Keith Casey <caseydk@users.sourceforge.net>
 *	@abstract
 */
abstract class w2p_Authenticators_Base
{
    /**
     * This is a simple MD5 Hash but should be replaced with something better
     *    as soon as possible. I did it this way so that replacement is easier
     *    than just finding and updating all the instances.
     * 
     * @param type $password
     * @return type 
     */
    public function hashPassword($password)
    {
        $hash = md5($password);

        return $hash;
    }

	public function userId() {
		return (int) $this->user_id;
	}
}