<?php /* $Id$ $URL$ */
##
## Calendar classes
##

$event_filter_list = array('my' => 'My Events', 'own' => 'Events I Created', 'all' => 'All Events');

/**
 * Event Class
 *
 * { Description }
 *
 */
class CEvent extends w2p_Core_BaseObject
{

    /**
     * @var int
     */
    public $event_id = null;

    /**
      @var string The title of the event */
    //TODO: because this is title instead of name, it doesn't match our convention. Ugh. - kc 19 Sept 2011
    public $event_name = null;
    public $event_start_date = null;
    public $event_end_date = null;
    public $event_parent = null;
    public $event_description = null;
    public $event_times_recuring = null;
    public $event_recurs = null;
    public $event_remind = null;
    public $event_icon = null;
    public $event_owner = null;
    public $event_project = null;
    public $event_private = null;
    public $event_type = null;
    public $event_notify = null;
    public $event_cwd = null;
    public $event_created = null;
    public $event_updated = null;
    public $event_creator = null;

    public function __construct()
    {
        parent::__construct('events', 'event_id', 'calendar');
    }

    public function loadFull($event_id)
    {
        $q = $this->_getQuery();
        $q->addTable('events', 'e');
        $q->addQuery('e.*, project_name, project_color_identifier, company_name');
        $q->leftJoin('projects', 'p', 'event_project = project_id');
        $q->leftJoin('companies', 'c', 'project_company = company_id');
        $q->addWhere('event_id = ' . (int) $event_id);
        $q->loadObject($this, true, false);
    }

    public function isValid()
    {
        $baseErrorMsg = get_class($this) . '::store-check failed - ';

        if ($this->event_start_date > $this->event_end_date) {
            $this->_error['start_after_end'] = $baseErrorMsg . 'start date is after end date';
        }
//TODO: I really don't like this. isValid() should never modify anything, just validate the object
        if (!$this->event_creator) {
            $this->event_creator = $this->_AppUI->user_id;
        }

        return (count($this->_error)) ? false : true;
    }

    protected function hook_preDelete()
    {
        $q = $this->_getQuery();
        $q->setDelete('user_events');
        $q->addWhere('event_id = ' . (int) $this->event_id);
        $q->exec();
    }

    public function hook_search()
    {
        $search['table'] = 'events';
        $search['table_alias'] = '';
        $search['table_module'] = 'calendar';
        $search['table_key'] = 'event_id'; // primary key in searched table
        $search['table_link'] = 'index.php?m=calendar&a=view&event_id='; // first part of link
        $search['table_title'] = 'Events';
        $search['table_orderby'] = 'event_start_date';
        $search['search_fields'] = array(
            'event_name', 'event_description',
            'event_start_date', 'event_end_date'
        );
        $search['display_fields'] = $search['search_fields'];

        return $search;
    }

    /**
     * Calculating if an recurrent date is in the given period
     * @param Date Start date of the period
     * @param Date End date of the period
     * @param Date Start date of the Date Object
     * @param Date End date of the Date Object
     * @param integer Type of Recurrence
     * @param integer Times of Recurrence
     * @param integer Time of Recurrence
     * @return array Calculated Start and End Dates for the recurrent Event for the given Period
     */
    public function getRecurrentEventforPeriod($start_date, $end_date, $event_start_date, $event_end_date, $event_recurs, $event_times_recuring, $j)
    {

        //this array will be returned
        $transferredEvent = array();

        //create Date Objects for Event Start and Event End
        $eventStart = new w2p_Utilities_Date($event_start_date);
        $eventEnd = new w2p_Utilities_Date($event_end_date);

        //Time of Recurence = 0 (first occurence of event) has to be checked, too.
        if ($j > 0) {
            switch ($event_recurs) {
                case 1:
                    $eventStart->addSpan(new Date_Span(3600 * $j));
                    $eventEnd->addSpan(new Date_Span(3600 * $j));
                    break;
                case 2:
                    $eventStart->addDays($j);
                    $eventEnd->addDays($j);
                    break;
                case 3:
                    $eventStart->addDays(7 * $j);
                    $eventEnd->addDays(7 * $j);
                    break;
                case 4:
                    $eventStart->addDays(14 * $j);
                    $eventEnd->addDays(14 * $j);
                    break;
                case 5:
                    $eventStart->addMonths($j);
                    $eventEnd->addMonths($j);
                    break;
                case 6:
                    $eventStart->addMonths(3 * $j);
                    $eventEnd->addMonths(3 * $j);
                    break;
                case 7:
                    $eventStart->addMonths(6 * $j);
                    $eventEnd->addMonths(6 * $j);
                    break;
                case 8:
                    $eventStart->addMonths(12 * $j);
                    $eventEnd->addMonths(12 * $j);
                    break;
                default:
                    break;
            }
        }

        if ($start_date->compare($start_date, $eventStart) <= 0 && $end_date->compare($end_date, $eventEnd) >= 0) {
            // add temporarily moved Event Start and End dates to returnArray
            $transferredEvent = array($eventStart, $eventEnd);
        }

        // return array with event start and end dates for given period (positive case)
        // or an empty array (negative case)
        return $transferredEvent;
    }

