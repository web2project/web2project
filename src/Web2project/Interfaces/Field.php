<?php
namespace Web2project\Interfaces;

/**
 * Class \Web2project\Interfaces\Field
 *
 * This is the standard interface for all of the filesystem operations.
 *
 * @package     web2project\filesystem
 */
interface Field
{
    public function view($value);
    public function edit($name, $value, $extraTags = '');
}