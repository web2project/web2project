<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly');
}
global $uistyle;
?>
<script language="javascript">
function gt_hide_tabs() {
	var tabs = document.getElementsByTagName('td');
	var i;
	for (i = 0, i_cmp = tabs.length; i < i_cmp; i++) {
		if (tabs[i].className == 'tabon') {
			tabs[i].className = 'taboff';
		}
	}
	var divs = document.getElementsByTagName('div');
	for (i =0, i_cmp = divs.length; i < i_cmp; i++) {
		if (divs[i].className == 'tab') {
			divs[i].style.display = 'none';
		}
	}
	var imgs = document.getElementsByTagName('img');
	for (i = 0, i_cmp = imgs.length; i < i_cmp; i++) {
		if (imgs[i].id) {
			if (imgs[i].id.substr(0,8) == 'lefttab_') {
				imgs[i].src = './style/<?php echo $uistyle; ?>/images/bar_top_left.gif';
			} else if (imgs[i].id.substr(0,9) == 'righttab_') {
				imgs[i].src = './style/<?php echo $uistyle; ?>/images/bar_top_right.gif';
			}
		}
	}
}

function gt_show_tab(i) {
	var tab = document.getElementById('tab_' + i);
	tab.style.display = 'block';
	tab = document.getElementById('toptab_' + i);
	tab.className = 'tabon';
	var img = document.getElementById('lefttab_' + i);
	img.src = './style/<?php echo $uistyle; ?>/images/bar_top_Selectedleft.gif';
	img = document.getElementById('righttab_' + i);
	img.src = './style/<?php echo $uistyle; ?>/images/bar_top_Selectedright.gif';
}
</script> 