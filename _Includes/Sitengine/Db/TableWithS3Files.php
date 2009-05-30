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

require_once 'Sitengine/Db/TableWithFiles.php';


class Sitengine_Db_TableWithS3Files extends Sitengine_Db_TableWithFiles
{

	# void
    protected function _resizeSaveUploadedImage($fileId, Sitengine_Upload $upload, $id)
    {
    	require_once 'Sitengine/Mime/Type.php';
    	$suffix = preg_replace('/.*\.(\w+)$/', "$1", $upload->getName());
    	
    	if($upload->getName() == $suffix)
    	{
    		# there is no suffix in the filename
        	require_once 'Sitengine/Exception.php';
            throw new Sitengine_Exception('file needs to have a valid suffix');
    	}
    	
    	$suffix = '.'.$suffix;
        $name = $this->makeFileName($fileId, $id, $suffix);
        $mime = Sitengine_Mime_Type::get($suffix);
        
        try {
            if(Sitengine_Mime_Type::isJpg($mime))
            {
            	require_once 'Sitengine/Image.php';
                $data = Sitengine_Image::resizeJpeg(
                    $upload->getTempName(),
                    $this->_configs[$fileId]['tempDir'].'/'.$name,
                    $this->_configs[$fileId]['length'],
                    $this->_configs[$fileId]['method'],
                    $this->_configs[$fileId]['mode'],
                    $this->_configs[$fileId]['jpgQuality']
                );
                #$this->_newFiles[$fileId] = $name;
                $data['name'] = $name;
                $data['source'] = $upload->getName();
                $this->_files[$fileId] = $data;
            }
            else if(Sitengine_Mime_Type::isGif($mime))
            {
            	require_once 'Sitengine/Image.php';
                $data = Sitengine_Image::resizeGif(
                    $upload->getTempName(),
                    $this->_configs[$fileId]['tempDir'].'/'.$name,
                    $this->_configs[$fileId]['length'],
                    $this->_configs[$fileId]['method'],
                    $this->_configs[$fileId]['mode'],
                    $this->_configs[$fileId]['transColor']
                );
                #$this->_newFiles[$fileId] = $name;
                $data['name'] = $name;
                $data['source'] = $upload->getName();
                $this->_files[$fileId] = $data;
            }
            else if(Sitengine_Mime_Type::isPng($mime))
            {
            	require_once 'Sitengine/Image.php';
                $data = Sitengine_Image::resizePng(
                    $upload->getTempName(),
                    $this->_configs[$fileId]['tempDir'].'/'.$name,
                    $this->_configs[$fileId]['length'],
                    $this->_configs[$fileId]['method'],
                    $this->_configs[$fileId]['mode'],
                    $this->_configs[$fileId]['transColor']
                );
                #$this->_newFiles[$fileId] = $name;
                $data['name'] = $name;
                $data['source'] = $upload->getName();
                $this->_files[$fileId] = $data;
            }
            
            if(
            	Sitengine_Mime_Type::isJpg($mime) ||
            	Sitengine_Mime_Type::isGif($mime) ||
            	Sitengine_Mime_Type::isPng($mime)
            )
            {
            	#$key = $this->_configs[$fileId]['prefix'].'/'.$name;
				#$object = $this->_configs[$fileId]['object'];
				
				require_once 'Sitengine/Amazon/S3/Object.php';
				$object = new Sitengine_Amazon_S3_Object(
					$this->_configs[$fileId]['connection'],
					$this->_configs[$fileId]['bucket'],
					$this->_configs[$fileId]['prefix'].'/'.$name,
					$this->_configs[$fileId]['cname'],
					$this->_configs[$fileId]['ssl']
				);
				
				$amzHeaders = $this->_configs[$fileId]['amzHeaders'];
				$response = $object->put($this->_configs[$fileId]['tempDir'].'/'.$name, array(), $amzHeaders);
				
				if($response->getHttpResponse()->isError())
				{
					require_once 'Sitengine/Exception.php';
					throw new Sitengine_Exception('file could not be uploaded to s3');
				}
				$this->_newFiles[$fileId] = $name;
				unlink($this->_configs[$fileId]['tempDir'].'/'.$name);
            }
        }
        catch (Exception $exception) {
        	$this->_rollback();
        	require_once 'Sitengine/Exception.php';
            throw new Sitengine_Exception('resize upload uploaded s3 image failed', $exception);
        }
    }
    
    
    # void
    protected function _saveUploadedFile($fileId, Sitengine_Upload $upload, $name)
    {
        try {
        	require_once 'Sitengine/Mime/Type.php';
            $width = 0;
            $height = 0;
            $mime = $upload->getMime();
            
            if($mime == 'application/octet-stream')
            {
            	#if(preg_match('/.*\.(gif|jpg|jpeg|png|mp3|pdf|wav|doc|xls|zip|aif|tif|css|sit|tar)$/i', $name))
            	#{
            		# try to fix mimetype if file is being uploaded through a flash app
            		require_once 'Sitengine/Mime/Type.php';
            		$mime = Sitengine_Mime_Type::get($name);
            	#}
            }
            
            if(
            	Sitengine_Mime_Type::isImage($mime)# ||
            	#preg_match('/.*\.(gif|jpg|jpeg|png)$/i', $name)
            )
            {
                $info = getimagesize($upload->getTempName());
                if(!$info) {
                	require_once 'Sitengine/Exception.php';
                    throw new Sitengine_Exception('uploaded file is not an image');
                }
                $width = $info[0];
                $height = $info[1];
            }
            $data = array(
                'name' => $name,
                'source' => $upload->getName(),
                'mime' => $mime,
                'size' => $upload->getSize(),
                'width' => $width,
                'height' => $height
            );
            
			#$key = $this->_configs[$fileId]['prefix'].'/'.$name;
			#$object = $this->_configs[$fileId]['object'];
			
			require_once 'Sitengine/Amazon/S3/Object.php';
			$object = new Sitengine_Amazon_S3_Object(
				$this->_configs[$fileId]['connection'],
				$this->_configs[$fileId]['bucket'],
				$this->_configs[$fileId]['prefix'].'/'.$name,
				$this->_configs[$fileId]['cname'],
				$this->_configs[$fileId]['ssl']
			);
			
			$amzHeaders = $this->_configs[$fileId]['amzHeaders'];
			$response = $object->put($upload->getTempName(), array(), $amzHeaders);
			
			if($response->getHttpResponse()->isError()) {
				require_once 'Sitengine/Exception.php';
				throw new Sitengine_Exception('file could not be uploaded to s3');
			}
			$this->_newFiles[$fileId] = $name;
			$this->_files[$fileId] = $data;
        }
        catch (Exception $exception) {
        	$this->_rollback();
        	throw $exception;
        }
    }
    
    
    
    
    # void
    protected function _removeFile($fileId, $name)
    {
    	try {
			$key = $this->_configs[$fileId]['prefix'].'/'.$name;
			#$rollbackKey = 'Temp/'.$this->_configs[$fileId]['prefix'].'/'.$name;
			#$object = $this->_configs[$fileId]['object'];
			
			require_once 'Sitengine/Amazon/S3/Object.php';
			$object = new Sitengine_Amazon_S3_Object(
				$this->_configs[$fileId]['connection'],
				$this->_configs[$fileId]['bucket'],
				$key,
				$this->_configs[$fileId]['cname'],
				$this->_configs[$fileId]['ssl']
			);
			
			require_once 'Sitengine/Amazon/S3/Object.php';
			$rollbackObject = new Sitengine_Amazon_S3_Object(
				$this->_configs[$fileId]['connection'],
				$this->_configs[$fileId]['bucket'],
				'Temp/'.$this->_configs[$fileId]['prefix'].'/'.$name,
				$this->_configs[$fileId]['cname'],
				$this->_configs[$fileId]['ssl']
			);
			
			require_once 'Sitengine/Mime/Type.php';
			$mime = Sitengine_Mime_Type::get($name);
			$response = $rollbackObject->copy($object->getBucketName(), $key, $mime, array(), $this->_configs[$fileId]['amzHeaders']);
			#print $response->getErrorMessage();
			if($response->isError())
			{
				require_once 'Sitengine/Exception.php';
				#throw new Sitengine_Exception('object could not be copied');
			}
			$response = $object->delete();
			
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
        foreach($this->_currentFiles as $fileId => $name)
        {
            #$rollbackKey = 'Temp/'.$this->_configs[$fileId]['prefix'].'/'.$name;
            #$object = $this->_configs[$fileId]['object'];
            
            require_once 'Sitengine/Amazon/S3/Object.php';
			$object = new Sitengine_Amazon_S3_Object(
				$this->_configs[$fileId]['connection'],
				$this->_configs[$fileId]['bucket'],
				'Temp/'.$this->_configs[$fileId]['prefix'].'/'.$name,
				$this->_configs[$fileId]['cname'],
				$this->_configs[$fileId]['ssl']
			);
			
            $response = $object->delete();
        }
        $this->_currentFiles = array();
    }
    
    
    
    
    # void
    protected function _rollback()
    {
    	#Sitengine_Debug::print_r($this->_newFiles);
    	#Sitengine_Debug::print_r($this->_currentFiles);
        # remove new files
        foreach($this->_newFiles as $fileId => $name)
        {
            #$key = $this->_configs[$fileId]['prefix'].'/'.$name;
            #$object = $this->_configs[$fileId]['object'];
            
            require_once 'Sitengine/Amazon/S3/Object.php';
			$object = new Sitengine_Amazon_S3_Object(
				$this->_configs[$fileId]['connection'],
				$this->_configs[$fileId]['bucket'],
				$this->_configs[$fileId]['prefix'].'/'.$name,
				$this->_configs[$fileId]['cname'],
				$this->_configs[$fileId]['ssl']
			);
			
			$response = $object->delete();
        }
        # restore current files
        foreach($this->_currentFiles as $fileId => $name)
        {
            #$key = $this->_configs[$fileId]['prefix'].'/'.$name;
            $rollbackKey = 'Temp/'.$this->_configs[$fileId]['prefix'].'/'.$name;
            #$object = $this->_configs[$fileId]['object'];
			
			require_once 'Sitengine/Amazon/S3/Object.php';
			$object = new Sitengine_Amazon_S3_Object(
				$this->_configs[$fileId]['connection'],
				$this->_configs[$fileId]['bucket'],
				$this->_configs[$fileId]['prefix'].'/'.$name,
				$this->_configs[$fileId]['cname'],
				$this->_configs[$fileId]['ssl']
			);
			
			require_once 'Sitengine/Amazon/S3/Object.php';
			$rollbackObject = new Sitengine_Amazon_S3_Object(
				$this->_configs[$fileId]['connection'],
				$this->_configs[$fileId]['bucket'],
				$rollbackKey,
				$this->_configs[$fileId]['cname'],
				$this->_configs[$fileId]['ssl']
			);
			
			require_once 'Sitengine/Mime/Type.php';
			$mime = Sitengine_Mime_Type::get($name);
            $response = $object->copy($rollbackObject->getBucketName(), $rollbackKey, $mime, array(), $this->_configs[$fileId]['amzHeaders']);
            $response = $rollbackObject->delete();
        }
        $this->_newFiles = array();
        $this->_currentFiles = array();
    }
    
    
    
    
    
    # int affected rows
    public function deleteRowAndFiles(Zend_Db_Table_Row_Abstract $row)
    {
    	try {
    		$row = $row->toArray();
    		$where = self::FIELD_ID.' = '.$this->getAdapter()->quote($row[self::FIELD_ID]);
    		$affectedRows = $this->getAdapter()->delete($this->_name, $where);
    		
			if($affectedRows > 0)
			{
				foreach($this->_files as $fileId => $v)
				{
					if($row[$fileId.self::FILETAG_NAME])
					{
						#$key = $this->_configs[$fileId]['prefix'].'/'.$row[$fileId.self::FILETAG_NAME];
						#$object = $this->_configs[$fileId]['object'];
						
						require_once 'Sitengine/Amazon/S3/Object.php';
						$object = new Sitengine_Amazon_S3_Object(
							$this->_configs[$fileId]['connection'],
							$this->_configs[$fileId]['bucket'],
							$this->_configs[$fileId]['prefix'].'/'.$row[$fileId.self::FILETAG_NAME],
							$this->_configs[$fileId]['cname'],
							$this->_configs[$fileId]['ssl']
						);
						
						$response = $object->delete();
					}
				}
			}
			return $affectedRows;
		}
		catch (Exception $exception) {
			require_once 'Sitengine/Exception.php';
			throw new Sitengine_Exception('delete row and s3 files error', $exception);
		}
    }
    
}

?>