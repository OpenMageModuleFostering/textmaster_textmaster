<?php
/**
 * Copyright (c) 2014 Textmaster
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category    Addonline
 * @package     Textmaster_Textmaster
 * @copyright   Copyright (c) 2014 Textmaster
 * @author 	    Addonline (http://www.addonline.fr)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Textmaster_Textmaster
 *
 * @category    Addonline
 * @package     Textmaster_Textmaster
 * @copyright   Copyright (c) 2014 Textmaster
 * @author 	    Addonline (http://www.addonline.fr)
 */
class Textmaster_Textmaster_Model_Source_Api_Lang{
	
	public function toOptionArray()
	{
		$_api = Mage::helper('textmaster')->getApi();
		$aOptions = array();
		
		if(!$_api->isUserConnected()) return $aOptions;
		
		$aLanguages = $_api->getLanguages();
		foreach ($aLanguages as $_item){
			$aOptions[] = array('value'=>$_item['code'], 'label'=>$_item['value']);
		}
		return $aOptions;
	}
	public function toOptions(){
		Mage::getModel('core/store')->getCollection()->toOptionHash();
	}
}