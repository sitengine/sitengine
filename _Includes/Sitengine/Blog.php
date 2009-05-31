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


abstract class Sitengine_Blog
{
    
    /*
    const POST_TYPE_TEXT = 'text';
    const POST_TYPE_PHOTO = 'photo';
    const POST_TYPE_GALLERY = 'gallery';
    const POST_TYPE_QUOTE = 'quote';
    const POST_TYPE_LINK = 'link';
    const POST_TYPE_AUDIO = 'audio';
    const POST_TYPE_VIDEO = 'video';
    */
    const PARAM_TYPE = 'type';
    
    protected $_env = null;
    protected $_request = null;
    protected $_response = null;
    protected $_config = null;
    
    
    # properties loaded from config
    protected $_blogsTableName = null;
    protected $_postsTableName = null;
    protected $_commentsTableName = null;
    protected $_filesTableName = null;
    
    #protected $_postTempDir = null;
    protected $_fileTempDir = null;
    /*
    protected $_tempDirPerUser = true;
    protected $_downloadHandler = null;
    protected $_postFile1OriginalDir = null;
    protected $_postFile1OriginalRequestDir = null;
    protected $_fileFile1OriginalDir = null;
    protected $_fileFile1ThumbnailDir = null;
    protected $_fileFile1OriginalRequestDir = null;
    protected $_fileFile1ThumbnailRequestDir = null;
    */
    protected $_postFile1OriginalPrefix = null;
    protected $_postFile1OriginalAmzHeaders = null;
    protected $_postFile1OriginalSsl = null;
	#protected $_postFile1OriginalExpire = null;
	#protected $_postFile1OriginalSsl = null;
	protected $_fileFile1OriginalPrefix = null;
	protected $_fileFile1OriginalAmzHeaders = null;
	protected $_fileFile1OriginalSsl = null;
	#protected $_fileFile1OriginalExpire = null;
	#protected $_fileFile1OriginalSsl = null;
	protected $_fileFile1ThumbnailPrefix = null;
	protected $_fileFile1ThumbnailAmzHeaders = null;
	protected $_fileFile1ThumbnailSsl = null;
	#protected $_fileFile1ThumbnailExpire = null;
	#protected $_fileFile1ThumbnailSsl = null;
	
