<?php /* $Id$ $URL$ */

/**
 *	@package web2project
 *	@subpackage output
 *	@version $Revision$
 */

include_once $AppUI->getLibraryClass('jpgraph/src/jpgraph');
include_once $AppUI->getLibraryClass('jpgraph/src/jpgraph_gantt');

class w2p_Output_GanttRenderer {
    private $graph = null;
    private $rowCount = null;
    private $rowMap = array();
    private $todayText = null;
    private $AppUI = null;
    private $df = null;

    private $taskArray = array();
    private $taskCount = 0;

    public function __construct(CAppUI $AppUI, $width)
    {
        $this->graph = new GanttGraph($width);
        $this->graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH | GANTT_HDAY | GANTT_HWEEK);
        $this->graph->SetFrame(false);
        $this->graph->SetBox(true, array(0, 0, 0), 2);
        $this->graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);
        $this->rowCount = 0;
        $this->AppUI = $AppUI;
    }

    public function localize()
    {
        $AppUI = $this->AppUI;
        $pLocale = setlocale(LC_TIME, 0); // get current locale for LC_TIME
        $res = setlocale(LC_TIME, $AppUI->user_lang[2]);
        if ($res) { // Setting locale doesn't fail
            $this->graph->scale->SetDateLocale($AppUI->user_lang[2]);
        }
        setlocale(LC_TIME, $pLocale);
        $this->df = $AppUI->getPref('SHDATEFORMAT');
        $this->todayText = $AppUI->_('Today', UI_OUTPUT_RAW);
    }

    public function setDateRange($start_date, $end_date)
    {
        $min_d_start = new w2p_Utilities_Date($start_date);
        $max_d_end = new w2p_Utilities_Date($end_date);

        // check day_diff and modify Headers
        $day_diff = $min_d_start->dateDiff($max_d_end);

        //-----------------------------------------
        // nice Gantt image
        // if diff(end_date,start_date) > 90 days it shows only
        //week number
        // if diff(end_date,start_date) > 240 days it shows only
        //month number
        //-----------------------------------------
        if ($day_diff > 1096) {
            //more than 3 years, show only the year scale
            $this->graph->ShowHeaders(GANTT_HYEAR);
            $this->graph->scale->year->grid->Show ();
            $this->graph->scale->year->grid->SetStyle (longdashed);
            $this->graph->scale->year->grid->SetColor ('lightgray');
            $this->graph->scale->year->SetFont(FF_CUSTOM, FS_NORMAL,  12);
        } else if ($day_diff > 480) {
            //more than 480 days show only the firstletter of the month
            $this->graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH);
            $this->graph->scale->month->SetStyle(MONTHSTYLE_SHORTNAME);
            $this->graph->scale->month->grid->Show ();
            $this->graph->scale->month->grid->SetStyle (longdashed);
            $this->graph->scale->month->grid->SetColor ('lightgray');
            $this->graph->scale->month->SetFont(FF_CUSTOM, FS_NORMAL, 10);
        } else if($day_diff > 240) {
            //more than 240 days and less than 481 show the month short name eg: Jan
            $this->graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH);
            $this->graph->scale->month->SetStyle(MONTHSTYLE_SHORTNAME);
            $this->graph->scale->month->grid->Show ();
            $this->graph->scale->month->grid->SetStyle (longdashed);
            $this->graph->scale->month->grid->SetColor ('lightgray');
            $this->graph->scale->month->SetFont(FF_CUSTOM, FS_NORMAL, 10);
        } else if ($day_diff > 90) {
            //more than 90 days and less of 241
            $this->graph->ShowHeaders(GANTT_HMONTH | GANTT_HWEEK);
            $this->graph->scale->week->SetStyle(WEEKSTYLE_WNBR);
            $this->graph->scale->week->SetFont(FF_CUSTOM, FS_NORMAL,  7);
        } else {
            //more than 90 days and less of 241
            $this->graph->ShowHeaders(GANTT_HMONTH | GANTT_HDAY);
            $this->graph->scale->day->SetStyle(DAYSTYLE_SHORTDATE4);
            $this->graph->scale->day->SetFont(FF_CUSTOM, FS_NORMAL,  7);
        }

        $this->graph->SetDateRange($start_date, $end_date);
    }

    public function setTitle($tableTitle = '', $background = '#eeeeee')
    {
        $this->graph->title->Set($tableTitle);
        // Use TTF font if it exists
        // try commenting out the following two lines if gantt charts do not display
        if (is_file(TTF_DIR . 'FreeSansBold.ttf')) {
            $this->graph->title->SetFont(FF_CUSTOM, FS_BOLD, 14);
        }
    }

    public function setColumnHeaders(array $columnNames, array $columnSizes)
    {
        $AppUI = $this->AppUI;
        foreach ($columnNames as $column) {
            $translatedColumns[] = $AppUI->_($column, UI_OUTPUT_RAW);
        }
        // Pedro A.
        //
        // The SetFont method changes the related bars caption text style and it will have all bars from its calling through all below
        // until a new SetFont call is executed again on the same object e subobjects.
        //
        // LOGIC: $graph->scale->actinfo->SetFont(font name, font style, font size);
        // EXAMPLE: $graph->scale->actinfo->SetFont(FF_CUSTOM, FS_BOLD, 10);
        //
        // Here is a list of possibilities you can use for the first parameter of the SetFont method:
        // TTF Font families (you must have them installed to use them):
        // FF_COURIER, FF_VERDANA, FF_TIMES, FF_COMIC, FF_CUSTOM, FF_GEORGIA, FF_TREBUCHE
        // Internal fonts:
        // FF_FONT0, FF_FONT1, FF_FONT2
        //
        // For the second parameter you have the TTF font style that can be:
        // FS_NORMAL, FS_BOLD, FS_ITALIC, FS_BOLDIT, FS_BOLDITALIC
        $this->graph->scale->actinfo->vgrid->SetColor('gray');
        $this->graph->scale->actinfo->SetColor('darkgray');
        $this->graph->scale->actinfo->SetColTitles($translatedColumns, $columnSizes);
        if (is_file(TTF_DIR . 'FreeSans.ttf')) {
            $this->graph->scale->actinfo->SetFont(FF_CUSTOM);
        }
    }

    public function addBar(array $columnValues, $caption = '', $height = '0.6',
        $barcolor = 'FFFFFF', $active = true, $progress = 0, $identifier = 0)
    {
        foreach($columnValues as $name => $value) {
            switch ($name) {
                case 'start_date':
                    $start = $value;
                    $startDate = new w2p_Utilities_Date($value);
                    $rowValues[] = $startDate->format($this->df);
                    break;
                case 'end_date':
                    $endDate = new w2p_Utilities_Date($value);
                    $rowValues[] = $endDate->format($this->df);
                    break;
                case 'actual_end':
                    if ('' == $value) {
                        $actual_end = $columnValues['end_date'];
                        $rowValues[] = $value;
                    } else {
                        $actual_end = $value;
                        $actual_endDate = new w2p_Utilities_Date($value);
                        $rowValues[] = $actual_endDate->format($this->df);
                    }
                    break;
                case 'project_name':
                case 'task_name':
                default:
                    $rowValues[] = $value;
            }
        }

        $bar = new GanttBar($this->rowCount++, $rowValues, $start, $actual_end, $caption, $height);
        $this->rowMap[$identifier] = $this->rowCount;
        $bar->progress->Set(min(($progress / 100), 1));

        $bar->title->SetFont(FF_CUSTOM, FS_NORMAL, 9);
        $bar->title->SetColor(bestColor('#ffffff', '#' . $barcolor, '#000000'));
        $bar->SetFillColor('#' . $barcolor);
        $bar->SetPattern(BAND_SOLID, '#' . $barcolor);

        if (0.1 == $height) {
            $bar->rightMark->Show();
            $bar->rightMark->SetType(MARK_RIGHTTRIANGLE);
            $bar->rightMark->SetWidth(3);
            $bar->rightMark->SetColor('black');
            $bar->rightMark->SetFillColor('black');

            $bar->leftMark->Show();
            $bar->leftMark->SetType(MARK_LEFTTRIANGLE);
            $bar->leftMark->SetWidth(3);
            $bar->leftMark->SetColor('black');
            $bar->leftMark->SetFillColor('black');

            $bar->SetPattern(BAND_SOLID, 'black');
        }

        //adding captions
        $bar->caption = new TextProperty($caption);
        $bar->caption->Align('left', 'center');
        if (is_file(TTF_DIR . 'FreeSans.ttf')) {
            $bar->caption->SetFont(FF_CUSTOM, FS_NORMAL, 8);
        }

        // gray out templates, completes, on ice, on hold
        if (!$active) {
            $bar->caption->SetColor('darkgray');
            $bar->title->SetColor('darkgray');
            $bar->SetColor('darkgray');
            $bar->SetFillColor('gray');
            $bar->progress->SetFillColor('darkgray');
            $bar->progress->SetPattern(BAND_SOLID, 'darkgray', 98);
        }
        $this->graph->Add($this->addDependencies($bar, $identifier));
    }

    public function addMilestone(array $columnValues, $start,
        $color = '#CC0000', $identifier = 0)
    {
        $tStartObj = new w2p_Utilities_Date($start);

        $bar = new MileStone($this->rowCount++, $columnValues, $start, $tStartObj->format($this->df));
        $bar->title->SetFont(FF_CUSTOM, FS_NORMAL, 9);
        $bar->title->SetColor($color);
        $bar->mark->SetType(MARK_DIAMOND);
        $bar->mark->SetWidth(10);
        $bar->mark->SetColor($color);
        $bar->mark->SetFillColor($color);

        $this->graph->Add($this->addDependencies($bar, $identifier));
    }

    public function loadTaskArray($gantt_array)
    {
        $this->taskArray = $gantt_array;
        $this->taskCount = count($gantt_array);
    }

    private function addDependencies($ganttBar, $task_id)
    {
        $gantt_arr = $this->taskArray;

        $q = new DBQuery;
        $q->addTable('task_dependencies');
        $q->addQuery('dependencies_task_id');
        $q->addWhere('dependencies_req_task_id=' . (int) $task_id);
        $query = $q->loadList();

        foreach ($query as $dep) {
            for ($d = 0; $d < $this->taskCount; $d++) {
                if ($gantt_arr[$d][0]['task_id'] == $dep['dependencies_task_id']) {
                    $ganttBar->SetConstrain($d, CONSTRAIN_ENDSTART);
                }
            }
        }
        return $ganttBar;
    }

    public function render($markToday = true, $filename = '')
    {
        if ($markToday)
        {
            $today = date('y-m-d');
            $vline = new GanttVLine($today, $this->todayText);
            $this->graph->Add($vline);
        }
        ($filename == '') ? $this->graph->Stroke() : $this->graph->Stroke($filename);
    }

    public function getGraph()
    {
        return $this->graph;
    }

    public function setProperties(array $params)
    {
        foreach ($params as $key => $value) {
            if ($value) {
                switch ($key) {
                    case 'showhgrid':
                        $this->graph->hgrid->Show ();
                        $this->graph->hgrid->SetRowFillColor ('darkblue@0.95');
                        break;
                    default:
                        //do nothing
                }
            }
        }
    }
}