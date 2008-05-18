<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Georg Ringer <http://www.just2b.com>
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
require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_rgmediaimages_fe extends tslib_pibase {
	var $prefixId      = 'tx_rgmediaimages_fe';		// Same as class name
	var $scriptRelPath = 'class.tx_rgmediaimages_fe.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'rgmediaimages';	// The extension key.
	var $pi_checkCHash = true;


   /**
    * Main function to get the FLV player working
    *
    * @param   string     $originalUrl: The uriginal url of the video
    * @param   array      $params: Possible configuration
    */
  function user_images($originalUrl,$conf) {
    $GLOBALS['TSFE']->additionalCSS['rgmediaimages'] = '.rgmi { text-align:center;}';

		require_once( t3lib_extMgm::extPath('rgmediaimages').'/class.tx_rgmediaimages_api.php');
		$this->media = t3lib_div::makeInstance('tx_rgmediaimages_api');
		
		// include the SWF Object File
		$this->media->initSwfObject();

		// set a unique ID
		$this->media->setUniqueID($this->cObj->data['uid']);

    // url to the media file
    $url = htmlspecialchars(t3lib_div::getIndpEnv('TYPO3_SITE_URL').$originalUrl);
    
    // configuration through the Content Element
    $pluginConf  = $this->cObj->stdWrap($conf['override'], $conf['override.']);
    $pluginConf = explode(',',$pluginConf);
    $pluginConf2 = array();
    foreach ($pluginConf as $key=>$value) {
      $value = str_replace('http://', '', trim($value));
      $split = explode(':',$value);
      $pluginConf2[$split[0]] = trim($split[1]);
    }

    // get width / height
    $widthBE = intval($this->cObj->data['imagewidth']);
    $conf['conf.']['width'] = ($widthBE!=0) ? $widthBE : intval($conf['conf.']['width']);
    
    $heightBE = intval($this->cObj->data['imageheight']);
    $conf['conf.']['height'] = ($heightBE!=0) ? $heightBE : intval($conf['conf.']['height']);

    // merge the configuration from the CE and from TS
    $c = array_merge($conf['conf.'],$pluginConf2);    

		$config = $this->media->getConfiguration($c);
		
    // callback, needs the EXT:rgmediaimagescallback
    if (t3lib_extMgm::isLoaded('rgmediaimagescallback') && $c['callback']==1 && 1==2 ) {
      $urlParameter = '%26file='.$originalUrl.'%26uid='.$this->cObj->data['uid'].'%26pid='.$GLOBALS['TSFE']->id;
      $securityKey = md5($urlParameter.$GLOBALS["TYPO3_CONF_VARS"]["SYS"]["encryptionKey"]);
      $urlParameter.='%26key='.$securityKey;
      
      $config['trackback'] = 'title="'.htmlspecialchars($url).'"&id='.md5($originalUrl).'&callback='.t3lib_div::getIndpEnv('TYPO3_SITE_URL').'index.php?eID=callback'.$urlParameter;
    }

    // delete the empty entries of the conig array
    foreach ($config as $key=>$value) {
    	if($value=='') {
        unset ($config[$key]);
      }
    }

    // height / width for the player
    $height2 = explode('=',$config['height']);
    $width2 = explode('=',$config['width']);
    
		$mootools = $this->media->checkForMootools($c['mootools']);
       
    /****************************************
     * FLV & mp3 files, played with the JW FLV Player
     ************************/       
    if (substr($originalUrl,-4)!='.rgg') {

      // output as swfObject
      if ($c['useSwfObject']==1) {
        // different syntax
        foreach ($config as $key=>$value) {
          $split = explode('=',$value);
        	$configuration.= 'so.addVariable("'.$split[0].'","'.$split[1].'");';
        }
        $video = $this->media->getVideoSwfObj($url, $configuration, $width2[1], $height2[1], $mootools);  
      
      // output as embed
      } else {
        $configuration = implode('&',$config);
        $video = $this->media->getVideoEmbed($url, $configuration, $width2[1], $height2[1]);       
      }
  
      /* // if the callback is enabled
      if (t3lib_extMgm::isLoaded('rgmediaimagescallback') && $c['callback']==1) {
        $old = 'Uhrzeit gecached: '. strftime('%D - %H:%M:%S',time()) . '<br />';
    		$new= 'Uhrzeit ungecached: '. $this->callUserINT('getTime',$config) . '<br />';
    		$confDownloads['uid'] =  $this->cObj->data['uid'];
    		$confDownloads['uniquekey'] = $securityKey;
    		$confDownloads['title'] = $originalUrl;
    		$downloadRates = $this->callUserINT('downloadRates',$confDownloads);    
      }
      */
    } 
    
    /****************************************
     * Embed videos from hosters and external files
     ************************/       
    else {
      // get the file 
      $filename=htmlspecialchars(trim($c['file']));

      // checks if a video is found
      $found = false;

      /****************************************
       * search for the supported hosters
       ************************/     
			if (!$found) {
				$obj = $this->media->getVideos($filename, $width2[1], $height2[1], $c);
				if ($obj !='') {
					$found = 1;
					$video = $this->media->getVideoSwfObj($filename, $config='', $width='', $height='', $mootools, $obj);
				}   
			}

      /****************************************
       * search for the supported video files: wmv, mov
       ************************/       
			if (!$found) {
	      // wmv (windows media file)
	      if (substr($filename,-4)=='.wmv') {
	        $found = true;
	        $video = $this->media->getWmv($filename, $width2[1], $height2[1]);
	      // mov (quicktime)
	      } elseif (substr($filename,-4)=='.mov') {
	        $found = true;
	        $video = $this->media->getMov($filename, $width2[1], $height2[1]);
	      }
	    }
      
      /****************************************
       * Display iframes and content elements
       ************************/       
      
      // if none of the hosters, check for other stuff
      if (!$found) {
        // content elements
        if (substr($filename,0,10)=='tt_content')  {
					$filename = explode('tt_content',$filename);
					$video = $this->media->getCE($filename[1], $width2[1], $height2[1]);
        }
        // iframes
        if (substr($filename,0,6)=='iframe')  {
        	$filename = explode('iframe',$filename);
					$video = $this->media->getIframe($filename[1], $width2[1], $height2[1]);
        }
      }

    } // end else
    
		// Adds hook for processing of extra media files
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rgmediaimages']['extraMediaHook'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['rgmediaimages']['extraMediaHook'] as $_classRef) {
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$video = $_procObj->extraMediaProcessor($video, $config, $width, $height, $originalUrl, $this);
			}
		}
		 
    return $video.$downloadRates;
  } 

   /**
    * Helping functions to get a USER_INT into a USER
    *
    * @param   string     $func: The function name
    * @param   array      $params: Possible configuration
    * @return   string      The content of the user_int
    */
	function callUserINT($func, $params = null) {
		$TS['conf'] = 'COA_INT';
		$TS['conf.']['10'] = 'USER';
		$TS['conf.']['10.']['userFunc'] = 'tx_rgmediaimages_fe->' . $func;
		if (is_array($params))
			$TS['conf.']['10.'] = array_merge($TS['conf.']['10.'], $params);
		return $this->cObj->cObjGetSingle($TS['conf'],$TS['conf.']);
	}

   /**
    * Test function to get the current time, formatted nicely
    *
    * @param   string      $content: current content
    * @param   array     $conf: possible configuration
    * @return   string      the current time
    */
	function getTime($content, $conf) {
		return strftime('%D - %H:%M:%S',time());
	}	
	
   /**
    * Get the number of views of a video
    *
    * @param   string      $content: current content
    * @param   array     $conf: possible configuration, holding the information of the video's properties
    * @return   string      Download counter
    */	
	function downloadRates($content,$conf) {
    // fetch existing data
    $where = 'hidden=0 AND deleted = 0 AND ceuid = '.$conf['uid'].' AND uniquekey="'.$conf['uniquekey'].'" AND title = "'.$GLOBALS['TYPO3_DB']->fullQuoteStr($conf['title'],'tx_rgmediaimagescallback_statistic').'"';
    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('countmedia,uid','tx_rgmediaimagescallback_statistic',$where);
    $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
    
    if ($row['uid']=='') {
      return 'never seen';
    } else {
      return 'seen '.$row['countmedia'].' time(s)!';
    }
  
  }

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rgmediaimages/class.tx_rgmediaimages_fe.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rgmediaimages/class.tx_rgmediaimages_fe.php']);
}

?>