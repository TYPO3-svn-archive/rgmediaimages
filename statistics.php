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

/*
This is a small script you can use for tracking statistics in the flash players. 
You have to set the location of this script as the "callback" flashvar for your player first.
Second, you'll have to create an empty textfile "statistics.txt" that is writeable in the same directory as this script.
Now, the script will add a line with the title each flash player item that is either started or completely listened.
You can also save the item's file url (it's variable is $file), or, if provided in the playlist, a unique id ($id)
*/

// Initialize FE user object:
$feUserObj = tslib_eidtools::initFeUser();

require_once(PATH_t3lib.'class.t3lib_page.php');
require_once(PATH_tslib . "class.tslib_content.php");

$temp_TSFEclassName = t3lib_div::makeInstanceClassName('tslib_fe');                 

// create object instances:
$temp_TSFEclassName = t3lib_div::makeInstanceClassName('tslib_fe');
$TSFE = new $temp_TSFEclassName($TYPO3_CONF_VARS, $page, 0, true);
    
    tslib_eidtools::connectDB();

extract($_POST, EXTR_PREFIX_SAME, "post_");

$tmp_confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rgmediaimagescallback']);


$securityKey = t3lib_div::_GET('key');
$pid = intval(t3lib_div::_GET('pid'));
$uid = intval(t3lib_div::_GET('uid'));
$file = $GLOBALS['TYPO3_DB']->fullQuoteStr(t3lib_div::_GET('file'),'tx_rgmediaimagescallback_statistic');

if ($file!='' && $title!='' && $id!='' && $securityKey!='' && $pid!='' && $uid!='' && $state=='start') {
  
  // fetch existing data
  $where = 'hidden=0 AND deleted = 0 AND pageid='.$pid.' AND ceuid = '.$uid.' AND uniquekey ="'.$securityKey.'" AND title="'.$file.'"';
  $res = $TYPO3_DB->exec_SELECTquery('countmedia,uid','tx_rgmediaimagescallback_statistic',$where);
  $row = $TYPO3_DB->sql_fetch_assoc($res);
  
  // there is already one record => update
  if ($row['uid']!='') {
    $update = array();
    $update['tstamp'] = time();
    $update['countmedia'] = $row['countmedia']+1;
    $TYPO3_DB->exec_UPDATEquery('tx_rgmediaimagescallback_statistic','uid = '.$row['uid'],$update);
    
  } else {
    $insert = array();
    $insert['pid'] = $tmp_confArr['pageid'];  
    $insert['title'] = $file;
    $insert['pageid'] = $pid;
    $insert['tstamp'] = time();
    $insert['crdate'] = time();
    $insert['ceuid'] = $uid;
    $insert['countmedia'] = 1;
    $insert['uniquekey'] = $securityKey;
    $insert['last'] ="$title $vars (id $id): $state ($duration sec.) $_SERVER[REMOTE_ADDR]; \n";;
  
    $TYPO3_DB->exec_INSERTquery('tx_rgmediaimagescallback_statistic',$insert);
  }

  
}

$filename = 'typo3conf/ext/rgmediaimagescallback/statistics.txt';


$somecontent = "$title $vars (id $id): $state ($duration sec.) $_SERVER[REMOTE_ADDR]; \n";



?>