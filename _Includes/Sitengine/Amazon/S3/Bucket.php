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

class Sitengine_Amazon_S3_Bucket
{
     
    
    protected $_connection = null;
    protected $_name = null;
    protected $_cname = false;
    protected $_ssl = false;
    
    
    public function __construct(Sitengine_Amazon_S3 $connection, $name, $cname = false, $ssl = false)
    {
    	$this->_connection = $connection;
    	$this->_name = $name;
    	$this->_cname = $cname;
    	$this->_ssl = $ssl;
    }
    
    
    
    public function getConnection()
    {
    	return $this->_connection;
    }
    
    
    
    public function getName()
    {
    	return $this->_name;
    }
    
    
     
   	
    public function put()
    {
    }
    
    
    public function get($prefix = '', $marker = '', $maxKeys = '', $delimiter = '')
    {
    	try {
			require_once 'Sitengine/Amazon/S3/Header.php';
			$header = new Sitengine_Amazon_S3_Header();
			require_once 'Sitengine/Amazon/S3/Authentication.php';
			$authentication = new Sitengine_Amazon_S3_Authentication($this->_connection);
			
			$query = '';
			$args = array(
				'prefix' => $prefix,
				'marker' => $marker,
				'max-keys' => $maxKeys,
				'delimiter' => $delimiter
			);
			foreach($args as $n => $v) {
				$query .= ($v !== '') ? (($query) ? '&' : '').$n.'='.$v : '';
			}
			
			$verb = 'GET';
			$date = gmdate(Sitengine_Amazon_S3::DATE_FORMAT);
			$md5 = '';
			$mime = '';
			$key = '';
			
			$authHeader = $authentication->generateSignature(
				$verb,
				$md5,
				$mime,
				$date,
				'',
				'/'.$this->_name.'/'
			);
			
			$header
				->setType($verb)
				->setUrl(Sitengine_Amazon_S3::getUrl($this->_name, $key, $query, $this->_cname, $this->_ssl))
				->add('Authorization: '.$authHeader)
				->add('Date: '.$date)
			;
			
			$client = Sitengine_Amazon_S3::getClient($header);
			$response = $client->request($verb);
			#Sitengine_Debug::print_r($client->getLastRequest());
			require_once 'Sitengine/Amazon/S3/Bucket/Response/Get.php';
			return new Sitengine_Amazon_S3_Bucket_Response_Get($client);
		}
		catch (Exception $exception) {
			require_once 'Sitengine/Amazon/S3/Exception.php';
        	throw new Sitengine_Amazon_S3_Exception('head object error', $exception);
		}
    }
     
    
    public function delete()
    {
    }
    
}

?>