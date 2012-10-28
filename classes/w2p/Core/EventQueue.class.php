<?php /* $Id$ $URL$ */

/**
 *	@package web2project
 *	@subpackage core
 *	@version $Revision$
 */

/**
 * Event handling queue class.
 *
 * The event queue uses the table event_queue to manage
 * event notifications and other timed events, as well as
 * outgoing emails.
 *
 * Copyright 2005           dotproject team
 * Copyright 2007-2012      web2project team
 *
 * @todo TODO: This can probably be converted to extend the w2p_Core_BaseObject
 *    and just get rid of a bunch of this code. ~ dkc 28 Oct 2012
 */

class w2p_Core_EventQueue {

	public $table = 'event_queue';
	public $update_list = array();
	public $delete_list = array();
	public $event_count = 0;

	public function __construct() {
	}

	/**
	 * Add an event to the queue.
	 *
	 * The callback can either be the name of a global function or the
	 * name of a class
	 * @param mixed $callback function to call when this event is due.
	 * @param mixed $args Arguments to pass to the callback
	 * @param string $module module, or originator of the event
	 * @param string $type type of event (to allow searching)
	 * @param integer $id id of originating event.
	 * @param integer $date Seconds since 1970 to trigger event.
	 * @param integer $repeat_interval seconds to repeat
	 * @param integer $repeat_count number of times to repeat
	 * @return integer queue id
	 */
	public function add($callback, &$args, $module, $sysmodule = false, $id = 0, $type = '', $date = 0, $repeat_interval = 0, $repeat_count = 1) {
		global $AppUI;

		if (!isset($AppUI)) {
			$user_id = 0;
		} else {
			$user_id = $AppUI->user_id;
		}

		if (is_array($callback)) {
			list($class, $method) = $callback;
			if (is_object($class)) {
				$class = get_class($class);
			}
			$caller = $class . '::' . $method;
		} else {
			$caller = $callback;
		}

		$q = new w2p_Database_Query;
		$q->addTable($this->table);
		$q->addInsert('queue_owner', $user_id);
		$q->addInsert('queue_start', $date);
		$q->addInsert('queue_callback', $caller);
		$q->addInsert('queue_data', serialize($args));
		$q->addInsert('queue_repeat_interval', $repeat_interval);
		$q->addInsert('queue_repeat_count', $repeat_count);
		$q->addInsert('queue_module', $module);
		$q->addInsert('queue_type', $type);
		$q->addInsert('queue_origin_id', $id);
		if ($sysmodule) {
			$q->addInsert('queue_module_type', 'system');
		} else {
			$q->addInsert('queue_module_type', 'module');
		}
		if ($q->exec()) {
			$return = db_insert_id();
		} else {
			$return = false;
		}
		$q->clear();
		return $return;
	}

	/**
	 * Remove the event from the queue. 
	 * 
	 */
	public function remove($id) {
		$q = new w2p_Database_Query;
		$q->setDelete($this->table);
		$q->addWhere("queue_id = $id");
		$q->exec();
		$q->clear();
	}

	/**
	 * Find a queue record (or records) based upon the
	 *
	 */
	public function find($module, $type, $id = 0) {
		$q = new w2p_Database_Query;
		$q->addTable($this->table);
		$q->addWhere("queue_module = '$module'");
		$q->addWhere("queue_type = '$type'");
		if ($id) {
			$q->addWhere("queue_origin_id = $id");
		}
		return $q->loadHashList('queue_id');
	}

	/**
	 * Execute a queue entry.  This involves resolving the
	 * method to execute and passing the arguments to it.
	 */
	public function execute(&$fields) {
		global $AppUI;

		$args = unserialize($fields['queue_data']);
		if (strpos($fields['queue_callback'], '::') !== false) {
			list($class, $method) = explode('::', $fields['queue_callback']);

            try {
                $object = new $class;
                return $object->$method($fields['queue_module'], $fields['queue_type'], $fields['queue_origin_id'], $fields['queue_owner'], $args);
            } catch (Exception $exc) {
                dprint(__file__, __line__, 2, 'Cannot process event: ' . $class . '::' . $method . ' does not exist');
                return false;
            }
		} else {
			$method = $fields['queue_callback'];

            try {
                return $method($fields['queue_module'], $fields['queue_type'], $fields['queue_origin_id'], $fields['queue_owner'], $args);
            } catch (Exception $exc) {
                dprint(__file__, __line__, 2, 'Cannot process event: ' . $method . ' does not exist');
                return false;
            }
		}
	}

	/**
	 * Scans the queue for entries that are older than current date.
	 * If it finds one it tries to execute the attached function.
	 * If successful, the entry is removed from the queue, or if
	 * it is a repeatable event the repeat time is added to the
	 * start time and the repeat count (if set) is decremented.
	 */
	public function scan() {
		$q = new w2p_Database_Query;
		$q->addTable($this->table);
		$now = time();
		$q->addWhere('queue_start < ' . $now);
		$rid = $q->exec();

		$this->event_count = 0;
		for ($rid; !$rid->EOF; $rid->moveNext()) {
			if ($this->execute($rid->fields)) {
				$this->update_event($rid->fields);
				$this->event_count++;
			}
		}
		$q->clear();

		$this->commit_updates();
	}

	public function update_event(&$fields) {
		if ($fields['queue_repeat_interval'] && $fields['queue_repeat_count']) {
			$fields['queue_start'] += $fields['queue_repeat_interval'];
			$fields['queue_repeat_count']--;
			$this->update_list[] = $fields;
		} else {
			$this->delete_list[] = $fields['queue_id'];
		}
	}

	public function commit_updates() {
		$q = new w2p_Database_Query;
		if (count($this->delete_list)) {
			$q->setDelete($this->table);
			$q->addWhere('queue_id IN (' . implode(',', $this->delete_list) . ')');
			$q->exec();
			$q->clear();
		}
		$this->delete_list = array();

		foreach ($this->update_list as $fields) {
			$q->addTable($this->table);
			$q->addUpdate('queue_repeat_count', $fields['queue_repeat_count']);
			$q->addUpdate('queue_start', $fields['queue_start']);
			$q->addWhere('queue_id = ' . $fields['queue_id']);
			$q->exec();
			$q->clear();
		}
		$this->update_list = array();
	}
}