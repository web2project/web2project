<?php
                    global $a, $AppUI;

                    $tab = (int) w2PgetParam($_GET, 'tab', 0);

                    $theme = new style_web2project($AppUI);
                    echo $theme->styleRenderBoxBottom($tab);
                    
                    $AppUI->loadFooterJS();
                    echo $AppUI->getMsg();
                    ?>
                </td>
            </tr>
        </table>