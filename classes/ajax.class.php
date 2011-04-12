<?php /* $Id$ $URL$ */

class w2PajaxResponse extends w2p_Extensions_AjaxResponse
{
    public function addCreateOptions($sSelectId, $options)
    {
        parent::addCreateOptions($sSelectId, $options);
        trigger_error("w2PajaxResponse has been deprecated in v2.3 and will be removed by v4.0. Please use w2p_Extensions_AjaxResponse instead.", E_USER_NOTICE );
    }
}