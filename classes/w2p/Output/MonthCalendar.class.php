<?php

/**
 *	@package web2project
 *	@subpackage output
 *	@version $Revision$
 *
 *  As of v3.0, this class has moved from the Calendar module to its own structure.
 *
 */

if (!isset($AppUI)) {
    $AppUI = new CAppUI;
}
require_once $AppUI->getLibraryClass('PEAR/Date');

/**
 * Displays a configuration month calendar
 *
 * All Date objects are based on the PEAR Date package
 */
class w2p_Output_MonthCalendar {
	/**#@+
	* @var Date
	*/
	public $this_month;
	public $prev_month;
	public $next_month;
	public $prev_year;
	public $next_year;
	/**#@-*/

	/**
 	@var string The css style name of the Title */
	public $styleTitle;

	/**
 	@var string The css style name of the main calendar */
	public $styleMain;

	/**
 	@var string The name of the javascript function that a 'day' link should call when clicked */
	public $callback;

	/**
 	@var boolean Show the heading */
	public $showHeader;

	/**
 	@var boolean Show the previous/next month arrows */
	public $showArrows;

	/**
 	@var boolean Show the day name column headings */
	public $showDays;

	/**
 	@var boolean Show the week link (no pun intended) in the first column */
	public $showWeek;

	/**
 	@var boolean Show the month name as link */
	public $clickMonth;

	/**
 	@var boolean Show events in the calendar boxes */
	public $showEvents;

	/**
 	@var string */
	public $dayFunc;

	/**
 	@var string */
	public $weekFunc;

	/**
 	@var boolean Show highlighting in the calendar boxes */
	public $showHighlightedDays;

	/**
	 * @param Date $date
	 */
	public function __construct($date = null) {
		$this->setDate($date);

		$this->classes = array();
		$this->callback = '';
		$this->showTitle = true;
		$this->showArrows = true;
		$this->showDays = true;
		$this->showWeek = true;
		$this->showEvents = true;
		$this->showHighlightedDays = true;

		$this->styleTitle = '';
		$this->styleMain = '';

		$this->dayFunc = '';
		$this->weekFunc = '';

		$this->events = array();
		$this->highlightedDays = array();
	}

	// setting functions

	/**
	 * CMonthCalendar::setDate()
	 *
	 * { Description }
	 *
	 * @param [type] $date
	 */
	public function setDate($date = null) {
		global $AppUI;

        $this->this_month = new w2p_Utilities_Date($date);

		$d = $this->this_month->getDay();
		$m = $this->this_month->getMonth();
		$y = $this->this_month->getYear();

		$this->prev_year = new w2p_Utilities_Date($date);
		$this->prev_year->setYear($this->prev_year->getYear() - 1);

		$this->next_year = new w2p_Utilities_Date($date);
		$this->next_year->setYear($this->next_year->getYear() + 1);

		setlocale(LC_TIME, 'en');
		$date = Date_Calc::beginOfPrevMonth($d, $m, $y, FMT_TIMESTAMP_DATE);
		setlocale(LC_ALL, $AppUI->user_lang);

		$this->prev_month = new w2p_Utilities_Date($date);

		setlocale(LC_TIME, 'en');
		$date = Date_Calc::beginOfNextMonth($d, $m, $y, FMT_TIMESTAMP_DATE);
		setlocale(LC_ALL, $AppUI->user_lang);
		$this->next_month = new w2p_Utilities_Date($date);

	}

	/**
	 * CMonthCalendar::setStyles()
	 *
	 * { Description }
	 *
	 */
	public function setStyles($title, $main) {
		$this->styleTitle = $title;
		$this->styleMain = $main;
	}

	/**
	 * CMonthCalendar::setLinkFunctions()
	 *
	 * { Description }
	 *
	 * @param string $day
	 * @param string $week
	 */
	public function setLinkFunctions($day = '', $week = '') {
		$this->dayFunc = $day;
		$this->weekFunc = $week;
	}

	/**
	 * CMonthCalendar::setCallback()
	 *
	 * { Description }
	 *
	 */
	public function setCallback($function) {
		$this->callback = $function;
	}

	/**
	 * CMonthCalendar::setEvents()
	 *
	 * { Description }
	 *
	 */
	public function setEvents($e) {
		$this->events = $e;
	}

