<?php
/**
 * This is the Dispatcher used for cross-module communications and heavily
 * based on http://dustint.com/post/38/building-a-php-publish-subscribe-system
 * from Dustin Thomson. Used here with permission received on 21 Aug 2011.
 *
 * @package     web2project\system
 * @author      Dustin Thomson <dustin@dustint.com>
 * @author      Keith Casey (maintainer) <caseydk@sourceforge.net>
 */

class w2p_System_Event {
    /**
     * The name of the resource publishing this event
     * @var string
     */
    protected $resourceName;
 
    /**
     * The name of this event
     * @var string
     */
    protected $eventName;
 
    /**
     * Any data associated with this event
     * @var mixed
     */
    protected $data;
 
    /**
     * @param string $resourceName    name of the publisher
     * @param string $eventName        name of the event
     * @param mixed $data            [OPTIONAL] Additional event data
     */
    public function __construct($resourceName, $eventName, $data=null)
    {
        $this->resourceName = $resourceName;
        $this->eventName = $eventName;
        $this->data = $data;
    }

    public function getResourceName() {
        return $this->resourceName;
    }

    public function getEventName() {
        return $this->eventName;
    }
}