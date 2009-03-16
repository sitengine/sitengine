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
 * @package    Sitengine_Newsletter
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


require_once 'Sitengine/Db/TableWithS3Files.php';


class Sitengine_Newsletter_Attachments_Table extends Sitengine_Db_TableWithS3Files
{
    
    
    
    const FILE1ORIGINAL_ID = 'file1Original';
    const VALUE_NONESELECTED = 'noneSelected';
    
    
    protected $_newsletterPackage = null;
    
    
    
    
    public function __construct(array $config = array())
    {
    	if(
    		isset($config['newsletterPackage']) &&
    		$config['newsletterPackage'] instanceof Sitengine_Newsletter
    	) {
    		$this->_newsletterPackage = $config['newsletterPackage'];
    		$this->_name = $this->_newsletterPackage->getAttachmentsTableName();
    		$this->_primary = 'id';
    		
    		parent::__construct($config);
    		/*
    		$this->_files[self::FILE1ORIGINAL_ID] = array();
			
			# upload 1
			$this->_configs[self::FILE1ORIGINAL_ID] = array(
				'dir' => $this->_newsletterPackage->getAttachmentFile1OriginalDir(),
				'mode' => 0644
			);
			*/
			/*
			require_once 'Sitengine/Amazon/S3.php';
    		require_once 'Sitengine/Amazon/S3/Header.php';
			require_once 'Sitengine/Amazon/S3/Object.php';
			$config = $this->_newsletterPackage->getEnv()->getAmazonConfig('default');
			$connection = new Sitengine_Amazon_S3($config['accessKey'], $config['secretKey']);
			*/
			$config = $this->_newsletterPackage->getEnv()->getAmazonConfig('default');
			
			require_once 'Sitengine/Amazon/S3.php';
			$connection = new Sitengine_Amazon_S3($config['accessKey'], $config['secretKey']);
			
			$this->_files[self::FILE1ORIGINAL_ID] = array();
			
			# upload 1
			$this->_configs[self::FILE1ORIGINAL_ID] = array(
				'connection' => $connection,
				'bucket' => $config['bucket'],
				'cname' => $config['cname'],
				'ssl' => $this->_newsletterPackage->getAttachmentFile1OriginalSsl(),
				'prefix' => $this->_newsletterPackage->getAttachmentFile1OriginalPrefix(),
				'amzHeaders' => $this->_newsletterPackage->getAttachmentFile1OriginalAmzHeaders()
			);
    	}
    	else {
			require_once 'Sitengine/Newsletter/Exception.php';
			throw new Sitengine_Newsletter_Exception('attachments table class init error');
		}
    }
    
    
    
    public function getNewsletterPackage()
    {
    	return $this->_newsletterPackage;
    }
    
    
    
    
    public function complementRow(Sitengine_Newsletter_Attachments_Row $row)
    {
		$data = $row->toArray();
		$data = $this->_complementFileData($data, self::FILE1ORIGINAL_ID);
		return $data;
    }
    
    
    
    protected function _complementFileData(array $data, $fileId)
    {
    	if($data[$fileId.'Name'])
		{
			/*
			$args = array(
				Sitengine_Env::PARAM_CONTROLLER => 'attachments',
				Sitengine_Env::PARAM_FILE => $fileId,
				Sitengine_Env::PARAM_ID => $data['id']
			);
			$uri  = $this->_newsletterPackage->getDownloadHandler();
			$uri .= Sitengine_Controller_Request_Http::makeNameValueQuery($args);
			*/
			
			/*
			$uri  = $this->_newsletterPackage->getAttachmentFile1OriginalRequestDir();
			$uri .= '/'.$data[$fileId.'Name'];
			*/
			
			#$config = $this->_newsletterPackage->getEnv()->getAmazonConfig('default');
			$key = $this->_configs[$fileId]['prefix'].'/'.$data[$fileId.'Name'];
			$uri = Sitengine_Amazon_S3::getUrl(
				$this->_configs[$fileId]['bucket'],
				$key,
				'',
				$this->_configs[$fileId]['cname'],
				$this->_configs[$fileId]['ssl']
			);
			
			require_once 'Sitengine/Mime/Type.php';
			$data[$fileId.'Path'] = $uri;
			$data[$fileId.'Uri'] = $uri;
			$data[$fileId.'IsImage'] = Sitengine_Mime_Type::isImage($data[$fileId.'Mime']);
			$data[$fileId.'IsFlash'] = Sitengine_Mime_Type::isFlash($data[$fileId.'Mime']);
			$data[$fileId.'SizeKb'] = round($data[$fileId.'Size']/1024);
			
			if($data[$fileId.'IsImage']) {
				$attr = array(
					'src' => $uri,
					'width' => $data[$fileId.'Width'],
					'height' => $data[$fileId.'Height'],
					'border' => 0
				);
				$data[$fileId.'Tag'] = '<img ';
				foreach($attr as $k => $v) { $data[$fileId.'Tag'] .= ' '.$k.'="'.$v.'"'; }
				$data[$fileId.'Tag'] .= ' />';
			}
		}
		return $data;
    }
    
    
    
