<?php
/**
 * This is the base list template used by nearly every module within the
 *   system. It provides a simple table layout with headers, support for our
 *   System Lookup Values, and it handles all the row generation.
 *
 * Like any template, this can be overridden by creating a list.php file
 *   within the root of your custom theme.
 *
 * The modules which use this template also include their own list templates
 *   which are included.. but are empty except for an include to this file.
 *   This is on purpose in case you just want to customize some of the list
 *   screens in certain modules instead of all of them at once.
 */

global $m;

$page = (int) w2PgetParam($_GET, 'page', 1);
$paginator = new w2p_Utilities_Paginator($items);
$items = $paginator->getItemsOnPage($page);

echo $paginator->buildNavigation($AppUI, $m, $tab);
$listTable = new w2p_Output_ListTable($AppUI);
echo $listTable->startTable();
echo $listTable->buildHeader($fields, $sortable, $m);
echo $listTable->buildRows($items, $customLookups);
echo $listTable->endTable();
echo $paginator->buildNavigation($AppUI, $m, $tab);