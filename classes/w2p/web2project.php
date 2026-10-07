<?php

function web2project_autoload($className) {
    $library_name = 'w2p_';

    if (substr($className, 0, strlen($library_name)) != $library_name) {
        return false;
    }
    $file = str_replace('_', '/', $className);
    $file = str_replace('w2p/', '', $file);
    return include dirname(__FILE__) . "/$file.class.php";
}