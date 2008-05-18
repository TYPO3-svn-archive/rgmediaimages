<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Georg Ringer <http://www.ringer.it/>
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
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Hook for the 'rgmediaimages' extension.
 *
 * @author	Georg Ringer <http://www.ringer.it/>
 */
class tx_rgmediaimages_api {

   /**
    * Load the swfobject.js
    *
    * @param   string     $path: Path to override original file
    * 		 		 
    * @return   void
    */
	function initSwfObject($path='') {
		if ($path=='') {
			$path = t3lib_div::getIndpEnv('TYPO3_SITE_URL').t3lib_extMgm::siteRelpath('rgmediaimages').'res/swfobject.js'; 
		}
		$GLOBALS['TSFE']->additionalHeaderData['rgmediaimages'] = '<script type="text/javascript" src="'.$path.'"></script>';
		$this->local_cObj = t3lib_div::makeInstance('tslib_cObj'); // Local cObj.	
	}

   /**
    * Set a unique key which is used e.g. for a unique class name
    * @param   string     $id The unique key		 		 
    * @return  void
    */	
	function setUniqueID($key) {
		$this->uniqueID = $key;
	}

   /**
    * Get a unique key 		 		 
    * @return  string unique key
    */	
	function getUniqueID() {
		return $this->uniqueID;
	}	

   /**
    * Create the for the FLV player understandable configuration
    * @param   array     $c: Configuration from Constants/Setup	 		 
    * @return   modified and configuration array
    */	
	function getConfiguration($c) {
    $config = array();  
    $config['width'] = 'width='.$c['width'];
    $config['height'] = 'height='.$c['height'];
    $config['backgroundColor'] = ($c['backgroundColor']!='FFFFFF') ? 'backcolor=0x'.$c['backgroundColor'] : '';
    $config['foregroundColor'] = ($c['foregroundColor']!='000000') ? 'frontcolor=0x'.$c['foregroundColor'] : '';
    $config['highlightColor'] = ($c['highlightColor']!='000000') ? 'lightcolor=0x'.$c['highlightColor'] : '';
    $config['screenColor'] = ($c['screenColor']!='000000') ? 'screencolor='.$c['screenColor'] : '';
    $config['backgroundImage'] = ($c['backgroundImage']!='') ? 'image=http://'.$c['backgroundImage'] : '';
    $config['largeControllBar'] = ($c['largeControllBar']==1) ? 'largecontrols=true' : '';
    $config['showDigits'] = ($c['showDigits']!='true') ? 'showdigits='.$c['showDigits'] : '';
#    $config['showDownload'] = ($c['showDownload']==1) ? 'showdownload=true&link="'.t3lib_div::getIndpEnv('TYPO3_SITE_URL').t3lib_extMgm::siteRelpath('rgmediaimages').'saveFile.php&file='.$url.'"': '';
    $config['showEqualizer'] = ($c['showEqualizer']==1) ? 'showeq=true' : '';
    $config['showLoadPlay'] = ($c['showLoadPlay']==0) ? 'showicons=false' : '';
    $config['showVolume'] = ($c['showVolume']==0) ? 'showvolume=false' : '';
    $config['autoStart'] = ($c['autoStart']!='false') ? 'autostart='.$c['autoStart'] : '';
    $config['autoRepeat'] = ($c['autoRepeat']!='false') ? 'repeat='.$c['autoRepeat'] : '';    
    $config['volume'] = ($c['volume']!=80) ? 'volume='.intval($c['volume']) : '';
    $config['logo'] = ($c['logo']!='') ? 'logo=http://'.$c['logo'] : '';
    
    return $config;
	}	
	
