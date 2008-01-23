<?php
function dplink_init() {
	pnModSetVar('w2plink', 'url', '/web2project');
	pnModSetVar('w2plink', 'use_window', 0);
	pnModSetVar('w2plink', 'use_postwrap', 0);
	return true;
}

function dplink_upgrade($oldversion) {
	switch ($oldversion) {
		case '1.0':
			break;
		case '1.01':
			break;
	}
	return true;
}

function dplink_delete() {
	pnModDelVar('w2plink', 'url');
	pnModDelVar('w2plink', 'use_window');
	pnModDelVar('w2plink', 'use_postwrap');
	return true;
}
?>