<?php
namespace Web2project\Fields;

/**
 * @package     Web2project\Fields
 */
class Percent implements \Web2project\Interfaces\Field
{
    public function view($value)
    {
        return round($value).'%';
    }

    public function edit($name, $value, $extraTags = '')
    {
        return '';
    }
}