   /**
    * Load a video from an external video hoster by using an swfObject
    *
    * @param   string     $url: The url to the video
    * @param   int     $width: width of the wideo
    * @param   int     $height: height of the wideo
    * @param   array     $config: some configuration
    * @return   string     SwfObject including the video and its parameter
    */  
  function getVideos($url, $width, $height, $config) {
  	$video = '';

		$url = trim($url);
		$url = str_replace('http://', '', $url);

    // youtube
    if (strpos($url,'outube.com'))  {
      $found = 1;
      $split = explode('=',$url);
      $video = 'new SWFObject("http://www.youtube.com/v/'.$split[1].'", "sfwvideo", "'.$width.'","'.$height.'", "9", "#'.$config['backgroundColor'].'", "wmode", "transparent");';
    // Dailymotion
    } elseif (strpos($url,'ailymotion.co'))  {
      $found = 1;
      $video = 'new SWFObject("http://'.$url.'", "sfwvideo", "'.$width.'","'.$height.'", "9", "#'.$config['backgroundColor'].'", "wmode", "transparent");';
    // video.google.com/.de
    } elseif (strpos($url,'ideo.google.'))  {
      $found = 1;        
      $split = explode('=',$url);
      $video = 'new SWFObject("http://video.google.com/googleplayer.swf?docId='.$split[1].'&hl='.$GLOBALS['TSFE']->lang.'", "sfwvideo", "'.$width.'","'.$height.'", "9", "#'.$config['backgroundColor'].'", "wmode", "transparent");';  
    // Metacafe
    } elseif (strpos($url,'metacafe.'))  {
      $found = 1;
      $split = explode('/',$url);
      $video = 'new SWFObject("http://www.metacafe.com/fplayer/'.$split[2].'/.swf", "sfwvideo", "'.$width.'","'.$height.'", "9", "#'.$config['backgroundColor'].'", "wmode", "transparent");';  
    // MyVideo.de
    } elseif (strpos($url,'yvideo.de'))  {
      $found = 1;
      $split = explode('/',$url);
      $video = 'new SWFObject("http://www.myvideo.de/movie/'.$split[2].'", "sfwvideo", "'.$width.'","'.$height.'", "9", "#'.$config['backgroundColor'].'", "wmode", "transparent");';  
    // clipfish.de
		} elseif (strpos($url,'lipfish.de'))  {
      $found = 1;
      $split = explode('=',$url);
      $video = 'new SWFObject("http://www.clipfish.de/videoplayer.swf?as=0&videoid='.$split[1].'", "sfwvideo", "'.$width.'","'.$height.'", "9", "#'.$config['backgroundColor'].'", "wmode", "transparent");';   
		// sevenload
		} elseif (strpos($url,'sevenload.com'))  {
      $found = 1;
      $split = explode('/',$url);
      $video = 'new SWFObject("http://de.sevenload.com/pl/'.$split[2].'/'.$width.'x'.$height.'/swf", "sfwvideo", "'.$width.'","'.$height.'", "9", "#'.$config['backgroundColor'].'", "wmode", "transparent");';  
    }     

		return $video;
	}

   /**
    * Emebed a SwfObject using JS 
    *
    * @param   string     $url: The url to the video
    * @param   string     $config: configuration of the swfobject
    * @param   int     $width: width of the wideo
    * @param   int     $height: height of the wideo  
    * @param   array     $mootools: If mootools is on the page, the array is filled with some code to use it
    * @param   string     $overrideSwfObj: override the swfObject
    * 		 		 
    * @return   string     The video
    */
	function getVideoSwfObj($url, $config, $width, $height, $mootools, $overrideSwfObj='') {
		$uniqueUid = ' rgmi'.$this->getUniqueID().' ';
		$uniqueKey = md5($url);
		
		$url = trim($url);
		$url = str_replace('http://', '', $url);
		
		$videoObject = '';
		if ($overrideSwfObj=='') {
			$videoObject = 'var so = new SWFObject("'.t3lib_div::getIndpEnv('TYPO3_SITE_URL').t3lib_extMgm::siteRelpath('rgmediaimages').'res/mediaplayer.swf","mpl","'.$width.'","'.$height.'","8");';
		} else {
			$videoObject = 'var so = '.$overrideSwfObj;
		}
		
		$video = '<span class="rgmediaimages-player'.$uniqueUid.'" id="player'.$uniqueKey.'"></span>
		          <script type="text/javascript">
		            '.$mootools['begin'].'
								'.$videoObject.'
		            so.addParam("allowscriptaccess","always");
		            so.addParam("allowfullscreen","true");
		            so.addVariable("file","http://'.$url.'");
		            '.$config.'
		            so.write("player'.$uniqueKey.'");
		            '.$mootools['end'].'
		          </script>';	
		return $video;
	}

   /**
    * Emebed a SwfObject using the emebed tag. Not the best way because code is not valid anymore! 
    * You should use JS to emebed the video 
    *
    * @param   string     $url: The url to the video
    * @param   string     $config: configuration of the swfobject
    * @param   int     $width: width of the wideo
    * @param   int     $height: height of the wideo     
    * @return   string     The video
    */
	function getVideoEmebed($url, $config, $width, $height) {
		$uniqueUid = ' rgmi'.$this->getUniqueID().' '; 
		$video = '<span class="rgmediaimages-player'.$uniqueUid.'">
                <embed src="'.t3lib_extMgm::siteRelpath('rgmediaimages').'res/mediaplayer.swf" width="'.$width.'" height="'.$height.'" allowfullscreen="true" allowscriptaccess="always" flashvars="&file='.$url.'&'.$config.'" />
              </span>';
		return $video;	
	}
	
