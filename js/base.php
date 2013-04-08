<?php
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly');
}
global $uistyle, $AppUI;
?>
<script language="javascript" type="text/javascript">
var w2p = {
    lang: '<?= $AppUI->user_lang[3] ?>',
    style: '<?= $uistyle ?>'
};
function gt_hide_tabs() {
    $('.tabon').removeClass('tabon').addClass('taboff');
    $('div.tab').css('display', 'none');
    $('img[id^="lefttab_"]').attr('src', 'style/<?= $uistyle ?>/images/bar_top_left.gif');
    $('img[id^="righttab_"]').attr('src', 'style/<?= $uistyle ?>/images/bar_top_right.gif');
}

function gt_show_tab(i) {
    $('#tab_' + i).css('display', 'block');
    $('#toptab_' + i).removeClass('taboff').addClass('tabon');
    $('#lefttab_' + i).attr('src', 'style/<?= $uistyle ?>/images/bar_top_Selectedleft.gif');
    $('#righttab_' + i).attr('src', 'style/<?= $uistyle ?>/images/bar_top_Selectedright.gif');
}
</script> 