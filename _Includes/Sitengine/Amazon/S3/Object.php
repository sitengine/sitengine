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

class Sitengine_Amazon_S3_Object
{
    
    protected $_connection = null;
    protected $_bucketName = null;
    protected $_cname = false;
    protected $_ssl = false;
    
    
    public function __construct(
    	Sitengine_Amazon_S3 $connection,
    	$bucketName,
    	$key,
    	$cname = false,
    	$ssl = false
    )
    {
    	$this->_connection = $connection;
    	$this->_bucketName = $bucketName;
    	$this->_key = $key;
    	$this->_cname = $cname;
    	$this->_ssl = $ssl;
    }
    
    
    
    public function getBucketName()
    {
    	return $this->_bucketName;
    }
    
    
    
    
    public function get($path = null, array $headers = array(), array $amzHeaders = array())
    {
    	# todo
    }
    
    
    
    
    public function put($path, array $headers = array(), array $amzHeaders = array())
    {
    	try {
			require_once 'Sitengine/Amazon/S3/Header.php';
			$header = new Sitengine_Amazon_S3_Header();
			require_once 'Sitengine/Amazon/S3/Authentication.php';
			$authentication = new Sitengine_Amazon_S3_Authentication($this->_connection);
			require_once 'Sitengine/Amazon/S3/Object/File.php';
			$file = new Sitengine_Amazon_S3_Object_File($path);
			
			foreach($amzHeaders as $h) {
				$header->addAmz($h);
			}
			
			$verb = 'PUT';
			$date = gmdate(Sitengine_Amazon_S3::DATE_FORMAT);
			$md5 = $file->getMd5();
			$mime = $file->getMime();
			$query = '';
			
			$authHeader = $authentication->generateSignature(
				$verb,
				$md5,
				$mime,
				$date,
				$header->getCanonicalizedAmzs(),
				'/'.$this->_bucketName.'/'.$this->_key
			);
			
			$header
				->setType($verb)
				->setUrl(Sitengine_Amazon_S3::getUrl($this->_bucketName, $this->_key, $query, $this->_cname, $this->_ssl))
				->add('Authorization: '.$authHeader)
				->add('Date: '.$date)
				->add('Content-Type: '.$mime)
				->add('Content-Length: '.$file->getSize())
				->add('Content-MD5: '.$md5)
				->add('Expect: 100-continue')
			;
			
			foreach($headers as $h) {
				$header->add($h);
			}
			
			$body = file_get_contents($file->getPath());
			$client = Sitengine_Amazon_S3::getClient($header, $body);
			$response = $client->request($verb);
			require_once 'Sitengine/Amazon/S3/Response.php';
			return new Sitengine_Amazon_S3_Response($client);
		}
		catch (Exception $exception) {
			throw $exception;
			#require_once 'Sitengine/Amazon/S3/Exception.php';
        	#throw new Sitengine_Amazon_S3_Exception('put object error', $exception);
		}
    }
    
    
    
    
    
