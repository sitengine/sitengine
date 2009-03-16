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
 * @package    Sitengine_Permiso
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


require_once 'Sitengine/Db/TableWithFiles.php';


class Sitengine_Permiso_Users_Table extends Sitengine_Db_TableWithFiles
{
    
    
    
    const AVATAR_ORIGINAL = 'avatarOriginal';
    const AVATAR_THUMB = 'avatarThumb';
    
    
    protected $_permisoPackage = null;
    
    
    
    
    public function __construct(array $config = array())
    {
    	if(
    		isset($config['permisoPackage']) &&
    		$config['permisoPackage'] instanceof Sitengine_Permiso
    	) {
    		$this->_permisoPackage = $config['permisoPackage'];
    		$this->_name = $this->_permisoPackage->getUsersTableName();
    		$this->_primary = 'id';
    		
    		parent::__construct($config);
    		
    		$this->_files[self::AVATAR_ORIGINAL] = array();
			$this->_files[self::AVATAR_THUMB] = array();
			
			# upload config
			$this->_configs[self::AVATAR_ORIGINAL] = array(
				'dir' => $this->_permisoPackage->getUserAvatarOriginalDir(),
				'mode' => 0644
			);
			$this->_configs[self::AVATAR_THUMB] = array(
				'dir' => $this->_permisoPackage->getUserAvatarThumbDir(),
				'mode' => 0644,
				'length' => 160,
				'method' => 'width',
				'jpgQuality' => 100
			);
    	}
    	else {
			require_once 'Sitengine/Permiso/Exception.php';
			throw new Sitengine_Permiso_Exception('users table class init error');
		}
    }
    
    
    
    public function getPermisoPackage()
    {
    	return $this->_permisoPackage;
    }
    
    
    
    
    protected function _checkModifyException(Zend_Exception $exception)
    {
    	if(preg_match('/Duplicate entry.*for key (2|\'nickname\')/i', $exception->getMessage())) {
    		$this->_setError('nicknameExists');
            return false;
    	}
    	if(preg_match('/Duplicate entry.*for key (3|\'name\')/i', $exception->getMessage())) {
    		$this->_setError('nameExists');
            return false;
    	}
    	return true;
    }
    
    
    
    
    
