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



require_once 'Sitengine/Regex.php';


/*
 * Collection of string handling functions. Call Sitengine_String::<function_name>.
 *
 */
abstract class Sitengine_String
{
	
	/*
	function stripwhitespace($buffer)
	{
		$pzcr = 0;
		$pzed = strlen($buffer)-1;
		$rst = "";
		while($pzcr<$pzed)
		{
			$t_poz_start = stripos($buffer,"<textarea",$pzcr);
			if($t_poz_start===false)
			{
				$bufferstp = substr($buffer,$pzcr);
				$temp = stripBuffer($bufferstp);
				$rst .= $temp;
				$pzcr = $pzed;
			}
			else {
				$bufferstp = substr($buffer,$pzcr,$t_poz_start-$pzcr);
				$temp = stripBuffer($bufferstp);
				$rst .= $temp;
				$t_poz_end = stripos($buffer,"</textarea>",$t_poz_start);
				$temp = substr($buffer,$t_poz_start,$t_poz_end-$t_poz_start);
				$rst .= $temp;
				$pzcr = $t_poz_end;
			}
		}
		return $rst;
	}
	
	
	
	function stripBuffer($buffer)
	{
		$buffer = str_replace(array("\r\r\r","\r\r","\r\n","\n\r","\n\n\n","\n\n"),"\n", $buffer);
		
		$buffer = str_replace(array(">\r<a",">\n<a"),"><a", $buffer);
		$buffer = str_replace(array(">\r<b",">\n<b"),"><b", $buffer);
		
		$buffer = str_replace(array(">\r<d",">\n<d"),"><d", $buffer);
		$buffer = str_replace(array(">\r<h",">\n<h"),"><h", $buffer);
		
		$buffer = str_replace(array(">\r<i",">\n<i"),"><i", $buffer);
		$buffer = str_replace(array(">\r<i",">\n<i"),"><i", $buffer);
		
		$buffer = str_replace(array(">\r<l",">\n<l"),"><l", $buffer);
		$buffer = str_replace(array(">\r<m",">\n<m"),"><m", $buffer);
		
		$buffer = str_replace(array(">\r<p",">\n<p"),"><p", $buffer);
		$buffer = str_replace(array(">\r<t",">\n<t"),"><t", $buffer);
		
		$buffer = str_replace(array(">\r</u",">\n</u"),"></u", $buffer);
		$buffer = str_replace(array(">\r</d",">\n</d"),"></d", $buffer);
		
		$buffer = str_replace(array(">\r<!",">\n<!"),"><!", $buffer);
		$buffer = str_replace(array(">\r</h",">\n</h"),"></h", $buffer);
		
		$buffer = str_replace(array("\r<u","\n<u"),"<u", $buffer);
		$buffer = str_replace(array("/>\r","/>\n"),"/>", $buffer);
		
		$buffer = ereg_replace(" {2,}",' ', $buffer);
		$buffer = str_replace("> <","><", $buffer);
		$buffer = str_replace(" &nbsp;","&nbsp;", $buffer);
		
		$buffer = str_replace("&nbsp; ","&nbsp;", $buffer);
		return $buffer;
	}
		
		
	#ob_start("stripwhitespace");
	*/ 

    
    
    /**
     * Add slashes depending on the PHP magic_quotes_gpc setting.
     * This will ensure that the variable is not quoted twice
     * once by the program and once by the magic_quotes_gpc.
     * %paramtype in string
     * %returntype string
     */
    public static function gpcAddSlashes($in)
    {
        return (get_magic_quotes_gpc()) ? $in : addslashes($in);
    }
    
    
    
    /**
     * Strip slashes depending on the PHP magic_quotes_gpc setting.
     * This will ensure that the variable is not stripped twice
     * once by the program and once by the magic_quotes_gpc.
     * %paramtype in string
     * %returntype string
     */
    public static function gpcStripSlashes($in)
    {
        return (get_magic_quotes_gpc()) ? stripslashes($in) : $in;
    }
    
    
    
    /**
     * Strip slashes depending on the PHP magic_quotes_runtime setting.
     * This will ensure that the variable is not stripped twice
     * once by the program and once by the magic_quotes_runtime.
     * %paramtype in string
     * %returntype string
     */
    public static function runtimeStripSlashes($in)
    {
        return (get_magic_quotes_runtime()) ? stripslashes($in) : $in;
    }
    
    
    
