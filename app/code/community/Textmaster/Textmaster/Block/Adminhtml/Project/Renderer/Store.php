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
 class Textmaster_Textmaster_Block_Adminhtml_Project_Renderer_Store extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	public function render(Varien_Object $row)
	{
		$stores_to = explode(';',$row->getData('store_name_to'));
		
		$value = '';
		$storeToIds = array();
		if(is_array($stores_to)){
			foreach ($stores_to as $k =>$storeToId){
				if($storeToId!='' && !in_array($storeToId,$storeToIds)){
					$icoTo = substr(Mage::getStoreConfig('general/locale/code',$storeToId),0,2);
					$tmphtml2 = '<img height="10" src="'.$this->getSkinUrl('images/textmaster/'.$icoTo.'.png').'" alt="'.$icoTo.'"/>';
					$value .= $tmphtml2.' - ';
					$storeToIds[]=$storeToId;
				}
			}
		}
		if(strlen($value)>3){
			$value = substr($value,0,-3);
		}
		
		
		return ''.$value.'';
		return $result;
	}
}