    public function getDefaultPermissionData(Sitengine_Permiso $permiso, $ownerGroup)
    {
    	$gid = $permiso->getDirectory()->getGroupId($ownerGroup);
		$gid = (!is_null($gid)) ? $gid : Sitengine_Permiso::GID_ADMINISTRATORS;
		
		return array(
			Sitengine_Permiso::FIELD_UID => $permiso->getAuth()->getId(),
			Sitengine_Permiso::FIELD_GID => $gid,
			Sitengine_Permiso::FIELD_RAG => 1,
			Sitengine_Permiso::FIELD_RAW => 1,
			Sitengine_Permiso::FIELD_UAG => 1,
			Sitengine_Permiso::FIELD_UAW => 0,
			Sitengine_Permiso::FIELD_DAG => 1,
			Sitengine_Permiso::FIELD_DAW => 0
		);
    }
    
    
    
    
    public function makeFileName($fileId, $id, $suffix)
    {
        return $id.$suffix;
    }
    
    
    
    
    public function handleFileImport($id, $sourcePath)
    {
    	try {
    		$suffix = '.'.preg_replace('/.*\.(\w+)$/', "$1", $sourcePath);
			$filename = $this->makeFileName(self::FILE1ORIGINAL_ID, $id, $suffix);
			$this->_saveImportedFile(self::FILE1ORIGINAL_ID, $sourcePath, $filename);
		}
        catch (Exception $exception) {
        	require_once 'Sitengine/Newsletter/Exception.php';
        	throw new Sitengine_Newsletter_Exception('handle file import error', $exception);
        }
    }
    
    
    
    /*
    public function handleInsertUploads($id)
    {
        try {
            # upload 1
            $upload = new Sitengine_Upload(self::FILE1ORIGINAL_ID);
            
            if($upload->isFile())
            {
                $file1OriginalName = $this->makeFileName(
                    self::FILE1ORIGINAL_ID,
                    $id,
                    Sitengine_Mime_Type::getSuffix($upload->getMime())
                );
                # don't overwrite if a file with the same name exists (duplicate id)
                if(is_file($this->_configs[self::FILE1ORIGINAL_ID]['dir'].'/'.$file1OriginalName)) {
                    $this->_rollback();
                    require_once 'Sitengine/Newsletter/Exception.php';
        			throw new Sitengine_Newsletter_Exception('insert upload error on file1 (duplicate id)');
                }
                $this->_saveUploadedFile(self::FILE1ORIGINAL_ID, $upload, $file1OriginalName);
            }
        }
        catch (Exception $exception) {
        	require_once 'Sitengine/Newsletter/Exception.php';
        	throw new Sitengine_Newsletter_Exception('handle insert upload error', $exception);
        }
    }
    
    
    
    
    public function handleUpdateUploads($id, array $stored)
    {
        try {
            # upload 1
            $upload = new Sitengine_Upload(self::FILE1ORIGINAL_ID);
            
            if($upload->isFile())
            {
                if($stored[self::FILE1ORIGINAL_ID.self::FILETAG_NAME])
                {
                    $this->_removeFile(
                        self::FILE1ORIGINAL_ID,
                        $stored[self::FILE1ORIGINAL_ID.self::FILETAG_NAME]
                    );
                }
                $name = $this->makeFileName(
                    self::FILE1ORIGINAL_ID,
                    $id, Sitengine_Mime_Type::getSuffix($upload->getMime())
                );
                $this->_saveUploadedFile(self::FILE1ORIGINAL_ID, $upload, $name);
            }
        }
        catch (Exception $exception) {
        	require_once 'Sitengine/Newsletter/Exception.php';
        	throw new Sitengine_Newsletter_Exception('handle insert upload error', $exception);
        }
	}
	
	
	
	
	
	public function delete($where)
    {
    	try {
    		$deleted = 0;
    		$attachments = $this->selectRowsAndFiles($where);
    		
    		foreach($attachments as $attachment)
    		{
				$deleted += $this->deleteRowAndFiles($attachment);
    		}
    		return $deleted;
		}
		catch (Exception $exception) {
			require_once 'Sitengine/Newsletter/Exception.php';
        	throw new Sitengine_Newsletter_Exception('attachment delete error', $exception);
		}
    }
    */
    
    
    
    
    public function handleInsertUploads($id)
    {
        try {
            $upload = new Sitengine_Upload(self::FILE1ORIGINAL_ID);
            
            if(!$upload->isFile()) {
            	# upload comes from spinelab flash applet
            	$upload = new Sitengine_Upload('Filedata');
            }
            
            if($upload->isFile())
            {
            	require_once 'Sitengine/Mime/Type.php';
				$suffix = Sitengine_Mime_Type::getSuffix($upload->getMime());
				if(!$suffix) {
					# fix suffix if file is being uploaded through spinelab flash app
            		$suffix = '.'.preg_replace('/.*\.(\w+)$/', "$1", $upload->getName());
            	}
				$file1OriginalName = $this->makeFileName(self::FILE1ORIGINAL_ID, $id, $suffix);
				#$key = $this->_configs[self::FILE1ORIGINAL_ID]['prefix'].'/'.$file1OriginalName;
				#$object = $this->_configs[self::FILE1ORIGINAL_ID]['object'];
				
				require_once 'Sitengine/Amazon/S3/Object.php';
				$object = new Sitengine_Amazon_S3_Object(
					$this->_configs[self::FILE1ORIGINAL_ID]['connection'],
					$this->_configs[self::FILE1ORIGINAL_ID]['bucket'],
					$this->_configs[self::FILE1ORIGINAL_ID]['prefix'].'/'.$file1OriginalName,
					$this->_configs[self::FILE1ORIGINAL_ID]['cname'],
					$this->_configs[self::FILE1ORIGINAL_ID]['ssl']
				);
				
				$response = $object->head();
				
				if($response->getHttpResponse()->getStatus() == 200)
				{
					require_once 'Sitengine/Newsletter/Exception.php';
                    throw new Sitengine_Newsletter_Exception('insert upload error on file1 (duplicate id)');
                }
				$this->_saveUploadedFile(self::FILE1ORIGINAL_ID, $upload, $file1OriginalName);
            }
        }
        catch (Exception $exception) {
        	$this->_rollback();
        	require_once 'Sitengine/Newsletter/Exception.php';
            throw new Sitengine_Newsletter_Exception('handle insert upload error', $exception);
        }
    }
    
    
    
    
    
