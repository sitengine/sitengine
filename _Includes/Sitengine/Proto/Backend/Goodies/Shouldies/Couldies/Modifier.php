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



require_once 'Zend/Date.php';
require_once 'Sitengine/String.php';
require_once 'Sitengine/Validator.php';
require_once 'Sitengine/Upload.php';


abstract class Sitengine_Proto_Backend_Goodies_Shouldies_Couldies_Modifier
{
    
    protected $_controller = null;
    protected $_payloads = null;
    
    
    public function __construct(Sitengine_Proto_Backend_Goodies_Shouldies_Couldies_Controller $controller)
    {
        $this->_controller = $controller;
        
		$table = $this->_controller->getFrontController()->getProtoPackage()->getCouldiesTable();
        $transcripts = $table->getTranscripts();
        require_once 'Sitengine/Form/TranscriptsPayloads.php';
        $this->_payloads = new Sitengine_Form_TranscriptsPayloads($transcripts);
    }
    
    
    
    protected function _getFields()
    {
		$table = $this->_controller->getFrontController()->getProtoPackage()->getCouldiesTable();
        $transcripts = $table->getTranscripts();
        
        $fields[$this->_payloads->getMainName()] = array(
            Sitengine_Permiso::FIELD_UID => '',
            Sitengine_Permiso::FIELD_GID => '',
            Sitengine_Permiso::FIELD_RAG => 0,
            Sitengine_Permiso::FIELD_RAW => 0,
            Sitengine_Permiso::FIELD_UAG => 0,
            Sitengine_Permiso::FIELD_UAW => 0,
            Sitengine_Permiso::FIELD_DAG => 0,
            Sitengine_Permiso::FIELD_DAW => 0,
            'type' => '',
            'titleLang'.$transcripts->getDefaultIndex() => '',
            'sorting' => '',
            'publish' => 0,
            'locked' => 0,
            'displayThis' => 0
        );
        
        foreach($transcripts->get() as $index => $symbol)
        {
        	$payloadName = $this->_payloads->makeTranscriptName($symbol);
        	$fields[$payloadName] = array(
				'titleLang'.$index => '',
				'textLang'.$index => ''
        	);
        }
        
        return $fields[$this->_payloads->getName()];
    }
    
    
    
    
    public function uploadToTempDir()
    {
        try {
        	/*
            # php setting to play with:
            print 'upload_tmp_dir: '.ini_get('upload_tmp_dir').'<br />'; # PHP_INI_SYSTEM
			print 'file_uploads: '.ini_get('file_uploads').'<br />'; # PHP_INI_SYSTEM
			print 'upload_max_filesize: '.ini_get('upload_max_filesize').'<br />'; # PHP_INI_PERDIR
			print 'post_max_size: '.ini_get('post_max_size').'<br />'; # PHP_INI_PERDIR
			print 'max_input_time: '.ini_get('max_input_time').'<br />'; # PHP_INI_PERDIR
			print 'memory_limit: '.ini_get('memory_limit').'<br />'; # PHP_INI_ALL
			print 'max_execution_time: '.ini_get('max_execution_time').'<br />'; # PHP_INI_ALL
			
			# .htaccess php settings
			php_value upload_max_filesize 666M
			php_value post_max_size 777M
			php_value max_input_time 888
			php_value memory_limit 999
			
			# .htaccess apache settings
			LimitRequestBody
			
			# apache settings
			TimeOut
			*/
			
            $fileId = 'Filedata';
			$upload = new Sitengine_Upload($fileId);
			
			if(!$upload->isFile()) {
				return $this->_controller->getTranslate()->translate(Sitengine_Env::STATUS_UPLOAD_ERROR);
			}
			else if($upload->getError() > 0)
			{
				switch($upload->getError())
				{
					case 1:
						# uploaded file exceeds the UPLOAD_MAX_FILESIZE directive in php.ini
						return $this->_controller->getTranslate()->translate(Sitengine_Env::STATUS_UPLOAD_SIZEEXCEEDED);
					case 2:
						# uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form
						return $this->_controller->getTranslate()->translate(Sitengine_Env::STATUS_UPLOAD_SIZEEXCEEDED);
					case 3:
						# uploaded file was only partially uploaded
						return $this->_controller->getTranslate()->translate(Sitengine_Env::STATUS_UPLOAD_INCOMPLETE);
					case 4:
						return $this->_controller->getTranslate()->translate(Sitengine_Env::STATUS_UPLOAD_NOFILE);
					default:
						return $this->_controller->getTranslate()->translate(Sitengine_Env::STATUS_UPLOAD_ERROR);
				}
			}
			
			
			$filename = preg_replace("/[^a-zA-Z0-9._-]/", "_", $upload->getName());
            $path = $this->_controller->getTempDir().'/'.$filename;
			
			if(file_exists($path) )
			{
				return $this->_controller->getTranslate()->translate(Sitengine_Env::STATUS_UPLOAD_EXISTS);
			}
			else if(is_uploaded_file($upload->getTempName()))
			{
				if(move_uploaded_file($upload->getTempName(), $path))
				{
					@chmod($path, 0666);
					return 'OK';
				}
			}
			return $this->_controller->getTranslate()->translate(Sitengine_Env::STATUS_UPLOAD_ERROR);
        }
        catch (Exception $exception) {
            return $this->_controller->getTranslate()->translate(Sitengine_Env::STATUS_UPLOAD_ERROR);
        }
    }
    
    
    
    
    public function insert()
    {
        try {
            $this->_payloads->start();
            
            $shouldyId = $this->_controller->getEntity()->getAncestorId();
        	$fields = $this->_getFields();
        	$input = $this->_controller->getRequest()->getPost(null);
        	$id = Sitengine_String::createId();
            $data  = array();
            
            if(!$this->_checkInput()) { return null; }
            $this->_controller->getFrontController()->getProtoPackage()->getCouldiesTable()->handleInsertUploads($id);
            
            foreach($fields as $k => $v)
            {
                $data[$k] = (array_key_exists($k, $input)) ? $input[$k] : $v;
            }
            $data['id'] = $id;
            $data['shouldyId']= $shouldyId;
            #$data[Sitengine_Permiso::FIELD_OID] = $this->_controller->getPermiso()->getOrganization()->getId();
            
            $date = new Zend_Date();
			$date->setTimezone('UTC');
			$data['cdate'] = $date->get('YYYY-MM-dd HH:mm:ss', Sitengine_Env::LANGUAGE_EN);
			$data['mdate'] = $data['cdate'];
			
            $data = array_merge($data, $this->_controller->getFrontController()->getProtoPackage()->getCouldiesTable()->getFileData());
            #Sitengine_Debug::print_r($data);
            $insertId = $this->_controller->getFrontController()->getProtoPackage()->getCouldiesTable()->insertOrRollback($data);
            if(!$insertId)
            {
            	$error = $this->_controller->getFrontController()->getProtoPackage()->getCouldiesTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Proto/Backend/Goodies/Shouldies/Couldies/Exception.php';
            throw new Sitengine_Proto_Backend_Goodies_Shouldies_Couldies_Exception('insert error', $exception);
        }
    }
    
    
    
    
    public function update()
    {
        try {
            $this->_payloads->start($this->_controller->getRequest()->get(Sitengine_Env::PARAM_PAYLOAD_NAME));
            
            $id = $this->_controller->getRequest()->get(Sitengine_Env::PARAM_ID);
            $stored = $this->_controller->getEntity()->getRow()->toArray();
        	$fields = $this->_getFields();
            $input = $this->_controller->getRequest()->getPost(null);
            $data = array();
            
            if(!$this->_controller->getPermiso()->getDac()->updateAccessGranted($stored)) {
                $message = $this->_controller->getTranslate()->translate(Sitengine_Env::HINT_INVALID_ACTION);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            if($this->_controller->getRequest()->getPost('mdate') != $stored['mdate']) {
                $message = $this->_controller->getTranslate()->translate(Sitengine_Env::HINT_DATA_EXPIRED);
                $this->_controller->getStatus()->addHint('modifier', $message);
                return null;
            }
            
            if(!$this->_checkInput()) { return null; }
            
            $this->_controller->getFrontController()->getProtoPackage()->getCouldiesTable()->handleUpdateUploads($id, $stored);
            # sanitize data
            foreach($fields as $k => $v)
            {
                if(array_key_exists($k, $input)) { $data[$k] = $input[$k]; }
            }
            if(
                $stored[Sitengine_Permiso::FIELD_UID]!=$this->_controller->getPermiso()->getAuth()->getId() &&
                #!$this->_controller->getPermiso()->getUser()->hasSupervisorRights() &&
                #!$this->_controller->getPermiso()->getUser()->hasModeratorRights()
                !$this->_controller->getPermiso()->getDirectory()->userIsMember($this->_controller->getPermiso()->getAuth()->getId(), Sitengine_Permiso::GID_ADMINISTRATORS)
            ) {
                unset($data[Sitengine_Permiso::FIELD_UID]);
                unset($data[Sitengine_Permiso::FIELD_GID]);
                unset($data[Sitengine_Permiso::FIELD_RAG]);
                unset($data[Sitengine_Permiso::FIELD_RAW]);
                unset($data[Sitengine_Permiso::FIELD_UAG]);
                unset($data[Sitengine_Permiso::FIELD_UAW]);
                unset($data[Sitengine_Permiso::FIELD_DAG]);
                unset($data[Sitengine_Permiso::FIELD_DAW]);
            }
            $date = new Zend_Date();
			$date->setTimezone('UTC');
			$data['mdate'] = $date->get('YYYY-MM-dd HH:mm:ss', Sitengine_Env::LANGUAGE_EN);
            $data = array_merge($data, $this->_controller->getFrontController()->getProtoPackage()->getCouldiesTable()->getFileData());
            unset($data['id']);
            #Sitengine_Debug::print_r($data);
            
            $where = $this->_controller->getDatabase()->quoteInto('id = ?', $id);
            $affectedRows = $this->_controller->getFrontController()->getProtoPackage()->getCouldiesTable()->updateOrRollback($data, $where);
            if(!$affectedRows)
            {
            	$error = $this->_controller->getFrontController()->getProtoPackage()->getCouldiesTable()->getError();
            	if($error === null) { return null; }
            	$message = $this->_controller->getTranslate()->translate('hints'.ucfirst($error));
    			$this->_controller->getStatus()->addHint('record', $message);
    			return null;
            }
            return $data;
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Proto/Backend/Goodies/Shouldies/Couldies/Exception.php';
            throw new Sitengine_Proto_Backend_Goodies_Shouldies_Couldies_Exception('update error', $exception);
        }
    }
    
    
    
    
    protected function _checkInput()
    {
		$table = $this->_controller->getFrontController()->getProtoPackage()->getCouldiesTable();
        $transcripts = $table->getTranscripts();
		
        if(
        	$this->_payloads->isMain() ||
        	$this->_payloads->isDefaultTranscript()
        )
        {
        	$name = 'titleLang'.$transcripts->getDefaultIndex();
			if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name))) {
				$message = $this->_controller->getTranslate()->translate('hintsTitleRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
        
        }
        
        
        if($this->_payloads->isMain())
        {
			$name = 'gid';
			if($this->_controller->getRequest()->getPost($name)==Sitengine_Proto_Backend_Goodies_Shouldies_Couldies_Controller::VALUE_NONESELECTED) {
				$message = $this->_controller->getTranslate()->translate('hintsGidRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
			
			$name = 'type';
			if(Sitengine_Validator::nada($this->_controller->getRequest()->getPost($name), Sitengine_Proto_Backend_Goodies_Shouldies_Couldies_Controller::VALUE_NONESELECTED)) {
				$message = $this->_controller->getTranslate()->translate('hintsTypeRequired');
				$this->_controller->getStatus()->addHint($name, $message);
			}
        
			$fileId = 'file1Original';
			$upload = new Sitengine_Upload($fileId);
			
			if($upload->isFile())
			{
				$name = 'transColor';
				$val = $this->_controller->getRequest()->getPost($name);
				if($val && !Sitengine_Validator::rgbColor($val)) {
					$message = $this->_controller->getTranslate()->translate('hintsTransColorInvalid');
					$this->_controller->getStatus()->addHint($name, $messages);
				}
				
				$messages = array();
				
				if(!preg_match('/(gif|jpg|jpeg|png|pdf|mpeg|quicktime|msword|excel)/i', $upload->getMime())) {
					$messages[] = $this->_controller->getTranslate()->translate('hintsFile1OriginalFiletype');
				}
				if($upload->getSize() > '1048576') { # 1M
					$messages[] = $this->_controller->getTranslate()->translate('hintsFile1OriginalFilesize');
				}
				if(sizeof($messages)) {
					$this->_controller->getStatus()->addHint($fileId, $messages);
				}
			}
        }
        return (!$this->_controller->getStatus()->hasHints());
    }
    
    
    
    public function delete($id)
    {
        try {
            $whereClauses = array(
            	'id = '.$this->_controller->getDatabase()->quote($id),
            	$this->_controller->getPermiso()->getDac()->getDeleteAccessSql($this->_controller->getFrontController()->getProtoPackage()->getAuthorizedGroups(), '', false)
            );
            require_once 'Sitengine/Sql.php';
			$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
            return $this->_controller->getFrontController()->getProtoPackage()->getCouldiesTable()->delete($where);
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Proto/Backend/Goodies/Shouldies/Couldies/Exception.php';
            throw new Sitengine_Proto_Backend_Goodies_Shouldies_Couldies_Exception('delete error', $exception);
        }
    }
    
    
    
    
    
    public function updateFromList($id, array $data)
    {
        try {
            # sanitize data
            foreach($data as $k => $v) {
                if(!preg_match('/^(sorting|publish|locked|displayThis)$/', $k)) {
                    unset($data[$k]);
                }
            }
            $date = new Zend_Date();
			$date->setTimezone('UTC');
			$data['mdate'] = $date->get('YYYY-MM-dd HH:mm:ss', Sitengine_Env::LANGUAGE_EN);
            $whereClauses = array(
            	'id = '.$this->_controller->getDatabase()->quote($id),
            	$this->_controller->getPermiso()->getDac()->getUpdateAccessSql($this->_controller->getFrontController()->getProtoPackage()->getAuthorizedGroups(), '', false)
            );
            require_once 'Sitengine/Sql.php';
    		$where = Sitengine_Sql::getWhereStatement($whereClauses, false);
            return $this->_controller->getFrontController()->getProtoPackage()->getCouldiesTable()->updateOrRollback($data, $where);
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Proto/Backend/Goodies/Shouldies/Couldies/Exception.php';
            throw new Sitengine_Proto_Backend_Goodies_Shouldies_Couldies_Exception('update from list error', $exception);
        }
    }
    
    
    
    
    
    public function assignFromList($filename, array $data)
    {
        try {
        	# sanitize data
            foreach($data as $k => $v) {
                if(!preg_match('/^(type|cdate|mdate|gid|shouldyId)$/', $k)) {
                    unset($data[$k]);
                }
            }
            
            $sourcePath = $this->_controller->getTempDir().'/'.$filename;
			if(!is_writeable($sourcePath) || !is_file($sourcePath)) { return 0; }
            
        	$id = Sitengine_String::createId();
            $data['id'] = $id;
            #$data[Sitengine_Permiso::FIELD_OID] = $this->_controller->getPermiso()->getOrganization()->getId();
            $data[Sitengine_Permiso::FIELD_UID] = $this->_controller->getPermiso()->getAuth()->getId();
            $data[Sitengine_Permiso::FIELD_RAG] = 1;
            $data[Sitengine_Permiso::FIELD_RAW] = 1;
            $data[Sitengine_Permiso::FIELD_UAG] = 1;
            $data[Sitengine_Permiso::FIELD_UAW] = 0;
            $data[Sitengine_Permiso::FIELD_DAG] = 1;
            $data[Sitengine_Permiso::FIELD_DAW] = 0;
            $data['titleLang0']= basename($filename);
            
			$this->_controller->getFrontController()->getProtoPackage()->getCouldiesTable()->handleFileImport($id, $sourcePath);
			$data = array_merge($data, $this->_controller->getFrontController()->getProtoPackage()->getCouldiesTable()->getFileData());
			#Sitengine_Debug::print_r($data);
			return $this->_controller->getFrontController()->getProtoPackage()->getCouldiesTable()->insertFileImport($data);
        }
        catch (Exception $exception) {
            require_once 'Sitengine/Proto/Backend/Goodies/Shouldies/Couldies/Exception.php';
            throw new Sitengine_Proto_Backend_Goodies_Shouldies_Couldies_Exception('assign from list error', $exception);
        }
    }
    
}
?>