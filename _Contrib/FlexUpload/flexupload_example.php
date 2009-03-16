<?php
/**
 * flexupload_example.php
 *
 * example how to use the FlexUpload class
 *
 * Copyright (C) 2007 SPLINELAB, Mirko Schaal
 * http://www.splinelab.de/flexupload/
 *
 * All rights reserved
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 2 of the License, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * A copy is found in the textfile GPL and important notices to the license from
 * the author is found in the LICENSE file distributed with the program.
 *
 * This copyright notice MUST APPEAR in all copies of the program!
 *
 * ---------------------------------------------------------------------------
 * Notes for migration from JavaUpload:
 *
 * This example is exactly the same like the one for the JavaUpload Applet.
 * As you can see migration from JavaUpload to FlexUpload is really simple.
 * Just change the class name to "FlexUpload" and you are done.
 *
 * FlexUpload uses a different approach to handle locale. Translations are
 * now stored in external xml files and not in property lists compiled into
 * the application. This is a great advantage because it's much more easier
 * to maintain the language files.
 * To specify the locale you now have to set the name of the language file
 * including the path to it!
 *
 * e.g.:
 * in JavaUpload you wrote
 * $jup = new JavaUpload();
 * $jup->setLocale("de_DE");
 *
 * in FlexUpload you write
 * $fup = new FlexUpload();
 * $fup->setLocale('locale/de.xml');
 *
 *
 * @version 1.0
 * @author Mirko Schaal <ms@splinelab.com>
 * @package FlexUpload
 * @subpackage example
 */

/**
 * including the FlexUpload class
 */
require_once("./class.flexupload.inc.php");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>FlexUpload example</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body link="#FF6600" vlink="#FF6600" alink="#FF6600" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<center>
<h3><hr width="100%"><font face="Arial, Helvetica, sans-serif">Example for FlexUpload in english</font><hr width="100%"></h3>
<p>
<?php
// should work in most cases to generate the url to the upload file
// if it don't work, set a hard coded string e.g.
// $url = 'http://localhost/upload_example.php';
$url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/upload_example.php';

$fup = new FlexUpload($url.'?myGETVariable='.rawurlencode('you can even pass variables via GET e.g. a SESSION_ID to authenticate the user'));
$fup->setMaxFileSize(5*1024*1024);
$fup->printHTML(true, 'flexupload1');

?>
</p>
<h3><hr width="100%"><font face="Arial, Helvetica, sans-serif">Beispiel für FlexUpload in Deutsch</font><hr width="100%"></h3>
<p>

<?php

$fup = new FlexUpload($url);
$fup->setMaxFileSize(5*1024*1024);
$fup->setLocale('locale/de.xml');
$fup->printHTML(true, 'flexupload2', false);


?>

<hr width="100%"><font size=-2 face="Arial, Helvetica, sans-serif"><a href="http://www.splinelab.com/flexupload/">(C) 2007 SPLINELAB</a></font>
</center>
</body>
</html>
