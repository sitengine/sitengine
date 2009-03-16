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
 * @package    Sitengine_Blog
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


require_once 'Sitengine/Db/TableWithS3Files.php';


class Sitengine_Blog_Files_Table extends Sitengine_Db_TableWithS3Files
{
    
    
    const FILE1ORIGINAL_ID = 'file1Original';
    const FILE1THUMBNAIL_ID = 'file1Thumbnail';
    const VALUE_NONESELECTED = 'noneSelected';
    
    
    protected $_blogPackage = null;
	protected $_translation = null;
    
    
    
    
    public function __construct(array $config = array())
    {
    	if(
    		isset($config['blogPackage']) &&
    		$config['blogPackage'] instanceof Sitengine_Blog
    	) {
    		$this->_blogPackage = $config['blogPackage'];
    		$this->_name = $this->_blogPackage->getFilesTableName();
    		$this->_primary = 'id';
    		
    		parent::__construct($config);
    		
			#require_once 'Sitengine/Amazon/S3/Header.php';
			#require_once 'Sitengine/Amazon/S3/Object.php';
			$config = $this->_blogPackage->getEnv()->getAmazonConfig('default');
			
			require_once 'Sitengine/Amazon/S3.php';
			$connection = new Sitengine_Amazon_S3($config['accessKey'], $config['secretKey']);
			#$prefix = $this->_blogPackage->getPostFile1OriginalPrefix();
			
			$this->_files[self::FILE1ORIGINAL_ID] = array();
			$this->_files[self::FILE1THUMBNAIL_ID] = array();
			
			$this->_configs[self::FILE1ORIGINAL_ID] = array(
				'connection' => $connection,
				'bucket' => $config['bucket'],
				'cname' => $config['cname'],
				'ssl' => $this->_blogPackage->getFileFile1OriginalSsl(),
				'prefix' => $this->_blogPackage->getFileFile1OriginalPrefix(),
				'amzHeaders' => $this->_blogPackage->getFileFile1OriginalAmzHeaders()
			);
			
			$this->_configs[self::FILE1THUMBNAIL_ID] = array(
				'connection' => $connection,
				'bucket' => $config['bucket'],
				'cname' => $config['cname'],
				'ssl' => $this->_blogPackage->getFileFile1ThumbnailSsl(),
				'prefix' => $this->_blogPackage->getFileFile1ThumbnailPrefix(),
				'amzHeaders' => $this->_blogPackage->getFileFile1ThumbnailAmzHeaders(),
				'tempDir' => $this->_blogPackage->getFileTempDir(),
				'mode' => 0644,
				'length' => $this->_blogPackage->getFileFile1ThumbnailResizeLength(),
				'method' => $this->_blogPackage->getFileFile1ThumbnailResizeMethod(),
				'jpgQuality' => $this->_blogPackage->getFileFile1ThumbnailResizeJpgQuality(),
				'transColor' => $this->_blogPackage->getRequest()->getPost('transColor')
			);
    	}
    	else {
			require_once 'Sitengine/Blog/Exception.php';
			throw new Sitengine_Blog_Exception('files table class init error');
		}
    }
    
    
    
    public function getBlogPackage()
    {
    	return $this->_blogPackage;
    }
    
    
    
