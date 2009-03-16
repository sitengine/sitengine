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



class Sitengine_Permiso_Acl
{
    
    
    protected $_permiso = null;
    #protected $_items = array(); # access control list items
    
    
    
    public function __construct(Sitengine_Permiso $permiso)
    {
        $this->_permiso = $permiso;
    }
    
    
    /*
    # user must be listed in $users or she must be member of a group in $groups
    public function addPrivateItem($name, array $groups=array(), array $users=array())
    {
        $this->_items[$name] = array(
            'mode' => 0,
            'groups' => $groups,
            'users' => $users
        );
    }
    
    
    
    # allow all
    public function addPublicItem($name)
    {
        $this->_items[$name] = array(
            'mode' => 1,
            'groups' => array(),
            'users' => array()
        );
    }
    
    
    
    # allow authenticated users
    public function addRestrictedItem($name)
    {
        $this->_items[$name] = array(
            'mode' => 2,
            'groups' => array(),
            'users' => array()
        );
    }
    
    
    
    public function itemExists($name)
       {
        return isset($this->_items[$name]);
    }
    
    
    
    public function itemAccessGranted($name)
    {
        try {
            return (
            	$this->_items[$name]['mode']==1 ||
            	($this->_items[$name]['mode']==2 && $this->authenticatedAccessGranted()) ||
            	$this->privateAccessGranted($this->_items[$name]['users'], $this->_items[$name]['groups'])
            );
        }
        catch (Exception $exception) { throw $exception; }
    }
    */
    
    
    public function privateAccessGranted(array $groups = array(), array $users = array())
    {
        try {
            return (
            	$this->_permiso->getDirectory()->userIsMember($this->_permiso->getAuth()->getId(), Sitengine_Permiso::GID_ADMINISTRATORS) ||
                $this->_permiso->getAuth()->getId() == Sitengine_Permiso::UID_ROOT ||
                $this->_authorizedAsUser($users) ||
                $this->_authorizedAsMember($groups)
            );
        }
        catch (Exception $exception) { throw $exception; }
    }
    
    
    
    public function authenticatedAccessGranted()
    {
        return $this->_permiso->getAuth()->hasIdentity();
    }
    
    
    
    protected function _authorizedAsUser(array $users)
    {
        foreach($users as $v)
        {
            if(
            	$this->_permiso->getAuth()->getIdentity() == $v &&
            	$this->_permiso->getAuth()->hasIdentity()
            )
            {
            	return true;
            }
        }
        return false;
    }
    
    
    
    protected function _authorizedAsMember(array $groups)
    {
        try {
            foreach($groups as $v)
            {
                $groupId = $this->_permiso->getDirectory()->getGroupId($v);
                
                if(!is_null($groupId))
                {
					if($this->_permiso->getDirectory()->userIsMember($this->_permiso->getAuth()->getId(), $groupId))
					{
						return true;
					}
				}
            }
            return false;
        }
        catch (Exception $exception) { throw $exception; }
    }
    
}
?>