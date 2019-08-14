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
class Textmaster_Textmaster_Block_Adminhtml_System_Config_Form_Field_Islog extends Mage_Adminhtml_Block_System_Config_Form_Field
{
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
		
		$_api = Mage::helper('textmaster')->getApi();
		
		$_isLog = $_api->getAPIConnection();
				
		if($_isLog) {
			$html = '<input type="hidden" id="textmaster_textmaster_istextmasterlog" name="istextmasterlog" value="1">';
			$html .= "<script>
			    var textmaster_is_log = true;
				
			</script>";
		} else {
			$html = '<input type="hidden" id="textmaster_textmaster_istextmasterlog" name="istextmasterlog" value="0">';
			$html .= "<script>
			     var textmaster_is_log = false;    				
			</script>";
		}
		
		
		return $html;
	}
}
