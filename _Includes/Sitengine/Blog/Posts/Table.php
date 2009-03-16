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


class Sitengine_Blog_Posts_Table extends Sitengine_Db_TableWithS3Files
{
    
    const TYPE_TEXT = 'text';
    const TYPE_PHOTO = 'photo';
    const TYPE_GALLERY = 'gallery';
    const TYPE_QUOTE = 'quote';
    const TYPE_LINK = 'link';
    const TYPE_AUDIO = 'audio';
    const TYPE_VIDEO = 'video';
    const FILE1ORIGINAL_ID = 'file1Original';
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
    		$this->_name = $this->_blogPackage->getPostsTableName();
    		$this->_primary = 'id';
    		
    		parent::__construct($config);
    		/*
    		require_once 'Sitengine/Amazon/S3.php';
    		require_once 'Sitengine/Amazon/S3/Header.php';
			require_once 'Sitengine/Amazon/S3/Object.php';
			$config = $this->_blogPackage->getEnv()->getAmazonConfig('default');
			$connection = new Sitengine_Amazon_S3($config['accessKey'], $config['secretKey']);
			*/
			$config = $this->_blogPackage->getEnv()->getAmazonConfig('default');
			
			require_once 'Sitengine/Amazon/S3.php';
			$connection = new Sitengine_Amazon_S3($config['accessKey'], $config['secretKey']);
			
			$this->_files[self::FILE1ORIGINAL_ID] = array();
			
			# upload 1
			$this->_configs[self::FILE1ORIGINAL_ID] = array(
				'connection' => $connection,
				'bucket' => $config['bucket'],
				'cname' => $config['cname'],
				'ssl' => $this->_blogPackage->getPostFile1OriginalSsl(),
				'prefix' => $this->_blogPackage->getPostFile1OriginalPrefix(),
				'amzHeaders' => $this->_blogPackage->getPostFile1OriginalAmzHeaders()
			);
    	}
    	else {
			require_once 'Sitengine/Blog/Exception.php';
			throw new Sitengine_Blog_Exception('posts table class init error');
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
    
    
    
    
    
    public function complementRow(Sitengine_Blog_Posts_Row $row)
    {
		$data = $row->toArray();
		$translations = $this->getTranslations();
		$translations->setLanguage($this->_translation);
		$index = $translations->getIndex();
		$default = $translations->getDefaultIndex();
		
		$data['title'] = ($data['titleLang'.$index]) ? $data['titleLang'.$index] : $data['titleLang'.$default];
		$data['markup'] = ($data['markupLang'.$index]) ? $data['markupLang'.$index] : $data['markupLang'.$default];
		$data['teaser'] = ($data['teaserLang'.$index]) ? $data['teaserLang'.$index] : $data['teaserLang'.$default];
		$data['translationMissing'] = (!$data['titleLang'.$index]);
		
		if($data[self::FILE1ORIGINAL_ID.'Name'])
		{
			/*
			$args = array(
				Sitengine_Env::PARAM_CONTROLLER => 'posts',
				#Sitengine_Env::PARAM_FILE => self::FILE1ORIGINAL_ID,
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
			#$data[self::FILE1ORIGINAL_ID.'Path'] = $this->_blogPackage->getPostFile1OriginalDir().'/'.$data[self::FILE1ORIGINAL_ID.'Name'];
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
				
				if($response->getHttpResponse()->getStatus() == 200) {
					require_once 'Sitengine/Blog/Exception.php';
                    throw new Sitengine_Blog_Exception('insert upload error on file1 (duplicate id)');
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
        	require_once 'Sitengine/Blog/Exception.php';
			throw new Sitengine_Blog_Exception('handle update upload error', $exception);
        }
    }
    
    
    
    
    public function delete($where)
    {
    	try {
    		$deleted = 0;
    		$posts = $this->selectRowsAndFiles($where);
    		
    		foreach($posts as $post)
    		{
    			$where = $this->getAdapter()->quoteInto('parentId = ?', $post->id);
				$deleted += $this->_blogPackage->getFilesTable()->delete($where);
				$deleted += $this->_blogPackage->getCommentsTable()->delete($where);
				$deleted += $this->deleteRowAndFiles($post);
    		}
    		return $deleted;
		}
		catch (Exception $exception) {
			require_once 'Sitengine/Blog/Exception.php';
        	throw new Sitengine_Blog_Exception('post delete error', $exception);
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
    	Zend_Session_Namespace $namespace = null
    )
    {
    	require_once 'Sitengine/Grid/Search.php';
    	$filter = new Sitengine_Grid_Search();
		$reset = ($request->get($params['reset']));
		
		
		### filter element ###
		if($namespace === null)
		{
			$filter->registerVal(
				$request,
				$params['type'],
				self::VALUE_NONESELECTED
			);
		}
		else {
			if($reset) { $filter->resetSessionVal($params['type'], $namespace); }
			$filter->registerSessionVal(
				$namespace,
				$request,
				$params['type'],
				self::VALUE_NONESELECTED
			);
		}
		# set clause
		if($filter->getVal($params['type'])) {
			$value = $this->getAdapter()->quote($filter->getVal($params['type']));
			$clause = "{$this->_name}.type = $value";
			$filter->setClause($params['type'], $clause);
		}
		
		
		### filter element ###
		if($namespace === null)
		{
			$filter->registerVal(
				$request,
				$params['find'],
				self::VALUE_NONESELECTED
			);
		}
		else {
			if($reset) { $filter->resetSessionVal($params['find'], $namespace); }
			$filter->registerSessionVal(
				$namespace,
				$request,
				$params['find'],
				self::VALUE_NONESELECTED
			);
		}
		# set clause
		if($filter->getVal($params['find'])) {
			$value = $this->getAdapter()->quote($filter->getVal($params['find']));
			$value = preg_replace('/\s+/', ' ', $value);
			$value = preg_replace('/.(.*)./', "$1", trim($value)); # remove quote delimiters
			$clause  = "(";
			$clause .= "LOWER({$this->_name}.titleLang0) LIKE LOWER('%$value%')";
			$clause .= " OR LOWER({$this->_name}.markupLang0) LIKE LOWER('%$value%')";
			$clause .= " OR LOWER({$this->_name}.teaserLang0) LIKE LOWER('%$value%')";
			$clause .= " OR LOWER({$this->_name}.titleLang1) LIKE LOWER('%$value%')";
			$clause .= " OR LOWER({$this->_name}.markupLang1) LIKE LOWER('%$value%')";
			$clause .= " OR LOWER({$this->_name}.teaserLang1) LIKE LOWER('%$value%')";
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
			->addRule('type', 'asc', "{$this->_name}.type asc", "{$this->_name}.type desc")
			->addRule('title', 'asc', "titleLang$index asc", "titleLang$index desc")
			->setDefaultRule('cdate')
		;
		return $sorting;
    }
    
}


?>