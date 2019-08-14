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
class Textmaster_Textmaster_Block_Adminhtml_System_Config_Form_Field_Briefing extends Mage_Adminhtml_Block_System_Config_Form_Field
{
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
		
		$_api = Mage::helper('textmaster')->getApi();		
		$languages = $_api->getLanguages();
		
		$original_name = $element->getName();
		
		if(strpos($element->getHtmlId(),'translation')!==false){
		    $element_id = 'briefing_message_translation';
		} else{
		    $element_id = 'briefing_message_proofreading';
		}
		
		$html = '';
		foreach ($languages as $langue){
		    $name = str_replace($element_id,$element_id.'_'.$langue['code'],$original_name);
		    $value = Mage::getStoreConfig('textmaster/defaultvalue/'.$element_id.'_'.$langue['code']);
		    $html .= '<div><textarea name="'.$name.'" id="'.$element_id.'_'.$langue['code'].'" data-label="'.$langue['value'].'" style="display:none;">'.$value.'</textarea></div>';
		}
		
		$html .= "<script>
			var textmaster_breifing_element_id = '".$element_id."';
		</script>";
		
		
		
		return $html;
	}
}
