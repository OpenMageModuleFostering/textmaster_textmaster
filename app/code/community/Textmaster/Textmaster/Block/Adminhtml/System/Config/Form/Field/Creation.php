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
class Textmaster_Textmaster_Block_Adminhtml_System_Config_Form_Field_Creation extends Mage_Adminhtml_Block_System_Config_Form_Field
{
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
		
		$_api = Mage::helper('textmaster')->getApi();
		
		$_isLog = $_api->getAPIConnection();

		$html  = '<input type="text" placeholder="'.Mage::helper('textmaster')->__('Email').'" name="email" id="email" class=" input-text" /><span style="margin-left:10px;"></span><br/>';
		$html .= '<input type="password" placeholder="'.Mage::helper('textmaster')->__('Password').'" name="create_password" id="create_password" class=" input-text" /><span style="margin-left:10px;"></span><br/>';
		$html .= '<input type="password" placeholder="'.Mage::helper('textmaster')->__('Confirm password').'" name="create_password2" id="create_password2" class=" input-text" /><span style="margin-left:10px;"></span><br/>';
		$html .= '<input type="text" placeholder="'.Mage::helper('textmaster')->__('Phone').'" name="tel" id="tel" class="input-text" /><span style="margin-left:10px;"></span><br/>';
		$html .= '<button type="button" style="margin-top:5px;" class="scalable generate_key" onclick="createAjaxAction()"><span>'.Mage::helper('textmaster')->__('Create your free account').'</span></button><br/>';
		$html .= '<div id="result2_ajax" style="margin-top:7px;"></div>';
		$html .= "<script>
		function createAjaxAction(){

			var reloadurl = '". $this->getUrl('textmaster/adminhtml_ajax/create/')."';
			new Ajax.Request(reloadurl, {
				method: 'post',
				parameters: {
					login : \$F('email'),
					password: \$F('create_password'),
					tel : \$F('tel'),
				},
				requestHeaders: {Accept: 'application/json'},
	            
				onLoading: function (transport) {
					$('result2_ajax').update('".Mage::helper('textmaster')->__('Searching...')."');
				},
				onComplete: function(transport) {
					
					data = transport.responseText.evalJSON();
					if(data.errors){
						$('result2_ajax').update(data.errors);
					} else {
						$('result2_ajax').update(data.html);
						window.location = '".$this->getUrl('textmaster/adminhtml_project/index/')."';
					}
				}
			});
		}
		</script>";
		if($_isLog) {
			//$html .= '<input type="hidden" class="is_textmasterlog" value="1">';
		} else {
			$html .= '<input type="hidden" class="is_textmasterlog" value="0">';
		}
		return $html;
	}
}