   /**
    * Check if mootools is anywhere on the website. 
    * Check is based on if t3mootools is configured or if $check is true    
    *
    * @param   boolean     $check: Just an additional value
    * @return   array      Set the window.addEvent
    */
	function checkForMootools($check=false) {
		$mootools = array();

		if (t3lib_extMgm::isLoaded('t3mootools'))    {
		 require_once(t3lib_extMgm::extPath('t3mootools').'class.tx_t3mootools.php');
		} 	 
		if (defined('T3MOOTOOLS') || $check) {
		  if (defined('T3MOOTOOLS')) {
		    tx_t3mootools::addMooJS();
		  }
		  $mootools['begin'] = 'window.addEvent("load", function(){';
		  $mootools['end'] = ' });';
		}	
		return $mootools;
	}
	
   /**
    * Load content elements with their ID
    *
    * @param   string     $ceElements: The content elements
    * @param   int     $width: width of the content element
    * @param   int     $height: height of the content element     
    * @return   string    The content element
    */ 
	function getCE($ceElements, $width, $height) {
		$uniqueUid = ' rgmi'.$this->getUniqueID().' ';
		$ceElements = trim($ceElements);

	  $ceConfig = array('tables' => 'tt_content','source' => $ceElements,'dontCheckPid' => 1);    
	  $ce = '<div class="rgmediaimages-content'.$uniqueUid.'" style="width:'.$width.'px; height:'.$height.'px;>'.$this->local_cObj->RECORDS($ceConfig).'</div>';
		
		return $ce;
	}


   /**
    * Displays an Iframe
    *
    * @param   string     $url: url to the iframe
    * @param   int     $width: width of the iframe
    * @param   int     $height: height of the iframe   
    * @return   string    The iframe
    */
	function getIframe($url, $width, $height) {
		$uniqueUid = ' rgmi'.$this->getUniqueID().' ';        
		$url = trim($url);
		$url = str_replace('http://', '', $url);
    
    $iframe = '<iframe class="rgmediaimages-iframe'.$uniqueUid.'" height="'.$height.'" width="'.$width.'" src="http://'.$url.'" scrolling="yes"></iframe> ';
    
    return $iframe;
	}
	
   /**
    * Load an external MOV video
    *
    * @param   string     $url: The url
    * @param   int     $width: width of the wideo
    * @param   int     $height: height of the wideo     
    * @return   string     Embed object including the video and its parameter
    */  
	function getMov($url, $width, $height) {
		$url = trim($url);
		$url = str_replace('http://', '', $url);
	
		$height = $height+16;
    $video = '<object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab" width="'.$width.'" height="'.$height.'" >
              <param name="src" value="http://'.$url.'">
              <param name="autoplay" value="true">
              <param name="type" value="video/quicktime" width="'.$width.'" height="'.$height.'">      
              <embed src="http://'.$url.'" width="'.$width.'" height="'.$height.'" autoplay="false" type="video/quicktime" pluginspage="http://www.apple.com/quicktime/download/">
            </object>';
    return $video;
	}

   /**
    * Load an external WMV video
    *
    * @param   string     $url: The url
    * @param   int     $width: width of the wideo
    * @param   int     $height: height of the wideo     
    * @return   string     Embed object including the video and its parameter
    */  
	function getWmv ($url, $width, $height) {
		$url = trim($url);
		$url = str_replace('http://', '', $url);

		$video = '<object id="MediaPlayer" width='.$width.' height='.$height.' classid="CLSID:22D6f312-B0F6-11D0-94AB-0080C74C7E95" standby="Loading Windows Media Player components…" type="application/x-oleobject" codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6,4,7,1112">
              <param name="filename" value="http://'.$url.'">
              <param name="Showcontrols" value="True">
              <param name="autoStart" value="False">
              <embed type="application/x-mplayer2" src="http://'.$url.'" name="MediaPlayer" width="'.$width.'" height="'.$height.'"></embed>
            </object>';
		
		return $video; 	
	} 

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rgmediaimages/class.tx_rgmediaimages_api.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rgmediaimages/class.tx_rgmediaimages_api.php']);
}