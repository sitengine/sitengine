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


class Sitengine_Form_TranslationPayloads extends Sitengine_Form_Payloads
{
	
    const NAME_TRANSLATIONS_PREFIX = 'translation';
    
	protected $_translations = null;
	
	public function getTranslationNamePrefix()
	{
		return self::NAME_TRANSLATIONS_PREFIX;
	}
	
	
	public function __construct(Sitengine_Translations $translations, array $names = array())
	{
		$this->_translations = $translations;
		
		$this->_names = array_merge(array(self::NAME_MAIN), $names);
		foreach($this->_translations->get() as $index => $symbol) {
			$this->_names[] = $this->makeTranslationName($symbol);
		}
	}
	
	
	public function makeTranslationName($symbol)
	{
		return self::NAME_TRANSLATIONS_PREFIX.'_'.$symbol;
	}
    
    
    public function getDefaultTranslationName()
    {
    	$index = sizeof($this->_names) - sizeof($this->_translations->get());
    	return $this->_names[$index];
    }
    
    
    public function isDefaultTranslation()
    {
    	return ($this->_name == $this->getDefaultTranslationName());
    }
    
    
    public function getTranslationIndex()
    {
    	if($this->_name != self::NAME_MAIN)
		{
			$symbol = preg_replace('/'.self::NAME_TRANSLATIONS_PREFIX.'_(.*)/', "$1", $this->_name);
			return $this->_translations->getIndexBySymbol($symbol);
		}
		return $this->_translations->getDefaultIndex();
    }
    
    
    public function getTranslationSymbol()
    {
    	if($this->_name != self::NAME_MAIN)
		{
			return preg_replace('/'.self::NAME_TRANSLATIONS_PREFIX.'_(.*)/', "$1", $this->_name);
		}
		return $this->_translations->getDefaultSymbol();
    }
    
}


?>