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



class Sitengine_Permiso_Dac
{
    
    
    protected $_permiso = null;
    protected $_customFields = array();
    
    
    public function __construct(Sitengine_Permiso $permiso)
    {
        $this->_permiso = $permiso;
    }
    
    
    public function addCustomField($fieldName, array $groupNames)
    {
    	$this->_customFields[$fieldName] = $groupNames;
    }
    
    
    public function clearCustomFields()
    {
    	$this->_customFields = array();
    }
    
    
    public function getCustomAccessSql($worldField, $tableName = '', $startWithAnd = true)
    {
    	try {
    		$sql = $worldField.' = "1"';
    		
    		foreach($this->_customFields as $fieldName => $groupNames)
    		{
    			foreach($groupNames as $groupName)
    			{
					$gid = $this->_permiso->getDirectory()->getGroupId($groupName);
					if(!is_null($gid) && $this->_permiso->getDirectory()->userIsMember($this->_permiso->getAuth()->getId(), $gid)) {
						$sql .= ' OR '.$tableName.'.'.$fieldName.' = "1"';
					}
				}
			}
			return (($startWithAnd) ? ' AND ' : '').'('.$sql.')';
		}
		catch (Exception $exception) { throw $exception; }
    }
    
    
    public function customAccessGranted($worldField, array $data)
    {
    	try {
    		if($data['readAccessPublic']) { return true; }
    		foreach($this->_customFields as $fieldName => $groupNames)
    		{
    			foreach($groupNames as $groupName)
    			{
					$gid = $this->_permiso->getDirectory()->getGroupId($groupName);
					if(
						$data[$fieldName] &&
						!is_null($gid) &&
						$this->_permiso->getDirectory()->userIsMember($this->_permiso->getAuth()->getId(), $gid)
					) {
						return true;
					}
				}
    		}
    		return false;
		}
		catch (Exception $exception) { throw $exception; }
    }
    
    
    
    public function readAccessGranted(array $data)
    {
        try { return $this->_accessGranted('r', $data); }
        catch (Exception $exception) { throw $exception; }
    }
    
    
    
    public function updateAccessGranted(array $data)
    {
        try { return $this->_accessGranted('u', $data); }
        catch (Exception $exception) { throw $exception; }
    }
    
    
    
    public function deleteAccessGranted(array $data)
    {
        try { return $this->_accessGranted('d', $data); }
        catch (Exception $exception) { throw $exception; }
    }
    
    
    
    protected function _accessGranted($accessType, array $data)
    {
        try {
            $permission = $data[$accessType.'ag'].$data[$accessType.'aw'];
            $groupBit = ($this->_permiso->getDirectory()->userIsMember($this->_permiso->getAuth()->getId(), $data[Sitengine_Permiso::FIELD_GID])) ? 1 : 0;
            
            #        gw           !g      g
            #--------------------------------------
            $matrix['00'] = array('0'=>0, '1'=>0);
            $matrix['01'] = array('0'=>1, '1'=>1);
            $matrix['10'] = array('0'=>0, '1'=>1);
            $matrix['11'] = array('0'=>1, '1'=>1);
            
            /*
            print 'accessType: '.$accessType.'<br />';
            print 'permission: '.$permission.'<br />';
            */
            if($this->_permiso->getAuth()->getId() == Sitengine_Permiso::UID_ROOT) { return true; }
            else if($this->_permiso->getDirectory()->userIsMember($this->_permiso->getAuth()->getId(), Sitengine_Permiso::GID_ADMINISTRATORS)) { return true; }
            else if($data[Sitengine_Permiso::FIELD_UID]==$this->_permiso->getAuth()->getId()) { return true; } # is owner
            else if($matrix[$permission][$groupBit]) { return true; }
            else { return false; }
        }
        catch (Exception $exception) { throw $exception; }
    }
    
    
    
    public function getReadAccessSql(array $authorizedGroups = array(), $tableName = '', $startWithAnd = true)
    {
        try { return $this->_getSql('r', $authorizedGroups, $tableName, $startWithAnd); }
        catch (Exception $exception) { throw $exception; }
    }
    
    
    
    public function getUpdateAccessSql(array $authorizedGroups = array(), $tableName = '', $startWithAnd = true)
    {
        try { return $this->_getSql('u', $authorizedGroups, $tableName, $startWithAnd); }
        catch (Exception $exception) { throw $exception; }
    }
    
    
    
    public function getDeleteAccessSql(array $authorizedGroups = array(), $tableName = '', $startWithAnd = true)
    {
        try { return $this->_getSql('d', $authorizedGroups, $tableName, $startWithAnd); }
        catch (Exception $exception) { throw $exception; }
    }
    
    
    
    # authorizedGroups: val=groupName
    protected function _getSql($accessType, array $authorizedGroups = array(), $tableName = '', $startWithAnd = true)
    {
        try {
            $q = '';
            $tableName = ($tableName) ? $tableName.'.' : '';
            
            if(!$this->_permiso->getDirectory()->userIsMember($this->_permiso->getAuth()->getId(), Sitengine_Permiso::GID_ADMINISTRATORS))
            {
                $q .= ($startWithAnd) ? ' AND (' : ' (';
                $q .= ' ('.$tableName.Sitengine_Permiso::FIELD_UID.' = "'.$this->_permiso->getAuth()->getId().'")'; # the owner can always access her records
                if(sizeof($authorizedGroups)) {
                    foreach($authorizedGroups as $v) {
                        $gid = $this->_permiso->getDirectory()->getGroupId($v);
                        if(!is_null($gid)) {
                            if($this->_permiso->getDirectory()->userIsMember($this->_permiso->getAuth()->getId(), $gid)) {
                                $q .= ' OR ('.$tableName.Sitengine_Permiso::FIELD_GID.' = "'.$gid.'" AND '.$tableName.$accessType.'ag = "1")';
                            }
                        }
                    }
                }
                $q .= ' OR ('.$tableName.$accessType.'aw = "1")'; 
                $q .= ') ';
            }
            return $q;
        }
        catch (Exception $exception) { throw $exception; }
    }
    
}
?>