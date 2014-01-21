<?php
                    global $a, $AppUI;

                    $tab = (int) w2PgetParam($_GET, 'tab', 0);

                    echo $AppUI->getTheme()->styleRenderBoxBottom($tab);
                    
                    echo $AppUI->loadFooterJS();
                    echo $AppUI->getMsg();
                    ?>
                </td>
            </tr>
        </table>