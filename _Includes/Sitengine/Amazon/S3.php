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
 
class Sitengine_Amazon_S3
{
	
	const DATE_FORMAT = 'D, d M Y G:i:s T';
	
	
	protected $_accessKey = null;
	protected $_secretKey = null;
	
    
    public function __construct($accessKey, $secretKey)
    {
    	$this->_accessKey = $accessKey;
    	$this->_secretKey = $secretKey;
    }
    
    
    public function getAccessKey()
    {
    	return $this->_accessKey;
    }
    
    
    public function getSecretKey()
    {
    	return $this->_secretKey;
    }
    
    
    public static function getClient(Sitengine_Amazon_S3_Header $header, $body = '')
    {
        require_once 'Zend/Http/Client.php';
		$client = new Zend_Http_Client($header->getUrl());
		$client->setRawData($body);
		
		foreach($header->toArray() as $h)
		{
			$client->setHeaders($h);
		}
		return $client;
    }
    
    
    public static function getUrl($bucket, $key = '', $query = '', $cname = false, $ssl = false)
    {
    	$key = ($key) ? '/'.$key : '';
    	$query = ($query) ? '?'.$query : '';
    	$url = ($cname) ? "http://$bucket$key$query" : "http://$bucket.s3.amazonaws.com$key$query";
		return ($ssl) ? "https://$bucket.s3.amazonaws.com$key$query" : $url;
    }
}

?>