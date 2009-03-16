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



require_once 'Sitengine/Permiso/Exception.php';


class Sitengine_Permiso_Directory
{
    
    protected $_permiso = null;
    protected $_database = null;
    protected $_memberships = array(); # cached membership lookups
    protected $_groupNames = array(); # cached group names
    
    
    public function __construct(
    	Sitengine_Permiso $permiso,
    	Zend_Db_Adapter_Abstract $database
    )
    {
    	$this->_permiso = $permiso;
        $this->_database = $database;
    }
    
    
    
    # find memberships in enabled groups
    public function getUserMemberships($uid)
    {
    	try {
			$this->_memberships[$uid] = array();
			
			$q  = 'SELECT ';
			$q .= $this->_permiso->getGroupsTableName().'.id AS groupId, ';
			$q .= $this->_permiso->getGroupsTableName().'.name AS groupName ';
			
			$q .= 'FROM ';
			$q .= $this->_permiso->getGroupsTableName().', ';
			$q .= $this->_permiso->getMembershipsTableName().' ';
			
			$q .= 'WHERE ';
			$q .= $this->_permiso->getMembershipsTableName().'.userId = :uid ';
			$q .= 'AND '.$this->_permiso->getMembershipsTableName().'.groupId = '.$this->_permiso->getGroupsTableName().'.id ';
			$q .= 'AND '.$this->_permiso->getGroupsTableName().'.enabled = "1"';
			
			$bind = array(':uid' => $uid);
			$statement = $this->_database->prepare($q);
			$statement->execute($bind);
			$result = $statement->fetchAll(Zend_Db::FETCH_ASSOC);
			
			# cache memberships of this user
			foreach($result as $k => $v) {
				$this->_memberships[$uid][$v['groupId']] = $v['groupName'];
			}
			return $result;
		}
		catch (Exception $exception) {
			throw new Sitengine_Permiso_Exception('error looking up user memberships', $exception);
		}
    }
    
    
    
    public function userIsMember($userId, $groupId)
    {
        try {
            if(!isset($this->_memberships[$userId])) {
                # memberships for this user have not been looked up yet
                $this->getUserMemberships($userId);
                return $this->userIsMember($userId, $groupId);
            }
            else {
                return (isset($this->_memberships[$userId][$groupId]));
            }
        }
        catch (Exception $exception) { throw $exception; }
    }
    
    
    
    public function getGroupId($name)
    {
    	try {
			if(isset($this->_groupNames[$name])) {
				# name has already been looked up
				return $this->_groupNames[$name];
			}
			else {
				$q  = 'SELECT id FROM '.$this->_permiso->getGroupsTableName();
				$q .= ' WHERE name = :name';
				#$q .= ' AND '.Sitengine_Permiso::FIELD_OID.' = :oid';
				$q .= ' AND enabled = "1"';
				
				$bind = array(
					':name' => $name,
					#':oid' => $this->_permiso->getOrganization()->getId()
				);
				
				$statement = $this->_database->prepare($q);
				$statement->execute($bind);
				$result = $statement->fetchAll(Zend_Db::FETCH_ASSOC);
				
				if($result) {
					$this->_groupNames[$name] = $result[0]['id'];
					return $result[0]['id'];
				}
				return null;
			}
		}
		catch (Exception $exception) {
			throw new Sitengine_Permiso_Exception('error looking up group id', $exception);
		}
    }
    
    
    
    public function getUserId($name)
    {
    	try {
			$q  = 'SELECT id';
			$q .= ' FROM '.$this->_permiso->getUsersTableName();
			$q .= ' WHERE name = :name';
			#$q .= ' AND '.Sitengine_Permiso::FIELD_OID.' = :oid';
			
			$bind = array(
				':name' => $name,
				#':oid' => $this->_permiso->getOrganization()->getId()
			);
			
			$statement = $this->_database->prepare($q);
			$statement->execute($bind);
			$result = $statement->fetchAll(Zend_Db::FETCH_ASSOC);
			return ($result) ? $result[0]['id'] : null;
		}
		catch (Exception $exception) {
			throw new Sitengine_Permiso_Exception('error looking up user id', $exception);
		}
    }
    
    
    
    public function findUserById($id)
    {
    	try {
			$q  = 'SELECT *';
			$q .= ' FROM '.$this->_permiso->getUsersTableName();
			$q .= ' WHERE id = :id';
			
			$bind = array(':id' => $id);
			$statement = $this->_database->prepare($q);
			$statement->execute($bind);
			$result = $statement->fetchAll(Zend_Db::FETCH_ASSOC);
			return ($result) ? $result[0] : null;
		}
		catch (Exception $exception) {
			throw new Sitengine_Permiso_Exception('error looking up user by id', $exception);
		}
    }
    
    
    
    public function findUserByNickname($nickname)
    {
    	try {
			$q  = 'SELECT *';
			$q .= ' FROM '.$this->_permiso->getUsersTableName();
			$q .= ' WHERE nickname = :nickname';
			
			$bind = array(':nickname' => $nickname);
			$statement = $this->_database->prepare($q);
			$statement->execute($bind);
			$result = $statement->fetchAll(Zend_Db::FETCH_ASSOC);
			return ($result) ? $result[0] : null;
		}
		catch (Exception $exception) {
			throw new Sitengine_Permiso_Exception('error looking up user by nickname', $exception);
		}
    }
    
    
    
