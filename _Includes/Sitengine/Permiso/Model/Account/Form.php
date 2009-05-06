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
 
 
require_once 'Zend/Form.php';


class Sitengine_Permiso_Model_Account_Form extends Zend_Form
{
	
	
	protected $_permiso = null;
	protected $_started = false;
	
	
	
	public function __construct($options = null)
    {
        if(
    		!isset($options['permiso']) ||
    		!$options['permiso'] instanceof Sitengine_Permiso
    	) {
			require_once 'Sitengine/Permiso/Exception.php';
			throw new Sitengine_Permiso_Exception(get_class($this).' init error');
		}
		
		parent::__construct($options);
		$this->_permiso = $options['permiso'];
		$this->setTranslator($this->getPermiso()->getModelTranslator());
    }
    
    
    
    public function getPermiso()
    {
    	return $this->_permiso;
    }
    
    
    
    public function renderHiddens(Zend_View $view)
	{
		$hiddens  = '';
		$hiddens .= $this->getIntendedMethodElement()->render($view);
		return $hiddens;
	}
    
    
    
    public function start(Sitengine_Permiso_Model_Account $account)
    {
    	if($this->_started) { return $this; }
    	
    	$this->_started = true;
    	
    	$this
    		->setMethod(self::METHOD_POST)
    		->setEnctype(self::ENCTYPE_MULTIPART)
			->addIntendedMethodElement()
			->addNameElement(($account->isLoaded()) ? $account->getName() : '')
			->addNicknameElement(($account->isLoaded()) ? $account->getNickname() : '')
			->addFirstnameElement(($account->isLoaded()) ? $account->getFirstname() : '')
			->addLastnameElement(($account->isLoaded()) ? $account->getLastname() : '')
			->addCountryElement(($account->isLoaded()) ? $account->getCountry() : '')
			->addTimezoneElement(($account->isLoaded()) ? $account->getTimezone() : '')
			->addNewsletterElement(($account->isLoaded()) ? $account->getNewsletter() : '')
			->addEnabledElement(($account->isLoaded()) ? $account->getEnabled() : '')
			->addSubmitElement()
		;
		
		if($account->isLoaded())
		{
			$method = Sitengine_Env::METHOD_PUT;
			
			$this
				->addPasswordElement(false)
				->addPasswordVerifyElement(false)
			;
		}
		else {
			$method = Sitengine_Env::METHOD_POST;
			
			$this
				->addPasswordElement()
				->addPasswordVerifyElement()
			;
		}
		
		$this->getIntendedMethodElement()->setValue($method);
    }
    
    
    
    
    
    protected $_intendedMethodParam = '_method';
	
	public function addIntendedMethodElement()
	{
		require_once 'Zend/Form/Element/Hidden.php';
     	$element = new Zend_Form_Element_Hidden($this->getIntendedMethodParam());
     	$element
     		->clearDecorators()
			->addDecorator('viewHelper')
        	->setValue('')
        ;
        $this->addElement($element);
        return $this;
	}
	
	public function getIntendedMethodElement()
	{
		return $this->getElement($this->getIntendedMethodParam());
	}
	
	public function setIntendedMethodParam($name)
	{
		$this->_intendedMethodParam = $name;
		return $this;
	}
	
	public function getIntendedMethodParam()
	{
		return $this->_intendedMethodParam;
	}
	
	public function getIntendedMethodVal()
	{
		return $this->getValue($this->getIntendedMethodParam());
	}
	
	
	
	
	
	
	
	
	
	protected $_nameParam = 'name';
	
