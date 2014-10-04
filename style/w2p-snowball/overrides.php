<?php

class style_w2psnowball extends w2p_Theme_Base
{
    public function __construct($AppUI, $m = '') {
        $this->_uistyle = 'w2p-snowball';

        parent::__construct($AppUI, $m);
    }
}

/**
 * Class CTabBox
 *
 * This doesn't need any methods, it just needs to be a concrete class we can instantiate.
 */
class CTabBox extends w2p_Theme_TabBox { }