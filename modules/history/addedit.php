<?php

trigger_error("?m=history&a=addedit has been deprecated. Please remove all references to it.", E_USER_NOTICE );

$AppUI->setMsg('You cannot add or edit entries in the History module.', UI_MSG_ERROR);
$AppUI->redirect('m=history');