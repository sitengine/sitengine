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
 
class Sitengine_Amazon_S3_Authentication
{
	
	protected $_connection = null;
	
	
    public function __construct(Sitengine_Amazon_S3 $connection)
    {
    	$this->_connection = $connection;
    }
 
    
    
    public function generateSignature($verb, $md5, $mime, $date, $canonicalizedAmzHeaders, $canonicalizedResource)
    {
    	#Sitengine_Debug::print_r(explode("\n", $canonicalizedAmzHeaders));
    	$s = $verb."\n".$md5."\n".$mime."\n".$date."\n".$canonicalizedAmzHeaders.$canonicalizedResource;
    	$s = mb_convert_encoding($s, 'UTF-8');
    	$s = hash_hmac('sha1', $s, $this->_connection->getSecretKey(), true);
		$signature = base64_encode($s);
		return 'AWS '.$this->_connection->getAccessKey().':'.$signature;
    }
    
    
    
    
    public function generateQuerySignature($bucket, $key, $expires, $argSep = '&')
    {
    	$expires = time() + $expires;
		$s = "GET\n\n\n$expires\n/$bucket/$key";
		$signature = urlencode(
			base64_encode(
				hash_hmac('sha1', $s, $this->_connection->getSecretKey(), true)
			)
		);
		
    	$args = array(
			'AWSAccessKeyId' => $this->_connection->getAccessKey(),
			'Expires' => $expires,
			'Signature' => $signature
		);
		
		$query = '';
		
		foreach($args as $name => $val) {
			$query .= ($val != '') ? (($query) ? $argSep : '').$name.'='.$val : '';
		}
		return '?'.$query;
    }
}

?>