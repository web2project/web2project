<?php

/**
 * Loads w2p_* classes: w2p_Foo_Bar is classes/w2p/Foo/Bar.class.php.
 *
 * Names with no matching file are left to the next autoloader (or to PHP's
 * "Class not found"), without include warnings that would reveal the
 * install path.
 *
 * @param string $className
 */
function web2project_autoload($className) {
    $library_name = 'w2p_';

    if (substr($className, 0, strlen($library_name)) != $library_name) {
        return;
    }
    $file = str_replace('_', '/', substr($className, strlen($library_name)));
    $path = __DIR__ . "/$file.class.php";
    if (!is_file($path)) {
        return;
    }

    require_once $path;
}
