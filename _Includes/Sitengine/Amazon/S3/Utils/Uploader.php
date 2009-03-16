<?php


class Sitengine_Amazon_S3_Utils_Uploader
{
	
	protected $_connection = null;
	protected $_pathName = null;
	protected $_bucketName = null;
	protected $_force = false; # overwrite destination if exists
	protected $_printMessages = false;
	protected $_excludePatterns = array();
	
	protected $_countKeys = 0;
	#protected $_countExcluded = 0;
	protected $_countSkipped = 0;
	protected $_countReplaced = 0;
	protected $_countCopied = 0;
	protected $_countErrors = 0;
	
	protected $_countExisting = 0;
	protected $_countMissing = 0;
	
	
	
	public function __construct($accessKey, $secretKey, $pathName, $bucketName)
	{
		require_once 'Sitengine/Amazon/S3.php';
		$this->_connection = new Sitengine_Amazon_S3($accessKey, $secretKey);
		
		$this->_pathName = $pathName;
		$this->_bucketName = $bucketName;
	}
	
	
	/*
	public function addExcludePattern($pattern)
	{
		$this->_excludePatterns[] = $pattern;
	}
	
	
	
	public function setExcludePattern(array $patterns)
	{
		$this->_excludePatterns = $patterns;
	}
	*/
	
	
	
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
		#print "EXCLUDED: {$this->_countExcluded}\n";
		print "SKIPPED: {$this->_countSkipped}\n";
		print "REPLACED: {$this->_countReplaced}\n";
		print "COPIED: {$this->_countCopied}\n";
		print "ERRORS: {$this->_countErrors}\n";
		print "TOTAL: {$this->_countKeys}\n";
		print "=====================================\n";
	}
	
	
	
	
	
	public function run($prefix, array $amzHeaders)
	{
		$this->_countSkipped = 0;
		$this->_countReplaced = 0;
		$this->_countCopied = 0;
		$this->_countErrors = 0;
		$this->_countKeys = 0;
		
		$uploadDir = dir($this->_pathName);
		
		while (false !== ($file = $uploadDir->read()))
		{
			if(preg_match('/^\.(\.|DS_Store)?$/', $file)) { continue; }
			
			try {
				$this->_countKeys++;
				$key = ($prefix) ? $prefix.'/'.$file : $file;
				$inFile = $this->_pathName.'/'.$file;
				
				$this->_print('uploading: '.$file.' -> '.$key);
				
				require_once 'Sitengine/Amazon/S3/Object.php';
				$object = new Sitengine_Amazon_S3_Object(
					$this->_connection,
					$this->_bucketName,
					$key
				);
				
				$head = null;
				
				if(!$this->_force)
				{
					$head = $object->head();
					
					if($head->getHttpResponse()->getStatus() == 200)
					{
						$size = $head->getClient()->getLastResponse()->getHeader('Content-length');
						
						if($size !== null && $size == filesize($inFile))
						{
							$this->_print(" -> SKIPPED (EXISTS)\n");
							$this->_countSkipped++;
							continue;
						}
					}
				}
				
				$put = $object->put($inFile, array(), $amzHeaders);
				
				if($put->getHttpResponse()->isError())
				{
					# try again
					$put = $object->put($inFile, array(), $amzHeaders);
					
					if($put->getHttpResponse()->isError())
					{
						require_once 'Sitengine/Amazon/S3/Utils/Exception.php';
						throw new Sitengine_Amazon_S3_Utils_Exception('Upload Error');
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
			}
			catch (Exception $exception)
			{
				$this->_countErrors++;
				$msg = " -> ERROR (".$exception->getMessage().")\n";
				$this->_print($msg);
			}
		}
		
		$uploadDir->close();
		
		return array(
			'skipped' => $this->_countSkipped,
			'replaced' => $this->_countReplaced,
			'copied' => $this->_countCopied,
			'errors' => $this->_countErrors,
			'keys' => $this->_countKeys
		);
	}
	
	
	
	
	
	
	public function printCheckSummary()
	{
		print "=====================================\n";
		#print "EXCLUDED: {$this->_countExcluded}\n";
		print "EXISTING: {$this->_countExisting}\n";
		print "MISSING: {$this->_countMissing}\n";
		print "CHANGED: {$this->_countChanged}\n";
		print "ERRORS: {$this->_countErrors}\n";
		print "TOTAL: {$this->_countKeys}\n";
		print "=====================================\n";
	}
	
	
	
	
	public function check($prefix)
	{
		$this->_countExisting = 0;
		$this->_countMissing = 0;
		$this->_countChanged = 0;
		$this->_countErrors = 0;
		$this->_countKeys = 0;
		
		$uploadDir = dir($this->_pathName);
		
		while (false !== ($file = $uploadDir->read()))
		{
			if(preg_match('/^\.(\.|DS_Store)?$/', $file)) { continue; }
			
			try {
				$this->_countKeys++;
				$key = ($prefix) ? $prefix.'/'.$file : $file;
				$inFile = $this->_pathName.'/'.$file;
				
				$this->_print('checking: '.$file.' -> '.$key);
				
				require_once 'Sitengine/Amazon/S3/Object.php';
				$object = new Sitengine_Amazon_S3_Object(
					$this->_connection,
					$this->_bucketName,
					$key
				);
				
				$head = $object->head();
				
				if($head->getHttpResponse()->getStatus() == 200)
				{
					$size = $head->getClient()->getLastResponse()->getHeader('Content-length');
					
					if($size !== null && $size == filesize($inFile))
					{
						$this->_print(" -> EXISTS\n");
						$this->_countExisting++;
						continue;
					}
					
					$this->_print(" -> CHANGED\n");
					$this->_countChanged++;
					continue;
				}
				
				if($head->getHttpResponse()->getStatus() == 404)
				{
					$this->_print(" -> MISSING\n");
					$this->_countMissing++;
					continue;
				}
				
				if($head->getHttpResponse()->isError())
				{
					require_once 'Sitengine/Amazon/S3/Utils/Exception.php';
					throw new Sitengine_Amazon_S3_Utils_Exception('Check Upload Error');
				}
			}
			catch (Exception $exception)
			{
				$this->_countErrors++;
				$msg = " -> ERROR (".$exception->getMessage().")\n";
				$this->_print($msg);
			}
		}
		
		$uploadDir->close();
		
		return array(
			'existing' => $this->_countExisting,
			'missing' => $this->_countMissing,
			'changed' => $this->_countChanged,
			'errors' => $this->_countErrors,
			'keys' => $this->_countKeys
		);
	}
	
	
	
	
	
	protected function _print($msg)
	{
		if($this->_printMessages) { print $msg; }
	}
	

}

?>