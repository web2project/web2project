<?php /* $Id: BaseObject.class.php 1261 2010-07-30 05:41:59Z caseydk $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/trunk/classes/w2p/Core/BaseObject.class.php $ */
/*
* To change this template, choose Tools | Templates
* and open the template in the editor.
*/

class CW2pObject extends w2p_Core_BaseObject
{
	public function __construct($table, $key, $module = '')
	{
		parent::__construct($table, $key, $module);
		//trigger_error("CW2pObject has been deprecated in v2.0 and will be removed in v3.0. Please use w2p_Core_Object instead.", E_USER_NOTICE );
	}
}
