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

class Sitengine_Amazon_S3_Header
{
	
	const CANNED_ACL_NAME = 'x-amz-acl';
	const ACL_PRIVATE = 'private';
	const ACL_PUBLIC_READ = 'public-read';
	const ACL_PUBLIC_READ_WRITE = 'public-read-write';
	const ACL_AUTHENTICATED_READ = 'authenticated-read';
	const COPY_SOURCE = 'x-amz-copy-source';
	const METADATA_DIRECTIVE = 'x-amz-metadata-directive';
	const METADATA_DIRECTIVE_COPY = 'COPY';
	const METADATA_DIRECTIVE_REPLACE = 'REPLACE';
	
	
	protected $_type = null;
	protected $_url = null;
	protected $_headers = array();
	protected $_amzs = array();
    
    
    public function getAmzs()
    {
    	$amzs = array();
    	$final = array();
    	
    	foreach($this->_amzs as $h)
    	{
    		if(preg_match('/^x-amz/i', $h))
    		{
				$n = strtolower(trim(preg_replace('/^(x-amz-[a-zA-Z0-9-]*):.*/i', "$1", $h)));
				$v = trim(preg_replace('/^x-amz-[a-zA-Z0-9-]*:(.*)/i', "$1", $h));
				$v = preg_replace('/[\t\n\r\s]+/', ' ', $v);
				if(isset($amzs[$n])) { $amzs[$n] .= ','.$v; }
				else { $amzs[$n] = $v; }
			}
    	}
    	ksort($amzs);
    	foreach($amzs as $n => $v) { $final[] = $n.':'.$v; }
    	return $final;
    }
    
    
    public function getCanonicalizedAmzs()
    {
    	$canonicalized = '';
    	foreach($this->getAmzs() as $v)
    	{
    		$canonicalized .= $v."\n";
    	}
    	return $canonicalized;
    }
    
    
    public function toArray($amzOnly = false)
    {
        return ($amzOnly) ? $this->getAmzs() : array_merge($this->getAmzs(), $this->_headers);
    }
 
    
    public function setType($type)
    {
        $this->_type = $type;
        return $this;
    }
     
    
    public function getType()
    {
    	return $this->_type;
    }
 
    
    public function add($s)
    {
    	$this->_headers[] = $s;
    	return $this;
    }
    
    
    public function setUrl($url)
    {
    	$this->_url = $url;
    	return $this;
    }
    
    
    public function getUrl()
    {
    	return $this->_url;
    }
    
    
    public function addAmz($s)
    {
    	$this->_amzs[] = $s;
    	return $this;
    }
    
}

?>