	protected $_ownerGroup = null;
    protected $_authorizedGroups = null;
    protected $_commentSenderName = null;
    protected $_blogSlug = null;
    protected $_name = null;

    
    public function __construct(
    	Sitengine_Env_Default $env,
    	Sitengine_Controller_Request_Http $request,
    	Zend_Controller_Response_Http $response,
    	Zend_Config $config
    )
    {
		$this->_env = $env;
		#$this->_request = $request;
		#$this->_response = $response;
		$this->_config = $config;
		$this->_mapConfig($config);
    }
    
    
    protected function _mapConfig(Zend_Config $config)
    {
    	if(
			isset($config->tableBlogs) &&
			isset($config->tablePosts) &&
			isset($config->tableComments) &&
			isset($config->tableFiles) &&
			#isset($config->postTempDir) &&
			isset($config->fileTempDir) &&
			#isset($config->tempDirPerUser) &&
			#isset($config->downloadHandler) &&
    		#isset($config->postFile1OriginalDir) &&
    		#isset($config->postFile1OriginalRequestDir) &&
    		#isset($config->fileFile1OriginalDir) &&
    		#isset($config->fileFile1ThumbnailDir) &&
    		#isset($config->fileFile1OriginalRequestDir) &&
    		#isset($config->fileFile1ThumbnailRequestDir) &&
    		
    		isset($config->postFile1OriginalPrefix) &&
    		isset($config->postFile1OriginalAmzHeaders) &&
    		isset($config->postFile1OriginalSsl) &&
			#isset($config->postFile1OriginalExpire) &&
			#isset($config->postFile1OriginalSsl) &&
			isset($config->fileFile1OriginalPrefix) &&
			isset($config->fileFile1OriginalAmzHeaders) &&
			isset($config->fileFile1OriginalSsl) &&
			#isset($config->fileFile1OriginalExpire) &&
			#isset($config->fileFile1OriginalSsl) &&
			isset($config->fileFile1ThumbnailPrefix) &&
			isset($config->fileFile1ThumbnailAmzHeaders) &&
			isset($config->fileFile1ThumbnailSsl) &&
			#isset($config->fileFile1ThumbnailExpire) &&
			#isset($config->fileFile1ThumbnailSsl)
			
			isset($config->ownerGroup) &&
    		isset($config->authorizedGroups) &&
    		isset($config->commentSenderName) &&
    		isset($config->name)
		)
		{
			$this->_blogsTableName = $config->tableBlogs;
			$this->_postsTableName = $config->tablePosts;
			$this->_commentsTableName = $config->tableComments;
			$this->_filesTableName = $config->tableFiles;
			#$this->_postTempDir = $config->postTempDir;
			$this->_fileTempDir = $config->fileTempDir;
			/*
			$this->_tempDirPerUser = $config->tempDirPerUser;
			$this->_downloadHandler = $config->downloadHandler;
    		$this->_postFile1OriginalDir = $config->postFile1OriginalDir;
    		$this->_postFile1OriginalRequestDir = $config->postFile1OriginalRequestDir;
    		$this->_fileFile1OriginalDir = $config->fileFile1OriginalDir;
    		$this->_fileFile1ThumbnailDir = $config->fileFile1ThumbnailDir;
    		$this->_fileFile1OriginalRequestDir = $config->fileFile1OriginalRequestDir;
    		$this->_fileFile1ThumbnailRequestDir = $config->fileFile1ThumbnailRequestDir;
    		*/
    		$this->_postFile1OriginalPrefix = $config->postFile1OriginalPrefix;
    		$this->_postFile1OriginalAmzHeaders = $config->postFile1OriginalAmzHeaders->toArray();
    		$this->_postFile1OriginalSsl = $config->postFile1OriginalSsl;
			#$this->_postFile1OriginalExpire = $config->postFile1OriginalExpire;
			#$this->_postFile1OriginalSsl = $config->postFile1OriginalSsl;
			$this->_fileFile1OriginalPrefix = $config->fileFile1OriginalPrefix;
			$this->_fileFile1OriginalAmzHeaders = $config->fileFile1OriginalAmzHeaders->toArray();
			$this->_fileFile1OriginalSsl = $config->fileFile1OriginalSsl;
			#$this->_fileFile1OriginalExpire = $config->fileFile1OriginalExpire;
			#$this->_fileFile1OriginalSsl = $config->fileFile1OriginalSsl;
			$this->_fileFile1ThumbnailPrefix = $config->fileFile1ThumbnailPrefix;
			$this->_fileFile1ThumbnailAmzHeaders = $config->fileFile1ThumbnailAmzHeaders->toArray();
			$this->_fileFile1ThumbnailSsl = $config->fileFile1ThumbnailSsl;
			#$this->_fileFile1ThumbnailExpire = $config->fileFile1ThumbnailExpire;
			#$this->_fileFile1ThumbnailSsl = $config->fileFile1ThumbnailSsl;
			
			$this->_ownerGroup = $config->ownerGroup;
    		$this->_authorizedGroups = $config->authorizedGroups->toArray();
    		$this->_commentSenderName = $config->commentSenderName;
    		$this->_name = $config->name;
		}
		else {
			require_once 'Sitengine/Blog/Exception.php';
        	throw new Sitengine_Blog_Exception('package config error');
       	}
       	
       	
       	# optional configs
       	if(isset($config->blogSlug))
		{
    		$this->_blogSlug = $config->blogSlug;
		}
    }
    
    
    public function getEnv()
    {
    	return $this->_env;
    }
    
    
    public function getRequest()
    {
    	return $this->_request;
    }
    
    
    public function getBlogsTableName()
    {
    	return $this->_blogsTableName;
    }
    
    
    public function getPostsTableName()
    {
    	return $this->_postsTableName;
    }
    
    
    public function getCommentsTableName()
    {
    	return $this->_commentsTableName;
    }
    
    
    public function getFilesTableName()
    {
    	return $this->_filesTableName;
    }
    