	/**
	 * CMonthCalendar::setHighlightedDays()
	 * ie 	['20040517'] => '#ff0000',
	 *
	 * { Description }
	 *
	 */
	public function setHighlightedDays($hd) {
		$this->highlightedDays = $hd;
	}

	// drawing functions
	/**
	 * CMonthCalendar::show()
	 *
	 * { Description }
	 *
	 */
	public function show() {
		$s = '';
		if ($this->showTitle) {
			$s .= $this->_drawTitle();
		}
		$s .= '<table border="0" cellspacing="1" cellpadding="2" width="100%" class="' . $this->styleMain . '">';

		if ($this->showDays) {
			$s .= $this->_drawDays();
		}

		$s .= $this->_drawMain();

		$s .= '</table>';

		return $s;
	}

	/**
	 * CMonthCalendar::_drawTitle()
	 *
	 * { Description }
	 *
	 */
	private function _drawTitle() {
		global $AppUI, $m, $a;
		$url = 'index.php?m=' . $m;
		$url .= $a ? '&amp;a=' . $a : '';
		$url .= isset($_GET['dialog']) ? '&amp;dialog=1' : '';

		$s = '<table border="0" cellspacing="0" cellpadding="3" width="100%" class="' . $this->styleTitle . '">';
		$s .= '<tr>';

		if ($this->showArrows) {
			$href = $url . '&amp;date=' . $this->prev_month->format(FMT_TIMESTAMP_DATE) . ($this->callback ? '&amp;callback=' . $this->callback : '') . ((count($this->highlightedDays) > 0) ? '&uts=' . key($this->highlightedDays) : '');
			$s .= '<td align="left">';
			$s .= '<a href="' . $href . '">' . w2PshowImage('prev.gif', 16, 16, $AppUI->_('previous month')) . '</a>';
			$s .= '</td>';
		}

		$s .= '<th width="99%" align="center">';
		if ($this->clickMonth) {
			$s .= '<a href="index.php?m=' . $m . '&amp;date=' . $this->this_month->format(FMT_TIMESTAMP_DATE) . '">';
		}
		setlocale(LC_TIME, 'en');
		$s .= $AppUI->_($this->this_month->format('%B')) . ' ' . $this->this_month->format('%Y') . (($this->clickMonth) ? '</a>' : '');
		setlocale(LC_ALL, $AppUI->user_lang);
		$s .= '</th>';

		if ($this->showArrows) {
			$href = ($url . '&amp;date=' . $this->next_month->format(FMT_TIMESTAMP_DATE) . (($this->callback) ? ('&amp;callback=' . $this->callback) : '') . ((count($this->highlightedDays) > 0) ? ('&amp;uts=' . key($this->highlightedDays)) : ''));
			$s .= '<td align="right">';
			$s .= ('<a href="' . $href . '">' . w2PshowImage('next.gif', 16, 16, $AppUI->_('next month')) . '</a>');
			$s .= '</td>';
		}

		$s .= '</tr>';
		$s .= '</table>';

		return $s;
	}
	/**
	 * CMonthCalendar::_drawDays()
	 *
	 * { Description }
	 *
	 * @return string Returns table a row with the day names
	 */
	private function _drawDays() {
		global $AppUI;

		setlocale(LC_TIME, 'en');
		$wk = Date_Calc::getCalendarWeek(null, null, null, '%a', LOCALE_FIRST_DAY);
		setlocale(LC_ALL, $AppUI->user_lang);

		$s = (($this->showWeek) ? ('<th>&nbsp;</th>') : '');
		foreach ($wk as $day) {
			$s .= ('<th width="14%">' . $AppUI->_($day) . '</th>');
		}

		return ('<tr>' . $s . '</tr>');
	}

