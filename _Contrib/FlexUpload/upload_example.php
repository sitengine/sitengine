<?php
/**
 * upload_example.php
 *
 * Simple upload-script to demonstrate and test FlexUpload.
 * Feel free to use this to start developing your own scripts.
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
 *
 * Note:
 * Your upload script have to return some information for FlexUpload.
 *
 * If the upload was successfull just print "OK" (uppercase without quotation marks)
 * If something went wrong print a nice error message to inform the user what happened.
 *
 *
 * Important Note:
 *
 * In real-life your upload script have to be more secure than this one.
 * You have to make sure that only authorized users can upload files to your server!
 * You also should check the extension of the uploaded file to prevent bad guys to upload
 * malicious executable files (you should not accept files endig with .ph*, .cgi, .pl, ...)
 *
 *
 * Notes for migration from JavaUpload:
 *
 * The field name has changed to "Filedata" (this is the default in Flex) so you have to use
 * $_FILES['Filedata'] to access the uploaded file.
 *
 * The format of the return value also has changed. If you print something different than "OK"
 * FlexUpload assumes an error.
 * E.g. In JavaUpload you printed "success=1\r\n" if your upload succeeds and
 * "success=0\r\nSome error occurred"
 * Now you print "OK" if your upload succeeds and "Some error occured". This helps to avoid
 * parsing errors of the return value if your script raises some php warnings or errors.
 *
 *
 * @version 1.0
 * @author Mirko Schaal <ms@splinelab.com>
 * @package FlexUpload
 * @subpackage example
 */


// just test GET parameters provided to the postURL parameter...
//echo "myGETVariable: ".$_GET['myGETVariable'];


// is Filedata there?
if (! isset($_FILES['Filedata'])) {
	echo "Whooops! There is no file! (maybe filesize is greater than POST_MAX_SIZE directive in php.ini)";
	exit;
}

// make nicer filenames ;)
$fn = preg_replace("/[^a-zA-Z0-9._-]/", "_", $_FILES['Filedata']['name']);
// and set the directory
$fn = 'uploaddir/'.$fn;


// check if the file already exists
if ( file_exists($fn) ) {
	echo "File exists - i don't like to overwrite it!";
	exit;
}

// move the uploaded file
if (is_uploaded_file($_FILES['Filedata']['tmp_name'])) {
	if (move_uploaded_file($_FILES['Filedata']['tmp_name'], $fn)) {
		@chmod($fn, 0666);
		echo 'OK';
	} else {
		echo 'can\'t move the uploaded file';
	}
} else {
	switch($_FILES['Filedata']['error']) {
		case 0:
			echo 'possible file attack!';
			break;
		case 1:
			echo 'uploaded file exceeds the UPLOAD_MAX_FILESIZE directive in php.ini';
			break;
		case 2:
			echo 'uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form';
			break;
		case 3:
			echo 'uploaded file was only partially uploaded';
			break;
		case 4:
			echo 'no file was uploaded';
			break;
		default: //a default error, just in case!  :)
			echo 'default error - that\'s magic!';
			break;
	}
}
?>