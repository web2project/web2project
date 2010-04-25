<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class CW2pObject extends w2p_Core_BaseObject {
	public function __construct($table, $key) {
		parent::__construct($table, $key);
        //trigger_error("CW2pObject has been deprecated in v2.0 and will be removed in v3.0. Please use w2p_Core_Object instead.", E_USER_NOTICE );
	}
}