    public function getTranslations()
    {
    	require_once 'Sitengine/Env.php';
        require_once 'Sitengine/Translations.php';
    	$translations = new Sitengine_Translations(
    		array(
    			Sitengine_Env::LANGUAGE_EN,
    			#Sitengine_Env::LANGUAGE_DE
    		)
    	);
    	return $translations;
    }
    
    
    
    
    public function setTranslation($language)
    {
    	$this->_translation = $language;
    }
    
    
    
    
    public function getDefaultPermissionData(Sitengine_Permiso $permiso, $ownerGroup)
    {
    	$gid = $permiso->getDirectory()->getGroupId($ownerGroup);
		$gid = (!is_null($gid)) ? $gid : Sitengine_Permiso::GID_ADMINISTRATORS;
		
		return array(
			Sitengine_Permiso::FIELD_UID => $permiso->getAuth()->getId(),
			Sitengine_Permiso::FIELD_GID => $gid,
			Sitengine_Permiso::FIELD_RAG => 0,
			Sitengine_Permiso::FIELD_RAW => 0,
			Sitengine_Permiso::FIELD_UAG => 0,
			Sitengine_Permiso::FIELD_UAW => 0,
			Sitengine_Permiso::FIELD_DAG => 0,
			Sitengine_Permiso::FIELD_DAW => 0
		);
    }
    
    
    
    
    public function complementRow(Sitengine_Blog_Files_Row $row)
    {
		$data = $row->toArray();
		$translations = $this->getTranslations();
		$translations->setLanguage($this->_translation);
		$index = $translations->getIndex();
		$default = $translations->getDefaultIndex();
		
		$data['title'] = ($data['titleLang'.$index]) ? $data['titleLang'.$index] : $data['titleLang'.$default];
		$data['markup'] = ($data['markupLang'.$index]) ? $data['markupLang'.$index] : $data['markupLang'.$default];
		$data['translationMissing'] = (!$data['titleLang'.$index]);
		
    	require_once 'Sitengine/Amazon/S3.php';
    	
		if($data[self::FILE1ORIGINAL_ID.'Name'])
		{
			/*
			$args = array(
				Sitengine_Env::PARAM_CONTROLLER => 'files',
				Sitengine_Env::PARAM_ID => $data['id']
			);
			$uri  = $this->_blogPackage->getDownloadHandler();
			$uri .= Sitengine_Controller_Request_Http::makeNameValueQuery($args);
			*/
			#$config = $this->_blogPackage->getEnv()->getAmazonConfig('default');
			$key = $this->_configs[self::FILE1ORIGINAL_ID]['prefix'].'/'.$data[self::FILE1ORIGINAL_ID.'Name'];
			$uri = Sitengine_Amazon_S3::getUrl(
				$this->_configs[self::FILE1ORIGINAL_ID]['bucket'],
				$key,
				'',
				$this->_configs[self::FILE1ORIGINAL_ID]['cname'],
				$this->_configs[self::FILE1ORIGINAL_ID]['ssl']
			);
			
			require_once 'Sitengine/Mime/Type.php';
			#$data[self::FILE1ORIGINAL_ID.'Path'] = $this->_blogPackage->getFileFile1OriginalDir().'/'.$data[self::FILE1ORIGINAL_ID.'Name'];
			$data[self::FILE1ORIGINAL_ID.'Path'] = $uri;
			$data[self::FILE1ORIGINAL_ID.'Uri'] = $uri;
			$data[self::FILE1ORIGINAL_ID.'IsImage'] = Sitengine_Mime_Type::isImage($data[self::FILE1ORIGINAL_ID.'Mime']);
			$data[self::FILE1ORIGINAL_ID.'IsFlash'] = Sitengine_Mime_Type::isFlash($data[self::FILE1ORIGINAL_ID.'Mime']);
			$data[self::FILE1ORIGINAL_ID.'SizeKb'] = round($data[self::FILE1ORIGINAL_ID.'Size']/1024);
			
			if($data[self::FILE1ORIGINAL_ID.'IsImage']) {
				$attr = array(
					'src' => $uri,
					'width' => $data[self::FILE1ORIGINAL_ID.'Width'],
					'height' => $data[self::FILE1ORIGINAL_ID.'Height'],
					'border' => 0
				);
				$data[self::FILE1ORIGINAL_ID.'Tag'] = '<img ';
				foreach($attr as $k => $v) { $data[self::FILE1ORIGINAL_ID.'Tag'] .= ' '.$k.'="'.$v.'"'; }
				$data[self::FILE1ORIGINAL_ID.'Tag'] .= ' />';
			}
		}
		
		
		if($data[self::FILE1THUMBNAIL_ID.'Name'])
		{
			/*
			$args = array(
				Sitengine_Env::PARAM_CONTROLLER => 'files',
				Sitengine_Env::PARAM_FILE => 'thumb',
				Sitengine_Env::PARAM_ID => $data['id']
			);
			$uri  = $this->_blogPackage->getDownloadHandler();
			$uri .= Sitengine_Controller_Request_Http::makeNameValueQuery($args);
			*/
			$key = $this->_configs[self::FILE1THUMBNAIL_ID]['prefix'].'/'.$data[self::FILE1THUMBNAIL_ID.'Name'];
			$uri = Sitengine_Amazon_S3::getUrl(
				$this->_configs[self::FILE1THUMBNAIL_ID]['bucket'],
				$key,
				'',
				$this->_configs[self::FILE1THUMBNAIL_ID]['cname'],
				$this->_configs[self::FILE1THUMBNAIL_ID]['ssl']
			);
			
			require_once 'Sitengine/Mime/Type.php';
			#$data[self::FILE1THUMBNAIL_ID.'Path'] = $this->_blogPackage->getFileFile1ThumbnailDir().'/'.$data[self::FILE1THUMBNAIL_ID.'Name'];
			$data[self::FILE1THUMBNAIL_ID.'Path'] = $uri;
			$data[self::FILE1THUMBNAIL_ID.'Uri'] = $uri;
			$data[self::FILE1THUMBNAIL_ID.'IsImage'] = Sitengine_Mime_Type::isImage($data[self::FILE1THUMBNAIL_ID.'Mime']);
			$data[self::FILE1THUMBNAIL_ID.'IsFlash'] = Sitengine_Mime_Type::isFlash($data[self::FILE1THUMBNAIL_ID.'Mime']);
			$data[self::FILE1THUMBNAIL_ID.'SizeKb'] = round($data[self::FILE1THUMBNAIL_ID.'Size']/1024);
			
			if($data[self::FILE1THUMBNAIL_ID.'IsImage']) {
				$attr = array(
					'src' => $uri,
					'width' => $data[self::FILE1THUMBNAIL_ID.'Width'],
					'height' => $data[self::FILE1THUMBNAIL_ID.'Height'],
					'border' => 0
				);
				$data[self::FILE1THUMBNAIL_ID.'Tag'] = '<img ';
				foreach($attr as $k => $v) { $data[self::FILE1THUMBNAIL_ID.'Tag'] .= ' '.$k.'="'.$v.'"'; }
				$data[self::FILE1THUMBNAIL_ID.'Tag'] .= ' />';
			}
		}
		return $data;
    }
    
    
    
