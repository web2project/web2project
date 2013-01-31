<?php

/**
 * @package     web2project\extensions
 */

$AppUI = is_object($AppUI) ? $AppUI : new w2p_Core_CAppUI();

class w2p_Extensions_AjaxResponse extends xajaxResponse {

	public function addCreateOptions($sSelectId, $options) {
		if (sizeof($options) > 0) {
			foreach ($options as $key => $option) {
				$this->script("addOption('" . $sSelectId . "','" . $key . "','" . $option . "');");
			}
		}
	}

}