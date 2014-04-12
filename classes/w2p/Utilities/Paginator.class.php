<?php /* $Id$ $URL$ */

/**
 * This class takes an array of items and allows us to paginate over them. This
 *   does not change the order of the elements, it just gets a subset.
 *
 * @package web2project
 * @subpackage utilities
 *
 */

class w2p_Utilities_Paginator
{
    protected $_itemList    = array();
    protected $_itemCount   = 0;
    protected $_pagesize    = 25;
    protected $_currentPage = 1;

    public function __construct(array $items)
    {
        $this->_itemList = $items;
        $this->_itemCount = count($this->_itemList);
        $this->_pagesize = w2PgetConfig('page_size', $this->_pagesize);
    }

    /**
     * This method goes to the specified $page and gives you the next $pagesize
     *   items in an array. This keeps the original indexes intact so you can
     *   use the objects' ids safely.
     *
     * @param   int $page
     * @return  array
     */
    public function getItemsOnPage($page = 1)
    {
        $this->_currentPage = (int) $page;
        $offset = $this->_pagesize * ($this->_currentPage - 1);

        return array_slice($this->_itemList, $offset, $this->_pagesize, true);
    }

    /**
     * This generates page navigation for lists that are sensitive to the
     *   user's language ($AppUI), the module ($m), and the active tab ($tab).
     * The $params are to add extra query parameters. This is especially
     *   necessary when you have pagination within a subview.
     *
     * @param   w2p_Coretype_CAppUI $AppUI
     * @param   string              $m
     * @param   int                 $tab
     * @param   array               $params
     * @return  string
     */
    public function buildNavigation($AppUI, $m, $tab, $params = array())
    {
        $m .= count($params) ? '&' . http_build_query($params) : '';

        return buildPaginationNav($AppUI, $m, $tab,
                    $this->_itemCount, $this->_pagesize, $this->_currentPage);
    }
}