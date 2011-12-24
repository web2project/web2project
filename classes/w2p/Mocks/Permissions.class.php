<?php /* $Id$ $URL$ */
/*
* Permissions system extends the phpgacl class.  Very few changes have
* been made, however the main one is to provide the database details from
* the main w2P environment.
*/

class w2p_Mocks_Permissions extends w2p_Extensions_Permissions {

	public function __construct($opts = null) {

        parent::__construct($opts);
    }

    public function w2Pacl_nuclear($userid, $module, $item, $mod_class = array()) {
        return array('access' => 1, 'acl_id' => 'checked');
    }
}