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
class Textmaster_Textmaster_Block_Adminhtml_System_Config_Form_Field_Login extends Mage_Adminhtml_Block_System_Config_Form_Field
{
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
		
		$_api = Mage::helper('textmaster')->getApi();
		
		$_isLog = $_api->getAPIConnection();
		
		if(!$_isLog){
	
			$html  = '<input type="text" placeholder="'.Mage::helper('textmaster')->__('Login').'" name="login" id="textmaster_textmaster_login" class="login input-text" /><span style="margin-left:10px;"></span>';
			$html .= '<input type="password" placeholder="'.Mage::helper('textmaster')->__('Password').'" name="password" id="textmaster_textmaster_password" class="login input-text" /><span style="margin-left:10px;"></span>';
			$html .= '<button type="button" style="margin-top:5px;" class="scalable generate_key" onclick="loginAjaxAction()"><span>'.Mage::helper('textmaster')->__('Connexion').'</span></button>';
			$html .= '<div id="result_ajax" style="margin-top:7px;"></div>';
			$html .= "<script>			
			function loginAjaxAction(){
	
				var reloadurl = '". $this->getUrl('textmaster/adminhtml_ajax/login/')."';
				new Ajax.Request(reloadurl, {
					method: 'post',
					parameters: {
						login : \$F('textmaster_textmaster_login'),
						password: \$F('textmaster_textmaster_password'),
					},
					requestHeaders: {Accept: 'application/json'},
		            
					onLoading: function (transport) {
						$('result_ajax').update('".Mage::helper('textmaster')->__('Searching...')."');
	
					},
					onComplete: function(transport) {
						
						data = transport.responseText.evalJSON();
						if(data.errors){					
							$('result_ajax').update(data.errors);
						} else {						
							$('result_ajax').update(data.html);
							window.location = '".$this->getUrl('textmaster/adminhtml_project/index/')."';
						}
					}
				});
			}
			</script>";
		}
		if($_isLog) {
			$html = '<input type="hidden" class="is_textmasterlog" value="1">';
			$html .= '<button type="button" style="margin-top:5px;" class="scalable generate_key" onclick="logoutAjaxAction()"><span>'.Mage::helper('textmaster')->__('Logout').'</span></button>';
			$html .= "<script>
			function logoutAjaxAction(){
			
				var reloadurl = '". $this->getUrl('textmaster/adminhtml_ajax/logout/')."';
				new Ajax.Request(reloadurl, {
					method: 'post',				
					requestHeaders: {Accept: 'application/json'},
			
					onLoading: function (transport) {
						$('result_ajax').update('".Mage::helper('textmaster')->__('Searching...')."');
			
					},
					onComplete: function(transport) {
			
						window.location = window.location;
					}
				});
			}
			</script>";
				
		} else {
			$html .= '<input type="hidden" class="is_textmasterlog" value="0">';
		}
		return $html;
	}
}
