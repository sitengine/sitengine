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



require_once 'Sitengine/Exception.php';
require_once 'Sitengine/Mime/Type.php';
require_once 'Sitengine/Image.php';


/**
 *
 * Define and manage a database record and associated files
 *
 * Implement the logic create, modify and delete records and associated files
 *
 */

class Sitengine_Record
{
    
    const FIELD_ID = 'id';
	const FILETAG_NAME = 'Name';
	const FILETAG_SOURCE = 'Source';
	const FILETAG_MIME = 'Mime';
	const FILETAG_Width = 'Width';
	const FILETAG_HEIGHT = 'Height';
	const FILETAG_SIZE = 'Size';
	
	protected $_data = null;
	protected $_error = null;
    protected $_database = null;
    protected $_lastInsertId = ''; # assigned by insert()
    protected $_table = '';
    protected $_configs = array(); # file configs
    protected $_files = array(); # file data
    protected $_newFiles = array(); # rollback registry for new files
    protected $_currentFiles = array(); # rollback registry for current files
    protected $_importedFiles = array(); # rollback registry for imported files
    
    
    
    function __construct(Zend_Db_Adapter_Abstract $database)
    {
        $this->_database = $database;
    }
    
    
    
    public function getError()
    {
    	return $this->_error;
    }
    
    
    protected function _setError($error)
    {
    	$this->_error = $error;
    }
    
    
    
    public function setTable($name)
    {
        return $this->_table = $name;
        return $this;
    }
    
    
    
