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

class Sitengine_Amazon_S3_Response
{
    
    protected $_xml = null;
    protected $_client = null;
    
    
    public function __construct(Zend_Http_Client $client)
    {
    	$this->_client = $client;
		$this->_xml = simplexml_load_string($client->getLastResponse()->getBody());
    }
    
    
    
    public function getClient()
    {
    	return $this->_client;
    }
    
    
    
    public function getXml()
    {
    	return $this->_xml;
    }
    
    
    
    public function getHttpRequest()
    {
    	return $this->_client->getLastRequest();
    }
    
    
    
    public function getHttpResponse()
    {
    	return $this->_client->getLastResponse();
    }
    
    
    
    public function isError()
    {
    	return (
    		$this->_client->getLastResponse()->isError() ||
    		$this->getErrorCode() !== null ||
    		$this->getErrorMessage() !== null
    	);
    }
    
    
    
    public function getErrorCode()
    {
    	if(isset($this->_xml->Code))
		{
			return $this->_xml->Code;
		}
		return null;
    }
    
    
    
    public function getErrorMessage()
    {
    	if(isset($this->_xml->Message))
		{
			return $this->_xml->Message;
		}
		return null;
    }
    
    
}

?>