<?php
/**
 * A gantt chart label drawn in more than one font.
 *
 * JpGraph draws each piece of text in a single font. DejaVu Sans, the chart
 * font, has no Chinese, Japanese or Korean glyphs, and CJK fonts lack many
 * Latin, Greek and Cyrillic ones (accented Greek, for example). A label
 * like "Ελληνικό έργο / 日本語" is split into runs, and each run is drawn in a
 * font that has its glyphs, on one shared baseline.
 *
 * JpGraph accepts an object in place of a string in a row's columns (and in
 * a caption given as an array) and calls its GetWidth() and Stroke().
 * w2p_Output_GanttRenderer::text() builds these.
 *
 * @package web2project\output
 */
class w2p_Output_GanttText
{
    /** @var Image The chart image, used to measure text in GetWidth() */
    private $img;
    /** @var array Runs of array(text, font family, size) */
    private $runs;
    private $style;
    private $halign;
    private $valign;

    /**
     * @param Image  $img    The chart image (GanttGraph->img)
     * @param array  $runs   array(array(text, FF_* family, size), ...)
     * @param int    $style  FS_* style for every run
     * @param string $halign Horizontal alignment JpGraph uses for this label
     * @param string $valign Vertical alignment JpGraph uses for this label
     */
    public function __construct($img, array $runs, $style = FS_NORMAL, $halign = 'left', $valign = 'center')
    {
        $this->img = $img;
        $this->runs = $runs;
        $this->style = $style;
        $this->halign = $halign;
        $this->valign = $valign;
    }

    /**
     * Called by JpGraph without arguments.
     *
     * @return float Width in pixels
     */
    public function GetWidth()
    {
        $width = 0;
        foreach ($this->runs as $run) {
            $this->img->SetFont($run[1], $this->style, $run[2]);
            $width += $this->img->GetTextWidth($run[0]);
        }

        return $width;
    }

    /**
     * Draws the runs left to right on one baseline. ($x, $y) is the anchor
     * point for the alignment given to the constructor, as for a string.
     *
     * @param Image $aImg
     * @param int   $x
     * @param int   $y
     */
    public function Stroke($aImg, $x, $y)
    {
        // Measure every run relative to its baseline. GD's y axis points
        // down, so $top is negative and $bottom positive.
        $top = 0;
        $bottom = 0;
        $widths = array();
        foreach ($this->runs as $i => $run) {
            $aImg->SetFont($run[1], $this->style, $run[2]);
            $bbox = $aImg->GetBBoxTTF($run[0]);
            $top = min($top, $bbox[5], $bbox[7]);
            $bottom = max($bottom, $bbox[1], $bbox[3]);
            $widths[$i] = $aImg->GetTextWidth($run[0]);
        }
        $total = array_sum($widths);

        if ('right' == $this->halign) {
            $x -= $total;
        } elseif ('center' == $this->halign) {
            $x -= $total / 2;
        }
        if ('top' == $this->valign) {
            $baseline = $y - $top;
        } elseif ('center' == $this->valign) {
            $baseline = $y - ($top + $bottom) / 2;
        } else {
            $baseline = $y - $bottom;
        }

        // 'basepoint' puts the left end of the baseline at (x, y).
        $aImg->SetTextAlign('left', 'basepoint');
        foreach ($this->runs as $i => $run) {
            $aImg->SetFont($run[1], $this->style, $run[2]);
            $aImg->StrokeText($x, $baseline, $run[0]);
            $x += $widths[$i];
        }
        // JpGraph set this alignment for the whole row and doesn't reset it.
        $aImg->SetTextAlign($this->halign, $this->valign);
    }
}
