<?php /* $Id: ajax.class.php 38 2008-02-11 11:38:51Z pedroix $ $URL: https://web2project.svn.sourceforge.net/svnroot/web2project/trunk/classes/ajax.class.php $ */
include_once $AppUI->getLibraryClass('xajax/xajax_core/xajax.inc');

class w2p_Extensions_AjaxResponse extends xajaxResponse {

	public function addCreateOptions($sSelectId, $options) {
		if (sizeof($options) > 0) {
			foreach ($options as $key => $option) {
				$this->script("addOption('" . $sSelectId . "','" . $key . "','" . $option . "');");
			}
		}
	}

}