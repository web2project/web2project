<?php
/** @var w2p_Core_CAppUI $AppUI */
if (!defined('W2P_BASE_DIR')) {
    die('You should not access this file directly.');
}
global $a, $AppUI;

$tab = (int) w2PgetParam($_GET, 'tab', 0);

echo $AppUI->getTheme()->styleRenderBoxBottom($tab);
echo $AppUI->getTheme()->loadFooterJS();
echo $AppUI->getTheme()->messageHandler();
?>
        </main>
        <script type="text/javascript" src="./style/bootstrap/js/bootstrap.bundle.min.js"></script>
