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
 * @package    Sitengine_Proto
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


require_once 'Sitengine/Db/TableWithFiles.php';


class Sitengine_Proto_Shouldies_Table extends Sitengine_Db_TableWithFiles
{
    
    
    
    const FILE1ORIGINAL_ID = 'file1Original';
    const FILE1THUMBNAIL_ID = 'file1Thumbnail';
    const FILE1FITTED_ID = 'file1Fitted';
    const FILE2ORIGINAL_ID = 'file2Original';
    const FILE2THUMBNAIL_ID = 'file2Thumbnail';
    const FILE2FITTED_ID = 'file2Fitted';
    const VALUE_NONESELECTED = 'noneSelected';
    
    
    protected $_protoPackage = null;
	protected $_translation = null;
    
    
    
    
    public function __construct(array $config = array())
    {
    	if(
    		isset($config['protoPackage']) &&
    		$config['protoPackage'] instanceof Sitengine_Proto
    	) {
    		$this->_protoPackage = $config['protoPackage'];
    		$this->_name = $this->_protoPackage->getShouldiesTableName();
    		$this->_primary = 'id';
    		
    		parent::__construct($config);
    		
    		$this->_files[self::FILE1ORIGINAL_ID] = array();
			$this->_files[self::FILE1THUMBNAIL_ID] = array();
			$this->_files[self::FILE1FITTED_ID] = array();
			$this->_files[self::FILE2ORIGINAL_ID] = array();
			$this->_files[self::FILE2THUMBNAIL_ID] = array();
			$this->_files[self::FILE2FITTED_ID] = array();
			
			$transColor = $this->_protoPackage->getRequest()->getPost('transColor');
			
			# upload 1
			$this->_configs[self::FILE1ORIGINAL_ID] = array(
				'dir' => $this->_protoPackage->getShouldyFile1OriginalDir(),
				'mode' => 0644
			);
			$this->_configs[self::FILE1THUMBNAIL_ID] = array(
				'dir' => $this->_protoPackage->getShouldyFile1ThumbnailDir(),
				'mode' => 0644,
				'length' => 100,
				'method' => 'width',
				'jpgQuality' => 100,
				'transColor' => $transColor
			);
			$this->_configs[self::FILE1FITTED_ID] = array(
				'dir' => $this->_protoPackage->getShouldyFile1FittedDir(),
				'mode' => 0644,
				'length' => 400,
				'method' => 'width',
				'jpgQuality' => 100,
				'transColor' => $transColor
			);
			
			# upload 2
			$this->_configs[self::FILE2ORIGINAL_ID] = array(
				'dir' => $this->_protoPackage->getShouldyFile2OriginalDir(),
				'mode' => 0644
			);
			$this->_configs[self::FILE2THUMBNAIL_ID] = array(
				'dir' => $this->_protoPackage->getShouldyFile2ThumbnailDir(),
				'mode' => 0644,
				'length' => 100,
				'method' => 'width',
				'jpgQuality' => 100,
				'transColor' => $transColor
			);
			$this->_configs[self::FILE2FITTED_ID] = array(
				'dir' => $this->_protoPackage->getShouldyFile2FittedDir(),
				'mode' => 0644,
				'length' => 400,
				'method' => 'width',
				'jpgQuality' => 100,
				'transColor' => $transColor
			);
    	}
    	else {
			require_once 'Sitengine/Proto/Exception.php';
			throw new Sitengine_Proto_Exception('shouldies table class init error');
		}
    }
    
    
    
    public function getProtoPackage()
    {
    	return $this->_protoPackage;
    }
    
    
    
