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

    /** @var array Rows by task id: task_id => array(bar or milestone, row) */
    private $taskRows = array();
    /** @var array Every row's labels, for fitTable(): array(object, raw strings, style) */
    private $rows = array();
    /** @var array Translated column titles and minimum widths from setColumnHeaders() */
    private $columnTitles = array();
    private $columnSizes = array();

    /** @var int|null Days in the range from setDateRange(), null if not set */
    private $dayCount = null;
    /** @var string|null Font file used for Chinese, Japanese and Korean text */
    private $cjkFont = null;
    /** @var array Bars and milestones with a caption to the right: array(object, gap in px) */
    private $captioned = array();

    /** Han, kana, Hangul, Bopomofo, CJK punctuation and fullwidth forms */
    const CJK_CHARS = '\p{Han}\p{Hiragana}\p{Katakana}\p{Hangul}\p{Bopomofo}\x{3000}-\x{303F}\x{FF00}-\x{FFEF}';

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
     * The font family for chart text.
     *
     * JpGraph ships DejaVu Sans in its own fonts folder and checks there
     * first, so nothing needs to be installed. It takes UTF-8 text and covers
     * Latin, Greek, Cyrillic and more. The built-in bitmap fonts (FF_FONT0-2)
     * only cover Latin-1 and turned other names into garbage. Without
     * FreeType in GD, fall back to the bitmap font.
     *
     * DejaVu has no Chinese, Japanese or Korean glyphs; text() handles labels
     * that contain them.
     *
     * @return int A JpGraph FF_* constant
     */
    private function font()
    {
        return function_exists('imagettftext') ? FF_DV_SANSSERIF : FF_FONT0;
    }

    /**
     * @param mixed $text
     *
     * @return bool True if $text is a string with CJK characters and a CJK
     *              font was found to draw them
     */
    private function hasCjk($text)
    {
        return $this->cjkFont && is_string($text)
            && preg_match('/[' . self::CJK_CHARS . ']/u', $text);
    }

    /**
     * Returns $text unchanged, or for text with CJK characters, a
     *  w2p_Output_GanttText that draws the CJK runs in the CJK font and the
     *  rest in DejaVu Sans. CJK runs are one point larger: at the same point
     *  size, CJK glyphs look smaller than DejaVu's.
     *
     * @param mixed  $text
     * @param int    $style  FS_* style
     * @param int    $size   Point size for the DejaVu runs
     * @param string $halign Alignment JpGraph uses for this label
     * @param string $valign
     *
     * @return mixed
     */
    private function text($text, $style, $size, $halign = 'left', $valign = 'center')
    {
        if (!$this->hasCjk($text)) {
            return $text;
        }
        $runs = array();
        $parts = preg_split('/([' . self::CJK_CHARS . ']+)/u', $text, -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        foreach ($parts as $part) {
            $runs[] = preg_match('/^[' . self::CJK_CHARS . ']/u', $part)
                ? array($part, FF_USERFONT, $size + 1)
                : array($part, FF_DV_SANSSERIF, $size);
        }

        return new w2p_Output_GanttText($this->graph->img, $runs, $style, $halign, $valign);
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
        // layout() can call this twice; start from JpGraph's defaults.
        $scale->year->grid->Show(false);
        $scale->month->grid->Show(false);
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
        // The title is a JpGraph Text, which can't hold a w2p_Output_GanttText,
        // so a title with CJK characters is drawn entirely in the CJK font.
        if ($this->hasCjk($tableTitle)) {
            $this->graph->title->SetFont(FF_USERFONT, FS_BOLD, 12);
        } else {
            $this->graph->title->SetFont($this->font(), FS_BOLD, 11);
        }
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
        $this->graph->scale->actinfo->SetFont($this->font(), FS_NORMAL, 8);
        $this->columnTitles = $translatedColumns;
        $this->columnSizes = $columnSizes;
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

        // Dynamic (parent) tasks are drawn as a thin bracket with a bold title.
        $dynamic = (0.1 == $height);
        $titleStyle = $dynamic ? FS_BOLD : FS_NORMAL;
        $rawValues = $rowValues;
        foreach ($rowValues as $i => $value) {
            $rowValues[$i] = $this->text($value, $titleStyle, 8);
        }

        $row = $this->rowCount++;
        $bar = new GanttBar($row, $rowValues, $start, $actual_end, $caption, $height);
        $bar->progress->Set(min(($progress / 100), 1));

        $bar->title->SetFont($this->font(), $titleStyle, 8);
        $bar->title->SetColor(bestColor('#ffffff', '#' . $barcolor, '#000000'));
        $bar->SetFillColor('#' . $barcolor);
        $bar->SetPattern(BAND_SOLID, '#' . $barcolor);

        if ($dynamic) {
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
        $captionText = $this->text($caption, FS_NORMAL, 7);
        $bar->caption = new TextProperty(is_object($captionText) ? array($captionText) : $caption);
        $bar->caption->Align('left', 'center');
        $bar->caption->SetFont($this->font(), FS_NORMAL, 7);
        // GanttBar draws the caption iCaptionMargin past the bar end, and past
        // the end mark of a dynamic task.
        $this->captioned[] = array($bar, $bar->iCaptionMargin + ($dynamic ? $bar->rightMark->GetWidth() : 0));

        // gray out templates, completes, on ice, on hold
        if (!$active) {
            $bar->caption->SetColor('darkgray');
            $bar->title->SetColor('darkgray');
            $bar->SetColor('darkgray');
            $bar->SetFillColor('gray');
            $bar->progress->SetFillColor('darkgray');
            $bar->progress->SetPattern(BAND_SOLID, 'darkgray', 98);
        }
        $this->addRow($bar, $row, $rawValues, $titleStyle, $identifier);
    }

    public function addMilestone(array $columnValues, $start,
        $color = '#CC0000', $identifier = 0, $captionToTheLeft=false)
    {
        $tStartObj = new w2p_Utilities_Date($start);

        $rawValues = $columnValues;
        foreach ($columnValues as $i => $value) {
            $columnValues[$i] = $this->text($value, FS_NORMAL, 8);
        }
        $row = $this->rowCount++;
        $bar = new MileStone($row, $columnValues, $start, $tStartObj->format($this->df));
        $bar->title->SetFont($this->font(), FS_NORMAL, 8);
        $bar->title->SetColor($color);
        $bar->caption->SetFont($this->font(), FS_NORMAL, 7);
        $bar->mark->SetType(MARK_DIAMOND);
        $bar->mark->SetWidth(10);
        $bar->mark->SetColor($color);
        $bar->mark->SetFillColor($color);

        if ($captionToTheLeft) {
            $bar->caption->Align("right", "center");
            $bar->SetCaptionMargin(-15);
        } else {
            // MileStone draws the caption iCaptionMargin past the diamond.
            $this->captioned[] = array($bar, $bar->mark->width / 2 + $bar->iCaptionMargin);
        }

        $this->addRow($bar, $row, $rawValues, FS_NORMAL, $identifier);
    }

    /**
     * Adds a bar or milestone to the chart and remembers its row.
     *
     * @param GanttPlotObject $obj
     * @param int    $row
     * @param array  $rawValues The row's labels before text() wrapped them
     * @param int    $style     FS_* style of the labels
     * @param int    $taskId    The task this row draws, or 0 for a project,
     *                          assignee or other row. Dependency arrows are
     *                          drawn between task rows; the first row added
     *                          for a task is the one arrows attach to.
     */
    private function addRow($obj, $row, array $rawValues, $style, $taskId)
    {
        $this->rows[] = array($obj, array_values($rawValues), $style);
        $taskId = (int) $taskId;
        if ($taskId > 0 && !isset($this->taskRows[$taskId])) {
            $this->taskRows[$taskId] = array($obj, $row);
        }
        $this->graph->Add($obj);
    }

    /**
     * @deprecated Rows are now matched to tasks by the task id passed to
     *             addBar()/addMilestone(), on every chart. Kept so callers
     *             don't break; it does nothing.
     *
     * @param array $gantt_array
     */
    public function loadTaskArray($gantt_array)
    {
    }

    /**
     * Draws an end-to-start arrow from each task to the tasks that depend on
     *  it, when both are on the chart. One query covers every task row.
     */
    private function addDependencyArrows()
    {
        if (count($this->taskRows) < 2) {
            return;
        }
        $ids = implode(',', array_map('intval', array_keys($this->taskRows)));

        $q = new w2p_Database_Query;
        $q->addTable('task_dependencies');
        $q->addQuery('dependencies_task_id, dependencies_req_task_id');
        $q->addWhere('dependencies_req_task_id IN (' . $ids . ')');
        $q->addWhere('dependencies_task_id IN (' . $ids . ')');

        foreach ($q->loadList() as $dep) {
            list($required) = $this->taskRows[(int) $dep['dependencies_req_task_id']];
            list(, $dependentRow) = $this->taskRows[(int) $dep['dependencies_task_id']];
            $required->SetConstrain($dependentRow, CONSTRAIN_ENDSTART);
        }
    }

    public function render($markToday = true, $filename = '')
    {
        if ($markToday)
        {
            // Today's date (in the user's time zone) at 00:00. GanttVLine adds
            // half a day, so the line sits in the middle of today's column.
            $today = new w2p_Utilities_Date();
            $today->convertTZ($this->AppUI->getPref('TIMEZONE'));
            $vline = new GanttVLine($today->format('%Y-%m-%d'), $this->todayText);
            $vline->title->setFont($this->font(), FS_NORMAL, 7);
            $this->graph->Add($vline);
        }
        $this->addDependencyArrows();
        if ($this->rowCount > 0) {
            $this->layout();
        }
        ($filename == '') ? $this->graph->Stroke() : $this->graph->Stroke($filename);
    }

    /**
     * Picks the header rows and the right margin. This does the date-range
     *  steps GanttGraph::Stroke() would do first, so the measurements match
     *  the chart that gets drawn: with no setDateRange() (the sub-projects
     *  gantt) the range is fitted to the bars, and the range is extended to
     *  whole weeks, which Stroke() does whenever month or year headers are
     *  shown (always, here). Both steps are safe to repeat.
     */
    private function layout()
    {
        $scale = $this->graph->scale;
        if (!$scale->IsRangeSet()) {
            list($min, $max) = $this->graph->GetBarMinMax();
            $scale->SetRange($min, $max);
        }
        $this->fitTable();
        // The thresholds in setHeaders() are for the range that was asked for.
        $days = (null !== $this->dayCount) ? $this->dayCount : $scale->GetNumberOfDays();

        $this->setHeaders($days, $this->pixelsPerDay($scale->GetNumberOfDays()));
        $scale->AdjustStartEndDay();
        $this->fitCaptions($scale->GetNumberOfDays(), $scale->iEndDate);
        // A wider margin narrows the days, so check the header rows again.
        $this->setHeaders($days, $this->pixelsPerDay($scale->GetNumberOfDays()));
    }

    /**
     * The task table's width in pixels, as GanttGraph::Stroke() measures it.
     *
     * @return float
     */
    private function tableWidth()
    {
        $img = $this->graph->img;

        return max($this->graph->scale->actinfo->GetWidth($img),
            $this->graph->GetMaxLabelWidth(),
            $this->graph->scale->tableTitle->GetWidth($img));
    }

    /**
     * Keeps the task table to at most half the image, so a narrow chart
     *  still has room for the bars. The table is as wide as its column
     *  minimums and its widest labels. First drop columns from the right
     *  (the bars show the dates), keeping the first, then shorten the
     *  first column's labels with an ellipsis.
     */
    private function fitTable()
    {
        $count = count($this->columnTitles);
        if (!$count) {
            return;
        }
        $budget = $this->graph->img->width / 2;
        while ($count > 1 && $this->tableWidth() > $budget) {
            $this->showColumns(--$count);
        }
        if ($this->tableWidth() <= $budget) {
            return;
        }
        $longest = 0;
        foreach ($this->rows as $row) {
            $longest = max($longest, mb_strlen((string) reset($row[1])));
        }
        for ($chars = $longest - 2; $chars >= 8; $chars = (int) floor($chars * 0.85)) {
            $this->showColumns($count, $chars);
            if ($this->tableWidth() <= $budget) {
                return;
            }
        }
    }

    /**
     * Shows the first $count columns, and if $maxChars is set, shortens
     *  the first column's labels to that many characters plus an ellipsis.
     *
     * @param int      $count
     * @param int|null $maxChars
     */
    private function showColumns($count, $maxChars = null)
    {
        $sizes = array_slice($this->columnSizes, 0, $count);
        if (null !== $maxChars && $sizes) {
            $sizes[0] = 0;  // let the shortened labels decide the width
        }
        $this->graph->scale->actinfo->SetColTitles(array_slice($this->columnTitles, 0, $count), $sizes);
        foreach ($this->rows as $row) {
            list($obj, $raw, $style) = $row;
            $values = array_slice($raw, 0, $count);
            if (null !== $maxChars && isset($values[0]) && is_string($values[0])
                && mb_strlen($values[0]) > $maxChars + 1) {
                $values[0] = rtrim(mb_substr($values[0], 0, $maxChars)) . '…';
            }
            foreach ($values as $i => $value) {
                $values[$i] = $this->text($value, $style, 8);
            }
            $obj->title->Set($values);
        }
    }

    /**
     * Widens the right margin so captions to the right of bars and milestones
     *  near the end of the range aren't cut off at the image edge. A wider
     *  margin narrows the days, which moves the bar ends, so repeat a few
     *  times until the margin settles. The margin takes at most a third of the
     *  plot area, so on a narrow chart a long caption can still be cut off.
     *
     * @param int $days     Days in the range
     * @param int $rangeEnd Timestamp of the end of the range
     */
    private function fitCaptions($days, $rangeEnd)
    {
        if (!$this->captioned) {
            return;
        }
        $img = $this->graph->img;
        $scale = $this->graph->scale;
        $baseMargin = $img->right_margin;
        $maxExtra = max(0, (int) floor($this->pixelsPerDay($days) * $days / 3));
        for ($pass = 0; $pass < 3; $pass++) {
            $pxPerDay = $this->pixelsPerDay($days);
            $overflow = 0;
            foreach ($this->captioned as $item) {
                list($obj, $gap) = $item;
                $end = $scale->NormalizeDate($obj->GetMaxDate());
                if (false === $end) {
                    continue;
                }
                if ($end > $rangeEnd) {
                    // JpGraph draws no caption for a bar cut at the range end
                    // (its end, 23:59:59, is past the range end, 23:59) or for
                    // a milestone past it.
                    continue;
                }
                $room = ($rangeEnd - $end) / SECPERDAY * $pxPerDay;
                $overflow = max($overflow, $gap + $obj->caption->GetWidth($img) - $room);
            }
            $margin = $baseMargin + min((int) ceil($overflow), $maxExtra);
            if ($margin == $img->right_margin) {
                break;
            }
            $img->SetMargin($img->left_margin, $margin, $img->top_margin, $img->bottom_margin);
        }
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
