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

/** 
 * This is the base class of the core web2project classes. As we build things
 *  out in the "proper" 5.3/5.4 sense of the word, this class will grow.
 */
class w2p_web2project
{
    
}