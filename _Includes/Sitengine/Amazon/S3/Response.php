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
    
    protected $_client = null;
    protected $_amzErrorCode = null;
    protected $_amzErrorMessage = null;
    
    
    public function __construct(Zend_Http_Client $client)
    {
    	if($client->getLastResponse()->isError())
    	{
    		$xml = simplexml_load_string($client->getLastResponse()->getBody());
    		
    		if(isset($xml->Code))
			{
				$this->_amzErrorCode = $xml->Code;
			}
			
			if(isset($xml->Message))
			{
				$this->_amzErrorMessage = $xml->Message;
			}
    	}
    	
    	$this->_client = $client;
    }
    
    
    
    public function getClient()
    {
    	return $this->_client;
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
    	return $this->_client->getLastResponse()->isError();
    }
    
    
    public function getAmzErrorCode()
    {
    	return $this->_amzErrorCode;
    }
    
    
    public function getAmzErrorMessage()
    {
    	return $this->_amzErrorMessage;
    }
    
    
}

?>