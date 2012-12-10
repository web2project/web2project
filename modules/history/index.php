<?php /* $Id$ $URL$ */
if (!defined('W2P_BASE_DIR')) {
	die('You should not access this file directly.');
}

$filter_param = w2PgetParam($_REQUEST, 'filter', '');

$options = array(' ' => $AppUI->_('Select Filter'), '0' => $AppUI->_('Show all'),
        'companies' => $AppUI->_('Companies'), 'projects' => $AppUI->_('Projects'),
        'tasks' => $AppUI->_('Tasks'), 'files' => $AppUI->_('Files'),
        'forums' => $AppUI->_('Forums'), 'login' => $AppUI->_('Login/Logouts'));

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