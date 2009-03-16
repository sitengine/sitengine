<?php

/**
 * Sitengine - An example serving as a pattern
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @category   Sitengine
 * @package    Sitengine
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


/**
 * Calculate indices and create navigation element to be able
 * to browse large lists of items/rows in smaller portions
 */


class Sitengine_Grid_Pager
{
    
    
    protected $_numItems = null;
    protected $_offset = null;
    protected $_firstItem = null;
    protected $_lastItem = null;
    protected $_itemsPerPage = null;
    protected $_itemsOnCurrentPage = null;
    protected $_numPages = null;
    protected $_currPage = null;
    protected $_prevPage = null;
    protected $_nextPage = null;
    
    
    public function __construct($currPage, $itemsPerPage)
    {
        $this->_currPage = (strval(intval($currPage))) ? $currPage : 1;
        $this->_itemsPerPage = (strval(intval($itemsPerPage))) ? $itemsPerPage : 20;
        $this->_offset = ($this->_currPage-1)*$this->_itemsPerPage;
    }    
        
        
    public function calculate($numItems)
    {    
        $this->_numItems = (strval(intval($numItems))) ? $numItems : 0;
        
        # calculate
        $this->_numPages = ceil($this->_numItems/$this->_itemsPerPage);
        $this->_currPage = ($this->_currPage <= $this->_numPages) ? $this->_currPage : 1;
        $this->_prevPage = ($this->_currPage==1) ? $this->_numPages : $this->_currPage-1;
        $this->_nextPage = ($this->_currPage==$this->_numPages) ? 1 : $this->_currPage+1;
        $this->_offset = ($this->_currPage-1)*$this->_itemsPerPage;
        
        # how many items on current page
        if($this->_numItems==0) {
            $this->_itemsOnCurrentPage = 0;
        }
        else if($this->_numItems==$this->_itemsPerPage) {
            $this->_itemsOnCurrentPage = $this->_itemsPerPage;
        }
        else if ($this->_currPage==$this->_numPages) {
            $this->_itemsOnCurrentPage = $this->_numItems % $this->_itemsPerPage;
        }
        else {
            $this->_itemsOnCurrentPage = $this->_itemsPerPage;
        }
        
        # first/last item
        $this->_firstItem = ($this->_numItems>0) ? $this->_offset+1 : 0;
        $this->_lastItem = ($this->_itemsOnCurrentPage==0) ? $this->_offset+$this->_itemsPerPage : $this->_offset+$this->_itemsOnCurrentPage;
        $this->_lastItem = ($this->_numItems==0) ? 0 : $this->_lastItem;
    }
    
    
    
    public function calculateSimple($hasNextPage)
	{
		$this->_currPage = (is_numeric($this->_currPage)) ? $this->_currPage : 1;
		$this->_currPage = ($this->_currPage >= 1) ? $this->_currPage : 1;
		$this->_nextPage = ($hasNextPage) ? $this->_currPage+1 : 1;
		$this->_prevPage = ($this->_currPage > 1) ? $this->_currPage-1 : 1;
	}
	
    
    
    public function getNumItems() { return $this->_numItems; }
    public function getOffset() { return $this->_offset; }
    public function getFirstItem() { return $this->_firstItem; }
    public function getLastItem() { return $this->_lastItem; }
    public function getItemsPerPage() { return $this->_itemsPerPage; }
    public function getItemsOnCurrPage() { return $this->_itemsOnCurrentPage; }
    public function getNumPages() { return $this->_numPages; }
    public function getCurrPage() { return $this->_currPage; }
    public function getPrevPage() { return $this->_prevPage; }
    public function getNextPage() { return $this->_nextPage; }
    
    
    public function printSelf()
    {
        $s  = "<table width=600 border=1>";
        $s .= "<tr><td colspan=2><h2>Pager</h2></td></tr>";
        $s .= "<tr><td>numItems</td><td>".$this->_numItems."&nbsp;</td></tr>";
        $s .= "<tr><td>offset</td><td>".$this->_offset."&nbsp;</td></tr>";
        $s .= "<tr><td>itemsPerPage</td><td>".$this->_itemsPerPage."&nbsp;</td></tr>";
        $s .= "<tr><td>itemsOnCurrentPage</td><td>".$this->_itemsOnCurrentPage."&nbsp;</td></tr>";
        $s .= "<tr><td>prev page</td><td>".$this->_prevPage."&nbsp;</td></tr>";
        $s .= "<tr><td>currPage</td><td>".$this->_currPage."&nbsp;</td></tr>";
        $s .= "<tr><td>next page</td><td>".$this->_nextPage."&nbsp;</td></tr>";
        $s .= "<tr><td>numPages</td><td>".$this->_numPages."&nbsp;</td></tr>";
        $s .= "<tr><td>firstItem</td><td>".$this->_firstItem."&nbsp;</td></tr>";
        $s .= "<tr><td>lastItem</td><td>".$this->_lastItem."&nbsp;</td></tr>";
        $s .= "<tr><td>lastItem</td><td>".$this->_lastItem."&nbsp;</td></tr>";
        $s .= "</table><br />";
        print $s;
    }
}
?>