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


require_once 'Zend/Translate/Adapter/Xliff.php';


class Sitengine_Translate_Adapter_Xliff extends Zend_Translate_Adapter_Xliff
{
    
	public function getTranslationTable()
	{
		return $this->_translate;
	}
	
	
	protected function _merge(Sitengine_Translate_Adapter_Xliff $adapter)
	{
		$tables = $adapter->getTranslationTable();
		
		foreach($this->_translate as $lang => $units)
		{
			if(array_key_exists($lang, $tables))
			{
				$this->_translate[$lang] = array_merge($this->_translate[$lang], $tables[$lang]);
			}
			else {
				$this->_translate[$lang] = $this->_translate[$lang];
			}
		}
		
		foreach($tables as $lang => $units)
		{
			if(array_key_exists($lang, $this->_translate))
			{
				$this->_translate[$lang] = array_merge($this->_translate[$lang], $tables[$lang]);
			}
			else {
				$this->_translate[$lang] = $tables[$lang];
			}
		}
	}
	
	
	public function addMergeTranslation(array $files, $locale = null, array $options = array())
	{
		foreach($files as $file)
		{
			$translate = new Sitengine_Translate(Sitengine_Translate::AN_XLIFF, $file, $locale, $options);
			$this->_merge($translate->getAdapter());
		}
	}
}
?>