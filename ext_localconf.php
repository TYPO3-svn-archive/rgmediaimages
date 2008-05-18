<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');


// hook for tt_news
if (TYPO3_MODE == 'FE')    {
    require_once(t3lib_extMgm::extPath($_EXTKEY).'class.tx_rgmediaimages_news.php');
}
$TYPO3_CONF_VARS['EXTCONF']['tt_news']['extraItemMarkerHook'][]   = 'tx_rgmediaimages_news';


#$TYPO3_CONF_VARS['EXTCONF']['rgmediaimages']['extraMediaHook'][]   = 'tx_rgmediaimages_news';
?>