    public function checkUserModifyData(
    	Sitengine_Status $status,
    	Sitengine_Controller_Request_Http $request,
    	Sitengine_Dictionary $dictionary
    )
    {
    	require_once 'Sitengine/Validator.php';
    	
		$name = 'name';
		$val = $request->getPost($name);
		if(Sitengine_Validator::nada($val)) {
			$message = $dictionary->getFromHints('nameRequired');
			$status->addHint($name, $message);
		}
		else if(!Sitengine_Validator::emailAddress($val)) {
			$message = $dictionary->getFromHints('nameValidEmailRequired');
			$status->addHint($name, $message);
		}
		
		$name = 'nickname';
		$val = $request->getPost($name);
		if(Sitengine_Validator::nada($val)) {
			$message = $dictionary->getFromHints('nicknameRequired');
			$status->addHint($name, $message);
		}
		
		$name = 'firstname';
		$val = $request->getPost($name);
		if(Sitengine_Validator::nada($val)) {
			$message = $dictionary->getFromHints('firstnameRequired');
			$status->addHint($name, $message);
		}
		
		$name = 'lastname';
		$val = $request->getPost($name);
		if(Sitengine_Validator::nada($val)) {
			$message = $dictionary->getFromHints('lastnameRequired');
			$status->addHint($name, $message);
		}
		
		$name = 'password';
		$val = $request->getPost($name);
		if($val != $request->getPost('passwordConfirm')) {
			$message = $dictionary->getFromHints('passwordsDontMatch');
			$status->addHint($name, $message);
		}
		
		if($val != '')
		{
			require_once 'Zend/Validate/StringLength.php';
			$validator = new Zend_Validate_StringLength(
				$this->getPermisoPackage()->getMinimalPasswordLength()
			);
			
			$validator->setMessage(
				$dictionary->getFromHints('passwordTooShort'),
				Zend_Validate_StringLength::TOO_SHORT)
			;
			
			if(!$validator->isValid($val))
			{
				$messages = $validator->getMessages();
				$status->addHint($name, $messages);
			}
		}
		
		$name = 'country';
		if(Sitengine_Validator::nada($request->getPost($name), 'noneSelected')) {
			$message = $dictionary->getFromHints('countryRequired');
			$status->addHint($name, $message);
		}
		
		$name = 'timezone';
		if(Sitengine_Validator::nada($request->getPost($name), 'noneSelected')) {
			$message = $dictionary->getFromHints('timezoneRequired');
			#$status->addHint($name, $message);
		}
	
		$fileId = 'avatarOriginal';
		
		require_once 'Sitengine/Upload.php';
		$upload = new Sitengine_Upload($fileId);
		
		if($upload->isFile())
		{
			$messages = array();
			
			if(!preg_match('/(gif|jpg|jpeg)/i', $upload->getMime()))
			{
				$messages[] = $dictionary->getFromHints('avatarOriginalFiletype');
			}
			
			if($upload->getSize() > 1024 * 1024)
			{
				$messages[] = $dictionary->getFromHints('avatarOriginalFilesize');
			}
			
			if(sizeof($messages))
			{
				$status->addHint($fileId, $messages);
			}
		}
        return (!$status->hasHints());
    }
    
    
    
    
    public function complementRow(Sitengine_Permiso_Users_Row $row)
    {
		$data = $row->toArray();
		$data = $this->_complementFileData($data, self::AVATAR_ORIGINAL, $this->_permisoPackage->getUserAvatarOriginalDir());
		$data = $this->_complementFileData($data, self::AVATAR_THUMB, $this->_permisoPackage->getUserAvatarThumbDir());
		return $data;
    }
    
    
    
