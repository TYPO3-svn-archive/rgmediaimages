<?php
  
  if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
  
  // new allowed file types
  $GLOBALS['TCA']['tt_content']['columns']['image']['config']['allowed'] .= ',flv,swf,rtmp,mp3,rgg';
  
  // if DAM is used
  if (t3lib_extMgm::isLoaded('dam') && t3lib_extMgm::isLoaded('dam_ttcontent')) {
    $GLOBALS['T3_VAR']['ext']['dam']['TCA']['image_field']['config']['allowed_types'].= ',flv,swf,rtmp,mp3,rgg';
  }
  
  // get extension configuration
  $confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['rgmediaimages']);
  
  // rename the fields if allowed
  if ($confArr['rename']==1) {
    $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items']['2']['0'] = 'LLL:EXT:rgmediaimages/locallang.xml:textpic';
    $GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items']['3']['0'] = 'LLL:EXT:rgmediaimages/locallang.xml:pic';

    // rename the fields in the content element wizard
    if (TYPO3_MODE=="BE") {
      $TBE_MODULES_EXT["xMOD_db_new_content_el"]["addElClasses"]["tx_rgmediaimages_pi1_wizicon"] = t3lib_extMgm::extPath($_EXTKEY).'class.tx_rgmediaimages_pi1_wizicon.php';
    } 

  }
  
  // add static TS
  t3lib_extMgm::addStaticFile($_EXTKEY,"static","Media files & images");
  
  // extend tt_news
  if ($confArr['tt_news']==1) {

		// get extension confArr
		$confArrNews = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['tt_news']);
		$l10n_mode_image = ($confArrNews['l10n_mode_imageExclude']?'exclude':'mergeIfNotBlank');

	  $tempColumns = Array (
	      "tx_rgmediaimages_video" => Array (        
	          "exclude" => 1,        
	          'l10n_mode' => $l10n_mode_image,
	          "label" => "LLL:EXT:rgmediaimages/locallang.xml:tt_news.tx_rgmediaimages_video",        
	          "config" => Array (
	              "type" => "group",
	              "internal_type" => "file",
	              "allowed" => "flv,swf,rtmp,mp3",    
	              "max_size" => 50000000,    
	              "uploadfolder" => "uploads/tx_rgmediaimages",
	              "size" => 5,    
	              "minitems" => 0,
	              "maxitems" => 10,
	              'disable_controls' =>'upload'
	          )
	      ),
		    "tx_rgmediaimages_config" => Array (        
		        "exclude" => 1,        
	          'l10n_mode' => $l10n_mode_image,
		        "label" => "LLL:EXT:rgmediaimages/locallang.xml:tt_news.tx_rgmediaimages_config",        
		        "config" => Array (
							'type' => 'text',
							'cols' => '40',
							'rows' => '3'
		        )
		    ),
		    "tx_rgmediaimages_caption" => Array (        
		        "exclude" => 1,        
	          'l10n_mode' => $l10n_mode_image,
		        "label" => "LLL:EXT:rgmediaimages/locallang.xml:tt_news.tx_rgmediaimages_caption",        
		        "config" => Array (
							'type' => 'text',
							'cols' => '20',
							'rows' => '3'
		        )
		    ),					
				'tx_rgmediaimages_width' => Array (
					'l10n_mode' =>'exclude',
	        "label" => "LLL:EXT:rgmediaimages/locallang.xml:tt_news.tx_rgmediaimages_width",
					'config' => Array (
						'type' => 'input',
						'size' => '3',
						'eval' => 'int',
					)
				),				      
				'tx_rgmediaimages_height' => Array (
					'l10n_mode' =>'exclude',
	        "label" => "LLL:EXT:rgmediaimages/locallang.xml:tt_news.tx_rgmediaimages_height",
					'config' => Array (
						'type' => 'input',
						'size' => '3',
						'eval' => 'int',
					)
				),
	  );
  
	  t3lib_div::loadTCA("tt_news");
	  t3lib_extMgm::addTCAcolumns("tt_news",$tempColumns,1);
#	  t3lib_extMgm::addToAllTCAtypes("tt_news","tx_rgmediaimages_video,tx_rgmediaimages_config;;;;1-1-1");

  	$GLOBALS['TCA']['tt_news']['palettes'][53]['showitem'] = 'tx_rgmediaimages_video, tx_rgmediaimages_caption, tx_rgmediaimages_width, tx_rgmediaimages_height';
  	$GLOBALS['TCA']['tt_news']['types'][0]['showitem'] .= ',tx_rgmediaimages_config;;53;;';    
  	
  	#t3lib_div::print_array($GLOBALS['TCA']['tt_news']['columns']);

  }
?>