    public function makeFileName($fileId, $id, $suffix)
    {
    	return $id.'-'.$fileId.$suffix;
    }
    
    
    
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
				$suffix = Sitengine_Mime_Type::getSuffix($upload->getMime());
				if(!$suffix) {
					# fix suffix if file is being uploaded through spinelab flash app
            		$suffix = '.'.preg_replace('/.*\.(\w+)$/', "$1", $upload->getName());
            	}
            	
				$file1OriginalName = $this->makeFileName(self::FILE1ORIGINAL_ID, $id, $suffix);
				#$key = $this->_configs[self::FILE1ORIGINAL_ID]['prefix'].'/'.$file1OriginalName;
				
				require_once 'Sitengine/Amazon/S3/Object.php';
				$object = new Sitengine_Amazon_S3_Object(
					$this->_configs[self::FILE1ORIGINAL_ID]['connection'],
					$this->_configs[self::FILE1ORIGINAL_ID]['bucket'],
					$this->_configs[self::FILE1ORIGINAL_ID]['prefix'].'/'.$file1OriginalName,
					$this->_configs[self::FILE1ORIGINAL_ID]['cname'],
					$this->_configs[self::FILE1ORIGINAL_ID]['ssl']
				);
				#$object = $this->_configs[self::FILE1ORIGINAL_ID]['object'];
				$response = $object->head();
				
				if($response->getHttpResponse()->getStatus() == 200) {
                    require_once 'Sitengine/Blog/Exception.php';
                    throw new Sitengine_Blog_Exception('insert upload error on file1 (duplicate id)');
                }
                
                $mime = Sitengine_Mime_Type::get($suffix);
                
                if(
                    Sitengine_Mime_Type::isJpg($mime) ||
                    Sitengine_Mime_Type::isGif($mime) ||
                    Sitengine_Mime_Type::isPng($mime)
                )
                {
                    $this->_resizeSaveUploadedImage(self::FILE1THUMBNAIL_ID, $upload, $id);
                }
                