    /**
     * Prevent cross site scripting (xss) flaws by replacing
     * problematic chars with their html entity
     *
     */
    public static function html($s)
    {
        $find = array(
            #'/#(?![0-9]+;)/',
            '/(?<!&)#/',
            '/\(/',
            '/\)/',
            '/\|/',
            '/\\\/',
        );
        $repl = array(
            "&#35;",
            "&#40;",
            "&#41;",
            "&#124;",
            "&#92;"
        );
        #$s = htmlentities($s, ENT_QUOTES);
        $s = htmlentities($s, ENT_QUOTES, 'UTF-8');
        return preg_replace($find, $repl, $s);
    }
    
    
    /*
    public static function makeArrayElementsString($path, $firstElementIsVar=false)
    {
        $elements = explode(, $path);
        $s = '';
        if($firstElementIsVar) {
            for($x=0; $x<sizeof($elements); $x++) {
                $s .= ($x==0) ? $elements[0] : "['".$elements[$x]."']";
            }
        }
        else { foreach($elements as $k => $v) { $s .= "['".$v."']"; } }
        return $s;
    }
    */
    
    
    /**
     * Create a pseudo-unique id.
     * Remember to seed the random number generator before use with srand().
     * %returntype string
     */
    public static function createId($length=12)
    {
        // no prefix
        #print md5(uniqid('')).'<br />';

        // better, difficult to guess
        #print uniqid('', true);
        
        $chars = array(
            '0','1','2','3','4','5','6','7','8','9',
            'a','A','b','B','c','C','d','D','e','E',
            'f','F','g','G','h','H','i','I','j','J',
            'k','K','l','L','m','M','n','N','o','O',
            'p','P','q','Q','r','R','s','S','t','T',
            'u','U','v','V','w','W','x','X','y','Y',
            'z','Z'
        );
        
        $id = '';
        
        for($y=0; $y<$length; $y++) {
            $id .= $chars[mt_rand(0, sizeof($chars)-1)];
        }
        return $id;
    }
    
    
    
    
    /**
     * Convert url's and email addresses within a text into hyperlinks.
     * %paramtype data string
     * %returntype string
     */
    public static function text2links($data, $target='_blank')
    {
        $data = self::text2href($data, $target);
        return self::text2mailto($data);
    }
    
    
    
    public static function text2href($data, $target='_blank')
    {
        $target = ($target) ? " target=\"$target\"" : '';
        $data = preg_replace('/(?<!http:\/\/)www\./i', 'http://www.', $data);
        
        $addr  = '/';
        $addr .= 'http\:\/\/(';
        $addr .= Sitengine_Regex::getServerName();
        $addr .= '(\/'.Sitengine_Regex::getFileName().')*';
        $addr .= '(\?[\w\d\.\+;&=%-]*)?';
        $addr .= ')/i';
        return preg_replace($addr, '<a href="http://\1"'.$target.'>\1</a>', $data);
    }
    
    
    
    
    public static function text2mailto($data)
    {
        $email  = '/(';
        $email .= Sitengine_Regex::getMailbox();
        $email .= Sitengine_Regex::getServerName();
        $email .= ')/';
        return preg_replace($email, '<a href="mailto:\1">\1</a>', $data);
    }
    
    
    
    
    public static function hex2bin($hex)
    {
        $hex = preg_replace('/^0x/', '', $hex);
        $len = strlen($hex);
        $bin = '';
        
        # allow hex numbers only
        if($len % 2 == 0 && !preg_match('/[^\da-fA-F]/', $hex)) {
            for($x=1; $x<=$len/2; $x++) {
                $bin .= chr(hexdec(substr($hex, 2*$x-2, 2)));
            }
        }
        return $bin;
    }
    
    
    
    
    public static function obfuscateEmail($address, $clickable = null)
    {
        $ahref = '<a href="mailto:'.$address.'">'.(($clickable === null) ? $address : $clickable).'</a>';
        $function = 'm'.md5(uniqid(''));
        
        $s  = "<script type=\"text/javascript\" language=\"Javascript\">\n";
        $s .= "//<![CDATA[\n";
        $s .= "//<!--\n";
        $s .= "function ".$function."() {\n";
        $s .= "\tvar a = new Array(";
        
        for($x=0; $x<strlen($ahref); $x++) {
            $sep = ($x==0) ? '' : ',';
            $s .= $sep."'".substr($ahref, $x, 1)."'";
        }
        $s .= ");\n";
        $s .= "\tvar s = '';\n";
        $s .= "\tfor(x=0; x<a.length; x++) { s += a[x]; }\n";
        $s .= "\tdocument.write(s);\n";
        $s .= "}\n";
        $s .= $function."();\n";
        $s .= "//-->\n";
        $s .= "//]]>\n";
        $s .= "</script>\n";
        return $s;
    }
    
    
    
    
    public static function truncate(
    	$string,
    	$length = 80,
    	$etc = '...',
    	$breakWords = false,
    	$middle = false
    )
	{
		if ($length == 0)
			return '';
	
		if (mb_strlen($string) > $length) {
			$length -= min($length, mb_strlen($etc));
			if (!$breakWords && !$middle) {
				$string = preg_replace('/\s+?(\S+)?$/', '', mb_substr($string, 0, $length+1));
			}
			if(!$middle) {
				return mb_substr($string, 0, $length) . $etc;
			} else {
				return mb_substr($string, 0, $length/2) . $etc . mb_substr($string, -$length/2);
			}
		} else {
			return $string;
		}
	}
	
	
	
	
	public static function lcFirst($s)
	{
		return preg_replace_callback(
			'/^(\w)/',
			create_function(
				'$matches',
				'return mb_convert_case($matches[1], MB_CASE_LOWER);'
			),
			$s
		);
	}
	
}
?>