    /**
     * Utility function to return an array of events with a period
     * @param Date Start date of the period
     * @param Date End date of the period
     * @return array A list of events
     *
     * WARNING: This is actually called staticly so $this is not available.
     */
    public function getEventsForPeriod($start_date, $end_date, $filter = 'all', $user_id = null, $project_id = 0, $company_id = 0)
    {
        global $AppUI;

        // convert to default db time stamp
		$db_start = $start_date->format(FMT_DATETIME_MYSQL);
		$db_start = $AppUI->convertToSystemTZ($db_start);
		$db_end = $end_date->format(FMT_DATETIME_MYSQL);
		$db_end = $AppUI->convertToSystemTZ($db_end);

		if (!isset($user_id)) {
			$user_id = $AppUI->user_id;
		}

		$project = new CProject();
		if ($project_id) {
			$p = &$AppUI->acl();

			if ($p->checkModuleItem('projects', 'view', $project_id, $user_id)) {
				$allowedProjects = array('p.project_id = ' . (int)$project_id);
			} else {
				$allowedProjects = array('1=0');
			}
		} else {
			$allowedProjects = $project->getAllowedSQL(($user_id ? $user_id : $AppUI->user_id), 'event_project');
		}

		//do similiar actions for recurring and non-recurring events
		$queries = array('q' => 'q', 'r' => 'r');

		foreach ($queries as $query_set) {
			$$query_set = new w2p_Database_Query();
			$$query_set->addTable('events', 'e');
			$$query_set->addQuery('distinct(e.event_id), e.*');
			$$query_set->addOrder('e.event_start_date, e.event_end_date ASC');

			$$query_set->leftJoin('projects', 'p', 'p.project_id =  e.event_project');
			$$query_set->leftJoin('project_departments', 'project_departments', 'p.project_id = project_departments.project_id OR project_departments.project_id IS NULL');
			$$query_set->leftJoin('departments', 'departments', 'departments.dept_id = project_departments.department_id OR dept_id IS NULL');
			if ($company_id) {
				$$query_set->addWhere('project_company = ' . (int)$company_id);
			} else {
				if (($AppUI->getState('CalIdxCompany'))) {
					$$query_set->addWhere('project_company = ' . $AppUI->getState('CalIdxCompany'));
				}
			}

			if (count($allowedProjects)) {
				$$query_set->addWhere('( ( ' . implode(' AND ', $allowedProjects) . ' ) ' . (($AppUI->getState('CalIdxCompany')) ? '' : ($project_id ? '' : ' OR event_project = 0 ')) . ')');
			}

			switch ($filter) {
				case 'my':
					$$query_set->addJoin('user_events', 'ue', 'ue.event_id = e.event_id AND ue.user_id =' . $user_id);
					$$query_set->addWhere('(ue.user_id = ' . (int)$user_id . ') AND (event_private = 0 OR event_owner=' . (int)$user_id . ')');
					break;
				case 'own':
					$$query_set->addWhere('event_owner =' . (int)$user_id);
					break;
				case 'all':     // this falls through on purpose
				default:
					$$query_set->addWhere('(event_private = 0 OR event_owner=' . (int)$user_id . ')');
			}

			if ($query_set == 'q') { // assemble query for non-recursive events
				$$query_set->addWhere('(event_recurs <= 0)');
				// following line is only good for *non-recursive* events
				$$query_set->addWhere('(event_start_date <= \'' . $db_end . '\' AND event_end_date >= \'' . $db_start . '\' OR event_start_date BETWEEN \'' . $db_start . '\' AND \'' . $db_end . '\')');
				$eventList = $$query_set->loadList();
			} elseif ($query_set == 'r') { // assemble query for recursive events
				$$query_set->addWhere('(event_recurs > 0)');
				$eventListRec = $$query_set->loadList();
			}
		}

		//Calculate the Length of Period (Daily, Weekly, Monthly View)
		setlocale(LC_TIME, 'en');
		$periodLength = Date_Calc::dateDiff($start_date->getDay(), $start_date->getMonth(), $start_date->getYear(), $end_date->getDay(), $end_date->getMonth(), $end_date->getYear());
		setlocale(LC_ALL, $AppUI->user_lang);

		// AJD: Should this be going off the end of the array?  I don't think so.
		// If it should then a comment to that effect would be nice.
		for ($i = 0, $i_cmp = sizeof($eventListRec); $i < $i_cmp; $i++) {
			//note from merlinyoda: j=0 is the original event according to getRecurrentEventforPeriod
			// So, since the event is *recurring* x times, the loop condition should be j <= x, not j < x.
			// This way the original and all recurrances are covered.
			for ($j = 0, $j_cmp = intval($eventListRec[$i]['event_times_recuring']); $j <= $j_cmp; $j++) {
				//Daily View
				//show all
				if ($periodLength <= 1) {
					$recEventDate = CEvent::getRecurrentEventforPeriod($start_date, $end_date, $eventListRec[$i]['event_start_date'], $eventListRec[$i]['event_end_date'], $eventListRec[$i]['event_recurs'], $eventListRec[$i]['event_times_recuring'], $j);
				}
				//Weekly or Monthly View and Hourly Recurrent Events
				//only show hourly recurrent event one time and add string 'hourly'
				elseif ($periodLength > 1 && $eventListRec[$i]['event_recurs'] == 1 && $j == 0) {
					$recEventDate = CEvent::getRecurrentEventforPeriod($start_date, $end_date, $eventListRec[$i]['event_start_date'], $eventListRec[$i]['event_end_date'], $eventListRec[$i]['event_recurs'], $eventListRec[$i]['event_times_recuring'], $j);
					$eventListRec[$i]['event_name'] = $eventListRec[$i]['event_name'] . ' (' . $this->_AppUI->_('Hourly') . ')';
				}
				//Weekly and Monthly View and higher recurrence mode
				//show all events of recurrence > 1
				elseif ($periodLength > 1 && $eventListRec[$i]['event_recurs'] > 1) {
					$recEventDate = CEvent::getRecurrentEventforPeriod($start_date, $end_date, $eventListRec[$i]['event_start_date'], $eventListRec[$i]['event_end_date'], $eventListRec[$i]['event_recurs'], $eventListRec[$i]['event_times_recuring'], $j);
				}

				//add values to the eventsArray if check for recurrent event was positive
				if (sizeof($recEventDate) > 0) {
					$eList[0] = $eventListRec[$i];
					$eList[0]['event_start_date'] = $recEventDate[0]->format(FMT_DATETIME_MYSQL);
					$eList[0]['event_end_date'] = $recEventDate[1]->format(FMT_DATETIME_MYSQL);
					$eventList = array_merge($eventList, $eList);
				}
				// clear array of positive recurrent events for the case that next loop recEventDate is empty in order to avoid double display
				$recEventDate = array();
			}

		}

		$i = 0;
		foreach($eventList as $event) {
			$eventList[$i]['event_start_date'] = $AppUI->formatTZAwareTime($event['event_start_date'], '%Y-%m-%d %H:%M:%S');
			$eventList[$i]['event_end_date'] = $AppUI->formatTZAwareTime($event['event_end_date'], '%Y-%m-%d %H:%M:%S');
			$i++;
		}
//echo '<pre>'; print_r($eventList); echo '</pre>';
		//return a list of non-recurrent and recurrent events
		return $eventList;
	}

