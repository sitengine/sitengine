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
    
    
    
    protected $_params = array();
    protected $_baseUrl = null;
    protected $_amp = '&amp;';
    protected $_paramName = null;
    
    
    public function setParams(array $params)
    {
    	$this->_params = $params;
    }
    
    
    public function setBaseUrl($baseUrl)
    {
    	$this->_baseUrl = $baseUrl;
    }
    
    
    public function setAmp($amp)
    {
    	$this->_amp = $amp;
    }
    
    
    public function setParamName($paramName)
    {
    	$this->_paramName = $paramName;
    }
    
    
    public function getBaseUrl()
    {
    	return $this->_baseUrl;
    }
    
    
    public function getHiddenElements()
    {
    	$hiddens = '';
    	require_once 'Sitengine/Form/Element.php';
    	
    	foreach($this->_params as $k => $v)
    	{
			$hiddens .= Sitengine_Form_Element::getHidden($k, $v);
		}
		
		return $hiddens;
    }
    
    
    public function getNextPageUrl()
    {
    	return $this->_makeUrl($this->getNextPage());
    }
    
    
    public function getPrevPageUrl()
    {
    	return $this->_makeUrl($this->getPrevPage());
    }
    
    
    public function getFirstPageUrl()
    {
    	return $this->_makeUrl(1);
    }
    
    
    public function getLastPageUrl()
    {
    	return $this->_makeUrl($this->getNumPages());
    }
    
    
    protected function _makeUrl($page)
    {
    	if($this->_baseUrl === null)
    	{
    		require_once 'Sitengine/Exception.php';
    		throw new Sitengine_Exception('base url has not been set');
    	}
    	
    	if($this->_paramName === null)
    	{
    		require_once 'Sitengine/Exception.php';
    		throw new Sitengine_Exception('page param name has not been set');
    	}
    	
    	$query = '';
    	
    	foreach($this->_params as $k => $v)
    	{
			$query .= ((!$query) ? '' : $this->_amp).$k.'='.$v;
		}
		
		$concat = (preg_match('/\?/', $this->_baseUrl)) ? $this->_amp : '?';
		
    	return (!$query)
    		? $this->_baseUrl.$concat.$this->_paramName.'='.$page
    		: $this->_baseUrl.$concat.$query.$this->_amp.$this->_paramName.'='.$page
    	;
    }
}
?>