    /*
    public function getPostTempDir()
    {
    	return $this->_postTempDir;
    }
    */
    
    public function getFileTempDir()
    {
    	return $this->_fileTempDir;
    }
    
    /*
    public function tempDirPerUser()
    {
    	return $this->_tempDirPerUser;
    }
    
    
    public function getDownloadHandler()
    {
    	return $this->_downloadHandler;
    }
    
    
    
    public function getPostFile1OriginalDir() { return $this->_postFile1OriginalDir; }
    public function getPostFile1OriginalRequestDir() { return $this->_postFile1OriginalRequestDir; }
    public function getFileFile1OriginalDir() { return $this->_fileFile1OriginalDir; }
    public function getFileFile1ThumbnailDir() { return $this->_fileFile1ThumbnailDir; }
    public function getFileFile1OriginalRequestDir() { return $this->_fileFile1OriginalRequestDir; }
    public function getFileFile1ThumbnailRequestDir() { return $this->_fileFile1ThumbnailRequestDir; }
    */
    
    
    
    
    public function getPostFile1OriginalPrefix()
    {
    	return $this->_postFile1OriginalPrefix;
    }
    
    public function getPostFile1OriginalAmzHeaders()
    {
    	return $this->_postFile1OriginalAmzHeaders;
    }
    
    /*
    public function getPostFile1OriginalExpire()
    {
    	return $this->_postFile1OriginalExpire;
    }
    */
    public function getPostFile1OriginalSsl()
    {
    	return $this->_postFile1OriginalSsl;
    }
    
    
    
    
    
    
    public function getFileFile1OriginalPrefix()
    {
    	return $this->_fileFile1OriginalPrefix;
    }
    
    public function getFileFile1OriginalAmzHeaders()
    {
    	return $this->_fileFile1OriginalAmzHeaders;
    }
    
    /*
    public function getFileFile1OriginalExpire()
    {
    	return $this->_fileFile1OriginalExpire;
    }
    */
    public function getFileFile1OriginalSsl()
    {
    	return $this->_fileFile1OriginalSsl;
    }
    
    
    
    
    
    public function getFileFile1ThumbnailPrefix()
    {
    	return $this->_fileFile1ThumbnailPrefix;
    }
    
    public function getFileFile1ThumbnailAmzHeaders()
    {
    	return $this->_fileFile1ThumbnailAmzHeaders;
    }
    
    /*
    public function getFileFile1ThumbnailExpire()
    {
    	return $this->_fileFile1ThumbnailExpire;
    }
    */
    public function getFileFile1ThumbnailSsl()
    {
    	return $this->_fileFile1ThumbnailSsl;
    }
    
    
    
    
    public function getFileFile1ThumbnailResizeLength()
    {
    	return 160;
    }
    
    
    
    
    public function getFileFile1ThumbnailResizeMethod()
    {
    	return 'width';
    }
    
    
    
    
    public function getFileFile1ThumbnailResizeJpgQuality()
    {
    	return 70;
    }
    
    
    
    /*
    
    
    
    public static function fetchAuthor($uid, Sitengine_Permiso $permiso)
    {
    	$user = $permiso->getDirectory()->findUserById($uid);
    	return array(
    		'authorFirstname' => ($user !== null) ? $user['firstname'] : '',
    		'authorLastname' => ($user !== null) ? $user['lastname'] : '',
    		'authorNickname' => ($user !== null) ? $user['nickname'] : '',
    		'authorName' => ($user !== null) ? $user['name'] : '',
    	);
    }
    */
    
    
    
