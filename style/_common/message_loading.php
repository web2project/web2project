<?php

echo '
    <!--AJAX loading messagebox -->
    <div id="loadingMessage" style="alpha(opacity=100);opacity:1;position: fixed; left: 0px; top: 1px;display: none;">
    <table width="80" cellpadding="1" cellspacing="1" border="0">
    <tr>
        <td>';
echo w2PshowImage('ajax-loader.gif', '', '', 'web2Project', 'Server Connection Running, Please wait...');
echo '
        </td>
        <td>
            <b>' . $AppUI->_('Loading') . '</b>
        </td>
    </tr>
    </table>
    </div>
    <!--End AJAX loading messagebox -->';