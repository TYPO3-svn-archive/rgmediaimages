<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Georg Ringer (just2b) <http://www.just2b.com>
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
 * Class that adds the wizard icon.
 *
 * @author	Georg Ringer (just2b) <http://www.ringer.it>
 * @package	TYPO3
 * @subpackage	tx_rgmediaimages
 */
class tx_rgmediaimages_pi1_wizicon {

	/**
	 * Processing the wizard items array
	 *
	 * @param	array		$wizardItems: The wizard items
	 * @return	Modified array with wizard items
	 */
	function proc($wizardItems)	{
		global $LANG;

		$LL = $this->includeLocalLang();
    
    // rename the CE "Text w/ images"
    $wizardItems['common_textImage']['title'] = $LANG->getLLL('wizard_textpic', $LL);
    $wizardItems['common_textImage']['description'] = $LANG->getLLL('wizard_textpic.description', $LL);
    
    // rename the CE "images only"
    $wizardItems['common_imagesOnly']['title'] = $LANG->getLLL('wizard_images', $LL);
    $wizardItems['common_imagesOnly']['description'] = $LANG->getLLL('wizard_images.description', $LL);
    
    return $wizardItems;
	}

	/**
	 * Reads the [extDir]/locallang.xml and returns the $LOCAL_LANG array found in that file.
	 *
	 * @return	The array with language labels
	 */
	function includeLocalLang()	{
		$llFile = t3lib_extMgm::extPath('rgmediaimages').'locallang.xml';
		$LOCAL_LANG = t3lib_div::readLLXMLfile($llFile, $GLOBALS['LANG']->lang);
		
		return $LOCAL_LANG;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rgmediaimages/class.tx_rgmediaimages_pi1_wizicon.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rgmediaimages/class.tx_rgmediaimages_pi1_wizicon.php']);
}

?>