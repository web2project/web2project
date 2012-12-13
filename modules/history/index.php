<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$filter_param = w2PgetParam($_REQUEST, 'filter', '');

$options = array();
$options[-1] = $AppUI->_('Show all');
$options = $options + $AppUI->getActiveModules();
$options['login'] = $AppUI->_('Login/Logouts');

/*
 * This validates that anything provided via the filter_param is definitely an
 *   active module and not some other crazy garbage.
 */
if (!isset($options[$filter_param])) {
    $filter_param = 'projects';
}

$options_combo = arraySelect($options, 'filter', 'class="text" onchange="javascript:document.filter.submit()"', $filter_param, false);

$titleBlock = new w2p_Theme_TitleBlock('History', 'stock_book_blue_48.png', 'history', 'history.' . $a);
$titleBlock->addCell('<form name="filter" action="?m=history" method="post" accept-charset="utf-8">
						<table>
							<tr>
								<td valign="top">
									<strong>' . $AppUI->_('Changes to') . '</strong> ' . $options_combo . '
								</td>
							</tr>
						</table>
                      </form>');
$titleBlock->show();

$tabBox = new CTabBox('?m=history', W2P_BASE_DIR . '/modules/history/');
$tabBox->add('index_table', $AppUI->_('History'));
$tabBox->show();