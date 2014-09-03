<?php
namespace Web2project\Fields;

class Select implements \Web2project\Interfaces\Field
{
    protected $options;

    public function view($value)
    {
        return $value;
    }

    public function edit($name, $value, $extraTags = '')
    {
        return arraySelect($this->options, $name, 'size="1" '. $extraTags, $value);
    }

    public function setOptions($options)
    {
        $this->options = $options;
    }
}
