<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('This file should not be called directly.');
}

class w2Pacl extends w2p_Extensions_Permissions {

	public function __construct($opts = null) {

		parent::__construct($opts);
		trigger_error("w2Pacl has been deprecated in v3.0 and will be removed by v4.0. Please use w2p_Extensions_Permissions instead.", E_USER_NOTICE );
	}
}