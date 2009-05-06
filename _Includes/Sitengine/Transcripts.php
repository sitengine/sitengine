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



class Sitengine_Transcripts
{
    
    protected $_index = 0;
    protected $_defaultIndex = 0;
    protected $_transcripts = array();
    
    
    public function __construct(array $transcripts)
    {
        $this->_transcripts = $transcripts;
    }
    
    
    public function setLanguage($symbol)
    {
		$this->_index = $this->getIndexBySymbol($symbol);
    }
    
    
    public function get()
    {
        return $this->_transcripts;
    }
   	
   	/*
   	public function resetIndex()
   	{
   		$this->_index = $this->_defaultIndex;
   	}
    
    
    public function setIndex($index)
    {
        $this->_index = $this->_checkIndex($index);
    }
    */
    
    public function getIndex()
    {
        return $this->_index;
    }
    
    
    public function getSymbol()
    {
    	return $this->_transcripts[$this->_index];
    }
    
    
    public function getDefaultIndex()
    {
        return $this->_defaultIndex;
    }
    
    
    public function getDefaultSymbol()
    {
        return $this->_transcripts[$this->_defaultIndex];
    }
    
    
    public function isDefault()
    {
    	return ($this->_index == $this->_defaultIndex);
    }
    
    
    public function getIndexBySymbol($symbol)
    {
        $index = array_search($symbol, $this->_transcripts);
        return ($index === false) ? 0 : $index;
    }
    
    
    public function getSymbolByIndex($index)
    {
        return $this->_transcripts[$index];
    }
    
}
?>