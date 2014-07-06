<?php

require_once '../base.php';
require_once W2P_BASE_DIR . '/includes/config.php';
require_once W2P_BASE_DIR . '/includes/main_functions.php';
require_once W2P_BASE_DIR . '/includes/db_adodb.php';

$session = new w2p_System_Session();
$session->start();
$AppUI = &$_SESSION['AppUI'];

$app = new \Slim\Slim(
    array('debug' => true)
);

$app->get('/' , function() use ($app) {
    $app->response->setStatus(301);
    $app->redirect('..');
});

$app->get('/:module' , function($module) use ($app, $AppUI) {
    if ($AppUI->isActiveModule($module)) {
        $gateway = new \Web2project\Database\Gateway($AppUI, $module);
        $results = $gateway->search('');

        echo json_encode($results);
    } else {
        $app->response->setStatus(404);
    }
});

$app->get('/:module/search', function ($module) use ($app, $AppUI) {
    if ($AppUI->isActiveModule($module)) {
        $search = $app->request->get('query');

        $gateway = new \Web2project\Database\Gateway($AppUI, $module);
        $results = $gateway->search($search);

        echo json_encode($results);
    } else {
        $app->response->setStatus(404);
    }
});

$app->run();