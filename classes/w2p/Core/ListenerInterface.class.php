<?php /* $Id$ $URL$ */

/**
 *	@package web2project
 *	@subpackage core
 *	@version $Revision$
 */

/**
 *	w2p_Core_ListenerInterface Class.
 *
 *	This is the Dispatcher used for cross-module communications and heavily 
 *    based on http://dustint.com/post/38/building-a-php-publish-subscribe-system
 *    from Dustin Thomson. Used here with permission received on 21 Aug 2011.
 *  
 *	@author Dustin Thomson <dustin@dustint.com>
 *	@author Keith Casey (maintainer) <caseydk@sourceforge.net>
 *
 */
interface w2p_Core_ListenerInterface
{
	/**
	 * Accepts an event and does something with it
	 *
	 * @param Event $event	The event to process
	 */
	public function publish(w2p_Core_Event $event);
}