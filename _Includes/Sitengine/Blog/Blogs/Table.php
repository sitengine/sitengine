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


require_once 'Sitengine/Db/TableWithFiles.php';


class Sitengine_Blog_Blogs_Table extends Sitengine_Db_TableWithFiles
{
    
    
    const VALUE_NONESELECTED = 'noneSelected';
    
    
    protected $_blogPackage = null;
	protected $_transcript = null;
    
    
    
    
    public function __construct(array $config = array())
    {
    	if(
    		isset($config['blogPackage']) &&
    		$config['blogPackage'] instanceof Sitengine_Blog
    	) {
    		$this->_blogPackage = $config['blogPackage'];
    		$this->_name = $this->_blogPackage->getBlogsTableName();
    		$this->_primary = 'id';
    		
    		parent::__construct($config);
    	}
    	else {
			require_once 'Sitengine/Blog/Exception.php';
			throw new Sitengine_Blog_Exception('blogs table class init error');
		}
    }
    
    
    
    public function getBlogPackage()
    {
    	return $this->_blogPackage;
    }
    
    
    
    public function getTranscripts()
    {
    	require_once 'Sitengine/Env.php';
        require_once 'Sitengine/Transcripts.php';
    	$transcripts = new Sitengine_Transcripts(
    		array(
    			Sitengine_Env::LANGUAGE_EN,
    			#Sitengine_Env::LANGUAGE_DE
    		)
    	);
    	return $transcripts;
    }
    
    
    
    
    public function setTranscript($language)
    {
    	$this->_transcript = $language;
    }
    
    
    
    
    public function getDefaultPermissionData(Sitengine_Permiso $permiso, $ownerGroup)
    {
    	$gid = $permiso->getDirectory()->getGroupId($ownerGroup);
		$gid = (!is_null($gid)) ? $gid : Sitengine_Permiso::GID_ADMINISTRATORS;
		
		return array(
			Sitengine_Permiso::FIELD_UID => $permiso->getAuth()->getId(),
			Sitengine_Permiso::FIELD_GID => $gid,
			Sitengine_Permiso::FIELD_RAG => 1,
			Sitengine_Permiso::FIELD_RAW => 1,
			Sitengine_Permiso::FIELD_UAG => 1,
			Sitengine_Permiso::FIELD_UAW => 0,
			Sitengine_Permiso::FIELD_DAG => 1,
			Sitengine_Permiso::FIELD_DAW => 0
		);
    }
    
    
    
    public function complementRow(Sitengine_Blog_Blogs_Row $row)
    {
		$transcripts = $this->getTranscripts();
		$transcripts->setLanguage($this->_transcript);
		$index = $transcripts->getIndex();
		$default = $transcripts->getDefaultIndex();
		
		$data = $row->toArray();
		$data['title'] = ($data['titleLang'.$index]) ? $data['titleLang'.$index] : $data['titleLang'.$default];
		$data['markup'] = ($data['markupLang'.$index]) ? $data['markupLang'.$index] : $data['markupLang'.$default];
		$data['transcriptMissing'] = (!$data['titleLang'.$index]);
		return $data;
    }
    
    
    
    public function checkModifyException(Zend_Exception $exception)
    {
    	if(preg_match('/Duplicate entry.*for key (2|\'slug\')/i', $exception->getMessage())) {
    		$this->_setError('slugExists');
            return false;
    	}
    	return true;
    }
    
    
    
    public function delete($where)
    {
    	try {
    		$deleted = 0;
    		$blogs = $this->selectRowsAndFiles($where);
    		
    		foreach($blogs as $blog)
    		{
    			$where = $this->getAdapter()->quoteInto('blogId = ?', $blog->id);
    			$table = $this->_blogPackage->getPostsTable();
				$select = $table->select()->from($table, array('id'))->where($where);
				if($table->fetchRow($select) !== null) { return 0; }
				
				$deleted += $this->_blogPackage->getPostsTable()->delete($where);
				$deleted += $this->deleteRowAndFiles($blog);
    		}
    		return $deleted;
		}
		catch (Exception $exception) {
			require_once 'Sitengine/Blog/Exception.php';
        	throw new Sitengine_Blog_Exception('blog delete error', $exception);
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
		$transcripts = $this->getTranscripts();
		$transcripts->setLanguage($this->_transcript);
		$index = $transcripts->getIndex();
        $default = $transcripts->getDefaultIndex();
		
    	require_once 'Sitengine/Grid/Search.php';
    	$filter = new Sitengine_Grid_Search();
		$reset = ($request->get($params['reset']));
		
		
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
			$clause .= "LOWER({$this->_name}.titleLang$index) LIKE LOWER('%$value%')";
			$clause .= " OR LOWER({$this->_name}.slug) LIKE LOWER('%$value%')";
			$clause .= ")";
			$filter->setClause($params['find'], $clause);
		}
		return $filter;
    }
    
    
    
    
    public function getSortingInstance($currentRule, $currentOrder)
    {
    	$transcripts = $this->getTranscripts();
		$transcripts->setLanguage($this->_transcript);
		$index = $transcripts->getIndex();
		
    	require_once 'Sitengine/Grid/Sorting.php';
    	$sorting = new Sitengine_Grid_Sorting($currentRule, $currentOrder);
		$sorting
			->addRule('cdate', 'desc', "{$this->_name}.cdate asc", "{$this->_name}.cdate desc")
			->addRule('mdate', 'desc', "{$this->_name}.mdate asc", "{$this->_name}.mdate desc")
			->addRule('publish', 'desc', "{$this->_name}.publish asc", "{$this->_name}.publish desc")
			->addRule('slug', 'desc', "{$this->_name}.slug asc", "{$this->_name}.slug desc")
			->addRule('title', 'asc', "titleLang$index asc", "titleLang$index desc")
			->setDefaultRule('title')
		;
		return $sorting;
    }
    
}


?>