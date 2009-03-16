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
 * @package    Sitengine_Amazon_S3
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */

class Sitengine_Amazon_S3_Object_File
{
    
    protected $_path = null;
    protected $_size = null;
    protected $_mime = null;
    protected $_md5 = null;
    
    
    public function __construct($path)
    {
        $this->_path = $path;
        
        if(!is_readable($path))
        {
        	require_once 'Sitengine/Amazon/S3/Exception.php';
        	throw new Sitengine_Amazon_S3_Exception('invaid file specified');
        }
        
        require_once 'Sitengine/Mime/Type.php';
        $this->_mime = Sitengine_Mime_Type::get(preg_replace('/.*\.(\w*)$/', '$1', $path));
        $this->_size = filesize($path);
        $this->_md5 = md5_file($path);
    }
    
    
    public function getPath()
    {
    	return $this->_path;
    }
    
    
    public function getSize()
    {
    	return $this->_size;
    }
    
    
    public function getMime()
    {
    	return $this->_mime;
    }
    
    
    public function setMime()
    {
    	$this->_mime = $mime;
    }
    
    
    public function getMd5()
    {
    	$raw = '';
		for($i=0; $i < strlen($this->_md5); $i+=2)
		{
			$raw .= chr(hexdec(substr($this->_md5, $i, 2)));
		}
		return base64_encode($raw);
    }
}

?>