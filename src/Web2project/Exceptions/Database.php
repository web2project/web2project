<?php
namespace Web2project\Exceptions;

/**
 * Class w2p_Exception_Database
 *
 * This is the overall exception handler for the Database classes. Initially it doesn't do anything but by
 *   separating it out, we can catch it separately and do things with it.
 *
 * @package     Web2project\Exceptions
 */

class Database extends \Exception { }