    public function copy($sourceBucket, $sourceKey, $mime = '', array $headers = array(), array $amzHeaders = array())
    {
    	try {
			require_once 'Sitengine/Amazon/S3/Header.php';
			$header = new Sitengine_Amazon_S3_Header();
			$header->addAmz(Sitengine_Amazon_S3_Header::COPY_SOURCE.':/'.$sourceBucket.'/'.$sourceKey);
			require_once 'Sitengine/Amazon/S3/Authentication.php';
			$authentication = new Sitengine_Amazon_S3_Authentication($this->_connection);
			
			foreach($amzHeaders as $h) {
				$header->addAmz($h);
			}
			
			if($mime || sizeof($headers) || sizeof($amzHeaders)) { $directive = Sitengine_Amazon_S3_Header::METADATA_DIRECTIVE_REPLACE; }
			else { $directive = Sitengine_Amazon_S3_Header::METADATA_DIRECTIVE_COPY; }
			$header->addAmz(Sitengine_Amazon_S3_Header::METADATA_DIRECTIVE.':'.$directive);
			
			$verb = 'PUT';
			$date = gmdate(Sitengine_Amazon_S3::DATE_FORMAT);
			$md5 = '';
			#$mime = '';
			$query = '';
			
			$authHeader = $authentication->generateSignature(
				$verb,
				$md5,
				$mime,
				$date,
				$header->getCanonicalizedAmzs(),
				'/'.$this->_bucketName.'/'.$this->_key
			);
			
			$header
				->setType($verb)
				->setUrl(Sitengine_Amazon_S3::getUrl($this->_bucketName, $this->_key, $query, $this->_cname, $this->_ssl))
				->add('Authorization: '.$authHeader)
				->add('Content-Type: '.$mime)
				->add('Date: '.$date)
			;
			
			foreach($headers as $h) {
				$header->add($h);
			}
			
			$client = Sitengine_Amazon_S3::getClient($header);
			$response = $client->request($verb);
			require_once 'Sitengine/Amazon/S3/Response.php';
			return new Sitengine_Amazon_S3_Response($client);
		}
		catch (Exception $exception) {
			throw $exception;
			#require_once 'Sitengine/Amazon/S3/Exception.php';
        	#throw new Sitengine_Amazon_S3_Exception('copy object error', $exception);
		}
    }
    
    
    
    
    public function head()
    {
    	try {
			require_once 'Sitengine/Amazon/S3/Header.php';
			$header = new Sitengine_Amazon_S3_Header();
			require_once 'Sitengine/Amazon/S3/Authentication.php';
			$authentication = new Sitengine_Amazon_S3_Authentication($this->_connection);
			
			$verb = 'HEAD';
			$date = gmdate(Sitengine_Amazon_S3::DATE_FORMAT);
			$md5 = '';
			$mime = '';
			$query = '';
			
			$authHeader = $authentication->generateSignature(
				$verb,
				$md5,
				$mime,
				$date,
				'',
				'/'.$this->_bucketName.'/'.$this->_key
			);
			
			$header
				->setType($verb)
				->setUrl(Sitengine_Amazon_S3::getUrl($this->_bucketName, $this->_key, $query, $this->_cname, $this->_ssl))
				->add('Authorization: '.$authHeader)
				->add('Date: '.$date)
			;
			
			$client = Sitengine_Amazon_S3::getClient($header);
			$response = $client->request($verb);
			require_once 'Sitengine/Amazon/S3/Response.php';
			return new Sitengine_Amazon_S3_Response($client);
		}
		catch (Exception $exception) {
			throw $exception;
			#require_once 'Sitengine/Amazon/S3/Exception.php';
        	#throw new Sitengine_Amazon_S3_Exception('head object error', $exception);
		}
    }
    
    
    
    
    public function delete()
    {
    	try {
			require_once 'Sitengine/Amazon/S3/Header.php';
			$header = new Sitengine_Amazon_S3_Header();
			require_once 'Sitengine/Amazon/S3/Authentication.php';
			$authentication = new Sitengine_Amazon_S3_Authentication($this->_connection);
			
			$verb = 'DELETE';
			$date = gmdate(Sitengine_Amazon_S3::DATE_FORMAT);
			$md5 = '';
			$mime = '';
			$query = '';
			
			$authHeader = $authentication->generateSignature(
				$verb,
				$md5,
				$mime,
				$date,
				'',
				'/'.$this->_bucketName.'/'.$this->_key
			);
			
			$header
				->setType($verb)
				->setUrl(Sitengine_Amazon_S3::getUrl($this->_bucketName, $this->_key, $query, $this->_cname, $this->_ssl))
				->add('Authorization: '.$authHeader)
				->add('Date: '.$date)
			;
			$client = Sitengine_Amazon_S3::getClient($header);
			$response = $client->request($verb);
			require_once 'Sitengine/Amazon/S3/Response.php';
			return new Sitengine_Amazon_S3_Response($client);
		}
		catch (Exception $exception) {
			throw $exception;
			#require_once 'Sitengine/Amazon/S3/Exception.php';
        	#throw new Sitengine_Amazon_S3_Exception('delete object error', $exception);
		}
    }
    
    
    
    
    
    public function acl()
    {
    	try {
			require_once 'Sitengine/Amazon/S3/Header.php';
			$header = new Sitengine_Amazon_S3_Header();
			require_once 'Sitengine/Amazon/S3/Authentication.php';
			$authentication = new Sitengine_Amazon_S3_Authentication($this->_connection);
			
			$verb = 'GET';
			$date = gmdate(Sitengine_Amazon_S3::DATE_FORMAT);
			$md5 = '';
			$mime = '';
			$query = 'acl';
			
			$authHeader = $authentication->generateSignature(
				$verb,
				$md5,
				$mime,
				$date,
				'',
				'/'.$this->_bucketName.'/'.$this->_key.'?'.$query
			);
			
			$header
				->setType($verb)
				->setUrl(Sitengine_Amazon_S3::getUrl($this->_bucketName, $this->_key, $query, $this->_cname, $this->_ssl))
				->add('Authorization: '.$authHeader)
				->add('Date: '.$date)
			;
			
			$client = Sitengine_Amazon_S3::getClient($header);
			$response = $client->request($verb);
			require_once 'Sitengine/Amazon/S3/Object/Response/Acl.php';
			return new Sitengine_Amazon_S3_Object_Response_Acl($client);
		}
		catch (Exception $exception) {
			throw $exception;
			#require_once 'Sitengine/Amazon/S3/Exception.php';
        	#throw new Sitengine_Amazon_S3_Exception('head object error', $exception);
		}
    }
    
}


/*
##### USAGE ######
require_once 'Sitengine/Amazon/S3.php';
$connection = new Sitengine_Amazon_S3('myAccessKey','mySecretKey');
require_once 'Sitengine/Amazon/S3/Object.php';
$object = new Sitengine_Amazon_S3_Object($connection, 'myBucket', 'myKey', true);

$headers = array(
	'Content-Encoding: ',
	'Content-Disposition: ',
	'Cache-Control: ',
	'Expires: '
);

$amzHeaders = array(
	Sitengine_Amazon_S3_Header::CANNED_ACL_NAME.':'.Sitengine_Amazon_S3_Header::ACL_PUBLIC_READ
);
$response = $object->put('/my/path', $headers, $amzHeaders);
$response = $object->head();
$response = $object->delete();
*/

?>