	public function getAssigned() {
		$q = $this->_getQuery();
		$q->addTable('users', 'u');
		$q->addTable('user_events', 'ue');
		$q->addTable('contacts', 'con');
		$q->addQuery('u.user_id, contact_display_name');
		$q->addWhere('ue.event_id = ' . (int)$this->event_id);
		$q->addWhere('user_contact = contact_id');
		$q->addWhere('ue.user_id = u.user_id');

		return $q->loadHashList();
	}

	/*
	 *  I'm  not sure of the logic behind this method.  It was implemented in
	 * addedit.php but what it actually does is kind of a mess...
	 *
	 */
	public function getAssigneeList($assignee_list) {
		$q = $this->_getQuery();
		$q->addTable('users', 'u');
		$q->addTable('contacts', 'con');
		$q->addQuery('user_id, contact_display_name');
		$q->addWhere('user_id IN ('.$assignee_list.')');
		$q->addWhere('user_contact = contact_id');

		return $q->loadHashList();
	}

	public function updateAssigned($assigned) {
		// First remove the assigned from the user_events table
		$q = $this->_getQuery();
		$q->setDelete('user_events');
		$q->addWhere('event_id = ' . (int)$this->event_id);
		$q->exec();
		$q->clear();

		if (is_array($assigned) && count($assigned)) {

			foreach ($assigned as $uid) {
				if ($uid) {
					$q->addTable('user_events', 'ue');
					$q->addInsert('event_id', $this->event_id);
					$q->addInsert('user_id', (int) $uid);
					$q->exec();
					$q->clear();
				}
			}

			if ($msg = db_error()) {
				$this->_AppUI->setMsg($msg, UI_MSG_ERROR);
			}
		}
	}