	/**
	 * CMonthCalendar::_drawMain()
	 *
	 * { Description }
	 *
	 */
	private function _drawMain() {
		global $AppUI;
		$today = new w2p_Utilities_Date();
		$today = $today->format('%Y%m%d%w');

		$date = $this->this_month;
		$this_day = intval($date->getDay());
		$this_month = intval($date->getMonth());
		$this_year = intval($date->getYear());
		setlocale(LC_TIME, 'en');
		$cal = Date_Calc::getCalendarMonth($this_month, $this_year, '%Y%m%d%w', LOCALE_FIRST_DAY);
		setlocale(LC_ALL, $AppUI->user_lang);

		$df = $AppUI->getPref('SHDATEFORMAT');

		$html = '';
		foreach ($cal as $week) {
			$html .= '<tr>';
			if ($this->showWeek) {
				$html .= '<td class="week">';
				$html .= $this->dayFunc ? "<a href=\"javascript:$this->weekFunc('$week[0]')\">" : '';
				$html .= '<img src="' . w2PfindImage('view.week.gif') . '" width="16" height="15" border="0" alt="Week View" /></a>';
				$html .= $this->dayFunc ? '</a>' : '';
				$html .= '</td>';
			}

			foreach ($week as $day) {
				$this_day = new w2p_Utilities_Date($day);
				$y = intval(substr($day, 0, 4));
				$m = intval(substr($day, 4, 2));
				$d = intval(substr($day, 6, 2));
				$dow = intval(substr($day, 8, 1));
				$cday = intval(substr($day, 0, 8));

				//If we are on minical mode and we find tasks or events for this day then lets color code the cell depending on that
				if (array_key_exists($cday, $this->events) && $this->styleMain == 'minical') {
					$nr_tasks = 0;
					$nr_events = 0;
					//lets count tasks and events
					foreach ($this->events[$cday] as $record) {
						if ($record['task']) {
							++$nr_tasks;
						} else {
							++$nr_events;
						}
					}
					if ($nr_events && $nr_tasks) {
						//if we find both
						$class = 'eventtask';
					} elseif ($nr_events) {
						//if we just find events
						$class = 'event';
					} elseif ($nr_tasks) {
						//if we just find tasks
						$class = 'task';
					}
					if ($day == $today) {
						$class .= 'today';
					}
				} elseif ($m != $this_month) {
					$class = 'empty';
				} elseif ($day == $today) {
					$class = 'today';
				} elseif ($dow == 0 || $dow == 6) {
					$class = 'weekend';
				} else {
					$class = 'day';
				}
				$day = substr($day, 0, 8);
				$html .= '<td class="' . $class . '"';
				if ($this->showHighlightedDays && isset($this->highlightedDays[$day])) {
					$html .= ' style="border: 1px solid ' . $this->highlightedDays[$day] . '"';
				}
				$html .= ' onclick="' . $this->dayFunc . '(\'' . $day . '\',\'' . $this_day->format($df) . '\')' . '">';

                if ($this->dayFunc) {
                    $html .= "<a href=\"javascript:$this->dayFunc('$day','" . $this_day->format($df) . "')\" class=\"$class\">";
                    $html .= $d;
                    $html .= '</a>';
                } else {
                    $html .= $d;
                }
                if ($this->showEvents) {
                    $html .= $this->_drawEvents(substr($day, 0, 8));
                }

				$html .= '</td>';
			}
			$html .= '</tr>';
		}
		return $html;
	}

	/**
	 * CMonthCalendar::_drawWeek()
	 *
	 * { Description }
	 *
	 */
	private function _drawWeek($dateObj) {
		$href = "javascript:$this->weekFunc(" . $dateObj->getTimestamp() . ',\'' . $dateObj->toString() . '\')';
		$w = '        <td class="week">';
		$w .= $this->dayFunc ? '<a href="' . $href . '">' : '';
		$w .= '<img src="' . w2PfindImage('view.week.gif') . '" width="16" height="15" border="0" alt="Week View" /></a>';
		$w .= $this->dayFunc ? '</a>' : '';
		$w .= '</td>';
		return $w;
	}

	/**
	 * CMonthCalendar::_drawEvents()
	 *
	 * { Description }
	 *
	 */
	private function _drawEvents($day) {
		$s = '';
		if (!isset($this->events[$day]) || $this->styleMain == 'minical') {
			return '';
		}
		$events = $this->events[$day];
		foreach ($events as $e) {
			$href = isset($e['href']) ? $e['href'] : null;
			$alt = isset($e['alt']) ? mb_str_replace("\n", ' ', $e['alt']) : null;

			$s .= '<br />';
			$s .= $href ? '<a href="'.$href.'" class="event" title="'.$alt.'">' : '';
			$s .= $e['text'];
			$s .= $href ? '</a>' : '';
		}
		return $s;
	}
}