    public function getTranslations()
    {
    	require_once 'Sitengine/Env.php';
        require_once 'Sitengine/Translations.php';
    	$translations = new Sitengine_Translations(
    		array(
    			Sitengine_Env::LANGUAGE_EN,
    			Sitengine_Env::LANGUAGE_DE
    		)
    	);
    	return $translations;
    }
    
    
    
    
    public function setTranslation($language)
    {
    	$this->_translation = $language;
    }
    
    
    
    
    public function complementRow(Sitengine_Proto_Shouldies_Row $row)
    {
		$data = $row->toArray();
		$data = $this->_complementFileData($data, self::FILE1ORIGINAL_ID, $this->_protoPackage->getShouldyFile1OriginalDir());
		$data = $this->_complementFileData($data, self::FILE1THUMBNAIL_ID, $this->_protoPackage->getShouldyFile1ThumbnailDir());
		$data = $this->_complementFileData($data, self::FILE1FITTED_ID, $this->_protoPackage->getShouldyFile1FittedDir());
		$data = $this->_complementFileData($data, self::FILE2ORIGINAL_ID, $this->_protoPackage->getShouldyFile2OriginalDir());
		$data = $this->_complementFileData($data, self::FILE2THUMBNAIL_ID, $this->_protoPackage->getShouldyFile2ThumbnailDir());
		$data = $this->_complementFileData($data, self::FILE2FITTED_ID, $this->_protoPackage->getShouldyFile2FittedDir());
		
		$translations = $this->getTranslations();
		$translations->setLanguage($this->_translation);
		$index = $translations->getIndex();
		$default = $translations->getDefaultIndex();
		
		$data['title'] = ($data['titleLang'.$index]) ? $data['titleLang'.$index] : $data['titleLang'.$default];
		$data['text'] = ($data['textLang'.$index]) ? $data['textLang'.$index] : $data['textLang'.$default];
		$data['translationMissing'] = (!$data['titleLang'.$index]);
		
		return $data;
    }
    
    
    
    protected function _complementFileData(array $data, $fileId, $dir)
    {
    	if($data[$fileId.'Name'])
		{
			$args = array(
				Sitengine_Env::PARAM_CONTROLLER => 'shouldies',
				Sitengine_Env::PARAM_FILE => $fileId,
				Sitengine_Env::PARAM_ID => $data['id']
			);
			$uri  = $this->_protoPackage->getDownloadHandler();
			$uri .= Sitengine_Controller_Request_Http::makeNameValueQuery($args);
			
			require_once 'Sitengine/Mime/Type.php';
			$data[$fileId.'Path'] = $dir.'/'.$data[$fileId.'Name'];
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
    
    
    
    public function makeFileName($fileId, $id, $suffix)
    {
        return $id.'-'.$fileId.$suffix;
    }
    
    
    
    
    public function handleFileImport($id, $sourcePath)
    {
    	try {
    		$suffix = '.'.preg_replace('/.*\.(\w+)$/', "$1", $sourcePath);
			$filename = $this->makeFileName(self::FILE1ORIGINAL_ID, $id, $suffix);
			$this->_resizeSaveImportedImage(self::FILE1THUMBNAIL_ID, $sourcePath, $id);
			$this->_resizeSaveImportedImage(self::FILE1FITTED_ID, $sourcePath, $id);
			$this->_saveImportedFile(self::FILE1ORIGINAL_ID, $sourcePath, $filename);
		}
        catch (Exception $exception) {
        	require_once 'Sitengine/Proto/Exception.php';
        	throw new Sitengine_Proto_Exception('handle file import error', $exception);
        }
    }
    
    
    
    
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
                    require_once 'Sitengine/Proto/Exception.php';
        			throw new Sitengine_Proto_Exception('insert upload error on file1 (duplicate id)');
                }
                if(
                    Sitengine_Mime_Type::isJpg($upload->getMime()) ||
                    Sitengine_Mime_Type::isGif($upload->getMime()) ||
                    Sitengine_Mime_Type::isPng($upload->getMime())
                )
                {
                    $this->_resizeSaveUploadedImage(self::FILE1THUMBNAIL_ID, $upload, $id);
                    $this->_resizeSaveUploadedImage(self::FILE1FITTED_ID, $upload, $id);
                }
                $this->_saveUploadedFile(self::FILE1ORIGINAL_ID, $upload, $file1OriginalName);
            }
            
            # upload 2
            $upload = new Sitengine_Upload(self::FILE2ORIGINAL_ID);
            
