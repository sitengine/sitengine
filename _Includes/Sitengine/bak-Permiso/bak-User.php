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


class Sitengine_Permiso_User
{
    
	protected $_permiso;
    protected $_lifetime = 3600;
	protected $_namespace = null;
    #protected $_isRoot = false;
    #protected $_isAdministratorsMember = false;
    #protected $_hasSupervisorRights = false;
    #protected $_hasModeratorRights = false;
    protected $_isLoggedIn = false;
    #protected $_isLoginSuccess = false;
    protected $_lastLogin = null;
    protected $_id = null;
    protected $_name = null;
    protected $_nickname = null;
    protected $_firstname = null;
    protected $_lastname = null;
    protected $_email = null;
    protected $_language = null;
    protected $_timezone = null;
    
    /*
    protected $_policyNameRequired = false;
    protected $_policyNicknameRequired = false;
    protected $_policyFirstnameRequired = false;
    protected $_policyLastnameRequired = false;
    protected $_policyEmailRequired = true;
    protected $_policyLanguageRequired = false;
    protected $_policyTimezoneRequired = false;
    
    
    public function nameRequired() { return $this->_policyNameRequired; }
    */
    
    
    
    public function __construct(
    	Sitengine_Permiso $permiso,
    	$lifetime = 3600
    )
    {
        $this->_permiso = $permiso;
        $this->_lifetime = $lifetime;
        $this->_reset();
    }
    
    
    
    # turn off useragent check when submitting from a flash application
    # call reinjectUseragent() after authenticate otherwise subjequent authenticate() will fail
    public function authenticate($loginu, $loginp)
    {
        try {
            # login attempt
            if($loginu && $loginp)
            {
                $loginu = mb_strtolower($loginu);
                
                if($loginu==Sitengine_Permiso::GUEST_NAME || $loginu==Sitengine_Permiso::LOSTFOUND_NAME) {
                    $this->deauthenticate();
                    return false;
                }
                else {
                    $data = $this->_permiso->getDirectory()->authenticateUser($loginu, md5($loginp));
                    if(!$data) { $this->deauthenticate(); return false; }
                    else { $this->_setCredentials(true, $data); return true; }
                }
            }
            # try to authenticate from session
            else {
                $expires = (isset($this->_namespace->expires)) ? $this->_namespace->expires : '';
                $ipAddress = (isset($this->_namespace->ipAddress)) ? $this->_namespace->ipAddress : '';
                #$ua = (isset($this->_namespace->userAgent)) ? $this->_namespace->userAgent : '';
                $name = (isset($this->_namespace->username)) ? $this->_namespace->username : '';
                
                if($name == Sitengine_Permiso::GUEST_NAME) {
                	#print 'name == guestname'; exit;
                    $this->deauthenticate();
                    return false;
                }
                # session expired
                else if($expires < time() && $expires != '') {
                	#print 'expired'; exit;
                    $this->deauthenticate();
                    return false;
                }
                # session possibly hijacked
                else if($ipAddress != $_SERVER['REMOTE_ADDR']) {
                	#print 'ipaddress ne remote address'; exit;
                    $this->deauthenticate();
                    return false;
                }
                /*
                # session possibly hijacked
                else if($ua != $_SERVER['HTTP_USER_AGENT']) {
                	#print 'useragen ne useragent'; exit;
                    $this->deauthenticate();
                    return false;
                }
                */
                # authenticate user from session
                else {
                    $data = $this->_permiso->getDirectory()->authenticateUser($name);
                    if(!$data) { $this->deauthenticate(); return false; }
                    else { $this->_setCredentials(false, $data); return true; }
                }
            }
        }
        catch (Exception $exception) { throw $exception; }
    }
    
    
    
    
    public function reauthenticate($loginu)
    {
        try {
            $data = $this->_permiso->getDirectory()->authenticateUser($loginu);
            if(!$data) { $this->deauthenticate(); return false; }
            else { $this->_setCredentials(false, $data); return true; }
        }
        catch (Exception $exception) { throw $exception; }
    }
    
    
    
    
    public function deauthenticate()
    {
        $this->_reset();
        $this->_namespace->unsetAll();
    }
    
    
    
    
    protected function _setCredentials($isLogin, array $data)
    {
        try {
        	require_once 'Zend/Date.php';
        	$date = new Zend_Date();
			$date->setTimezone('UTC');
			$now = $date->get(Zend_Date::ISO_8601);
			
            if($isLogin) {
            	Zend_Session::regenerateId();
                $isLoginSuccess = true;
                $lastLogin = $data['lastLogin'];
                $updateData = array(
                    'lastLogin' => $now,
                    'lastRequest' => $now
                );
            }
            else {
                $isLoginSuccess = false;
                $lastLogin = (isset($this->_namespace->lastLogin)) ? $this->_namespace->lastLogin : '';
                $updateData = array(
                    'lastRequest' => $now
                );
            }
            # update user with last login/last request datetime
            $this->_permiso->getDirectory()->updateUser($data['id'], $updateData);
            
            #$this->_isRoot = ($data['id'] == Sitengine_Permiso::UID_ROOT);
            #$this->_hasModeratorRights = false;
            #$this->_isAdministratorsMember = false;
            #$this->_hasSupervisorRights = false;
            #$this->_hasModeratorRights = false;
            $this->_isLoggedIn = true;
            #$this->_isLoginSuccess = $isLoginSuccess;
            $this->_lastLogin = $lastLogin;
            $this->_id = $data['id'];
            $this->_name = $data['name'];
            $this->_nickname = $data['nickname'];
            $this->_firstname = $data['firstname'];
            $this->_lastname = $data['lastname'];
            $this->_email = $data['email'];
            $this->_language = $data['language'];
            $this->_timezone = $data['timezone'];
            /*
            $memberships = $this->_permiso->getDirectory()->getUserMemberships($data['id']);
            
            foreach($memberships as $membership)
            {
                if($membership['groupId']==$this->_permiso->getOrganization()->getId()) {
                    $this->_isAdministratorsMember = true;
                }
                if($membership['supervisor']=='1') {
                    $this->_hasSupervisorRights = true;
                }
                if($membership['moderator']=='1') {
                    $this->_hasModeratorRights = true;
                }
            }
            */
            $this->_namespace->expires = time()+$this->_lifetime;
            $this->_namespace->username = $this->_name;
            $this->_namespace->lastLogin = $this->_lastLogin;
            $this->_namespace->ipAddress = $_SERVER['REMOTE_ADDR'];
            #$this->_namespace->userAgent = $_SERVER['HTTP_USER_AGENT'];
        }
        catch (Exception $exception) { throw $exception; }
    }
    
    
      
    
    
