<?php
/**
 * @package     web2project\extensions
 */

class w2p_Extensions_AjaxResponse extends xajaxResponse {

    public function addCreateOptions($sSelectId, $options) {
        if (sizeof($options) > 0) {
            foreach ($options as $key => $option) {
                $this->script("addOption('" . $sSelectId . "','" . $key . "','" . $option . "');");
            }
        }
    }

}