<?php

/**
 * Permissions system extends the phpgacl class.  Very few changes have
 * been made, however the main one is to provide the database details from
 * the main w2P environment.
 *
 * @package     web2project\mocks
 * @author      D. Keith Casey, Jr. <caseydk@users.sourceforge.net>
 */

class w2p_Mocks_Permissions extends w2p_Extensions_Permissions
{
    public function w2Pacl_nuclear($userid, $module, $item, $mod_class = array())
    {
        return array('access' => 1, 'acl_id' => 'checked');
    }

    public function w2Pacl_check($application = 'application', $op, $user = 'user', $userid, $app = 'app', $module)
    {
        return true;
    }

    public function w2Pacl_query($application = 'application', $op, $user = 'user', $userid, $module, $item)
    {
        return array('access' => 1, 'acl_id' => 'checked');
    }
}