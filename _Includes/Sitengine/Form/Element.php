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
 * @package    Sitengine
 * @copyright  Copyright (c) 2007, Christian Hoegl, Switzerland (http://sitengine.org)
 * @license    http://sitengine.org/license/new-bsd     New BSD License
 */



require_once 'Sitengine/String.php';


/*
 * Create HTML input element.
 *
 */
class Sitengine_Form_Element
{
    
    
    protected $_name = '';
    protected $_value = '';
    protected $_id = '';
    protected $_class = '';
    protected $_readonly = false;
    protected $_script = '';
    protected $_prefix = '';
    protected $_postfix = '';
    
    
    # $value = text/plain
    function __construct($name, $value='')
    {
        $this->_name = (string) $name;
        $this->_value = (is_object($value)) ? '' : (string) $value;
    }
    
    
    public function setId($id)
    {
        $this->_id = ($id!='') ? ' id="'.(string) $id.'"' : '';
    }
    
    
    public function setClass($class)
    {
        $this->_class = ($class!='') ? ' class="'.(string) $class.'"' : '';
    }
    
    
    public function readonly()
    {
        $this->_readonly = ' readonly';
    }
    
    
    public function setScript($script)
    {
        $this->_script = ($script !='') ? ' '.(string) $script : '';
    }
    
    /*
    public function setPrefix($prefix)
    {
        $this->_prefix = $prefix;
    }
    
    
    public function setPostfix($postfix)
    {
        $this->_postfix = $postfix;
    }
    */
    
    
    public function getText($size=40, $type='text', $maxlength='')
    {
        $t  = $this->_prefix;
        $t .= '<input';
        $t .= ' name="'.$this->_name.'"';
        $t .= ' value="'.Sitengine_String::html($this->_value).'"';
        $t .= $this->_id;
        $t .= $this->_class;
        $t .= $this->_readonly;
        $t .= $this->_script;
        $t .= ' type="'.(string) $type.'"';
        $t .= ' size="'.(int) $size.'"';
        $t .= ($maxlength!='') ? ' maxlength="'.(int) $maxlength.'"' : '';
        $t .= ' />';
        $t .= $this->_postfix;
        $t .= "\n\n";
        return $t;
    }
    
    
    
    public function getButton($type='submit')
    {
        $t  = $this->_prefix;
        $t .= '<input';
        $t .= ' name="'.$this->_name.'"';
        $t .= ' value="'.Sitengine_String::html($this->_value).'"';
        $t .= $this->_id;
        $t .= $this->_class;
        $t .= $this->_script;
        $t .= ' type="'.(string) $type.'"';
        $t .= ' />';
        $t .= $this->_postfix;
        $t .= "\n\n";
        return $t;
    }
    
    
    
    # $values = text/plain (array)
    public function getSelect(array $values, $size=1, $multiple=false)
    {
        $t = $this->_prefix;
        $t .= '<select';
        $t .= ' name="'.$this->_name.'"';
        $t .= $this->_id;
        $t .= $this->_class;
        $t .= $this->_readonly;
        $t .= $this->_script;
        $t .= ' size="'.(string) $size.'"';
        $t .= ($multiple) ? ' multiple' : '';
        $t .= ">\n";
        
        foreach($values as $k => $v) {
            $t .= '<option';
            $t .= ' value="'.Sitengine_String::html((string) $k).'"';
            $t .= ($k==$this->_value) ? ' selected="selected"' : '';
            $t .= '>';
            $t .= Sitengine_String::html($v);
            $t .= "</option>\n";
        }
        
        $t .= '</select>';
        $t .= $this->_postfix;
        $t .= "\n\n";
        return $t;
    }
    
    
    
    public function getCheckbox($checked=false)
    {
        $t  = $this->_prefix;
        $t .= '<input';
        $t .= ' name="'.$this->_name.'"';
        $t .= ' value="'.Sitengine_String::html($this->_value).'"';
        $t .= $this->_id;
        $t .= $this->_class;
        $t .= $this->_readonly;
        $t .= $this->_script;
        $t .= ' type="checkbox"';
        $t .= ($checked) ? ' checked="checked"' : '';
        $t .= ' />';
        $t .= $this->_postfix;
        $t .= "\n\n";
        return $t;
    }
    
    
    
    public function getRadio($checked=false)
    {
        $t  = $this->_prefix;
        $t .= '<input';
        $t .= ' name="'.$this->_name.'"';
        $t .= ' value="'.Sitengine_String::html($this->_value).'"';
        $t .= $this->_id;
        $t .= $this->_class;
        $t .= $this->_readonly;
        $t .= $this->_script;
        $t .= ' type="radio"';
        $t .= ($checked) ? ' checked="checked"' : '';
        $t .= ' />';
        $t .= $this->_postfix;
        $t .= "\n\n";
        return $t;
    }
    
    
    
    public function getTextarea($cols=40, $rows=10, $wrap='soft')
    {
        $t  = $this->_prefix;
        $t .= '<textarea';
        $t .= ' name="'.$this->_name.'"';
        $t .= $this->_id;
        $t .= $this->_class;
        $t .= $this->_readonly;
        $t .= $this->_script;
        $t .= ' cols="'.(int) $cols.'"';
        $t .= ' rows="'.(int) $rows.'"';
        $t .= ' wrap="'.(string) $wrap.'"';
        $t .= '>';
        $t .= Sitengine_String::html($this->_value);
        $t .= '</textarea>';
        $t .= $this->_postfix;
        $t .= "\n\n";
        return $t;
    }
    
    
    
    public function getFile($size=40)
    {
        $t  = $this->_prefix;
        $t .= '<input';
        $t .= ' name="'.$this->_name.'"';
        $t .= ' value="'.Sitengine_String::html($this->_value).'"';
        $t .= $this->_id;
        $t .= $this->_class;
        $t .= $this->_readonly;
        $t .= $this->_script;
        $t .= ' type="file"';
        $t .= ' size="'.(int) $size.'"';
        $t .= ' />';
        $t .= $this->_postfix;
        $t .= "\n\n";
        return $t;
    }
    
    
    
    # $value = text/plain
    public static function getHidden($name, $value)
    {
    	$value = (is_object($value)) ? '' : (string) $value;
        $t  = '<input';
        $t .= ' name="'.(string) $name.'"';
        $t .= ' value="'.Sitengine_String::html($value).'"';
        $t .= ' type="hidden"';
        $t .= ' />';
        $t .= "\n";
        return $t;
    }
    
}
?>