	public function notify($assignees, $update = false, $clash = false) {
		global $locale_char_set;

		$mail_owner = $this->_AppUI->getPref('MAILALL');
		$assignee_list = explode(',', $assignees);
		$owner_is_assigned = in_array($this->event_owner, $assignee_list);
		if ($mail_owner && !$owner_is_assigned && $this->event_owner) {
			array_push($assignee_list, $this->event_owner);
		}
		// Remove any empty elements otherwise implode has a problem
		foreach ($assignee_list as $key => $x) {
			if (!$x) {
				unset($assignee_list[$key]);
			}
		}
		if (!count($assignee_list)) {
			return;
		}

		$q = $this->_getQuery();
		$q->addTable('users', 'u');
		$q->addTable('contacts', 'con');
		$q->addQuery('user_id, con.contact_id, contact_email');
        $q->addQuery('contact_display_name, contact_display_name as contact_name');
        $q->addWhere('u.user_contact = con.contact_id');
        $q->addWhere('user_id in (' . implode(',', $assignee_list) . ')');
        $users = $q->loadHashList('user_id');

        $mail = new w2p_Utilities_Mail();
        $type = $update ? $this->_AppUI->_('Updated') : $this->_AppUI->_('New');
        if ($clash) {
            $mail->Subject($this->_AppUI->_('Requested Event') . ': ' . $this->event_name, $locale_char_set);
        } else {
            $mail->Subject($type . ' ' . $this->_AppUI->_('Event') . ': ' . $this->event_name, $locale_char_set);
        }

        $emailManager = new w2p_Output_EmailManager($this->_AppUI);
        $body = $emailManager->getEventNotify($this, $clash, $users);

        $mail->Body($body, $locale_char_set);
        foreach ($users as $user) {
            if (!$mail_owner && $user['user_id'] == $this->event_owner) {
                continue;
            }
            $mail->To($user['contact_email'], true);
            $mail->Send();
        }
    }