				$this->_saveUploadedFile(self::FILE1ORIGINAL_ID, $upload, $file1OriginalName);
            }
        }
        catch (Exception $exception) {
        	$this->_rollback();
        	require_once 'Sitengine/Blog/Exception.php';
			throw new Sitengine_Blog_Exception('handle insert upload error', $exception);
        }
    }
    
    
    
    
    
    public function handleUpdateUploads($id, array $stored)
    {
        try {
            $upload = new Sitengine_Upload(self::FILE1ORIGINAL_ID);
            
            if($upload->isFile())
            {
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
            		if($stored[self::FILE1THUMBNAIL_ID.'Name'])
            		{
            			$this->_removeFile(
							self::FILE1THUMBNAIL_ID,
							$stored[self::FILE1THUMBNAIL_ID.self::FILETAG_NAME]
						);
            		}
            	}
            	
                if(
                    Sitengine_Mime_Type::isJpg($upload->getMime()) ||
                    Sitengine_Mime_Type::isGif($upload->getMime()) ||
                    Sitengine_Mime_Type::isPng($upload->getMime())
                )
                {
                    $this->_resizeSaveUploadedImage(self::FILE1THUMBNAIL_ID, $upload, $id);
                }
				$this->_saveUploadedFile(self::FILE1ORIGINAL_ID, $upload, $file1OriginalName);
            }
        }
        catch (Exception $exception) {
        	$this->_rollback();
        	require_once 'Sitengine/Blog/Exception.php';
			throw new Sitengine_Blog_Exception('handle update upload error', $exception);
        }
    }
    
    
    
    
    public function delete($where)
    {
    	try {
    		$deleted = 0;
    		$files = $this->selectRowsAndFiles($where);
    		
    		foreach($files as $file)
    		{
				$deleted += $this->deleteRowAndFiles($file);
    		}
    		return $deleted;
		}
		catch (Exception $exception) {
			require_once 'Sitengine/Blog/Exception.php';
        	throw new Sitengine_Blog_Exception('file delete error', $exception);
		}
    }
    
    
    
    
    
    
    /*
    $params = array(
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
		if($reset) { $filter->resetSessionVal($params['type'], $namespace); }
		$filter->registerSessionVal(
			$namespace,
			$request,
			$params['type'],
			self::VALUE_NONESELECTED
		);
		# set clause
		if($filter->getVal($params['type'])) {
			$value = $this->getAdapter()->quote($filter->getVal($params['type']));
			$clause = "{$this->_name}.type = $value";
			$filter->setClause($params['type'], $clause);
		}
		
		
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
			$clause .= "LOWER({$this->_name}.titleLang0) LIKE LOWER('%$value%')";
			$clause .= " OR LOWER({$this->_name}.markupLang0) LIKE LOWER('%$value%')";
			$clause .= " OR LOWER({$this->_name}.titleLang1) LIKE LOWER('%$value%')";
			$clause .= " OR LOWER({$this->_name}.markupLang1) LIKE LOWER('%$value%')";
			$clause .= " OR LOWER({$this->_name}.file1OriginalSource) LIKE LOWER('%$value%')";
			$clause .= ")";
			$filter->setClause($params['find'], $clause);
		}
		return $filter;
    }
    
    
    
    
    public function getSortingInstance($currentRule, $currentOrder)
    {
    	$translations = $this->getTranslations();
		$translations->setLanguage($this->_translation);
		$index = $translations->getIndex();
		
    	require_once 'Sitengine/Grid/Sorting.php';
    	$sorting = new Sitengine_Grid_Sorting($currentRule, $currentOrder);
		$sorting
			->addRule('cdate', 'desc', "{$this->_name}.cdate asc", "{$this->_name}.cdate desc")
			->addRule('mdate', 'desc', "{$this->_name}.mdate asc", "{$this->_name}.mdate desc")
			->addRule('publish', 'asc', "{$this->_name}.publish asc", "{$this->_name}.publish desc")
			->addRule('sorting', 'asc', "{$this->_name}.sorting asc", "{$this->_name}.sorting desc")
			->addRule('title', 'asc', "titleLang$index asc", "titleLang$index desc")
			->setDefaultRule('sorting')
		;
		return $sorting;
    }
    
}


?>