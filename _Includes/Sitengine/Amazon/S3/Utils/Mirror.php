<?php


class Sitengine_Amazon_S3_Utils_Mirror
{
	
	protected $_connection = null;
	protected $_targetBucketName = null;
	protected $_force = false; # overwrite destination if exists
	protected $_printMessages = false;
	protected $_sourceBucket = null;
	protected $_excludePatterns = array();
	
	protected $_countKeys = 0;
	protected $_countExcluded = 0;
	protected $_countSkipped = 0;
	protected $_countReplaced = 0;
	protected $_countCopied = 0;
	protected $_countErrors = 0;
	
	
	
	public function __construct($accessKey, $secretKey, $sourceBucketName, $targetBucketName)
	{
		require_once 'Sitengine/Amazon/S3.php';
		$this->_connection = new Sitengine_Amazon_S3($accessKey, $secretKey);
		
		require_once 'Sitengine/Amazon/S3/Bucket.php';
		$this->_sourceBucket = new Sitengine_Amazon_S3_Bucket($this->_connection, $sourceBucketName);
		
		$this->_targetBucketName = $targetBucketName;
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
	
	
	
	
	public function force($onOff)
	{
		$this->_force = $onOff;
	}
	
	
	
	public function printSummary()
	{
		print "=====================================\n";
		print "EXCLUDED: {$this->_countExcluded}\n";
		print "SKIPPED: {$this->_countSkipped}\n";
		print "REPLACED: {$this->_countReplaced}\n";
		print "COPIED: {$this->_countCopied}\n";
		print "ERRORS: {$this->_countErrors}\n";
		print "TOTAL: {$this->_countKeys}\n";
		print "=====================================\n";
	}
	
	
	
	
	public function run($prefix = '', $targetPrefix = '', $headers = array(), $amzHeaders = array())
	{
		$this->_countKeys = 0;
		$this->_countExcluded = 0;
		$this->_countSkipped = 0;
		$this->_countReplaced = 0;
		$this->_countCopied = 0;
		$this->_countErrors = 0;
		
		$response = $this->_sourceBucket->get($prefix);
		$this->_copyKeys($response->getKeys(), $targetPrefix, $headers, $amzHeaders);
		
		while($response->isTruncated())
		{
			# traverse pages (1000 keys per page)
			$response = $this->_sourceBucket->get($prefix, $response->getLastKey());
			$this->_copyKeys($response->getKeys(), $targetPrefix, $headers, $amzHeaders);
		}
		
		return array(
			'excluded' => $this->_countExcluded,
			'skipped' => $this->_countSkipped,
			'replaced' => $this->_countReplaced,
			'copied' => $this->_countCopied,
			'errors' => $this->_countErrors,
			'keys' => $this->_countKeys
		);
	}
	
	
	
	protected $_targetRenamePatternFind = null;
	protected $_targetRenamePatternReplace = null;
	
	
	public function setTargetRenamePattern($find, $replace)
	{
		$this->_targetRenamePatternFind = $find;
		$this->_targetRenamePatternReplace = $replace;
	}
	
	
	
	
	protected function _copyKeys(
		array $keys,
		$targetPrefix,
		array $headers = array(),
		array $amzHeaders = array()
	)
	{
		foreach($keys as $key)
		{
			$currAmzHeaders = $amzHeaders;
			$this->_countKeys++;
			
			require_once 'Sitengine/Mime/Type.php';
			$mime = Sitengine_Mime_Type::get($key);
			$targetKey = $targetPrefix.$key;
			
			if($this->_targetRenamePatternFind !== null && $this->_targetRenamePatternReplace !== null)
			{
				$targetKey = preg_replace(
					$this->_targetRenamePatternFind,
					$this->_targetRenamePatternReplace,
					$targetKey
				);
			}
			
			try {
				$this->_print('copying: '.$key.' -> '.$targetKey);
				
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
				$targetObject = new Sitengine_Amazon_S3_Object($this->_connection, $this->_targetBucketName, $targetKey);
				$head = null;
				
				if(!$this->_force)
				{
					# check if target exists
					$head = $targetObject->head();
					
					if($head->getHttpResponse()->getStatus() == 200)
					{
						$eTag = $head->getClient()->getLastResponse()->getHeader('Etag');
						
						if($eTag !== null)
						{
							# set pre-condition
							$currAmzHeaders[] = "x-amz-copy-source-if-none-match: $eTag";
						}
						/*
						$this->_print(" -> TARGET EXISTS (SKIPPED)\n");
						$this->_countSkipped++;
						continue;
						*/
					}
				}
				
				$response = $targetObject->copy(
					$this->_sourceBucket->getName(),
					$key,
					$mime,
					$headers,
					$currAmzHeaders
				);
				
				
				$code = $response->getHttpResponse()->extractCode(
					$response->getHttpResponse()->getHeadersAsString()
				);
				
				if($code == 412)
				{
					$this->_print(" -> SKIPPED (EXISTS)\n");
					$this->_countSkipped++;
					continue;
				}
				
				if($response->isError())
				{
					# try again
					$response = $targetObject->copy(
						$this->_sourceBucket->getName(),
						$key,
						$mime,
						$headers,
						$currAmzHeaders
					);
					
					if($response->isError())
					{
						#print $response->getAmzErrorMessage()."\n";
						require_once 'Sitengine/Amazon/S3/Utils/Exception.php';
						throw new Sitengine_Amazon_S3_Utils_Exception('Copy Error');
					}
				}
				
				if($head !== null && $head->getHttpResponse()->getStatus() == 200)
				{
					$this->_countReplaced++;
					$this->_print(" -> REPLACE OK\n");
				}
				else {
					$this->_countCopied++;
					$this->_print(" -> COPY OK\n");
				}
				
				
				#$this->_countCopied++;
				#$this->_print(" -> OK\n");
				#$response = $targetObject->head($key);
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