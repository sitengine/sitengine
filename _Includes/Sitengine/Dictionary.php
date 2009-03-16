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


require_once 'Sitengine/Dictionary/Parser.php';
require_once 'Sitengine/Dictionary/Exception.php';


class Sitengine_Dictionary
{
    
    
    protected $_data = array();
    protected $_files = array();
    protected $_strict = false;
    
    
    public function __construct($strict = false)
    {
    	$this->_strict = $strict;
    }
    
    
    public function getData() { return $this->_data; }
    public function getFiles() { return $this->_files; }
    
    
    
    public function addFile($language, $file)
    {
        $this->_files[$language][$file] = $file;
    }
    
    
    
    public function addFiles($language, array $files)
    {
    	foreach($files as $file) {
    		 $this->_files[$language][$file] = $file;
    	}
    }
    
    
    
    public function getAvailableLanguages()
    {
        return array_keys($this->_files);
    }
    
    
    
    public function hasLanguage($language)
    {
        return isset($this->_files[$language]);
    }
    
    
    
    /**
     *
     * @throws Sitengine_Dictionary_Exception
     *
     */
    public function readFiles($language, $default = null)
    {
    	#Sitengine_Debug::print_r($this->_files);
    	if(isset($this->_files[$language])) { $files = $this->_files[$language]; }
    	else if(isset($this->_files[$default])) { $files = $this->_files[$default]; }
    	else { $files = (sizeof($this->_files) == 0) ? array() : current($this->_files); }
    	
        foreach($files as $file)
        {
        	$parser = new Sitengine_Dictionary_Parser();
        	$data = $parser->getData($file);
        	
            if(is_array($data))
            {
                foreach($data as $k => $v)
                {
                    if(!isset($this->_data[$k])) { $this->_data[$k] = $v; }
                    else { $this->_data[$k] = array_merge($this->_data[$k], $v); }
                }
            }
        }
    }
    
    
    
    /*
     * array|false getLXXX()
     * string|false getFromXXX('myString')
     *
     * The string 'Labels' in the method name can be anything
     * that exists in the dictionary file
     *
     */
    /**
     *
     * @throws Sitengine_Dictionary_Exception
     *
     */
    public function __call($element, $args)
    {
        #print $element.'<br />';
        
        if(preg_match('/^getFrom/', $element))
        {
            $group = preg_replace('/^GETFROM/', '', strtoupper($element));
            $key = (isset($args[0])) ? $args[0] : '';
            $default = (isset($args[1])) ? $args[1] : '';
            if(!$key) {
                $error = 'Missing argument 1 for '.get_class().'::'.$element.'()';
                throw new Sitengine_Dictionary_Exception($error);
            }
            else {
                if(isset($this->_data[$group][$key])) {
                    return $this->_data[$group][$key];
                }
                else {
                	if(!$this->_strict) { return $default; }
                	else {
						$error = 'Undefined dictionary item ('.$key.') in '.$group;
						throw new Sitengine_Dictionary_Exception($error);
					}
                }
            }
        }
        else if(preg_match('/^get/', $element))
        {
            $group = preg_replace('/^GET/', '', strtoupper($element));
            if(isset($this->_data[$group])) { return $this->_data[$group]; }
            else {
            	if(!$this->_strict) { return array(); }
                else {
                	$error = 'Undefined dictionary group: '.$group;
                	throw new Sitengine_Dictionary_Exception($error);
                }
            }
        }
        else if(preg_match('/^setInto/', $element))
        {
            $group = preg_replace('/^SETINTO/', '', strtoupper($element));
            $key = (isset($args[0])) ? $args[0] : '';
            $val = (isset($args[1])) ? $args[1] : '';
            
            if(!$key && !is_string($key) && !is_int($key)) {
                $error = 'Missing/Wrong argument 1 for '.get_class().'::'.$element.'()';
                throw new Sitengine_Dictionary_Exception($error);
            }
            else if(!$val && !is_string($val) && !is_int($val) && !is_array($val)) {
                $error = 'Missing/Wrong argument 2 for '.get_class().'::'.$element.'()';
                throw new Sitengine_Dictionary_Exception($error);
            }
            else {
                if(isset($this->_data[$group][$key]) && is_array($this->_data[$group][$key])) {
                    $this->_data[$group][$key] = array_merge($this->_data[$group][$key], $val);
                }
                else {
                    $this->_data[$group][$key] = $val;
                }
            }
        }
        else if(preg_match('/^set/', $element))
        {
            $group = preg_replace('/^SET/', '', strtoupper($element));
            $vals = (isset($args[0])) ? $args[0] : array();
            if(!$vals && !is_array($vals)) {
                $error = 'Missing/Wrong argument 1 for '.get_class().'::'.$element.'()';
                throw new Sitengine_Dictionary_Exception($error);
            }
            else {
                if(!isset($this->_data[$group])) { $this->_data[$group] = $vals; }
                else { $this->_data[$group] = array_merge($this->_data[$group], $vals); }
            }
        }
        else {
            $error = 'Call to undefined method '.get_class().'::'.$element.'()';
            throw new Sitengine_Dictionary_Exception($error);
        }
    }
    
}
?>