    public function getTable()
    {
        return $this->_table;
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
            throw new Sitengine_Exception('insert upload error');
        }
    }
    
    
    # void
    public function handleUpdateUploads($id, array $stored)
    {
        if(false) {
            throw new Sitengine_Exception('update upload error');
        }
    }
       
       
    # void
    protected function _resizeSaveUploadedImage($fileId, Sitengine_Upload $upload, $id)
    {
        $name = $this->makeFileName($fileId, $id, Sitengine_Mime_Type::getSuffix($upload->getMime()));
        
        try {
            if(Sitengine_Mime_Type::isJpg($upload->getMime()))
            {
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
            throw new Sitengine_Exception(
                'resize save uploaded image failed',
                $exception
            );
        }
    }
    
    
    # void
    protected function _saveUploadedFile($fileId, Sitengine_Upload $upload, $name)
    {
        try {
            $width = 0;
            $height = 0;
            
            if(Sitengine_Mime_Type::isImage($upload->getMime()))
            {
                $info = getimagesize($upload->getTempName());
                if(!$info) {
                    $this->_rollback();
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
            throw new Sitengine_Exception(
                'save uploaded file failed',
                $exception
            );
        }
    }
    
    
    # void
    protected function _removeFile($fileId, $name)
    {
        $path = $this->_configs[$fileId]['dir'].'/'.$name;
        $rollbackPath = $this->_configs[$fileId]['dir'].'/temp-'.$name;
        # keep file for rollbacks
        #if(is_file($path)) {
			if(!rename($path, $rollbackPath)) {
				$this->_rollback();
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
            throw new Sitengine_Exception('handle file import error');
        }
    }
    
    
    # void
    protected function _resizeSaveImportedImage($fileId, $sourcePath, $id)
    {
    	$suffix = '.'.preg_replace('/.*\.(\w+)$/', "$1", $sourcePath);
		$name = $this->makeFileName($fileId, $id, $suffix);
		$mime = Sitengine_Mime_Type::get($sourcePath);
        
        try {
            if(Sitengine_Mime_Type::isJpg($mime))
            {
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
            throw new Sitengine_Exception(
                'resize save imported image failed',
                $exception
            );
        }
    }
    
    
    
    # void
    protected function _saveImportedFile($fileId, $sourcePath, $filename)
    {
        try {
        	$width = 0;
            $height = 0;
            $mime = Sitengine_Mime_Type::get($sourcePath);
            
            if(!is_writeable($sourcePath) || !is_file($sourcePath)) {
            	throw new Sitengine_Exception('file can not be accessed');
            }
            
            if(Sitengine_Mime_Type::isImage($mime))
            {
            	$info = getimagesize($sourcePath);
                if(!$info) {
                    $this->_rollbackFileImport();
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
            	throw new Sitengine_Exception('file could not be copied');
            }
			if(!unlink($sourcePath)) {
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
            throw new Sitengine_Exception('file import failed', $exception);
        }
    }
    
    
    public function insertFileImport(array $data)
    {
        try {
            $this->_lastInsertId = $data[self::FIELD_ID];
            $affectedRows = $this->_database->insert($this->_table, $data);
            if($affectedRows==0) { $this->_rollbackFileImport(); }
            return $affectedRows;
        }
        catch (Exception $exception) {
			$this->_rollbackFileImport();
			if(!$this->checkModifyException($exception)) { return 0; }
			else { throw new Sitengine_Exception('insert file import error', $exception); }
		}
    }
    
    
    protected function _rollbackFileImport()
    {
    	foreach($this->_importedFiles as $fileId => $paths)
    	{
    		if(is_file($paths['sourcePath'])) {
    			# don't overwrite freshly uploaded files with same name
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
    public function checkModifyException(Zend_Exception $exception)
    {
        return true;
    }
    
    
    
    # int
    public function update(array $data, $where)
    {
        try {
            $affectedRows = $this->_database->update($this->_table, $data, $where);
            if($affectedRows==0) { $this->_rollback(); }
            else { $this->_cleanup(); }
            return $affectedRows;
        }
        catch (Exception $exception) {
			$this->_rollback();
			if(!$this->checkModifyException($exception)) { return 0; }
			else { throw new Sitengine_Exception('update error', $exception); }
		}
    }
    
    
    # int
    public function insert(array $data)
    {
        try {
            $this->_lastInsertId = $data[self::FIELD_ID];
            $affectedRows = $this->_database->insert($this->_table, $data);
            if($affectedRows==0) { $this->_rollback(); }
            return $affectedRows;
        }
        catch (Exception $exception) {
			$this->_rollback();
			if(!$this->checkModifyException($exception)) { return 0; }
			else { throw new Sitengine_Exception('insert error', $exception); }
		}
    }
    
    
    
    public function getLastInsertId()
    {
        return $this->_lastInsertId;
    }
    
    
    
    
    
    
    
    
    
    
    
    # int
    public function delete($where)
    {
    	try {
			return $this->_database->delete($this->_table, $where);
		}
		catch (Exception $exception) {
			throw new Sitengine_Exception('delete error', $exception);
		}
    }
    
    
    
    public function selectRowAndFiles($where, $fields = '')
    {
    	$result = $this->selectRowsAndFiles($where, $fields);
    	return ($result) ? $result[0] : null;
    }
    
    
    
    public function selectRowsAndFiles($where, $fields = '')
    {
    	try {
			$q  = 'SELECT';
			foreach($this->_files as $fileId => $v) {
				$q .= ' '.$fileId.self::FILETAG_NAME.',';
			}
			$q .= ' '.self::FIELD_ID;
			$q .= ($fields) ? ', '.$fields : '';
			$q .= ' FROM '.$this->_table;
			$q .= ($where) ? ' WHERE '.$where : '';
			$statement = $this->_database->prepare($q);
			$statement->execute();
			return $statement->fetchAll(Zend_Db::FETCH_ASSOC);
		}
		catch (Exception $exception) {
			throw new Sitengine_Exception('select to delete error', $exception);
		}
    }
    
    
    
    # int affected rows
    public function deleteRowAndFiles($row)
    {
    	try {
    		if($row === null) { return 0; }
    		$where = self::FIELD_ID.' = '.$this->_database->quote($row[self::FIELD_ID]);
    		$affectedRows = $this->_database->delete($this->_table, $where);
    		
			if($affectedRows > 0) {
				foreach($this->_files as $fileId => $v) {
					if($row[$fileId.self::FILETAG_NAME]) {
						$file = $this->_configs[$fileId]['dir'].'/'.$row[$fileId.self::FILETAG_NAME];
						if(is_writable($file)) { unlink($file); }
						else { throw new Sitengine_Exception('file could not be unlinked: '.$file); }
					}
				}
			}
			return $affectedRows;
		}
		catch (Exception $exception) {
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