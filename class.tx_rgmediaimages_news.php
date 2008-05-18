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
class tx_rgmediaimages_news {

	function extraMediaProcessor($video, $config, $width, $height, &$pObj) {
		return $video;
	}

	// hook for tt_news
	function extraItemMarkerProcessor($markerArray, $row, $lConf, &$pObj) {
		$this->cObj = t3lib_div::makeInstance('tslib_cObj'); // local cObj.	
		$this->pObj = &$pObj;
		$this->myConf = $pObj->conf['rgmediaimages.'];

		// default marker is empty
		$markerArray['###NEWS_VIDEOS###'] = '';
		
		// if one of the video fields is filled
		if ($row['tx_rgmediaimages_video']!='' || $row['tx_rgmediaimages_config']!='') {
			// require the main functions
			require_once( t3lib_extMgm::extPath('rgmediaimages').'/class.tx_rgmediaimages_api.php');
			$this->media = t3lib_div::makeInstance('tx_rgmediaimages_api');
			
			// Initialize the SWF Object
			$this->media->initSwfObject('');
			// set a unique id
			$this->media->setUniqueID($row['uid']);
			
			// caption
			$videoCaption = explode(chr(10),$row['tx_rgmediaimages_caption']);
			// width & height
			$width = $row['tx_rgmediaimages_width'] ? $row['tx_rgmediaimages_width'] : $this->myConf['width'];
			$height = $row['tx_rgmediaimages_height'] ? $row['tx_rgmediaimages_height'] : $this->myConf['height'];			
			// check for mootools
			$mootools = $this->media->checkForMootools();
			// count of videos
			$count = 0;
			// array holding all videos
			$media = array();
			
			// external files
			if ($row['tx_rgmediaimages_config']!='') {
				foreach (explode(chr(10),$row['tx_rgmediaimages_config']) as $key=>$mediaFile) {
					$url = trim($mediaFile);
					
					$found = false;
					
				/****************************************
				 * search for the supported video files: wmv, mov
				 ************************/
					if (substr($url,-4)=='.wmv') {
						$video = $this->media->getWmv($url, $width, $height);
						$media[] = $pObj->cObj->stdWrap($video.$pObj->cObj->stdWrap($videoCaption[$count], $this->myConf['caption.']), $this->myConf['singleMedia.']);				
						$found = true;
						$count++;	
					// mov (quicktime)
					} elseif (substr($url,-4)=='.mov') {
						$video = $this->media->getMov($url, $width, $height);
						$media[] = $pObj->cObj->stdWrap($video.$pObj->cObj->stdWrap($videoCaption[$count], $this->myConf['caption.']), $this->myConf['singleMedia.']);
						$found = true;
						$count++;
					}
	
	      /****************************************
	       * search for the supported hosters
	       ************************/
					if (!$found) {
						$swfObj =  $this->media->getVideos($url, $width, $height, $this->myConf['conf.']);
	
						if ($swfObj !='') {
							$video = $this->media->getVideoSwfObj($url, $config='', $width='', $height='', $mootools, $swfObj);			
							$media[] = $pObj->cObj->stdWrap($video.$pObj->cObj->stdWrap($videoCaption[$count], $this->myConf['caption.']), $this->myConf['singleMedia.']);
							$found = true;						
							$count++;
						}   
					}
					
		    /****************************************
		     * EXTERNAL FILES: FLV & mp3 files, played with the JW FLV Player
		     ************************/
					if (substr($url,-4)=='.flv' || substr($url,-4)=='.mp3' || substr($url,-4)=='.swf' || substr($url,-4)=='rtmp ' ) {
						$this->myConf['conf.']['width'] = $width;
						$this->myConf['conf.']['height'] = $height;
						
						$configuration = $this->media->getConfiguration($this->myConf['conf.']);
						foreach ($configuration as $key=>$value) {
							$split = explode('=',$value);
							if ($split[0]!='' && $split[1]!='') {
								$configuration2.= 'so.addVariable("'.$split[0].'","'.$split[1].'");';
							}
						}
	
						$video = $this->media->getVideoSwfObj($url, $configuration2, $width, $height, $mootools);  			
						$media[] = $pObj->cObj->stdWrap($video.$pObj->cObj->stdWrap($videoCaption[$count], $this->myConf['caption.']), $this->myConf['singleMedia.']);
	
						$found = true;
						$count++;
					}

					// if none of the hosters, check for other stuff
					if (!$found) {
						// content elements
						if (substr($url,0,10)=='tt_content')  {
							$url = explode('tt_content:',$url);
							$video = $this->media->getCE($url[1], $width, $height);
							$media[] = $pObj->cObj->stdWrap($video.$pObj->cObj->stdWrap($videoCaption[$count], $this->myConf['caption.']), $this->myConf['singleMedia.']);
							$found = true;
							$count++;

						}
						// iframes
						if (substr($url,0,6)=='iframe')  {
							$url = explode('iframe:',$url);
							$video = $this->media->getIframe($url[1], $width, $height);
							$media[] = $pObj->cObj->stdWrap($video.$pObj->cObj->stdWrap($videoCaption[$count], $this->myConf['caption.']), $this->myConf['singleMedia.']);
							$found = true;
							$count++;
						}
					}	
				} 
			} 

	    /****************************************
	     * LOCAL FILES: FLV & mp3 files, played with the JW FLV Player
	     ************************/ 				
	  	if ($row['tx_rgmediaimages_video']!='') {
				$this->myConf['conf.']['width'] = $width;
				$this->myConf['conf.']['height'] = $height;
				
				// get configuration throught constants
				$configuration = $this->media->getConfiguration($this->myConf['conf.']);

				// loop through the video field
				foreach (explode(',',$row['tx_rgmediaimages_video']) as $key=>$mediaFile) {
    			$mediaFile = t3lib_div::getIndpEnv('TYPO3_SITE_URL').'uploads/tx_rgmediaimages/'.$mediaFile;
    			// different syntax, using swfobj only.
					foreach ($configuration as $key=>$value) {
						$split = explode('=',$value);
						if ($split[0]!='' && $split[1]!='') {
							$configuration2.= 'so.addVariable("'.$split[0].'","'.$split[1].'");';
						}
					}

					$video = $this->media->getVideoSwfObj($mediaFile, $configuration2, $width, $height, $mootools);  			
					$media[] = $pObj->cObj->stdWrap($video.$pObj->cObj->stdWrap($videoCaption[$count], $this->myConf['caption.']), $this->myConf['singleMedia.']);

					$count++;
				}
			}

			// if there is any result, wrap the whole thing.
			if ($count>0) {
					// loop through the videos and search for a marker inside the news record which should be substituted
					foreach ($media as $key=>$singleVideo) {
						$key++;
						$markerArray['###NEWS_VIDEO_'.$key.'###'] = $singleVideo;
						if (strpos($markerArray['###NEWS_CONTENT###'], '###NEWS_VIDEO_'.$key.'###')) {
							// replace the marker with the video
							$markerArray['###NEWS_CONTENT###'] = str_replace('###NEWS_VIDEO_'.$key.'###', $singleVideo, $markerArray['###NEWS_CONTENT###']);
							// unset the video to prevent displaying it at the normal marker for the 2nd time 
							unset($media[$key--]);
						}

					}
					$markerArray['###NEWS_VIDEOS###'] = $pObj->cObj->stdWrap(implode('',$media), $this->myConf['videoWrapIfAny.']);
			}	
			
		}

		return $markerArray;
	}
	
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rgmediaimages/class.tx_rgmediaimages_news.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rgmediaimages/class.tx_rgmediaimages_news.php']);
}

?>