    public function authenticateUser($name, $password='')
    {
    	try {
			$q  = 'SELECT id, lastLogin, name, nickname, firstname, lastname, language, timezone, email, password';
			$q .= ' FROM '.$this->_permiso->getUsersTableName();
			$q .= ' WHERE name = :name';
			#$q .= ' AND '.Sitengine_Permiso::FIELD_OID.' = :oid';
			$q .= ' AND enabled = "1"';
			$q .= ($password!='') ? ' AND password = '.$this->_database->quote($password) : '';
			
			$bind = array(
				':name' => $name,
				#':oid' => $this->_permiso->getOrganization()->getId()
			);
			$statement = $this->_database->prepare($q);
			$statement->execute($bind);
			$result = $statement->fetchAll(Zend_Db::FETCH_ASSOC);
			#Sitengine_Debug::print_r($result);
			return ($result) ? $result[0] : null;
		}
		catch (Exception $exception) {
			throw new Sitengine_Permiso_Exception('error authenticating user', $exception);
		}
    }
    
    
    
    public function rootUsesDefaultPassword()
    {
    	try {
			$q  = 'SELECT id';
			$q .= ' FROM '.$this->_permiso->getUsersTableName();
			$q .= ' WHERE ';
			#$q .= ' id = :oid';
			$q .= ' password = "63a9f0ea7bb98050796b649e85481845"';
			
			$bind = array(
				#':oid' => $this->_permiso->getOrganization()->getId()
			);
			
			$statement = $this->_database->prepare($q);
			$statement->execute($bind);
			$result = $statement->fetchAll(Zend_Db::FETCH_ASSOC);
			return ($result) ? true : false;
		}
		catch (Exception $exception) {
			throw new Sitengine_Permiso_Exception('error checking root default password', $exception);
		}
    }
    
    
    
    public function updateUser($id, array $data)
    {
    	try {
    		return $this->_database->update(
    			$this->_permiso->getUsersTableName(),
    			$data,
    			"id = '$id'"
    		);
		}
		catch (Exception $exception) {
			throw new Sitengine_Permiso_Exception('error updating user', $exception);
		}
    }
    
    
    
    # used for user select lists
    public function getAllUsers()
    {
    	try {
			$q  = 'SELECT id, name, firstname, lastname';
			$q .= ' FROM '.$this->_permiso->getUsersTableName();
			#$q .= ' WHERE '.Sitengine_Permiso::FIELD_OID.' = :oid';
			$q .= ' ORDER BY name';
			
			$bind = array(
				#':oid' => $this->_permiso->getOrganization()->getId()
			);
			
			$statement = $this->_database->prepare($q);
			$statement->execute($bind);
			$result = $statement->fetchAll(Zend_Db::FETCH_ASSOC);
			$users = array();
			
			foreach($result as $row)
			{
				$fullname = '';
				$firstname = $row['firstname'];
				$lastname = $row['lastname'];
				if($firstname && $lastname) { $fullname = ' ('.$firstname.' '.$lastname.')'; }
				else if($firstname && !$lastname) { $fullname = ' ('.$firstname.')'; }
				else if(!$firstname && $lastname) { $fullname = ' ('.$lastname.')'; }
				$users[$row['id']] = $row['name'].$fullname;
			}
			return $users;
		}
		catch (Exception $exception) {
			throw new Sitengine_Permiso_Exception('error looking up all users', $exception);
		}
    }
    
    
    
    # used for group select lists
    public function getAllGroups()
    {
    	try {
			$q  = 'SELECT id, name';
			$q .= ' FROM '.$this->_permiso->getGroupsTableName();
			#$q .= ' WHERE '.Sitengine_Permiso::FIELD_OID.' = :oid';
			$q .= ' ORDER BY name';
			
			$bind = array(
				#':oid' => $this->_permiso->getOrganization()->getId()
			);
			
			$statement = $this->_database->prepare($q);
			$statement->execute($bind);
			$result = $statement->fetchAll(Zend_Db::FETCH_ASSOC);
			$groups = array();
			foreach($result as $row) { $groups[$row['id']] = $row['name']; }
			return $groups;
		}
		catch (Exception $exception) {
			throw new Sitengine_Permiso_Exception('error looing up all groups', $exception);
		}
    }
    
    
    
    public function findMembershipById($id)
    {
    	try {
			$q  = 'SELECT groupId FROM '.$this->_permiso->getMembershipsTableName();
			$q .= ' WHERE id = '.$this->_database->quote($id);
			$statement = $this->_database->prepare($q);
			$statement->execute();
			$result = $statement->fetchAll(Zend_Db::FETCH_ASSOC);
			return ($result) ? $result[0] : null;
		}
		catch (Exception $exception) {
			throw new Sitengine_Permiso_Exception('error finding membership by id', $exception);
		}
    }
    
}
?>