<?php

class CForumMessage extends CForum_Message {
	public function __construct() {
        parent::__construct();
        trigger_error("CForumMessage has been deprecated in v3.0 and will be removed by v4.0. Please use CForum_Message instead.", E_USER_NOTICE );
	}
}