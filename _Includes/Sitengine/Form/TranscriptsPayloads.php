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
 * @package    Sitengine_Form
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


require_once 'Sitengine/Form/Payloads.php';


class Sitengine_Form_TranscriptsPayloads extends Sitengine_Form_Payloads
{
	
    const NAME_TRANSCRIPTS_PREFIX = 'transcript';
    
	protected $_transcripts = null;
	
	public function getTranscriptNamePrefix()
	{
		return self::NAME_TRANSCRIPTS_PREFIX;
	}
	
	
	public function __construct(Sitengine_Transcripts $transcripts, array $names = array())
	{
		$this->_transcripts = $transcripts;
		
		$this->_names = array_merge(array(self::NAME_MAIN), $names);
		foreach($this->_transcripts->get() as $index => $symbol) {
			$this->_names[] = $this->makeTranscriptName($symbol);
		}
	}
	
	
	public function makeTranscriptName($symbol)
	{
		return self::NAME_TRANSCRIPTS_PREFIX.'_'.$symbol;
	}
    
    
    public function getDefaultTranscriptName()
    {
    	$index = sizeof($this->_names) - sizeof($this->_transcripts->get());
    	return $this->_names[$index];
    }
    
    
    public function isDefaultTranscript()
    {
    	return ($this->_name == $this->getDefaultTranscriptName());
    }
    
    
    public function getTranscriptIndex()
    {
    	if($this->_name != self::NAME_MAIN)
		{
			$symbol = preg_replace('/'.self::NAME_TRANSCRIPTS_PREFIX.'_(.*)/', "$1", $this->_name);
			return $this->_transcripts->getIndexBySymbol($symbol);
		}
		return $this->_transcripts->getDefaultIndex();
    }
    
    
    public function getTranscriptSymbol()
    {
    	if($this->_name != self::NAME_MAIN)
		{
			return preg_replace('/'.self::NAME_TRANSCRIPTS_PREFIX.'_(.*)/', "$1", $this->_name);
		}
		return $this->_transcripts->getDefaultSymbol();
    }
    
}


?>