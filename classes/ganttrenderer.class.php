<?php 

include_once $AppUI->getLibraryClass('jpgraph/src/jpgraph');
include_once $AppUI->getLibraryClass('jpgraph/src/jpgraph_gantt');

class GanttRenderer {
  private $graph = null;
  private $rowCount = null;
  private $todayText = null;
  private $AppUI = null;
  private $df = null;
  
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
    
    $min_d_start = new CDate($start_date);
    $max_d_end = new CDate($end_date);

    // check day_diff and modify Headers
    $day_diff = $min_d_start->dateDiff($max_d_end);
    
    //-----------------------------------------
    // nice Gantt image
    // if diff(end_date,start_date) > 90 days it shows only
    //week number
    // if diff(end_date,start_date) > 240 days it shows only
    //month number
    //-----------------------------------------
    if ($day_diff > 240) {
      //more than 240 days
      $this->graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH);
    } else {
      if ($day_diff > 90) {
        //more than 90 days and less of 241
        $this->graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH | GANTT_HWEEK);
        $this->graph->scale->week->SetStyle(WEEKSTYLE_WNBR);
      }
    }
    $this->graph->SetDateRange($start_date, $end_date);
  }

  public function setTitle($tableTitle = '', $background = '#eeeeee')
  {
    $this->graph->scale->tableTitle->Set($tableTitle);
    // Use TTF font if it exists
    // try commenting out the following two lines if gantt charts do not display
    if (is_file(TTF_DIR . 'FreeSansBold.ttf')) {
    	$this->graph->scale->tableTitle->SetFont(FF_CUSTOM, FS_BOLD, 12);
    }
    $this->graph->scale->SetTableTitleBackground($background);
    $font_color = bestColor($background);
    $this->graph->scale->tableTitle->SetColor($font_color);
    $this->graph->scale->tableTitle->Show(true);
  }

  public function setColumnHeaders(array $columnNames, array $columnSizes)
  {
    $AppUI = $this->AppUI;
    foreach ($columnNames as $column) {
      $translatedColumns[] = $AppUI->_($column, UI_OUTPUT_RAW);
    }

    $this->graph->scale->actinfo->vgrid->SetColor('gray');
    $this->graph->scale->actinfo->SetColor('darkgray');
    $this->graph->scale->actinfo->SetColTitles($translatedColumns, $columnSizes);
  }

  public function addBar(array $columnValues, $caption = '',
    $height = '0.6', $barcolor = 'FFFFFF', $active = true, $progress = 0)
  {
    foreach($columnValues as $name => $value) {
      switch ($name) {
        case 'start_date':
          $start = $value;
          $startDate = new CDate($value);
          $rowValues[] = $startDate->format($this->df);
          break;
        case 'end_date':
          $endDate = new CDate($value);
          $rowValues[] = $endDate->format($this->df);
          break;
        case 'actual_end':
          $actual_end = $value;
          $actual_endDate = new CDate($value);
          $rowValues[] = $actual_endDate->format($this->df);
          break;
        case 'project_name':
        default:
          $rowValues[] = $value;
      }
    }

    $bar = new GanttBar($this->rowCount++, $rowValues,$start, $actual_end, $caption, $height);
    $bar->progress->Set(min(($progress / 100), 1));

    $bar->title->SetFont(FF_CUSTOM, FS_NORMAL, 9);
    $bar->title->SetColor(bestColor('#ffffff', '#' . $barcolor, '#000000'));
    $bar->SetFillColor('#' . $barcolor);
    $bar->SetPattern(BAND_SOLID, '#' . $barcolor);

    //adding captions
    $bar->caption = new TextProperty($caption);
    $bar->caption->Align('left', 'center');

    // gray out templates, completes, on ice, on hold
    if (!$active) {
      $bar->caption->SetColor('darkgray');
      $bar->title->SetColor('darkgray');
      $bar->SetColor('darkgray');
      $bar->SetFillColor('gray');
      $bar->progress->SetFillColor('darkgray');
      $bar->progress->SetPattern(BAND_SOLID, 'darkgray', 98);
    }

    $this->graph->Add($bar);
  }

  public function addSubBar($label, $start, $end, $caption = '', 
    $height = '0.6', $barcolor = 'FFFFFF', $progress = 0)
  {
    $startDate = new CDate($start);
    $endDate = new CDate($end);

    $bar = new GanttBar($this->rowCount++, array($label, $startDate->format($this->df), $endDate->format($this->df), ' '), 
                          $start, $end, $caption, $height);
    $bar->progress->Set(min(($progress / 100), 1));

    $bar->title->SetFont(FF_CUSTOM, FS_NORMAL, 9);
    $bar->title->SetColor(bestColor('#ffffff', '#' . $barcolor, '#000000'));
    $bar->SetFillColor('#' . $barcolor);
    $bar->SetPattern(BAND_SOLID, '#' . $barcolor);

    //adding captions
    $bar->caption = new TextProperty($caption);
    $bar->caption->Align('left', 'center');
    
    $this->graph->Add($bar);
  }

  public function addSubSubBar($label, $start, $end, $caption = '', 
    $height = '0.6', $barcolor = 'FFFFFF', $progress = 0)
  {
    $startDate = new CDate($start);
    $endDate = new CDate($end);

    $bar = new GanttBar($this->rowCount++, array($label, ' ', ' ', ' '), $startDate->format(FMT_DATETIME_MYSQL), $endDate->format(FMT_DATETIME_MYSQL), 0.6);
    $bar->title->SetFont(FF_CUSTOM, FS_NORMAL, 9);
    $bar->SetFillColor('#' . $barcolor);
    $this->graph->Add($bar);
  }

  public function addMilestone($label, $start, $color = '#CC0000')
  {
    $tStartObj = new CDate($start);

    $bar = new MileStone($this->rowCount++, $label, $start, $tStartObj->format($this->df));
    $bar->title->SetFont(FF_CUSTOM, FS_NORMAL, 9);
    $bar->title->SetColor($color);
    $this->graph->Add($bar);
  }

  public function render($markToday = true)
  {
    if ($markToday)
    {
      $today = date('y-m-d');
      $vline = new GanttVLine($today, $this->todayText);
      $this->graph->Add($vline);
    }
    $this->graph->Stroke();
  }
  public function getGraph()
  {
    return $this->graph;
  }
}