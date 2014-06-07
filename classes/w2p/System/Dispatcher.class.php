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

class w2p_System_Dispatcher
{
    /**
     * Associative array of listeners.
     * Indicies are: [resourceName][event][listener hash]
     *
     * @var array
     */
    protected $_listeners = array();
    
    
   /**
     * Subscribes the listener to the resource's events.
     * If $resourceName is *, then the listener will be dispatched when the specified event is fired
     * If $event is *, then the listener will be dispatched for any dispatched event of the specified resource
     * If $resourceName and $event is *, the listener will be dispatched for any dispatched event for any resource
     *
     * @param Listener $listener
     * @param String $resourceName
     * @param Mixed $event
     * @return Dispatcher
     */
    public function subscribe(w2p_Interfaces_Listener $listener, $resourceName='*', $event='*'){
        $this->_listeners[$resourceName][$event][spl_object_hash($listener)] = $listener;
        return $this;
    }
 
    /**
     * Unsubscribes the listener from the resource's events
     *
     * @param Listener $listener
     * @param String $resourceName
     * @param Mixed $event
     * @return Dispatcher
     */
    public function unsubscribe(w2p_Interfaces_Listener $listener, $resourceName='*', $event='*'){
        unset($this->_listeners[$resourceName][$event][spl_object_hash($listener)]);
        return $this;
    }
 
    /**
     * Publishes an event to all the listeners listening to the specified event for the specified resource
     *
     * @param w2p_System_Event $event
     * @return Dispatcher
     */
    public function publish(w2p_System_Event $event ){
        $resourceName = $event->getResourceName();
        $eventName = $event->getEventName();

        //Loop through all the wildcard handlers
        if(isset($this->_listeners['*']['*'])){
            foreach($this->_listeners['*']['*'] as $listener){
                $listener->publish($event);
            }
        }
 
        //Dispatch wildcard Resources
        //These are events that are published no matter what the resource
        if(isset($this->_listeners['*'])){
            foreach($this->_listeners['*'] as $event => $listeners){
                if($event == $eventName){
                    foreach($listeners as $listener){
                        $listener->publish($event);
                    }
                }
            }
        }
 
        //Dispatch wildcard Events
        //these are listeners that are dispatched for a certain resource, despite the event
        if(isset($this->_listeners[$resourceName]['*'])){
            foreach($this->_listeners[$resourceName]['*'] as $listener){
                   $listener->publish($event);
            }
        }
 
        //Dispatch to a certain resource event
        if(isset($this->_listeners[$resourceName][$eventName])){
            foreach($this->_listeners[$resourceName][$eventName] as $listener){
                $listener->publish($event);
            }
        }

        return $this;
    }
}