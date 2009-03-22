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


require_once 'Zend/Translate/Adapter.php';


class Sitengine_Translate_Adapter_Xml extends Zend_Translate_Adapter
{
    
    
    protected $_tagStack = array(); # stack of xml tag name attributes
    protected $_refStack = array(); # stack of references into data
    protected $_lastItem = '';
    protected $_leafName = 'item';
    
    
    
    public function __construct($data, $locale = null, array $options = array())
    {
        parent::__construct($data, $locale, $options);
    }
    
    
    
    protected function _loadTranslationData($filename, $locale, array $options = array())
    {
        $options = $options + $this->_options;

        if($options['clear']) {
            $this->_translate = array();
        }
        
        if(!is_readable($filename)) {
            require_once 'Zend/Translate/Exception.php';
            throw new Zend_Translate_Exception('Translation file \'' . $filename . '\' is not readable.');
        }
        
        $data = array();
		$this->_tagstack = array();
		$this->_refStack = array();
		$this->_lastItem = '';
    	$this->_refStack[] =& $data;
		
        if(!is_readable($filename)) 
        {
        	$error = 'Can\'t read xml file: '.$filename;
        	require_once 'Zend/Translate/Exception.php';
        	throw new Zend_Translate_Exception($error);
    	}
    	
		$parser = xml_parser_create($this->_findEncoding($filename));
		xml_parser_set_option ($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_set_object($parser, $this);
		xml_set_element_handler($parser, '_tagOpen', '_tagClose');
		xml_set_character_data_handler($parser, '_cData');
		$fp = fopen($filename, 'r');
		
		while($line = fread($fp, filesize($filename)))
		{
			#require_once 'Sitengine/String.php';
			#$line = Sitengine_String::runtimeStripSlashes($line);
			if(!xml_parse($parser, $line, feof($fp)))
			{
				$e = "XML error: ";
				$e .= xml_error_string(xml_get_error_code($parser));
				$e .= ' at line ';
				$e .= xml_get_current_line_number($parser);
				require_once 'Zend/Translate/Exception.php';
				throw new Zend_Translate_Exception($e);
			}
		}
		xml_parser_free($parser);
    	$this->_translate[$locale] = $data;
    }
    
    
    protected function _tagOpen($parser, $element, $attr)
    {
    	if($element != $this->_leafName)
    	{
    		#print $attr['name'].'<br />';
    		array_push($this->_tagstack, $attr['name']);
    		if(sizeof($this->_tagstack) > 1)
    		{
    			$last =& $this->_refStack[sizeof($this->_refStack)-1];
    			$last[$attr['name']] = array();
    			$this->_refStack[] =& $last[$attr['name']];
    		}
    	}
    	else {
    		$this->_lastItem = $attr['name'];
    	}
    }
    
    
    protected function _tagClose($parser, $element)
    {
    	if($element != $this->_leafName)
    	{
    		array_pop($this->_tagstack);
    		array_pop($this->_refStack);
    	}
    	$this->_lastItem = '';
    }
    
    
    protected function _cData($parser, $data)
    {
    	if($this->_lastItem != '')
    	{
    		$last =& $this->_refStack[sizeof($this->_refStack)-1];
    		# data arrives line by line so we need to concatenate multiple lines
    		if(!isset($last[$this->_lastItem]))
    		{
    			$last[$this->_lastItem] = $data;
    		}
    		else {
    			$last[$this->_lastItem] .= $data;
    		}
    	}
    }
    
    
    private function _findEncoding($filename)
    {
        $file = file_get_contents($filename, null, null, 0, 100);
        if(strpos($file, "encoding") !== false) {
            $encoding = substr($file, strpos($file, "encoding") + 9);
            $encoding = substr($encoding, 1, strpos($encoding, $encoding[0], 1) - 1);
            return $encoding;
        }
        return 'UTF-8';
    }
    
    
    public function toString()
    {
        return "Xml";
    }
    
    
	public function getTranslationTable()
	{
		return $this->_translate;
	}
	
	
	public function getAvailableLanguages()
	{
		$languages = array();
		foreach($this->_translate as $language => $data)
		{
			$languages[] = $language;
		}
		return $languages;
	}
	
	
	protected function _merge(Sitengine_Translate_Adapter_Xml $adapter)
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
			$translate = new Sitengine_Translate(Sitengine_Translate::AN_XML, $file, $locale, $options);
			$this->_merge($translate->getAdapter());
		}
	}
	
	
	
	public function translateGroup($prefix, $locale = null)
	{
		if($locale === null)
		{
            $locale = $this->_options['locale'];
        }
        
        $locale = (string) $locale;
        $group = array();
        
        if(isset($this->_translate[$locale]))
        {
        	$group = $this->_getGroup($prefix, $locale);
        }
        
        if(!sizeof($group))
        {
			# take first locale in translation table
			foreach($this->_translate as $locale => $units)
			{
				require_once 'Sitengine/DataObject.php';
				return new Sitengine_DataObject($this->_getGroup($prefix, $locale));
			}
		}
		require_once 'Sitengine/DataObject.php';
        return new Sitengine_DataObject($group);
	}
	
	
	
	protected function _getGroup($prefix, $locale)
	{
		$group = array();
        
		foreach($this->_translate[$locale] as $key => $message)
		{
			if(preg_match('/^'.$prefix.'(.*)/', $key, $matches))
			{
				$first = mb_convert_case(mb_substr($matches[1], 0, 1), MB_CASE_LOWER);
				$id = preg_replace('/^./', $first, $matches[1]);
				$group[$id] = $message;
			}
		}
		return $group;
	}
	
}
?>