<?php /* $Id$ $URL$ */

class CAppUI extends w2p_Core_CAppUI {
    public function __construct()
    {
        parent::__construct();
        trigger_error("CAppUI has been deprecated in v3.0 and will be removed by v4.0. Please use w2p_Core_CAppUI instead.", E_USER_NOTICE );
    }
}

class CTabBox_core extends w2p_Theme_TabBox {

    public function __construct($title, $icon = '', $module = '', $helpref = '') {
		parent::__construct($title, $icon, $module, $helpref);
        trigger_error("CTabBox_core has been deprecated in v3.0 and will be removed by v4.0. Please use w2p_Theme_TabBox instead.", E_USER_NOTICE );
	}
}

class CInfoTabBox extends w2p_Theme_InfoTabBox {
	public function show($extra = '', $js_tabs = false, $alignment = 'left') {
		parent::show($extra, $js_tabs, $alignment);
        trigger_error("CInfoTabBox has been deprecated in v3.0 and will be removed by v4.0. Please use w2p_Theme_InfoTabBox instead.", E_USER_NOTICE );
    }

}

class CTitleBlock_core extends w2p_Theme_TitleBlock {

	public function __construct($title, $icon = '', $module = '', $helpref = '') {
		parent::__construct($title, $icon, $module, $helpref);
        trigger_error("CTitleBlock_core has been deprecated in v3.0 and will be removed by v4.0. Please use w2p_Theme_TitleBlock instead.", E_USER_NOTICE );
	}
}
