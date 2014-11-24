<?php
namespace Web2project\Mocks;
/**
 * @package     web2project\mocks
 * @author      D. Keith Casey, Jr. <caseydk@users.sourceforge.net>
 */

class Email extends \w2p_Utilities_Mail
{
    public function Send()
    {
        return true;
    }
}