    public function checkClash($userlist = null)
    {
        if (!isset($userlist)) {
            return false;
        }
        $users = explode(',', $userlist);

        // Now, remove the owner from the list, as we will always clash on this.
        $key = array_search($this->_AppUI->user_id, $users);
        if (isset($key) && $key !== false) { // Need both for change in php 4.2.0
            unset($users[$key]);
        }

        if (!count($users)) {
            return false;
        }

        $start_date = new w2p_Utilities_Date($this->event_start_date);
        $end_date = new w2p_Utilities_Date($this->event_end_date);

        // Now build a query to find matching events.
        $q = $this->_getQuery();
        $q->addTable('events', 'e');
        $q->addQuery('e.event_owner, ue.user_id, e.event_cwd, e.event_id, e.event_start_date, e.event_end_date');
        $q->addJoin('user_events', 'ue', 'ue.event_id = e.event_id');
        $q->addWhere('event_start_date <= \'' . $end_date->format(FMT_DATETIME_MYSQL) . '\'');
        $q->addWhere('event_end_date >= \'' . $start_date->format(FMT_DATETIME_MYSQL) . '\'');
        $q->addWhere('(e.event_owner IN (' . implode(',', $users) . ') OR ue.user_id IN (' . implode(',', $users) . ') )');
        $q->addWhere('e.event_id <>' . $this->event_id);

        $result = $q->exec();
        if (!$result) {
            return false;
        }

        $clashes = array();
        while ($row = $q->fetchRow()) {
            array_push($clashes, $row['event_owner']);
            if ($row['user_id']) {
                array_push($clashes, $row['user_id']);
            }
        }
        $clash = array_unique($clashes);
        $q->clear();
        if (count($clash)) {
            $q->addTable('users', 'u');
            $q->addTable('contacts', 'con');
            $q->addQuery('user_id');
            $q->addQuery('contact_display_name');
            $q->addWhere('user_id IN (' . implode(',', $clash) . ')');
            $q->addWhere('user_contact = contact_id');
            return $q->loadHashList();
        } else {
            return false;
        }
    }