            if($upload->isFile())
            {
                $file2OriginalName = $this->makeFileName(
                    self::FILE2ORIGINAL_ID,
                    $id,
                    Sitengine_Mime_Type::getSuffix($upload->getMime())
                );
                # don't overwrite if a file with the same name exists (duplicate id)
                if(is_file($this->_configs[self::FILE2ORIGINAL_ID]['dir'].'/'.$file2OriginalName)) {
                    $this->_rollback();
                    require_once 'Sitengine/Proto/Exception.php';
        			throw new Sitengine_Proto_Exception('insert upload error on file2 (duplicate id)');
                }
                if(
                    Sitengine_Mime_Type::isJpg($upload->getMime()) ||
                    Sitengine_Mime_Type::isGif($upload->getMime()) ||
                    Sitengine_Mime_Type::isPng($upload->getMime())
                )
                {
                    $this->_resizeSaveUploadedImage(self::FILE2THUMBNAIL_ID, $upload, $id);
                    $this->_resizeSaveUploadedImage(self::FILE2FITTED_ID, $upload, $id);
                }
                $this->_saveUploadedFile(self::FILE2ORIGINAL_ID, $upload, $file2OriginalName);
            }
        }
        catch (Exception $exception) {
        	require_once 'Sitengine/Proto/Exception.php';
        	throw new Sitengine_Proto_Exception('handle insert upload error', $exception);
        }
    }
    
    
    
    
    public function handleUpdateUploads($id, array $stored)
    {
        try {
            # upload 1
            $upload = new Sitengine_Upload(self::FILE1ORIGINAL_ID);
            $file1Delete = (isset($_POST[self::FILE1ORIGINAL_ID.'Delete']) && $_POST[self::FILE1ORIGINAL_ID.'Delete'] == 1);
            
            if($file1Delete || $upload->isFile())
            {
                if($stored[self::FILE1ORIGINAL_ID.self::FILETAG_NAME])
                {
                    $this->_removeFile(
                        self::FILE1ORIGINAL_ID,
                        $stored[self::FILE1ORIGINAL_ID.self::FILETAG_NAME]
                    );
                }
                if($stored[self::FILE1THUMBNAIL_ID.self::FILETAG_NAME])
                {
                    $this->_removeFile(
                        self::FILE1THUMBNAIL_ID,
                        $stored[self::FILE1THUMBNAIL_ID.self::FILETAG_NAME]
                    );
                }
                if($stored[self::FILE1FITTED_ID.self::FILETAG_NAME])
                {
                    $this->_removeFile(
                        self::FILE1FITTED_ID,
                        $stored[self::FILE1FITTED_ID.self::FILETAG_NAME]
                    );
                }
            }
            if($upload->isFile())
            {
                if(
                    Sitengine_Mime_Type::isJpg($upload->getMime()) ||
                    Sitengine_Mime_Type::isGif($upload->getMime()) ||
                    Sitengine_Mime_Type::isPng($upload->getMime())
                )
                {
                    $this->_resizeSaveUploadedImage(self::FILE1THUMBNAIL_ID, $upload, $id);
                    $this->_resizeSaveUploadedImage(self::FILE1FITTED_ID, $upload, $id);
                }
                $name = $this->makeFileName(
                    self::FILE1ORIGINAL_ID,
                    $id, Sitengine_Mime_Type::getSuffix($upload->getMime())
                );
                $this->_saveUploadedFile(self::FILE1ORIGINAL_ID, $upload, $name);
            }
            
            
            # upload 2
            $upload = new Sitengine_Upload(self::FILE2ORIGINAL_ID);
            $file2Delete = (isset($_POST[self::FILE2ORIGINAL_ID.'Delete']) && $_POST[self::FILE2ORIGINAL_ID.'Delete'] == 1);
            
            if($file2Delete || $upload->isFile())
            {
                if($stored[self::FILE2ORIGINAL_ID.self::FILETAG_NAME])
                {
                    $this->_removeFile(
                        self::FILE2ORIGINAL_ID,
                        $stored[self::FILE2ORIGINAL_ID.self::FILETAG_NAME]
                    );
                }
                if($stored[self::FILE2THUMBNAIL_ID.self::FILETAG_NAME])
                {
                    $this->_removeFile(
                        self::FILE2THUMBNAIL_ID,
                        $stored[self::FILE2THUMBNAIL_ID.self::FILETAG_NAME]
                    );
                }
                if($stored[self::FILE2FITTED_ID.self::FILETAG_NAME])
                {
                    $this->_removeFile(
                        self::FILE2FITTED_ID,
                        $stored[self::FILE2FITTED_ID.self::FILETAG_NAME]
                    );
                }
            }
            if($upload->isFile())
            {
                if(
                    Sitengine_Mime_Type::isJpg($upload->getMime()) ||
                    Sitengine_Mime_Type::isGif($upload->getMime()) ||
                    Sitengine_Mime_Type::isPng($upload->getMime())
                )
                {
                    $this->_resizeSaveUploadedImage(self::FILE2THUMBNAIL_ID, $upload, $id);
                    $this->_resizeSaveUploadedImage(self::FILE2FITTED_ID, $upload, $id);
                }
                $name = $this->makeFileName(
                    self::FILE2ORIGINAL_ID,
                    $id,
                    Sitengine_Mime_Type::getSuffix($upload->getMime())
                );
                $this->_saveUploadedFile(self::FILE2ORIGINAL_ID, $upload, $name);
            }
        }
        catch (Exception $exception) {
        	require_once 'Sitengine/Proto/Exception.php';
        	throw new Sitengine_Proto_Exception('handle insert upload error', $exception);
        }
	}
	
	
	
	
	
	public function delete($where)
    {
    	try {
    		$deleted = 0;
    		$shouldies = $this->selectRowsAndFiles($where);
    		
    		foreach($shouldies as $shouldy)
    		{
    			$where = $this->getAdapter()->quoteInto('shouldyId = ?', $shouldy->id);
				$deleted += $this->_protoPackage->getCouldiesTable()->delete($where);
				$deleted += $this->deleteRowAndFiles($shouldy);
    		}
    		return $deleted;
		}
		catch (Exception $exception) {
			require_once 'Sitengine/Proto/Exception.php';
        	throw new Sitengine_Proto_Exception('shouldy delete error', $exception);
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
		if($reset) { $filter->resetSessionVal($params['uid'], $namespace); }
		$filter->registerSessionVal(
			$namespace,
			$request,
			$params['uid'],
			self::VALUE_NONESELECTED
		);
		# set clause
		if($filter->getVal($params['uid'])) {
			$value = $this->getAdapter()->quote($filter->getVal($params['uid']));
			$clause = Sitengine_Permiso::FIELD_UID.' = '.$value;
			$filter->setClause($params['uid'], $clause);
		}
		
		
		### filter element ###
		if($reset) { $filter->resetSessionVal($params['gid'], $namespace); }
		$filter->registerSessionVal(
			$namespace,
			$request,
			$params['gid'],
			self::VALUE_NONESELECTED
		);
		# set clause
		if($filter->getVal($params['gid'])) {
			$value = $this->getAdapter()->quote($filter->getVal($params['gid']));
			$clause = Sitengine_Permiso::FIELD_GID.' = '.$value;
			$filter->setClause($params['gid'], $clause);
		}
		
		
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
			$clause .= " OR LOWER({$this->_name}.textLang0) LIKE LOWER('%$value%')";
			$clause .= " OR LOWER({$this->_name}.titleLang1) LIKE LOWER('%$value%')";
			$clause .= " OR LOWER({$this->_name}.textLang1) LIKE LOWER('%$value%')";
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
			->addRule('locked', 'asc', "{$this->_name}.locked asc", "{$this->_name}.locked desc")
			->addRule('type', 'asc', "{$this->_name}.type asc", "{$this->_name}.type desc")
			->addRule('title', 'asc', "titleLang$index asc", "titleLang$index desc")
			->addRule('displayThis', 'asc', "displayThis asc", "displayThis desc")
			->addRule('sorting', 'asc', "sorting asc", "sorting desc")
			->setDefaultRule('sorting')
		;
		return $sorting;
    }

    
}


?>