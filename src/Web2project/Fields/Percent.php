<?php
namespace Web2project\Fields;

class Percent
{
    public function view($value)
    {
        return round($value).'%';
    }
}