	public function addNameElement($default = '')
	{
		require_once 'Zend/Validate/NotEmpty.php';
		$notEmpty = new Zend_Validate_NotEmpty();
		$notEmpty->setMessages(
			array(
				Zend_Validate_NotEmpty::IS_EMPTY => $this->getTranslator()->translate('accountFormHintNameNotEmpty')
			)
		);
		
		require_once 'Zend/Filter/StringToLower.php';
		$filter = new Zend_Filter_StringToLower('utf-8');
		
        require_once 'Zend/Form/Element/Text.php';
     	$element = new Zend_Form_Element_Text($this->getNameParam());
     	$element
     		->clearDecorators()
			->addDecorator('viewHelper')
			->addValidator($notEmpty)
			->setRequired(true)
        	->addFilter($filter)
        	->setValue($default)
        ;
        
        if($this->getPermiso()->getRequest()->getPost($this->getNameParam()))
		{
			require_once 'Zend/Validate/EmailAddress.php';
			$emailAddress = new Zend_Validate_EmailAddress();
			$emailAddressMessage = $this->getTranslator()->translate('accountFormHintNameEmailAddressInvalid');
			$emailAddress->setMessages(
				array(
					Zend_Validate_EmailAddress::INVALID => $emailAddressMessage,
					Zend_Validate_EmailAddress::INVALID_HOSTNAME => $emailAddressMessage,
					Zend_Validate_EmailAddress::INVALID_MX_RECORD => $emailAddressMessage,
					Zend_Validate_EmailAddress::DOT_ATOM => $emailAddressMessage,
					Zend_Validate_EmailAddress::QUOTED_STRING => $emailAddressMessage,
					Zend_Validate_EmailAddress::INVALID_LOCAL_PART => $emailAddressMessage,
					Zend_Validate_EmailAddress::LENGTH_EXCEEDED => $emailAddressMessage
				)
			);
			
			$element
				->addValidator($emailAddress)
			;
		}
		
        $this->addElement($element);
        return $this;
	}
	
	public function getNameElement()
	{
		return $this->getElement($this->getNameParam());
	}
	
	public function setNameParam($name)
	{
		$this->_nameParam = $name;
		return $this;
	}
	
	public function getNameParam()
	{
		return $this->_nameParam;
	}
	
