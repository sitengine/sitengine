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


class Sitengine_Permiso_Model_Account_LoginForm extends Zend_Form
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
		$hiddens .= $this->getTargetElement()->render($view);
		return $hiddens;
	}
    
    
    
    public function start()
    {
    	if($this->_started) { return $this; }
    	
    	$this->_started = true;
    	
    	return $this
    		->setMethod(self::METHOD_POST)
    		->setEnctype(self::ENCTYPE_MULTIPART)
			->addTargetElement()
			->addNameElement()
			->addPasswordElement()
			->addSubmitElement()
		;
    }
	
	
	
	
	
	protected $_targetParam = 'target';
	
	public function addTargetElement()
	{
        require_once 'Zend/Form/Element/Hidden.php';
     	$element = new Zend_Form_Element_Hidden($this->getTargetParam());
     	$element
     		->clearDecorators()
			->addDecorator('viewHelper')
        ;
        $this->addElement($element);
        return $this;
	}
	
	public function getTargetElement()
	{
		return $this->getElement($this->getTargetParam());
	}
	
	public function setTargetParam($target)
	{
		$this->_targetParam = $target;
		return $this;
	}
	
	public function getTargetParam()
	{
		return $this->_targetParam;
	}
	
	public function getTargetVal()
	{
		return $this->getValue($this->getTargetParam());
	}
	
	
	
	
	
	
	
	
	
	protected $_nameParam = 'name';
	
	public function addNameElement()
	{
        require_once 'Zend/Form/Element/Text.php';
     	$element = new Zend_Form_Element_Text($this->getNameParam());
     	$element
     		->clearDecorators()
			->addDecorator('viewHelper')
        ;
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
	
	public function addPasswordElement()
	{
        require_once 'Zend/Form/Element/Password.php';
     	$element = new Zend_Form_Element_Password($this->getPasswordParam());
     	$element
     		->clearDecorators()
			->addDecorator('viewHelper')
        ;
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
	
	
	
	
	
	
	
	
	
	protected $_submitParam = 'submit';
	
	public function addSubmitElement()
	{
		require_once 'Zend/Form/Element/Submit.php';
     	$submit = new Zend_Form_Element_Submit(
     		$this->getSubmitParam(),
     		$this->getTranslator()->translate('accountLoginFormSubmitButton')
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