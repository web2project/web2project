<?php
namespace Web2project\Interfaces;

/**
 * Class \Web2project\Interfaces\Field
 *
 * This is the standard interface for all of the field view and edit templates.
 *
 * @package     Web2project\Interfaces
 */
interface Field
{
    public function view($value);
    public function edit($name, $value, $extraTags = '');
}