    public function getEventsInWindow($start_date, $end_date, $start_time, $end_time, $users = null)
    {
        if (!isset($users)) {
            return false;
        }
        if (!count($users)) {
            return false;
        }

        // Now build a query to find matching events.
        $q = $this->_getQuery();
        $q->addTable('events', 'e');
        $q->addQuery('e.event_id, event_name, event_sequence, event_owner, event_description,
            ue.user_id, event_cwd, event_start_date, event_end_date, event_updated, event_recurs');
        $q->addQuery('projects.project_id, projects.project_name');
        $q->addTable('events', 'e');
        $q->leftJoin('projects', 'projects', 'e.event_project = projects.project_id');
        $q->addJoin('user_events', 'ue', 'ue.event_id = e.event_id');
        $q->addWhere('event_start_date >= \'' . $start_date . '\'
     	  		AND event_end_date <= \'' . $end_date . '\'
     	  		AND EXTRACT(HOUR_MINUTE FROM e.event_end_date) >= \'' . $start_time . '\'
     	  		AND EXTRACT(HOUR_MINUTE FROM e.event_start_date) <= \'' . $end_time . '\'
     	  		AND ( e.event_owner in (' . implode(',', $users) . ')
     	 		OR ue.user_id in (' . implode(',', $users) . ') )');

        return $q->loadList();
    }

    public function getAllowedRecords($uid, $fields = '*', $orderby = '', $index = null, $extra = null)
    {
        $oPrj = new CProject();
        $oPrj->overrideDatabase($this->_query);

        $aPrjs = $oPrj->getAllowedRecords($uid, 'projects.project_id, project_name', '', null, null, 'projects');
        if (count($aPrjs)) {
            $buffer = '(event_project IN (' . implode(',', array_keys($aPrjs)) . ') OR event_project IS NULL OR event_project = \'\' OR event_project = 0)';

            if ($extra['where'] != '') {
                $extra['where'] = $extra['where'] . ' AND ' . $buffer;
            } else {
                $extra['where'] = $buffer;
            }
        } else {
            // There are no allowed projects, so only allow events with no project.
            if ($extra['where'] != '') {
                $extra['where'] = $extra['where'] . ' AND (event_project IS NULL OR event_project = \'\' OR event_project = 0) ';
            } else {
                $extra['where'] = '(event_project IS NULL OR event_project = \'\' OR event_project = 0)';
            }
        }
        return parent::getAllowedRecords($uid, $fields, $orderby, $index, $extra);
    }

    public function store()
    {
        $stored = false;
        $q = $this->_getQuery();

        if (!$this->event_recurs) {
            $this->event_times_recuring = 0;
        } else {
            //If the event recurs then set the end date day to be equal to the start date day and keep the hour:minute of the end date
            //so that the event starts recurring from the start day onwards n times after the start date for the period given
            //Meaning: The event end date day is useless as far as recurring events are concerned.

            $start_date = new w2p_Utilities_Date($this->event_start_date);
            $end_date = new w2p_Utilities_Date($this->event_end_date);
            $hour = $end_date->getHour();
            $minute = $end_date->getMinute();
            $end_date->setDate($start_date->getDate());
            $end_date->setHour($hour);
            $end_date->setMinute($minute);
            $this->event_end_date = $end_date->format(FMT_DATETIME_MYSQL);
        }

        // ensure changes to check boxes and select lists are honoured
        $this->event_private = (int) $this->event_private;
        $this->event_type = (int) $this->event_type;
        $this->event_cwd = (int) $this->event_cwd;

        $this->event_start_date = $this->_AppUI->convertToSystemTZ($this->event_start_date);
        $this->event_end_date = $this->_AppUI->convertToSystemTZ($this->event_end_date);
        /*
         * TODO: I don't like the duplication on each of these two branches, but I
         *   don't have a good idea on how to fix it at the moment...
         */
        if ($this->{$this->_tbl_key} && $this->canEdit()) {
            $this->event_updated = $q->dbfnNowWithTZ();

            $stored = parent::store();
        }
        if (0 == $this->{$this->_tbl_key} && $this->canCreate()) {
            $this->event_created = $q->dbfnNowWithTZ();
            $this->event_updated = $this->event_created;

            $stored = parent::store();
        }

        return $stored;
    }

    protected function hook_postStore()
    {
        // TODO:  I *really* don't like using the POST inside here..
        $this->updateAssigned(explode(',', $_POST['event_assigned']));

        $custom_fields = new w2p_Core_CustomFields('calendar', 'addedit', $this->event_id, 'edit');
        $custom_fields->bind($_POST);
        $custom_fields->store($this->event_id); // Store Custom Fields

        parent::hook_postStore();
    }

    protected function  hook_postUpdate() {
        parent::hook_postUpdate();

        $q = $this->_query;
        /*
         * TODO: I don't like that we have to run an update immediately after the store
         *   but I don't have a better solution at the moment.
         *                                      ~ caseydk 2012 Aug 04
         */
        $q->addTable('events');
        $q->addUpdate('event_sequence', "event_sequence+1", false, true);
        $q->addUpdate('event_updated', "'" . $q->dbfnNowWithTZ() . "'", false, true);
        $q->addWhere('event_id = ' . (int) $this->event_id);
        $q->exec();
    }

    public function hook_calendar($userId)
    {
        return $this->getCalendarEvents($userId);
    }

    public function getCalendarEvents($userId, $days = 30)
    {
        $eventList = array();

        $now   = time();
        $nowFormatted = date('Y-m-d H:i:s', $now);
        $Ndays = $now + $days * 24 * 60 * 60;
        $NdaysFormatted = date('Y-m-d H:i:s', $Ndays);

        $events = $this->getEventsInWindow($nowFormatted, $NdaysFormatted, '0000', '2359', array($userId));

        $start = new w2p_Utilities_Date($nowFormatted);
        $end   = new w2p_Utilities_Date($NdaysFormatted);

        foreach($events as $event) {
            for ($j = 0 ; $j <= $event['event_recurs']; $j++) {
                $myDates = $this->getRecurrentEventforPeriod($start, $end, $event['event_start_date'], $event['event_end_date'],
                                $event['event_recurs'], $event['event_times_recuring'], $j);
                /*
                 * This list of fields - id, name, description, startDate, endDate,
                 * updatedDate - are named specifically for the iCal creation.
                 * If you change them, it's probably going to break.  So don't do that.
                 */
                $url = W2P_BASE_URL . '/index.php?m=calendar&a=view&event_id=' . $event['event_id'];
                $eventList[] = array('id' => $event['event_id'], 'name' => $event['event_name'],
                            'sequence' => $event['event_sequence'], 'description' => $event['event_description'],
                            'startDate' => $myDates[0]->format(FMT_DATETIME_MYSQL), 'endDate' => $myDates[1]->format(FMT_DATETIME_MYSQL),
                            'updatedDate' => $event['event_updated'], 'url' => $url);
            }
        }

        return $eventList;
    }

}