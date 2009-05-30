<?php


class Sitengine_Amazon_S3_Utils_Acl
{
	
	protected $_connection = null;
	protected $_printMessages = false;
	protected $_bucket = null;
	protected $_excludePatterns = array();
	
	protected $_countKeys = 0;
	protected $_countExcluded = 0;
	protected $_countOk = 0;
	protected $_countErrors = 0;
	
	
	
	public function __construct($accessKey, $secretKey, $bucketName)
	{
		require_once 'Sitengine/Amazon/S3.php';
		$this->_connection = new Sitengine_Amazon_S3($accessKey, $secretKey);
		
		require_once 'Sitengine/Amazon/S3/Bucket.php';
		$this->_bucket = new Sitengine_Amazon_S3_Bucket($this->_connection, $bucketName);
	}
	
	
	
	public function addExcludePattern($pattern)
	{
		$this->_excludePatterns[] = $pattern;
	}
	
	
	
	public function setExcludePattern(array $patterns)
	{
		$this->_excludePatterns = $patterns;
	}
	
	
	
	
	public function printMessages($onOff)
	{
		$this->_printMessages = $onOff;
	}
	
	
	
	public function printSummary()
	{
		print "=====================================\n";
		print "OK: {$this->_countOk}\n";
		print "EXCLUDED: {$this->_countExcluded}\n";
		print "ERRORS: {$this->_countErrors}\n";
		print "TOTAL: {$this->_countKeys}\n";
		print "=====================================\n";
	}
	
	
	
	
	public function run($xml, $prefix = '')
	{
		$this->_countOk = 0;
		$this->_countExcluded = 0;
		$this->_countErrors = 0;
		$this->_countKeys = 0;
		
		$response = $this->_bucket->get($prefix);
		$this->_copyKeys($response->getKeys(), $xml);
		
		while($response->isTruncated())
		{
			# traverse pages (1000 keys per page)
			$response = $this->_bucket->get($prefix, $response->getLastKey());
			$this->_copyKeys($response->getKeys(), $xml);
		}
		
		return array(
			'ok' => $this->_countOk,
			'excluded' => $this->_countExcluded,
			'errors' => $this->_countErrors,
			'keys' => $this->_countKeys
		);
	}
	
	
	
	
	protected function _copyKeys(array $keys, $xml)
	{
		foreach($keys as $key)
		{
			$this->_countKeys++;
			
			try {
				$this->_print('set-acl: '.$key);
				
				$exclude = false;
				
				foreach($this->_excludePatterns as $pattern)
				{
					if(preg_match($pattern, $key)) { $exclude = true; }
				}
				
				if($exclude)
				{
					$this->_print(" -> EXCLUDED\n");
					$this->_countExcluded++;
					continue;
				}
				
				require_once 'Sitengine/Amazon/S3/Object.php';
				$object = new Sitengine_Amazon_S3_Object($this->_connection, $this->_bucket->getName(), $key);
				$head = null;
				
				$response = $object->acl($xml);
				
				if($response->isError())
				{
					# try again
					$response = $object->acl($xml);
					
					if($response->isError())
					{
						#print $response->getErrorMessage()."\n";
						require_once 'Sitengine/Amazon/S3/Utils/Exception.php';
						throw new Sitengine_Amazon_S3_Utils_Exception('Set Acl Error');
					}
				}
				
				$this->_countOk++;
				$this->_print(" -> SET-ACL OK\n");
				
				
				#$this->_countCopied++;
				#$this->_print(" -> OK\n");
				#$response = $object->head($key);
				#print $response->getHttpResponse()->asString();
			}
			catch (Exception $exception)
			{
				$this->_countErrors++;
				$msg = " -> ERROR (".$exception->getMessage().")\n";
				$this->_print($msg);
			}
		}
	}
	
	
	
	
	protected function _print($msg)
	{
		if($this->_printMessages) { print $msg; }
	}
	

}

?>