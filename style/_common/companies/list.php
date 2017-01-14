<?php

global $tab, $company_id;

$sortable   = true;
$m          = 'companies';
$a          = 'view';
$id         = $company_id;

$page = (int) w2PgetParam($_GET, 'page', 1);
$paginator = new w2p_Utilities_Paginator($items);
$items = $paginator->getItemsOnPage($page);

echo $paginator->buildNavigation($AppUI, $m, $tab, ['a' => 'view', 'company_id' => $company_id]);
$listTable = new w2p_Output_ListTable($AppUI);
echo $listTable->startTable();
echo $listTable->buildHeader($fields, $sortable, $m);
echo $listTable->buildRows($items, $customLookups);
echo $listTable->endTable();
echo $paginator->buildNavigation($AppUI, $m, $tab, ['a' => 'view', 'company_id' => $company_id]);