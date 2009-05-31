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
 * @package    Sitengine_Sitemap
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */



require_once 'Sitengine/Record.php';
require_once 'Sitengine/Mime/Type.php';
require_once 'Sitengine/Upload.php';


abstract class Sitengine_Sitemap_Backend_Record extends Sitengine_Record {

    
    protected $_controller = null;
    protected $_filenamePrefix = '';
    
    # files
    const FILE1ORIGINAL_ID = 'file1Original';
    const FILE1THUMBNAIL_ID = 'file1Thumbnail';
    
    
    public function __construct(
    	Sitengine_Sitemap_Backend_Controller $controller
    )
    {
        $this->_controller = $controller;
        parent::__construct($this->_controller->getDatabase());
        $this->_table = $this->_controller->getFrontController()->getSitemapPackage()->getTableSitemap();
        
        $this->_files[self::FILE1ORIGINAL_ID] = array();
        $this->_files[self::FILE1THUMBNAIL_ID] = array();
        
        # files
        $transColor = $this->_controller->getRequest()->getPost('transColor');
        
        $this->_configs[self::FILE1ORIGINAL_ID] = array(
            'dir' => $this->_controller->getFrontController()->getSitemapPackage()->getFile1OriginalDir(),
            'mode' => 0644
        );
        $this->_configs[self::FILE1THUMBNAIL_ID] = array(
            'dir' => $this->_controller->getFrontController()->getSitemapPackage()->getFile1ThumbnailDir(),
            'mode' => 0644,
            'length' => 100,
            'method' => 'width',
            'jpgQuality' => 50,
            'transColor' => null, #'transColor' => $transColor
        );
    }
    
    
    
    public function setFilenamePrefix($tag)
    {
        $this->_filenamePrefix = $tag.'_';
    }
    
    
    
    public function makeFileName($fileId, $id, $suffix)
    {
        $name  = $this->_filenamePrefix;
        $name .= $id.'-'.$fileId.$suffix;
        return $name;
    }
    
    
    
    public function checkModifyException(Zend_Exception $exception)
    {
    	if(preg_match('/Duplicate entry.*for key (2|\'keyword\')/i', $exception->getMessage())) {
    		$this->_setError('keywordExists');
            return false;
    	}
    	return true;
    }
    
    
    
    
    
    public function handleInsertUploads($id)
    {
        try {
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
                    require_once 'Sitengine/Sitemap/Backend/Exception.php';
                    throw new Sitengine_Sitemap_Backend_Exception('insert upload error on file1 (duplicate id)');
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
        	require_once 'Sitengine/Sitemap/Backend/Exception.php';
        	throw new Sitengine_Sitemap_Backend_Exception('handle insert upload error', $exception);
        }
    }
    
    
    
    
    public function handleUpdateUploads($id, array $stored)
    {
        try {
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
                if($stored[self::FILE1THUMBNAIL_ID.self::FILETAG_NAME])
                {
                    $this->_removeFile(
                        self::FILE1THUMBNAIL_ID,
                        $stored[self::FILE1THUMBNAIL_ID.self::FILETAG_NAME]
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
                }
                $name = $this->makeFileName(
                    self::FILE1ORIGINAL_ID,
                    $id, Sitengine_Mime_Type::getSuffix($upload->getMime())
                );
                $this->_saveUploadedFile(self::FILE1ORIGINAL_ID, $upload, $name);
            }
        }
        catch (Exception $exception) {
        	require_once 'Sitengine/Sitemap/Backend/Exception.php';
        	throw new Sitengine_Sitemap_Backend_Exception('handle insert upload error', $exception);
        }
	}
    
    
}
?>