	public function getNameVal()
	{
		return $this->getValue($this->getNameParam());
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	protected $_passwordParam = 'password';
	
	public function addPasswordElement($isInsert = true)
	{
        require_once 'Zend/Form/Element/Password.php';
     	$element = new Zend_Form_Element_Password($this->getPasswordParam());
     	$element
     		->clearDecorators()
			->addDecorator('viewHelper')
			->setRenderPassword(true)
        ;
        
        $password = $this->getPermiso()->getRequest()->getPost($this->getPasswordParam());
        
        if($isInsert || $password)
		{
			require_once 'Zend/Validate/NotEmpty.php';
			$notEmpty = new Zend_Validate_NotEmpty();
			$notEmpty->setMessages(
				array(
					Zend_Validate_NotEmpty::IS_EMPTY => $this->getTranslator()->translate('accountFormHintPasswordNotEmpty')
				)
			);
			
			$element
				->addValidator($notEmpty)
				->setRequired(true)
			;
			
			if($password)
			{
				require_once 'Zend/Validate/StringLength.php';
				$stringLength = new Zend_Validate_StringLength($this->getPermiso()->getMinimalPasswordLength());
				$stringLength->setMessages(
					array(
						Zend_Validate_StringLength::TOO_SHORT => sprintf(
								$this->getTranslator()->translate('accountFormHintPasswordStringLengthTooShort'),
								$this->getPermiso()->getMinimalPasswordLength()
							)
						
					)
				);
				
				$element
					->addValidator($stringLength)
				;
			}
		}
		
        $this->addElement($element);
        return $this;
	}
	
	public function getPasswordElement()
	{
		return $this->getElement($this->getPasswordParam());
	}
	
	public function setPasswordParam($password)
	{
		$this->_passwordParam = $password;
		return $this;
	}
	
	public function getPasswordParam()
	{
		return $this->_passwordParam;
	}
	
	public function getPasswordVal()
	{
		return $this->getValue($this->getPasswordParam());
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	protected $_passwordVerifyParam = 'passwordVerify';
	
	public function addPasswordVerifyElement($isInsert = true)
	{
        require_once 'Zend/Form/Element/Password.php';
     	$element = new Zend_Form_Element_Password($this->getPasswordVerifyParam());
     	$element
     		->clearDecorators()
			->addDecorator('viewHelper')
			->setRenderPassword(true)
        ;
        
        $password = $this->getPermiso()->getRequest()->getPost($this->getPasswordParam());
        $passwordVerify = $this->getPermiso()->getRequest()->getPost($this->getPasswordVerifyParam());
        
        if($isInsert || $password)
		{
			require_once 'Zend/Validate/NotEmpty.php';
			$notEmpty = new Zend_Validate_NotEmpty();
			$notEmpty->setMessages(
				array(
					Zend_Validate_NotEmpty::IS_EMPTY => $this->getTranslator()->translate('accountFormHintPasswordVerifyNotEmpty')
				)
			);
			
			$element
				->addValidator($notEmpty)
				->setRequired(true)
			;
			
			
			if($password && $passwordVerify)
			{
				require_once 'Zend/Validate/Identical.php';
				$identical = new Zend_Validate_Identical($password);
				$identical->setMessages(
					array(
						Zend_Validate_Identical::NOT_SAME => $this->getTranslator()->translate('accountFormHintPasswordVerifyIdentical')
					)
				);
				
				$element
					->addValidator($identical)
				;
			}
		}
		
        $this->addElement($element);
        return $this;
	}
	
	public function getPasswordVerifyElement()
	{
		return $this->getElement($this->getPasswordVerifyParam());
	}
	
	public function setPasswordVerifyParam($passwordVerify)
	{
		$this->_passwordVerifyParam = $passwordVerify;
		return $this;
	}
	
	public function getPasswordVerifyParam()
	{
		return $this->_passwordVerifyParam;
	}
	
	public function getPasswordVerifyVal()
	{
		return $this->getValue($this->getPasswordVerifyParam());
	}
	
	
	
	
	
	
	
	
	protected $_nicknameParam = 'nickname';
	
	public function addNicknameElement($default = '')
	{
		require_once 'Zend/Validate/NotEmpty.php';
		$notEmpty = new Zend_Validate_NotEmpty();
		$notEmpty->setMessages(
			array(
				Zend_Validate_NotEmpty::IS_EMPTY => $this->getTranslator()->translate('accountFormHintNicknameNotEmpty')
			)
		);
		
        require_once 'Zend/Form/Element/Text.php';
     	$element = new Zend_Form_Element_Text($this->getNicknameParam());
     	$element
     		->clearDecorators()
			->addDecorator('viewHelper')
			->addValidator($notEmpty)
        	->setValue($default)
        	->setRequired(true)
        ;
        $this->addElement($element);
        return $this;
	}
	
	public function getNicknameElement()
	{
		return $this->getElement($this->getNicknameParam());
	}
	
	public function setNicknameParam($nickname)
	{
		$this->_nicknameParam = $nickname;
		return $this;
	}
	
	public function getNicknameParam()
	{
		return $this->_nicknameParam;
	}
	
	public function getNicknameVal()
	{
		return $this->getValue($this->getNicknameParam());
	}
	
	
	
	
	
	
	
	
	
	
	protected $_firstnameParam = 'firstname';
	
	public function addFirstnameElement($default = '')
	{
		require_once 'Zend/Validate/NotEmpty.php';
		$notEmpty = new Zend_Validate_NotEmpty();
		$notEmpty->setMessages(
			array(
				Zend_Validate_NotEmpty::IS_EMPTY => $this->getTranslator()->translate('accountFormHintFirstnameNotEmpty')
			)
		);
		
        require_once 'Zend/Form/Element/Text.php';
     	$element = new Zend_Form_Element_Text($this->getFirstnameParam());
     	$element
     		->clearDecorators()
			->addDecorator('viewHelper')
			->addValidator($notEmpty)
        	->setValue($default)
        	->setRequired(true)
        ;
        $this->addElement($element);
        return $this;
	}
	
	public function getFirstnameElement()
	{
		return $this->getElement($this->getFirstnameParam());
	}
	
	public function setFirstnameParam($firstname)
	{
		$this->_firstnameParam = $firstname;
		return $this;
	}
	
	public function getFirstnameParam()
	{
		return $this->_firstnameParam;
	}
	
	public function getFirstnameVal()
	{
		return $this->getValue($this->getFirstnameParam());
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	protected $_lastnameParam = 'lastname';
	
	public function addLastnameElement($default = '')
	{
		require_once 'Zend/Validate/NotEmpty.php';
		$notEmpty = new Zend_Validate_NotEmpty();
		$notEmpty->setMessages(
			array(
				Zend_Validate_NotEmpty::IS_EMPTY => $this->getTranslator()->translate('accountFormHintLastnameNotEmpty')
			)
		);
		
        require_once 'Zend/Form/Element/Text.php';
     	$element = new Zend_Form_Element_Text($this->getLastnameParam());
     	$element
     		->clearDecorators()
			->addDecorator('viewHelper')
			->addValidator($notEmpty)
        	->setValue($default)
        	->setRequired(true)
        ;
        $this->addElement($element);
        return $this;
	}
	
	public function getLastnameElement()
	{
		return $this->getElement($this->getLastnameParam());
	}
	
	public function setLastnameParam($lastname)
	{
		$this->_lastnameParam = $lastname;
		return $this;
	}
	
	public function getLastnameParam()
	{
		return $this->_lastnameParam;
	}
	
	public function getLastnameVal()
	{
		return $this->getValue($this->getLastnameParam());
	}
	
	
	
	
	
	
	
	
	
	
	
	protected $_countryParam = 'country';
	
	public function addCountryElement($default = '')
	{
		require_once 'Zend/Validate/NotEmpty.php';
		$notEmpty = new Zend_Validate_NotEmpty();
		$notEmpty->setMessages(
			array(
				Zend_Validate_NotEmpty::IS_EMPTY => $this->getTranslator()->translate('accountFormHintCountryNotEmpty')
			)
		);
		
		if($this->getPermiso()->getEnv()->hasCache())
		{
			require_once 'Zend/Locale/Data.php';
			Zend_Locale_Data::setCache($this->getPermiso()->getEnv()->getCache());
		}
		
		$countries = array();
		
		require_once 'Zend/Locale/Data.php';
		$territories = Zend_Locale_Data::getList(
			$this->getPermiso()->getEnv()->getLocaleInstance(),
			'territory'
		);
		
		foreach($territories as $key => $name)
		{
			if(preg_match('/^[A-Z]{2,2}$/', $key))
			{
				# filter out undesired countries
				if(!preg_match('/(ZZ)/', $key))
				{
					$countries[$key] = $name;
				}
			}
		}
		asort($countries);
		
		$countries = array_merge(
			array('' => $this->getTranslator()->translate('accountFormFieldvalCountry')),
			$countries
		);
		
		
        require_once 'Zend/Form/Element/Select.php';
     	$element = new Zend_Form_Element_Select($this->getCountryParam());
     	$element
     		->clearDecorators()
			->addDecorator('viewHelper')
			->addValidator($notEmpty)
        	->setValue($default)
        	->setDisableTranslator(true) # increases performance
        	->setRequired(true)
        ;
        
        // set options directly for massively enhanced performance
        $element->options = $countries;
        $this->addElement($element);
        return $this;
	}
	
	public function getCountryElement()
	{
		return $this->getElement($this->getCountryParam());
	}
	
	public function setCountryParam($country)
	{
		$this->_countryParam = $country;
		return $this;
	}
	
	public function getCountryParam()
	{
		return $this->_countryParam;
	}
	
	public function getCountryVal()
	{
		return $this->getValue($this->getCountryParam());
	}
	
	
	
	
	
	
	
	
	
	
	protected $_timezoneParam = 'timezone';
	
	public function addTimezoneElement($default = '')
	{
		require_once 'Zend/Validate/NotEmpty.php';
		$notEmpty = new Zend_Validate_NotEmpty();
		$notEmpty->setMessages(
			array(
				Zend_Validate_NotEmpty::IS_EMPTY => $this->getTranslator()->translate('accountFormHintTimezoneNotEmpty')
			)
		);
		
        require_once 'Zend/Form/Element/Select.php';
     	$element = new Zend_Form_Element_Select($this->getTimezoneParam());
     	
     	$element
     		->clearDecorators()
			->addDecorator('viewHelper')
			->addValidator($notEmpty)
        	->setValue($default)
        	->setDisableTranslator(true) # increases performance
        	->setRequired(true)
        ;
        
        // set options directly for massively enhanced performance
        $element->options = $this->getPermiso()->getEnv()->getTimezones();
        $this->addElement($element);
        return $this;
	}
	
	public function getTimezoneElement()
	{
		return $this->getElement($this->getTimezoneParam());
	}
	
	public function setTimezoneParam($timezone)
	{
		$this->_timezoneParam = $timezone;
		return $this;
	}
	
	public function getTimezoneParam()
	{
		return $this->_timezoneParam;
	}
	
	public function getTimezoneVal()
	{
		return $this->getValue($this->getTimezoneParam());
	}
	
	
	
	
	
	
	
	
	
	
	
	protected $_newsletterParam = 'newsletter';
	
	public function addNewsletterElement($default = '')
	{
        require_once 'Zend/Form/Element/Checkbox.php';
     	$element = new Zend_Form_Element_Checkbox($this->getNewsletterParam());
     	$element
     		->clearDecorators()
			->addDecorator('viewHelper')
        	->setValue($default)
        ;
        $this->addElement($element);
        return $this;
	}
	
	public function getNewsletterElement()
	{
		return $this->getElement($this->getNewsletterParam());
	}
	
	public function setNewsletterParam($newsletter)
	{
		$this->_newsletterParam = $newsletter;
		return $this;
	}
	
	public function getNewsletterParam()
	{
		return $this->_newsletterParam;
	}
	
	public function getNewsletterVal()
	{
		return $this->getValue($this->getNewsletterParam());
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	protected $_enabledParam = 'enabled';
	
	public function addEnabledElement($default = '')
	{
        require_once 'Zend/Form/Element/Checkbox.php';
     	$element = new Zend_Form_Element_Checkbox($this->getEnabledParam());
     	$element
     		->clearDecorators()
			->addDecorator('viewHelper')
        	->setValue($default)
        ;
        $this->addElement($element);
        return $this;
	}
	
	public function getEnabledElement()
	{
		return $this->getElement($this->getEnabledParam());
	}
	
	public function setEnabledParam($enabled)
	{
		$this->_enabledParam = $enabled;
		return $this;
	}
	
	public function getEnabledParam()
	{
		return $this->_enabledParam;
	}
	
	public function getEnabledVal()
	{
		return $this->getValue($this->getEnabledParam());
	}
	
	
	
	
	
	
	
	
	
	
	protected $_submitParam = 'submit';
	
	public function addSubmitElement()
	{
		require_once 'Zend/Form/Element/Submit.php';
     	$submit = new Zend_Form_Element_Submit(
     		$this->getSubmitParam(),
     		$this->getTranslator()->translate('accountFormFieldvalSubmitButton')
     	);
     	$submit
     		->clearDecorators()
			->addDecorator('viewHelper')
		;
		$this->addElement($submit);
		return $this;
	}
	
	public function getSubmitElement()
	{
		return $this->getElement($this->getSubmitParam());
	}
	
	public function setSubmitParam($name)
	{
		$this->_submitParam = $name;
		return $this;
	}
	
	public function getSubmitParam()
	{
		return $this->_submitParam;
	}
	
	public function getSubmitVal()
	{
		return $this->getValue($this->getSubmitParam());
	}
    
}



?>