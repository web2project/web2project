<?php
/**
 * This is primarily a wrapper for the jpgraph class so we can generate Gantt
 * Charts. I chose this because working with it directly in the modules created
 * a huge amount of duplicated code. In addition, as we evaluate alternatives,
 * this will let us switch them out more easily.
 *
 * @package web2project\output
 * @author  D. Keith Casey, Jr. <caseydk@users.sourceforge.net>
 */

class w2p_Output_GanttRenderer {
    private $graph = null;
    private $rowCount = null;
    private $todayText = null;
    private $AppUI = null;
    private $df = null;

    private $taskArray = array();
    private $taskCount = 0;

    /** @var int|null Days in the range from setDateRange(), null if not set */
    private $dayCount = null;
    /** @var string|null Font file used for Chinese, Japanese and Korean text */
    private $cjkFont = null;

    public function __construct(w2p_Core_CAppUI $AppUI, $width)
    {
        $this->graph = new GanttGraph((int) $width);
        $this->graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH | GANTT_HDAY | GANTT_HWEEK);
        $this->graph->SetFrame(false);
        $this->graph->SetBox(true, array(0, 0, 0), 2);
        $this->graph->scale->week->SetStyle(WEEKSTYLE_FIRSTDAY);
        $this->rowCount = 0;
        $this->AppUI = $AppUI;

        if (function_exists('imagettftext')) {
            $this->cjkFont = $this->findCjkFont();
            if ($this->cjkFont) {
                $this->graph->img->ttf = $this->fontLoader($this->cjkFont);
            }
        }

