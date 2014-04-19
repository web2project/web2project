<?php
$htmlHelper = new w2p_Output_HTMLHelper($AppUI);
?>
<table class="std view forums">
    <tr>
        <td height="20" colspan="3" style="border: outset #D1D1CD 1px;background-color:#<?php echo $project->project_color_identifier; ?>">
            <font size="2" color="<?php echo bestColor($project->project_color_identifier); ?>"><strong><?php echo $forum->forum_name; ?></strong></font>
        </td>
    </tr>
    <tr>
        <td width="50%" valign="top" class="view-column">
            <strong><?php echo $AppUI->_('Details'); ?></strong>
            <table cellspacing="1" cellpadding="2" border="0" width="100%" class="well">
                <tr>
                    <td align="left" nowrap="nowrap"><?php echo $AppUI->_('Related Project'); ?>:</td>
                    <?php echo $htmlHelper->createCell('forum_project', $forum->forum_project); ?>
                </tr>
                <tr>
                    <td align="left"><?php echo $AppUI->_('Owner'); ?>:</td>
                    <?php echo $htmlHelper->createCell('forum_owner', $forum->forum_owner); ?>
                </tr>
                <tr>
                    <td align="left"><?php echo $AppUI->_('Created On'); ?>:</td>
                    <?php echo $htmlHelper->createCell('forum_create_date', $forum->forum_create_date); ?>
                </tr>
            </table>
        </td>
        <td width="50%" valign="top" class="view-column">
            <strong><?php echo $AppUI->_('Description'); ?></strong>
            <table cellspacing="1" cellpadding="2" border="0" width="100%" class="well">
                <tr>
                    <?php echo $htmlHelper->createCell('forum_description', $forum->forum_description); ?>
                </tr>
            </table>
        </td>
    </tr>
</table>