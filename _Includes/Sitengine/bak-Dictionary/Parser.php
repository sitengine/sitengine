<?php


class Sitengine_Dictionary_Parser
{
	
    protected $_tagStack = array(); # stack of xml tag name attributes
    protected $_refStack = array(); # stack of references into data
    protected $_lastItem = '';
    protected $_leafName = 'item';
    
    
    public function getData($file)
    {
    	$data = array();
		$this->_tagstack = array();
		$this->_refStack = array();
		$this->_lastItem = '';
    	$this->_refStack[] =& $data;
		
        if(!is_readable($file)) 
        {
        	$error = 'Can\'t read xml file: '.$file;
        	require_once 'Sitengine/Dictionary/Exception.php';
        	throw new Sitengine_Dictionary_Exception($error);
    	}
    	else {
            $parser = xml_parser_create();
            xml_parser_set_option ($parser, XML_OPTION_CASE_FOLDING, 0);
            xml_set_object($parser, $this);
            xml_set_element_handler($parser, '_tagOpen', '_tagClose');
            xml_set_character_data_handler($parser, '_cData');
            $fp = fopen($file, 'r');
            
            while($line = fread($fp, filesize($file)))
            {
            	require_once 'Sitengine/String.php';
            	$line = Sitengine_String::runtimeStripSlashes($line);
            	if(!xml_parse($parser, $line, feof($fp)))
            	{
            		$e = "XML error: ";
            		$e .= xml_error_string(xml_get_error_code($parser));
            		$e .= ' at line ';
            		$e .= xml_get_current_line_number($parser);
            		require_once 'Sitengine/Dictionary/Exception.php';
    				throw new Sitengine_Dictionary_Exception($e);
                }
            }
            xml_parser_free($parser);
        }
    	return $data;
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
    
}
?>