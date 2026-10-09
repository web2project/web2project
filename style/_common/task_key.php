<?php /** @var w2p_Core_CAppUI $AppUI */ ?>
<div class="d-flex flex-wrap align-items-center gap-3 my-2 small w2p-task-key">
    <span><?php echo $AppUI->_('Key'); ?>:</span>
    <span class="text-nowrap"><span class="d-inline-block border future w2p-swatch">&nbsp;</span> =<?php echo $AppUI->_('Future Task'); ?></span>
    <span class="text-nowrap"><span class="d-inline-block border active w2p-swatch">&nbsp;</span> =<?php echo $AppUI->_('Started and on time'); ?></span>
    <span class="text-nowrap"><span class="d-inline-block border notstarted w2p-swatch">&nbsp;</span> =<?php echo $AppUI->_('Should have started'); ?></span>
    <span class="text-nowrap"><span class="d-inline-block border late w2p-swatch">&nbsp;</span> =<?php echo $AppUI->_('Overdue'); ?></span>
    <span class="text-nowrap"><span class="d-inline-block border done w2p-swatch">&nbsp;</span> =<?php echo $AppUI->_('Done'); ?></span>
</div>
