<?php
namespace Web2project\Fields;

/**
 * @package     Web2project\Fields
 */
class Module implements \Web2project\Interfaces\Field
{
    protected $object;
    protected $prefix;

    public function setObject($object, $prefix)
    {
        $this->object = $object;
        $this->prefix = $prefix;
    }

    public function view($value)
    {
        if (!$value) {
            return '-';
        }
        $key = ($this->prefix == 'user' ? 'contact' : $this->prefix ) . '_name';

        $link = '?m=' . w2p_pluralize($this->prefix) . '&a=view&' . $this->prefix . '_id='.$value;

        return '<a href="'.$link.'">'.$this->object->$key.'</a>';
    }

    public function edit($name, $value, $extraTags = '')
    {
        return '';
    }
}
