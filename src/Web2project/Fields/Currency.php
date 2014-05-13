<?php
namespace Web2project\Fields;

class Currency extends Text
{
    protected $currency_symbol;
    protected $format;

    public function setOptions($currency_symbol, $format)
    {
        $this->currency_symbol = $currency_symbol;
        $this->format = $format;
    }

    public function view($value)
    {
        return $this->currency_symbol . formatCurrency($value, $this->format);
    }
}