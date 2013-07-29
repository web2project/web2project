<?php

$listTable = new w2p_Output_ListTable($AppUI);
echo $listTable->startTable();
echo $listTable->buildHeader($fields, true, 'companies');
echo $listTable->buildRows($companyList, $customLookups);
echo $listTable->endTable();