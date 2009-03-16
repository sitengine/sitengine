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

require_once 'Sitengine/Amazon/S3/Response.php';


class Sitengine_Amazon_S3_Bucket_Response_Get extends Sitengine_Amazon_S3_Response
{
    
    protected $_xml = null;
    
    
    public function __construct(Zend_Http_Client $client)
    {
    	parent::__construct($client);
    	
    	if(!$client->getLastResponse()->isError())
    	{
    		$this->_xml = simplexml_load_string($client->getLastResponse()->getBody());
    		#print_r($this->_xml);
		}
    }
    
    
    
    public function isTruncated()
    {
    	if(isset($this->_xml->IsTruncated))
    	{
    		return ($this->_xml->IsTruncated == 'true');
    	}
    	return false;
    }
    
    
    
    public function getKeys()
    {
    	if(!isset($this->_xml->Contents))
    	{
    		return array();
    	}
    	
    	$keys = array();
    	
		foreach($this->_xml->Contents as $key)
		{
			if(isset($key->Key))
			{
				$keys[] = trim($key->Key);
			}
		}
    	return $keys;
    }
    
    
    
    public function getLastKey()
    {
    	$keys = $this->getKeys();
    	return (sizeof($keys)) ? $keys[sizeof($keys)-1] : null;
    }
    
    
    
    public function getCommonPrefixes()
    {
    	if(!isset($this->_xml->CommonPrefixes))
    	{
    		return array();
    	}
    	
    	$prefixes = array();
    	
		foreach($this->_xml->CommonPrefixes as $prefix)
		{
			if(isset($prefix->Prefix))
			{
				$prefixes[] = trim($prefix->Prefix);
			}
		}
    	return $prefixes;
    }
    
    
}

?>