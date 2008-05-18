<?php
/***************************************************************
*  Copyright notice
*
*  (c) 1999-2007 Peter Klein (peter@umloud.dk)
*  (c) 1999-2007 Georg Ringer (http://www.just2b.com)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

define('TYPO3_MODE','BE');
define('TYPO3_cliMode', TRUE);
define('TYPO3_OS', stristr(PHP_OS,'win')&&!stristr(PHP_OS,'darwin')?'WIN':'');
define('PATH_thisScript',str_replace('//','/', str_replace('\\','/', (php_sapi_name()=='cgi'||php_sapi_name()=='isapi' ||php_sapi_name()=='cgi-fcgi')&&($_SERVER['ORIG_PATH_TRANSLATED']?$_SERVER['ORIG_PATH_TRANSLATED']:$_SERVER['PATH_TRANSLATED'])? ($_SERVER['ORIG_PATH_TRANSLATED']?$_SERVER['ORIG_PATH_TRANSLATED']:$_SERVER['PATH_TRANSLATED']):($_SERVER['ORIG_SCRIPT_FILENAME']?$_SERVER['ORIG_SCRIPT_FILENAME']:$_SERVER['SCRIPT_FILENAME']))));
define('PATH_site', ereg_replace('[^/]*.[^/]*$','',dirname(dirname(PATH_thisScript))));
define('PATH_typo3', PATH_site.'typo3/');
define('PATH_typo3conf', PATH_site.'typo3conf/');
define('PATH_t3lib', PATH_site.'t3lib/');
require_once(PATH_t3lib.'class.t3lib_div.php');


// get the file url from querystring
$filename = ($_GET['file']);


// Error: file is not found
if(!file_exists('../../'.$filename)) {
#	die("The requested file could not be found");
}
                        
// Check if the requested file has an valid image file extension, not the nicest security feature but at least it prevents from downloading php files like localconf.php.
// thx peter klein
$allowedExtensions = t3lib_div::trimExplode(',','gif,jpg,jpeg,tif,bmp,pcx,tga,png,pdf,ai,flv,swf,rtmp,mp3,wmv', 1);
$imageInfo = pathinfo($filename);

if(!in_array($imageInfo['extension'], $allowedExtensions)) { die('You try to download a file, you are not allowed to download'); }


// Error: PHP files cannot be downloaded
if(strToLower(substr($filename,strlen($filename)-3, 3) == 'php')) {
	die( "The requested file cannot be retrieved for security reasons.");
}

// required for IE, otherwise Content-disposition is ignored
if(ini_get('zlib.output_compression')) { ini_set('zlib.output_compression', 'Off'); }


// build file headers
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);

// header for the content type
$ext = strToLower(substr($filename,strlen($filename)-3, 3));
if ($ext == "mp3" ) { header("Content-Type: audio/x-mp3"); } 
else if ($ext == "jpg") { header("Content-Type: image/jpeg"); }
else if ($ext == "gif") { header("Content-Type: image/gif"); }
else if ($ext == "png") { header("Content-Type: image/png"); }
else if ($ext == "swf") { header("Content-Type: application/x-shockwave-flash"); }
else if ($ext == "flv") { header("Content-Type: video/flv"); }

// and some more headers
header("Content-Disposition: attachment; filename=\"".basename($filename)."\";" );
header("Content-Transfer-Encoding: binary");
header("Content-Length: ".filesize($filename));

// refer to file and exit
readfile("$filename");
exit();

?>
    