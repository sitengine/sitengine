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
 * @package    Sitengine_Newsletter
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */


require_once 'Sitengine/Db/TableWithFiles.php';


class Sitengine_Newsletter_Campaigns_Table extends Sitengine_Db_TableWithFiles
{
    
    
    
    const VALUE_NONESELECTED = 'noneSelected';
    
    
    protected $_newsletterPackage = null;
    
    
    
    
    public function __construct(array $config = array())
    {
    	if(
    		isset($config['newsletterPackage']) &&
    		$config['newsletterPackage'] instanceof Sitengine_Newsletter
    	) {
    		$this->_newsletterPackage = $config['newsletterPackage'];
    		$this->_name = $this->_newsletterPackage->getCampaignsTableName();
    		$this->_primary = 'id';
    		parent::__construct($config);
    	}
    	else {
			require_once 'Sitengine/Newsletter/Exception.php';
			throw new Sitengine_Newsletter_Exception('campaigns table class init error');
		}
    }
    
    
    
    public function getNewsletterPackage()
    {
    	return $this->_newsletterPackage;
    }
    
    
    
    
    public function complementRow(Sitengine_Newsletter_Campaigns_Row $row)
    {
		$data = $row->toArray();
		return $data;
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
    
	
	
	
	public function delete($where)
    {
    	try {
    		$deleted = 0;
    		$campaigns = $this->selectRowsAndFiles($where);
    		
    		foreach($campaigns as $campaign)
    		{
				$where = $this->getAdapter()->quoteInto('campaignId = ?', $campaign->id);
				$deleted += $this->_newsletterPackage->getAttachmentsTable()->delete($where);
				$deleted += $this->deleteRowAndFiles($campaign);
    		}
    		return $deleted;
		}
		catch (Exception $exception) {
			require_once 'Sitengine/Newsletter/Exception.php';
        	throw new Sitengine_Newsletter_Exception('campaign delete error', $exception);
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
    	Zend_Session_Namespace $namespace
    )
    {
    	require_once 'Sitengine/Grid/Search.php';
    	$filter = new Sitengine_Grid_Search();
		$reset = ($request->get($params['reset']));
		
		
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
			$clause = "$campaigns.type = $value";
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
			$clause .= "LOWER({$this->_name}.title) LIKE LOWER('%$value%')";
			$clause .= " OR LOWER({$this->_name}.body) LIKE LOWER('%$value%')";
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
			->addRule('cdate', 'desc', "{$this->_name}.cdate asc", "{$this->_name}.cdate desc")
			->addRule('mdate', 'desc', "{$this->_name}.mdate asc", "{$this->_name}.mdate desc")
			->addRule('publish', 'asc', "{$this->_name}.publish asc", "{$this->_name}.publish desc")
			->addRule('type', 'asc', "{$this->_name}.type asc", "{$this->_name}.type desc")
			->addRule('title', 'asc', "title asc", "title desc")
			->addRule('body', 'asc', "body asc", "body desc")
			->setDefaultRule('cdate')
		;
		return $sorting;
    }

    
}


?>