    protected function _complementFileData(array $data, $fileId, $dir)
    {
    	if($data[$fileId.'Name'])
		{
			$args = array(
				Sitengine_Env::PARAM_CONTROLLER => 'users',
				Sitengine_Env::PARAM_FILE => $fileId,
				Sitengine_Env::PARAM_ID => $data['id']
			);
			$uri  = $this->_permisoPackage->getDownloadHandler();
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
    
    
    
    
    public function handleInsertUploads($id)
    {
        try {
            # upload 1
            $upload = new Sitengine_Upload(self::AVATAR_ORIGINAL);
            
            if($upload->isFile())
            {
                $avatarOriginalName = $this->makeFileName(
                    self::AVATAR_ORIGINAL,
                    $id,
                    Sitengine_Mime_Type::getSuffix($upload->getMime())
                );
                # don't overwrite if a file with the same name exists (duplicate id)
                if(is_file($this->_configs[self::AVATAR_ORIGINAL]['dir'].'/'.$avatarOriginalName))
                {
                    $this->_rollback();
                    require_once 'Sitengine/Permiso/Exception.php';
        			throw new Sitengine_Permiso_Exception('insert upload error on avatar (duplicate id)');
                }
                if(
                    Sitengine_Mime_Type::isJpg($upload->getMime()) ||
                    Sitengine_Mime_Type::isGif($upload->getMime()) ||
                    Sitengine_Mime_Type::isPng($upload->getMime())
                )
                {
                    $this->_resizeSaveUploadedImage(self::AVATAR_THUMB, $upload, $id);
                }
                $this->_saveUploadedFile(self::AVATAR_ORIGINAL, $upload, $avatarOriginalName);
            }
        }
        catch (Exception $exception) {
        	require_once 'Sitengine/Permiso/Exception.php';
        	throw new Sitengine_Permiso_Exception('handle insert upload error', $exception);
        }
    }
    
    
    
    
    public function handleUpdateUploads($id, array $stored)
    {
        try {
            $upload = new Sitengine_Upload(self::AVATAR_ORIGINAL);
            $avatarDelete = (isset($_POST[self::AVATAR_ORIGINAL.'Delete']) && $_POST[self::AVATAR_ORIGINAL.'Delete'] == 1);
            
            if($avatarDelete || $upload->isFile())
            {
                if($stored[self::AVATAR_ORIGINAL.self::FILETAG_NAME])
                {
                    $this->_removeFile(
                        self::AVATAR_ORIGINAL,
                        $stored[self::AVATAR_ORIGINAL.self::FILETAG_NAME]
                    );
                }
                if($stored[self::AVATAR_THUMB.self::FILETAG_NAME])
                {
                    $this->_removeFile(
                        self::AVATAR_THUMB,
                        $stored[self::AVATAR_THUMB.self::FILETAG_NAME]
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
                    $this->_resizeSaveUploadedImage(self::AVATAR_THUMB, $upload, $id);
                }
                $name = $this->makeFileName(
                    self::AVATAR_ORIGINAL,
                    $id, Sitengine_Mime_Type::getSuffix($upload->getMime())
                );
                $this->_saveUploadedFile(self::AVATAR_ORIGINAL, $upload, $name);
            }
        }
        catch (Exception $exception) {
        	require_once 'Sitengine/Permiso/Exception.php';
        	throw new Sitengine_Permiso_Exception('handle insert upload error', $exception);
        }
	}
	
	
	
	
	
	public function delete($where)
    {
    	try {
    		$deleted = 0;
    		$users = $this->selectRowsAndFiles($where);
    		
    		foreach($users as $user)
    		{
				$where = $this->getAdapter()->quoteInto('userId = ?', $user->id);
				$deleted += $this->_permisoPackage->getMembershipsTable()->delete($where);
				$deleted += $this->deleteRowAndFiles($user);
    		}
    		return $deleted;
		}
		catch (Exception $exception) {
			require_once 'Sitengine/Permiso/Exception.php';
        	throw new Sitengine_Permiso_Exception('user delete error', $exception);
		}
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /*
    $params = array(
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
			$params['find']
		);
		# set clause
		if($filter->getVal($params['find']))
		{
			$value = $this->getAdapter()->quote($filter->getVal($params['find']));
			$value = preg_replace('/\s+/', ' ', $value);
			$value = preg_replace('/.(.*)./', "$1", trim($value)); # remove quote delimiters
			$clause  = "(";
			$clause .= "LOWER({$this->_name}.name) LIKE LOWER('%$value%')";
			$clause .= " OR LOWER({$this->_name}.nickname) LIKE LOWER('%$value%')";
			$clause .= " OR LOWER({$this->_name}.firstname) LIKE LOWER('%$value%')";
			$clause .= " OR LOWER({$this->_name}.lastname) LIKE LOWER('%$value%')";
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
			->addRule('lastLogin', 'desc', "{$this->_name}.lastLogin asc", "{$this->_name}.lastLogin desc")
			->addRule('cdate', 'desc', "{$this->_name}.cdate asc", "{$this->_name}.cdate desc")
			->addRule('mdate', 'desc', "{$this->_name}.mdate asc", "{$this->_name}.mdate desc")
			->addRule('enabled', 'asc', "{$this->_name}.enabled asc", "{$this->_name}.enabled desc")
			->addRule('locked', 'asc', "{$this->_name}.locked asc", "{$this->_name}.locked desc")
			->addRule('name', 'asc', "name asc", "name desc")
			->addRule('nickname', 'asc', "nickname asc", "nickname desc")
			->addRule('firstname', 'asc', "firstname asc", "firstname desc")
			->addRule('lastname', 'asc', "lastname asc", "lastname desc")
			->setDefaultRule('name')
		;
		return $sorting;
    }

    
}


?>