        // Header rows; render() picks which ones to show and adjusts sizes.
        foreach (array('year', 'month', 'week', 'day') as $header) {
            $this->graph->scale->$header->SetFont($this->font(), FS_NORMAL, 8);
        }
    }

    /**
     * The font family for a piece of chart text.
     *
     * JpGraph ships DejaVu Sans in its own fonts folder and checks there
     * first, so nothing needs to be installed. It takes UTF-8 text and covers
     * Latin, Greek, Cyrillic and more. The built-in bitmap fonts (FF_FONT0-2)
     * only cover Latin-1 and turned other names into garbage. Without
     * FreeType in GD, fall back to the bitmap font.
     *
     * DejaVu has no Chinese, Japanese or Korean glyphs. Text that contains
     * them uses the CJK font from findCjkFont(), if one was found.
     *
     * @param string|array $text The text (or row of texts) to be drawn
     *
     * @return int A JpGraph FF_* constant
     */
    private function font($text = '')
    {
        if (!function_exists('imagettftext')) {
            return FF_FONT0;
        }
        if ($this->cjkFont
            && preg_match('/[\p{Han}\p{Hiragana}\p{Katakana}\p{Hangul}]/u', implode(' ', (array) $text))) {
            return FF_USERFONT;
        }

        return FF_DV_SANSSERIF;
    }

    /**
     * Finds a font file that has Chinese, Japanese and Korean glyphs.
     *
     * $w2Pconfig['gantt_cjk_font'] in config.php wins. Otherwise use the first
     * common system CJK font that exists. These fonts are large (about 20 MB),
     * so web2project doesn't ship one.
     *
     * @return string|null Path to a .ttf or .ttc file
     */
    private function findCjkFont()
    {
        $candidates = array(
            w2PgetConfig('gantt_cjk_font', ''),
            '/usr/share/fonts/opentype/noto/NotoSansCJK-Regular.ttc',      // Debian, Ubuntu: fonts-noto-cjk
            '/usr/share/fonts/noto-cjk/NotoSansCJK-Regular.ttc',           // Arch: noto-fonts-cjk
            '/usr/share/fonts/google-noto-cjk/NotoSansCJK-Regular.ttc',    // Fedora, RHEL: google-noto-sans-cjk-ttc-fonts
            '/usr/share/fonts/google-noto-sans-cjk-fonts/NotoSansCJK-Regular.ttc', // Fedora 39+: google-noto-sans-cjk-fonts
            '/usr/share/fonts/truetype/wqy/wqy-microhei.ttc',              // Debian, Ubuntu: fonts-wqy-microhei
            '/System/Library/Fonts/Hiragino Sans GB.ttc',                  // macOS
            'C:/Windows/Fonts/msyh.ttc',                                    // Windows: Microsoft YaHei
        );
        foreach ($candidates as $file) {
            if ($file && @is_readable($file)) {
                return $file;
            }
        }

        return null;
    }

    /**
     * JpGraph's font loader only looks for font files in its own fonts folder
     * and TTF_DIR. This one also returns $cjkFile, a full path, for
     * FF_USERFONT (every style uses the same file).
     *
     * @param string $cjkFile
     *
     * @return TTF
     */
    private function fontLoader($cjkFile)
    {
        $ttf = new class extends TTF {
            public $userFontFile = '';

            public function File($family, $style = FS_NORMAL)
            {
                if ($family == FF_USERFONT && $this->userFontFile) {
                    return $this->userFontFile;
                }

                return parent::File($family, $style);
            }
        };
        $ttf->userFontFile = $cjkFile;

        return $ttf;
    }

    public function localize()
    {
        $AppUI = $this->AppUI;
        $pLocale = setlocale(LC_TIME, 0); // get current locale for LC_TIME
        $res = setlocale(LC_TIME, $AppUI->user_lang[1]);
        if ($res) { // Setting locale doesn't fail
            $this->graph->scale->SetDateLocale($AppUI->user_lang[1]);
        }
        setlocale(LC_TIME, $pLocale);
        $this->df = $AppUI->getPref('SHDATEFORMAT');
        $this->todayText = $AppUI->_('Today', UI_OUTPUT_RAW);
    }

    public function setDateRange($start_date, $end_date)
    {
        $min_d_start = new w2p_Utilities_Date($start_date);
        $max_d_end = new w2p_Utilities_Date($end_date);

        // render() picks the header rows once it knows how wide the chart is.
        $this->dayCount = $min_d_start->dateDiff($max_d_end);

        $start_date = is_object($start_date) ? $start_date->format(FMT_DATETIME_MYSQL) : $start_date;
        $end_date   = is_object($end_date)   ? $end_date->format(FMT_DATETIME_MYSQL)   : $end_date;

        $this->graph->SetDateRange($start_date, $end_date);
    }

    /**
     * The width of one day on the chart, in pixels. This is the same
     *  calculation JpGraph makes in GanttGraph::Stroke(): the plot area minus
     *  the task table, which is as wide as its widest column header or row.
     *
     * @param int $days Days in the range
     *
     * @return float
     */
    private function pixelsPerDay($days)
    {
        $img = $this->graph->img;
        $tableWidth = max($this->graph->scale->actinfo->GetWidth($img),
            $this->graph->GetMaxLabelWidth(),
            $this->graph->scale->tableTitle->GetWidth($img));
        $plotWidth = $img->width - $img->left_margin - $img->right_margin - $tableWidth;

        return $plotWidth / max(1, $days);
    }

    /**
     * Shows the most detailed header rows whose labels fit. Measured in
     *  DejaVu Sans, a day number ("28", 7pt) needs about 14px per day, a week
     *  number ("w52", 7pt) 22px per week and a month ("May", 8pt) 24px per
     *  month. Long ranges also get the coarser rows regardless of width.
     *
     * @param int   $days
     * @param float $pxPerDay
     */
    private function setHeaders($days, $pxPerDay)
    {
        $scale = $this->graph->scale;
        if ($days > 1096 || $pxPerDay < 0.8) {
            // more than 3 years: years only
            $this->graph->ShowHeaders(GANTT_HYEAR);
            $scale->year->grid->Show();
            $scale->year->grid->SetStyle('longdashed');
            $scale->year->grid->SetColor('lightgray');
            $scale->year->SetFont($this->font(), FS_NORMAL, 9);
        } elseif ($days > 240 || $pxPerDay < 3.2) {
            // more than 8 months: years and short month names, e.g. Jan
            $this->graph->ShowHeaders(GANTT_HYEAR | GANTT_HMONTH);
            $scale->month->SetStyle(MONTHSTYLE_SHORTNAME);
            $scale->month->grid->Show();
            $scale->month->grid->SetStyle('longdashed');
            $scale->month->grid->SetColor('lightgray');
            $scale->month->SetFont($this->font(), FS_NORMAL, 8);
        } elseif ($days > 90 || $pxPerDay < 14) {
            // more than 3 months: months and week numbers
            $this->graph->ShowHeaders(GANTT_HMONTH | GANTT_HWEEK);
            $scale->week->SetStyle(WEEKSTYLE_WNBR);
            $scale->week->SetFont($this->font(), FS_NORMAL, 7);
        } else {
            // up to 3 months: months and day numbers
            $this->graph->ShowHeaders(GANTT_HMONTH | GANTT_HDAY);
            $scale->day->SetStyle(DAYSTYLE_SHORTDATE4);
            $scale->day->SetFont($this->font(), FS_NORMAL, 7);
        }
    }

    public function setTitle($tableTitle = '')
    {
        $this->graph->title->Set($tableTitle);
        $this->graph->title->SetFont($this->font($tableTitle), FS_BOLD, 11);
    }

    /**
     * The SetFont method changes the related bars caption text style and it
     *  will have all bars from its calling through all below until a new
     *  SetFont call is executed again on the same object e subobjects.
     *
     * LOGIC: $graph->scale->actinfo->SetFont(font name, font style, font size);
     * EXAMPLE: $graph->scale->actinfo->SetFont($this->font(), FS_BOLD, 10);
     *
     * Use $this->font() for the family so every part of the chart uses the
     *  same font. Styles: FS_NORMAL, FS_BOLD, FS_ITALIC, FS_BOLDITALIC.
     *
     * @param array $columnNames
     * @param array $columnSizes
     *
     * @author Pedro A.
     */
    public function setColumnHeaders(array $columnNames, array $columnSizes)
    {
        $AppUI = $this->AppUI;
        foreach ($columnNames as $column) {
            $translatedColumns[] = $AppUI->_($column, UI_OUTPUT_RAW);
        }

        $this->graph->scale->actinfo->vgrid->SetColor('gray');
        $this->graph->scale->actinfo->SetColor('darkgray');
        $this->graph->scale->actinfo->SetColTitles($translatedColumns, $columnSizes);
        $this->graph->scale->actinfo->SetFont($this->font($translatedColumns), FS_NORMAL, 8);
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
                default:
                    $rowValues[] = $value;
            }
        }

        $bar = new GanttBar($this->rowCount++, $rowValues, $start, $actual_end, $caption, $height);
        $bar->progress->Set(min(($progress / 100), 1));

        $bar->title->SetFont($this->font($rowValues), FS_NORMAL, 8);
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

            $bar->title->SetFont($this->font($rowValues), FS_BOLD, 8);
        }

        //adding captions
        $bar->caption = new TextProperty($caption);
        $bar->caption->Align('left', 'center');
        $bar->caption->SetFont($this->font($caption), FS_NORMAL, 7);

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
        $color = '#CC0000', $identifier = 0, $captionToTheLeft=false)
    {
        $tStartObj = new w2p_Utilities_Date($start);

        $bar = new MileStone($this->rowCount++, $columnValues, $start, $tStartObj->format($this->df));
        $bar->title->SetFont($this->font($columnValues), FS_NORMAL, 8);
        $bar->title->SetColor($color);
        $bar->caption->SetFont($this->font(), FS_NORMAL, 7);
        $bar->mark->SetType(MARK_DIAMOND);
        $bar->mark->SetWidth(10);
        $bar->mark->SetColor($color);
        $bar->mark->SetFillColor($color);

        if ($captionToTheLeft) {
            $bar->caption->Align("right", "center");
            $bar->SetCaptionMargin(-15);
        }

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

        $q = new w2p_Database_Query;
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
            $today = new w2p_Utilities_Date();
            $today->convertTZ($this->AppUI->getPref('TIMEZONE'));
            $today->addDays(-1);
            $vline = new GanttVLine($today->format(FMT_DATETIME_MYSQL), $this->todayText);
            $vline->title->setFont($this->font($this->todayText), FS_NORMAL, 7);
            $this->graph->Add($vline);
        }
        $days = $this->dayCount;
        if (null === $days && $this->rowCount > 0) {
            // No setDateRange() (the sub-projects gantt): JpGraph fits the
            // range to the bars, so measure it the same way.
            list($min, $max) = $this->graph->GetBarMinMax();
            $days = (int) round(($max - $min) / SECPERDAY);
        }
        if (null !== $days) {
            $this->setHeaders($days, $this->pixelsPerDay($days));
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
