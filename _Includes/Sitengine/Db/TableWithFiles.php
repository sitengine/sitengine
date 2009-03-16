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
 * @package    Sitengine
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


/**
 *
 * Define and manage a database record and associated files
 *
 * Implement the logic create, modify and delete records and associated files
 *
 */

require_once 'Sitengine/Mime/Type.php';
require_once 'Zend/Db/Table/Abstract.php';


class Sitengine_Db_TableWithFiles extends Zend_Db_Table_Abstract
{
    
    const FIELD_ID = 'id';
	const FILETAG_NAME = 'Name';
	const FILETAG_SOURCE = 'Source';
	const FILETAG_MIME = 'Mime';
	const FILETAG_Width = 'Width';
	const FILETAG_HEIGHT = 'Height';
	const FILETAG_SIZE = 'Size';
	
	#protected $_data = null;
	protected $_error = null;
	protected $_errorMessage = null;
	protected $_warning = null;
	protected $_warningMessage = null;
    protected $_lastInsertId = ''; # assigned by insert()
    protected $_table = '';
    protected $_configs = array(); # file configs
    protected $_files = array(); # file data
    protected $_newFiles = array(); # rollback registry for new files
    protected $_currentFiles = array(); # rollback registry for current files
    protected $_importedFiles = array(); # rollback registry for imported files
    
    
    
    
    
    
    public function __construct(array $config = array())
    {
    	parent::__construct($config);
    		
    }
    
    
    public function reset()
    {
    	$this->_setError(null);
		$this->_setErrorMessage(null);
		$this->_setWarning(null);
		$this->_setWarningMessage(null);
    }
    
    
    public function getError()
    {
    	return $this->_error;
    }
    
    
    protected function _setError($error)
    {
    	$this->_error = $error;
    }
    
    
    public function getErrorMessage()
    {
    	return $this->_errorMessage;
    }
    
    
    protected function _setErrorMessage($errorMessage)
    {
    	$this->_errorMessage = $errorMessage;
    }
    
    
    public function getWarning()
    {
    	return $this->_warning;
    }
    
    
    protected function _setWarning($warning)
    {
    	$this->_warning = $warning;
    }
    
    
    public function getWarningMessage()
    {
    	return $this->_warningMessage;
    }
    
    
    protected function _setWarningMessage($warningMessage)
    {
    	$this->_warningMessage = $warningMessage;
    }
    
    
    /*
    public function setTable($name)
    {
        return $this->_name = $name;
        return $this;
    }
    
    
    
    public function getTable()
    {
        return $this->_name;
    }
    
    
    
    public function setData(array $data)
    {
    	$this->_data = $data;
		return $this;
    }
    
    
    
    public function getData()
    {
    	return $this->_data;
    }
    
    
    
    public function getField($field)
    {
    	if(!isset($this->_data[$field])) {
    		require_once 'Sitengine/Exception.php';
    		throw new Sitengine_Exception('invalid field name');
    	}
    	return $this->_data[$field];
    }
    */
    
    
    public function getFileData()
    {
        $data = array();
        foreach($this->_files as $fileId => $file)
        {
            if(isset($file['name'])) {
                $data[$fileId.self::FILETAG_NAME] = (isset($file['name'])) ? $file['name'] : '';
                $data[$fileId.self::FILETAG_SOURCE] = (isset($file['source'])) ? $file['source'] : '';
                $data[$fileId.self::FILETAG_MIME] = (isset($file['mime'])) ? $file['mime'] : '';
                $data[$fileId.self::FILETAG_SIZE] = (isset($file['size'])) ? $file['size'] : '';
                $data[$fileId.self::FILETAG_Width] = (isset($file['width'])) ? $file['width'] : '';
                $data[$fileId.self::FILETAG_HEIGHT] = (isset($file['height'])) ? $file['height'] : '';
            }
        }
        return $data;
    }
    
    
    
    
    # void
    public function makeFileName($fileId, $id, $suffix)
    {
        return $id.'-'.$fileId.$suffix;
    }
    
    
    
    
    
    
    # void
    public function handleInsertUploads($insertId)
    {
        if(false) {
        	require_once 'Sitengine/Exception.php';
            throw new Sitengine_Exception('insert upload error');
        }
    }
    
    
    # void
    public function handleUpdateUploads($id, array $stored)
    {
        if(false) {
        	require_once 'Sitengine/Exception.php';
            throw new Sitengine_Exception('update upload error');
        }
    }
       
       
    # void
    protected function _resizeSaveUploadedImage($fileId, Sitengine_Upload $upload, $id)
    {
    	require_once 'Sitengine/Mime/Type.php';
        $name = $this->makeFileName($fileId, $id, Sitengine_Mime_Type::getSuffix($upload->getMime()));
        
        try {
            if(Sitengine_Mime_Type::isJpg($upload->getMime()))
            {
            	require_once 'Sitengine/Image.php';
                $data = Sitengine_Image::resizeJpeg(
                    $upload->getTempName(),
                    $this->_configs[$fileId]['dir'].'/'.$name,
                    $this->_configs[$fileId]['length'],
                    $this->_configs[$fileId]['method'],
                    $this->_configs[$fileId]['mode'],
                    $this->_configs[$fileId]['jpgQuality']
                );
                $this->_newFiles[$fileId] = $name;
                $data['name'] = $name;
                $data['source'] = $upload->getName();
                $this->_files[$fileId] = $data;
            }
            else if(Sitengine_Mime_Type::isGif($upload->getMime()))
            {
            	require_once 'Sitengine/Image.php';
                $data = Sitengine_Image::resizeGif(
                    $upload->getTempName(),
                    $this->_configs[$fileId]['dir'].'/'.$name,
                    $this->_configs[$fileId]['length'],
                    $this->_configs[$fileId]['method'],
                    $this->_configs[$fileId]['mode'],
                    $this->_configs[$fileId]['transColor']
                );
                $this->_newFiles[$fileId] = $name;
                $data['name'] = $name;
                $data['source'] = $upload->getName();
                $this->_files[$fileId] = $data;
            }
            else if(Sitengine_Mime_Type::isPng($upload->getMime()))
            {
            	require_once 'Sitengine/Image.php';
                $data = Sitengine_Image::resizePng(
                    $upload->getTempName(),
                    $this->_configs[$fileId]['dir'].'/'.$name,
                    $this->_configs[$fileId]['length'],
                    $this->_configs[$fileId]['method'],
                    $this->_configs[$fileId]['mode'],
                    $this->_configs[$fileId]['transColor']
                );
                $this->_newFiles[$fileId] = $name;
                $data['name'] = $name;
                $data['source'] = $upload->getName();
                $this->_files[$fileId] = $data;
            }
        }
        catch (Exception $exception) {
            $this->_rollback();
            require_once 'Sitengine/Exception.php';
            throw new Sitengine_Exception('resize save uploaded image failed', $exception);
        }
    }
    
    
    # void
    protected function _saveUploadedFile($fileId, Sitengine_Upload $upload, $name)
    {
        try {
        	require_once 'Sitengine/Mime/Type.php';
            $width = 0;
            $height = 0;
            
            if(Sitengine_Mime_Type::isImage($upload->getMime()))
            {
                $info = getimagesize($upload->getTempName());
                if(!$info) {
                    #$this->_rollback();
                    require_once 'Sitengine/Exception.php';
                    throw new Sitengine_Exception('uploaded file is not an image');
                }
                $width = $info[0];
                $height = $info[1];
            }
            $data = array(
                'name' => $name,
                'source' => $upload->getName(),
                'mime' => $upload->getMime(),
                'size' => $upload->getSize(),
                'width' => $width,
                'height' => $height
            );
            $upload->save($this->_configs[$fileId]['dir'].'/'.$name);
            chmod($this->_configs[$fileId]['dir'].'/'.$name, $this->_configs[$fileId]['mode']);
            $this->_newFiles[$fileId] = $name;
            $this->_files[$fileId] = $data;
        }
        catch (Exception $exception) {
            $this->_rollback();
            require_once 'Sitengine/Exception.php';
            throw new Sitengine_Exception('save uploaded file failed', $exception);
        }
    }
    
    
    # void
    protected function _removeFile($fileId, $name)
    {
    	try {
			$path = $this->_configs[$fileId]['dir'].'/'.$name;
			$rollbackPath = $this->_configs[$fileId]['dir'].'/temp-'.$name;
			# keep file for rollbacks
			#if(is_file($path)) {
				if(!rename($path, $rollbackPath)) {
					#$this->_rollback();
					require_once 'Sitengine/Exception.php';
					throw new Sitengine_Exception('file could not be renamed');
				}
			#}
			# register current file for rollbacks
			$this->_currentFiles[$fileId] = $name;
			$this->_files[$fileId] = array(
				'source' => '',
				'name' => '',
				'mime' => '',
				'size' => '',
				'width' => '',
				'height' => ''
			);
		}
		catch (Exception $exception) {
        	$this->_rollback();
        	require_once 'Sitengine/Exception.php';
            throw new Sitengine_Exception('file could not be removed', $exception);
        }
    }
    
    
    # void - clean up after successful update
    protected function _cleanup()
    {
        foreach($this->_currentFiles as $fileId => $name) {
            $path = $this->_configs[$fileId]['dir'].'/temp-'.$name;
            if(is_writable($path)) { unlink($path); }
        }
        $this->_currentFiles = array();
    }
    
    
    # void
    protected function _rollback()
    {
        # remove new files
        foreach($this->_newFiles as $fileId => $name) {
            $path = $this->_configs[$fileId]['dir'].'/'.$name;
            if(is_writable($path)) { unlink($path); }
        }
        # restore current files
        foreach($this->_currentFiles as $fileId => $name) {
            $path = $this->_configs[$fileId]['dir'].'/'.$name;
            $currentFile = $this->_configs[$fileId]['dir'].'/temp-'.$name;
            rename($currentFile, $path);
        }
        $this->_newFiles = array();
        $this->_currentFiles = array();
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    # void
    public function handleFileImport($id, $sourcePath)
    {
        if(false) {
        	require_once 'Sitengine/Exception.php';
            throw new Sitengine_Exception('handle file import error');
        }
    }
    
    
    # void
    protected function _resizeSaveImportedImage($fileId, $sourcePath, $id)
    {
    	require_once 'Sitengine/Mime/Type.php';
    	$suffix = '.'.preg_replace('/.*\.(\w+)$/', "$1", $sourcePath);
		$name = $this->makeFileName($fileId, $id, $suffix);
		$mime = Sitengine_Mime_Type::get($sourcePath);
        
        try {
            if(Sitengine_Mime_Type::isJpg($mime))
            {
            	require_once 'Sitengine/Image.php';
                $data = Sitengine_Image::resizeJpeg(
                    $sourcePath,
                    $this->_configs[$fileId]['dir'].'/'.$name,
                    $this->_configs[$fileId]['length'],
                    $this->_configs[$fileId]['method'],
                    $this->_configs[$fileId]['mode'],
                    $this->_configs[$fileId]['jpgQuality']
                );
                $this->_newFiles[$fileId] = $name;
                $data['name'] = $name;
                $data['source'] = basename($sourcePath);
                $this->_files[$fileId] = $data;
            }
            else if(Sitengine_Mime_Type::isGif($mime))
            {
            	require_once 'Sitengine/Image.php';
                $data = Sitengine_Image::resizeGif(
                    $sourcePath,
                    $this->_configs[$fileId]['dir'].'/'.$name,
                    $this->_configs[$fileId]['length'],
                    $this->_configs[$fileId]['method'],
                    $this->_configs[$fileId]['mode'],
                    $this->_configs[$fileId]['transColor']
                );
                $this->_newFiles[$fileId] = $name;
                $data['name'] = $name;
                $data['source'] = basename($sourcePath);
                $this->_files[$fileId] = $data;
            }
            else if(Sitengine_Mime_Type::isPng($mime))
            {
            	require_once 'Sitengine/Image.php';
                $data = Sitengine_Image::resizePng(
                    $sourcePath,
                    $this->_configs[$fileId]['dir'].'/'.$name,
                    $this->_configs[$fileId]['length'],
                    $this->_configs[$fileId]['method'],
                    $this->_configs[$fileId]['mode'],
                    $this->_configs[$fileId]['transColor']
                );
                $this->_newFiles[$fileId] = $name;
                $data['name'] = $name;
                $data['source'] = basename($sourcePath);
                $this->_files[$fileId] = $data;
            }
        }
        catch (Exception $exception) {
            $this->_rollbackFileImport();
            require_once 'Sitengine/Exception.php';
            throw new Sitengine_Exception('resize save imported image failed', $exception);
        }
    }
    
    
    
    # void
    protected function _saveImportedFile($fileId, $sourcePath, $filename)
    {
        try {
        	require_once 'Sitengine/Mime/Type.php';
        	$width = 0;
            $height = 0;
            $mime = Sitengine_Mime_Type::get($sourcePath);
            
            if(!is_writeable($sourcePath) || !is_file($sourcePath)) {
            	require_once 'Sitengine/Exception.php';
            	throw new Sitengine_Exception('file can not be accessed');
            }
            
            if(Sitengine_Mime_Type::isImage($mime))
            {
            	$info = getimagesize($sourcePath);
                if(!$info) {
                    $this->_rollbackFileImport();
                    require_once 'Sitengine/Exception.php';
                    throw new Sitengine_Exception('file is not an image');
                }
                $width = $info[0];
                $height = $info[1];
            }
            $data = array(
                'name' => $filename,
                'source' => basename($sourcePath),
                'mime' => $mime,
                'size' => filesize($sourcePath),
                'width' => $width,
                'height' => $height
            );
            
            $finalPath = $this->_configs[$fileId]['dir'].'/'.$filename;
            
            if(!copy($sourcePath, $finalPath)) {
            	require_once 'Sitengine/Exception.php';
            	throw new Sitengine_Exception('file could not be copied');
            }
			if(!unlink($sourcePath)) {
				require_once 'Sitengine/Exception.php';
				throw new Sitengine_Exception('source file could not be deleted');
			}
			$this->_importedFiles[$fileId] = array(
				'sourcePath' => $sourcePath,
				'finalPath' => $finalPath
			);
            $this->_files[$fileId] = $data;
        }
        catch (Exception $exception) {
            $this->_rollbackFileImport();
            require_once 'Sitengine/Exception.php';
            throw new Sitengine_Exception('file import failed', $exception);
        }
    }
    
    
    public function insertFileImport(array $data)
    {
        try {
            $this->_lastInsertId = $data[self::FIELD_ID];
            $affectedRows = $this->getAdapter()->insert($this->_name, $data);
            if($affectedRows==0) { $this->_rollbackFileImport(); }
            return $affectedRows;
        }
        catch (Exception $exception) {
			$this->_rollbackFileImport();
			if(!$this->_checkModifyException($exception)) { return 0; }
			else {
				require_once 'Sitengine/Exception.php';
				throw new Sitengine_Exception('insert file import error', $exception);
			}
		}
    }
    
    
    protected function _rollbackFileImport()
    {
    	foreach($this->_importedFiles as $fileId => $paths)
    	{
    		if(is_file($paths['sourcePath'])) {
    			# don't overwrite freshly uploaded files with same name
    			require_once 'Sitengine/Exception.php';
    			throw new Sitengine_Exception('imported file could not be rolled back', $exception);
    		}
    		else {
    			copy($paths['finalPath'], $paths['sourcePath']); # copy file back to temp
            	if(is_writable($paths['finalPath'])) { unlink($paths['finalPath']); }
            }
        }
        foreach($this->_newFiles as $fileId => $name) {
            $path = $this->_configs[$fileId]['dir'].'/'.$name;
            if(is_writable($path)) { unlink($path); }
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /*
     *
     * Decide whether an exception should be thrown
     *
     * Note: Don't throw exceptions for errors that can be corrected by the user
     *
     */
    # void
    protected function _checkModifyException(Zend_Exception $exception)
    {
        return true;
    }
    
    
    
    # int
    public function update(array $data, $where)
    {
        try {
        	$this->reset();
            $affectedRows = $this->getAdapter()->update($this->_name, $data, $where);
            if($affectedRows == 0) { $this->_rollback(); }
            else { $this->_cleanup(); }
            return $affectedRows;
        }
        catch (Exception $exception) {
			$this->_rollback();
			if(!$this->_checkModifyException($exception)) { return 0; }
			else {
				require_once 'Sitengine/Exception.php';
				throw new Sitengine_Exception('update error', $exception);
			}
		}
    }
    
    
    # int
    public function insert(array $data)
    {
        try {
        	$this->reset();
            $this->_lastInsertId = $data[self::FIELD_ID];
            $primaryId = $this->getAdapter()->insert($this->_name, $data);
            if($primaryId == 0) { $this->_rollback(); }
            return $data[self::FIELD_ID];
        }
        catch (Exception $exception) {
			$this->_rollback();
			if(!$this->_checkModifyException($exception)) { return 0; }
			else {
				require_once 'Sitengine/Exception.php';
				throw new Sitengine_Exception('insert error', $exception);
			}
		}
    }
    
    
    
    public function getLastInsertId()
    {
        return $this->_lastInsertId;
    }
    
    
    
    
    
    
    
    
    
    
    /*
    # int
    public function delete($where)
    {
    	try {
			return $this->getAdapter()->delete($this->_name, $where);
		}
		catch (Exception $exception) {
			require_once 'Sitengine/Exception.php';
			throw new Sitengine_Exception('delete error', $exception);
		}
    }
    
    
    
    public function selectRowAndFiles($where, $fields = '')
    {
    	$rows = $this->selectRowsAndFiles($where, $fields);
    	return ($rows) ? $rows[0] : null;
    }
    */
    
    
    public function selectRowsAndFiles($where, array $fields = array())
    {
    	try {
    		$fields[] = self::FIELD_ID;
    		
    		foreach($this->_files as $fileId => $v)
    		{
    			$fields[] = $fileId.self::FILETAG_NAME;
			}
			$select = $this->select()->from($this, $fields)->where($where);
        	return $this->fetchAll($select);
		}
		catch (Exception $exception) {
			require_once 'Sitengine/Exception.php';
			throw new Sitengine_Exception('select to delete error', $exception);
		}
    }
    
    
    
    # int affected rows
    public function deleteRowAndFiles(Zend_Db_Table_Row_Abstract $row)
    {
    	try {
    		$row = $row->toArray();
    		$where = self::FIELD_ID.' = '.$this->getAdapter()->quote($row[self::FIELD_ID]);
    		$affectedRows = $this->getAdapter()->delete($this->_name, $where);
    		
			if($affectedRows > 0) {
				foreach($this->_files as $fileId => $v) {
					if($row[$fileId.self::FILETAG_NAME]) {
						$file = $this->_configs[$fileId]['dir'].'/'.$row[$fileId.self::FILETAG_NAME];
						if(is_writable($file)) { unlink($file); }
						else {
							require_once 'Sitengine/Exception.php';
							throw new Sitengine_Exception('file could not be unlinked: '.$file);
						}
					}
				}
			}
			return $affectedRows;
		}
		catch (Exception $exception) {
			require_once 'Sitengine/Exception.php';
			throw new Sitengine_Exception('delete row and files error', $exception);
		}
    }
    
    
    
    public function deleteRowsAndFilesRecursively($col, $val)
    {
        $whereClauses = array(
			$col.' = '.$this->_controller->getDatabase()->quote($val)
		);
		require_once 'Sitengine/Sql.php';
		$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
		$children = $this->selectRowsAndFiles($where);
		
		$deleted = 0;
        foreach($children as $child)
        {
            $deleted += $this->deleteRowAndFiles($child);
            $deleted += $this->deleteRowsAndFilesRecursively($col, $child['id']);
        }
        return $deleted;
    }
    
}

?>