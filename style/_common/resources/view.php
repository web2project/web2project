<?php

$view = new w2p_Output_HTML_ViewHelper($AppUI);

?>
<div class="std addedit companies">
    <div class="column left">
        <p><?php $view->showLabel('Identifier'); ?>
            <?php $view->showField('resource_key', $obj->resource_key); ?>
        </p>
        <p><?php $view->showLabel('Name'); ?>
            <?php $view->showField('resource_name', $obj->resource_name); ?>
        </p>
        <p><?php $view->showLabel('Type'); ?>
            <?php $view->showField('resource_type', $AppUI->_($types[$obj->resource_type])); ?>
        </p>
        <p><?php $view->showLabel('Percent Allocation'); ?>
            <?php $view->showField('percent', $obj->resource_max_allocation); ?>
        </p>
    </div>
    <div class="column right">
        <p><?php $view->showLabel('Description'); ?>
            <?php $view->showField('resource_description', $obj->resource_description); ?>
        </p>
    </div>
</div>