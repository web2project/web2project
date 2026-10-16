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
$w2pDialog = (1 == (int) w2PgetParam($_GET, 'dialog', 0));
?>
            <?php if (!$w2pDialog) { ?>
                <footer class="w2p-footer">
                    <a href="http://www.web2project.net/" target="_blank" rel="noopener">web2Project</a> <?php echo $AppUI->getVersion(); ?>
                </footer>
            <?php } ?>
        </main>
        <script type="text/javascript" src="./style/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script type="text/javascript" src="./style/bootstrap/js/theme.js"></script>
