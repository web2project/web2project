<?php /* $Id$ $URL$ */
include_once $AppUI->getLibraryClass('xajax/xajax_core/xajax.inc');
$xajax = new xajax();
//Comment next line to turn debuggin off
//$xajax->setFlags(array('debug'=>true));

class w2PajaxResponse extends xajaxResponse {

	public function addCreateOptions($sSelectId, $options) {
		if (sizeof($options) > 0) {
			foreach ($options as $key => $option) {
				$this->script("addOption('" . $sSelectId . "','" . $key . "','" . $option . "');");
			}
		}
	}
}