    public function getOwnerGroup() { return $this->_ownerGroup; }
    public function getAuthorizedGroups() { return $this->_authorizedGroups; }
    public function getCommentSenderName() { return $this->_commentSenderName; }
    public function getBlogSlug() { return $this->_blogSlug; }
    public function getName() { return $this->_name; }
    
    
    
    
    
    
    protected $_database = null;
    
    
    public function start(Zend_Db_Adapter_Abstract $database)
    {
    	$this->_database = $database;
    }
    
    
    
    
    protected $_blogsTable = null;
    
    public function getBlogsTable()
    {
    	if($this->_blogsTable === null)
    	{
    		if($this->_database === null)
    		{
    			$message = 'table init error - start() must be called first';
    			require_once 'Sitengine/Blog/Exception.php';
    			throw new Sitengine_Blog_Exception($message);
    		}
    		$this->_blogsTable = $this->_getBlogsTableInstance();
    	}
    	return $this->_blogsTable;
    }
    
    
    
    protected function _getBlogsTableInstance()
    {
    	require_once 'Sitengine/Blog/Blogs/Table.php';
		return new Sitengine_Blog_Blogs_Table(
			array(
				'db' => $this->_database,
				'rowClass' => 'Sitengine_Blog_Blogs_Row',
				'rowsetClass' => 'Sitengine_Blog_Blogs_Rowset',
				'blogPackage' => $this
			)
		);
    }
    
    
    
    
    
    protected $_postsTable = null;
    
    public function getPostsTable()
    {
    	if($this->_postsTable === null)
    	{
    		if($this->_database === null)
    		{
    			$message = 'table init error - start() must be called first';
    			require_once 'Sitengine/Blog/Exception.php';
    			throw new Sitengine_Blog_Exception($message);
    		}
    		$this->_postsTable = $this->_getPostsTableInstance();
    	}
    	return $this->_postsTable;
    }
    
    
    
    protected function _getPostsTableInstance()
    {
    	require_once 'Sitengine/Blog/Posts/Table.php';
		return new Sitengine_Blog_Posts_Table(
			array(
				'db' => $this->_database,
				'rowClass' => 'Sitengine_Blog_Posts_Row',
				'rowsetClass' => 'Sitengine_Blog_Posts_Rowset',
				'blogPackage' => $this
			)
		);
    }
    
    
    
    
    
    
    protected $_commentsTable = null;
    
    public function getCommentsTable()
    {
    	if($this->_commentsTable === null)
    	{
    		if($this->_database === null)
    		{
    			$message = 'table init error - start() must be called first';
    			require_once 'Sitengine/Blog/Exception.php';
    			throw new Sitengine_Blog_Exception($message);
    		}
    		$this->_commentsTable = $this->_getCommentsTableInstance();
    	}
    	return $this->_commentsTable;
    }
    
    
    
    protected function _getCommentsTableInstance()
    {
    	require_once 'Sitengine/Blog/Comments/Table.php';
		return new Sitengine_Blog_Comments_Table(
			array(
				'db' => $this->_database,
				'rowClass' => 'Sitengine_Blog_Comments_Row',
				'rowsetClass' => 'Sitengine_Blog_Comments_Rowset',
				'blogPackage' => $this
			)
		);
    }
    
    
    
    
    
    
    protected $_filesTable = null;
    
    public function getFilesTable()
    {
    	if($this->_filesTable === null)
    	{
    		if($this->_database === null)
    		{
    			$message = 'table init error - start() must be called first';
    			require_once 'Sitengine/Blog/Exception.php';
    			throw new Sitengine_Blog_Exception($message);
    		}
    		$this->_filesTable = $this->_getFilesTableInstance();
    	}
    	return $this->_filesTable;
    }
    
    
    
    protected function _getFilesTableInstance()
    {
    	require_once 'Sitengine/Blog/Files/Table.php';
		return new Sitengine_Blog_Files_Table(
			array(
				'db' => $this->_database,
				'rowClass' => 'Sitengine_Blog_Files_Row',
				'rowsetClass' => 'Sitengine_Blog_Files_Rowset',
				'blogPackage' => $this
			)
		);
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    public function getDateFormat()
    {
    	return 'dd-MM-YY HH:mm';
    }
}
?>