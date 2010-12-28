<?php /* $Id$ $URL$ */
/*
* To change this template, choose Tools | Templates
* and open the template in the editor.
*/

class CW2pObject extends w2p_Core_BaseObject
{
	public function __construct($table, $key, $module = '')
	{
		parent::__construct($table, $key, $module);
		trigger_error("CW2pObject has been deprecated in v2.0 and will be removed by v4.0. Please use w2p_Core_Object instead.", E_USER_NOTICE );
	}
}
