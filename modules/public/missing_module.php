<?php
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
$titleBlock = new w2p_Theme_TitleBlock($AppUI->_('Warning'), 'log-error.gif');
$titleBlock->show();

include $theme->resolveTemplate('public/missing_module');