    protected function _reset()
    {
    	require_once 'Zend/Session/Namespace.php';
		$this->_namespace = new Zend_Session_Namespace(__CLASS__);
		
        #$this->_isRoot = false;
        #$this->_isAdministratorsMember = false;
        #$this->_hasSupervisorRights = false;
        #$this->_hasModeratorRights = false;
        $this->_isLoggedIn = false;
        #$this->_isLoginSuccess = false;
        $this->_lastLogin = null;
        $this->_id = null;
        $this->_name = Sitengine_Permiso::GUEST_NAME;
        $this->_nickname = Sitengine_Permiso::GUEST_NAME;
        $this->_firstname = null;
        $this->_lastname = null;
        $this->_email = null;
        $this->_language = null;
        $this->_timezone = null;
    }
    
    
    
    
    public function getData(Zend_Locale $locale, $timezone = 'UTC')
    {
    	/*
    	if(!$this->_lastLogin) { $lastLogin = ''; }
    	else {
    		require_once 'Zend/Date.php';
			$lastLogin = new Zend_Date($this->_lastLogin, Zend_Date::ISO_8601, $locale);
			$lastLogin->setTimezone($timezone);
		}
        */
        return array(
            #'isRoot' => $this->_isRoot,
            #'isAdministratorsMember' => $this->_isAdministratorsMember,
            #'hasSupervisorRights' => $this->_hasSupervisorRights,
            #'hasModeratorRights' => $this->_hasModeratorRights,
            'isLoggedIn' => $this->_isLoggedIn,
            #'isLoginSuccess' => $this->_isLoginSuccess,
            'lastLogin' => $this->_lastLogin,
            'id' => $this->_id,
            'name' => $this->_name,
            'nickname' => $this->_nickname,
            'firstname' => $this->_firstname,
            'lastname' => $this->_lastname,
            'email' => $this->_email,
            'language' => $this->_language,
            'timezone' => $this->_timezone
        );
    }
    
    
    
    
    #public function isRoot() { return $this->_isRoot; }
    #public function isAdministratorsMember() { return $this->_isAdministratorsMember; }
    #public function hasSupervisorRights() { return $this->_hasSupervisorRights; }
    #public function hasModeratorRights() { return $this->_hasModeratorRights; }
    public function isLoggedIn() { return $this->_isLoggedIn; }
    #public function isLoginSuccess() { return $this->_isLoginSuccess; }
    public function lastLogin() { return $this->_lastLogin; }
    public function getId() { return $this->_id; }
    public function getName() { return $this->_name; }
    public function getNickname() { return $this->_nickname; }
    public function getLastname() { return $this->_lastname; }
    public function getFirstname() { return $this->_firstname; }
    public function getEmail() { return $this->_email; }
    public function getLanguage() { return $this->_language; }
    public function getTimezone() { return $this->_timezone; }
    
}
?>