    public function handleUpdateUploads($id, array $stored)
    {
        try {
            $upload = new Sitengine_Upload(self::FILE1ORIGINAL_ID);
            
            if($upload->isFile())
            {
            	require_once 'Sitengine/Mime/Type.php';
            	$suffix = Sitengine_Mime_Type::getSuffix($upload->getMime());
				$file1OriginalName = $this->makeFileName(self::FILE1ORIGINAL_ID, $id, $suffix);
				
            	if($stored[self::FILE1ORIGINAL_ID.'Name'] != $file1OriginalName)
            	{
            		if($stored[self::FILE1ORIGINAL_ID.'Name'])
            		{
            			$this->_removeFile(
							self::FILE1ORIGINAL_ID,
							$stored[self::FILE1ORIGINAL_ID.self::FILETAG_NAME]
						);
            		}
            	}
				$this->_saveUploadedFile(self::FILE1ORIGINAL_ID, $upload, $file1OriginalName);
            }
        }
        catch (Exception $exception) {
        	$this->_rollback();
        	require_once 'Sitengine/Newsletter/Exception.php';
			throw new Sitengine_Newsletter_Exception('handle update upload error', $exception);
        }
    }
    
    
    
    
    public function delete($where)
    {
    	try {
    		$deleted = 0;
    		$attachments = $this->selectRowsAndFiles($where);
    		
    		foreach($attachments as $attachment)
    		{
				$deleted += $this->deleteRowAndFiles($attachment);
    		}
    		return $deleted;
		}
		catch (Exception $exception) {
			require_once 'Sitengine/Newsletter/Exception.php';
        	throw new Sitengine_Newsletter_Exception('post delete error', $exception);
		}
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /*
    $params = array(
    	'uid' => '',
    	'gid' => '',
    	'type' => '',
    	'find' => '',
    	'reset' => ''
    );
    */
    public function getFilterInstance(
    	Sitengine_Controller_Request_Http $request,
    	array $params,
    	Zend_Session_Namespace $namespace
    )
    {
    	require_once 'Sitengine/Grid/Search.php';
    	$filter = new Sitengine_Grid_Search();
		$reset = ($request->get($params['reset']));
		
		
		### filter element ###
		if($reset) { $filter->resetSessionVal($params['find'], $namespace); }
		$filter->registerSessionVal(
			$namespace,
			$request,
			$params['find'],
			self::VALUE_NONESELECTED
		);
		# set clause
		if($filter->getVal($params['find'])) {
			$value = $this->getAdapter()->quote($filter->getVal($params['find']));
			$value = preg_replace('/\s+/', ' ', $value);
			$value = preg_replace('/.(.*)./', "$1", trim($value)); # remove quote delimiters
			$clause  = "(";
			$clause .= "LOWER({$this->_name}.title) LIKE LOWER('%$value%')";
			$clause .= ")";
			$filter->setClause($params['find'], $clause);
		}
		return $filter;
    }
    
    
    
    public function getSortingInstance($currentRule, $currentOrder)
    {
    	require_once 'Sitengine/Grid/Sorting.php';
    	$sorting = new Sitengine_Grid_Sorting($currentRule, $currentOrder);
		$sorting
			->addRule('cdate', 'desc', "{$this->_name}.cdate asc", "{$this->_name}.cdate desc")
			->addRule('mdate', 'desc', "{$this->_name}.mdate asc", "{$this->_name}.mdate desc")
			->addRule('title', 'asc', "title asc", "title desc")
			->setDefaultRule('title')
		;
		return $sorting;
    }

    
}


?>