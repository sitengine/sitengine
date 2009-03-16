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


class Sitengine_Amazon_S3_Object_Response_Acl extends Sitengine_Amazon_S3_Response
{
    
    
    protected $_ownerId = null;
    protected $_ownerDisplayName = null;
    
    
    public function __construct(Zend_Http_Client $client)
    {
    	parent::__construct($client);
    	
    	if(!$client->getLastResponse()->isError())
    	{
			$xml = simplexml_load_string($client->getLastResponse()->getBody());
			
			if(isset($xml->Owner->ID))
			{
				$this->_ownerId = $xml->Owner->ID;
			}
			
			if(isset($xml->Owner->DisplayName))
			{
				$this->_ownerDisplayName = $xml->Owner->DisplayName;
			}
    	}
    }
    
    
    public function getOwnerId()
    {
    	return $this->_ownerId;
    }
    
    
    public function getOwnerDisplayName()
    {
    	return $this->_ownerDisplayName;
    }
    
    
}

?>