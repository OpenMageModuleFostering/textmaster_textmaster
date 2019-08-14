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
class Textmaster_Textmaster_Adminhtml_Textmaster_AjaxController extends Mage_Adminhtml_Controller_action
{
    protected function _isAllowed()
    {
        return true;
    }
    
	public function loginAction(){
		
		$_api = Mage::helper('textmaster')->getApi();
		$email = $this->getRequest()->getPost('login');
		$password = $this->getRequest()->getPost('password');
		
		Mage::getSingleton('adminhtml/session')->unsTextmasterUserInfos();
		Mage::getSingleton('adminhtml/session')->unsTextmasterMyAuthors();
		Mage::getSingleton('adminhtml/session')->unsTextmasterCategories();
		Mage::getSingleton('adminhtml/session')->unsTextmasterLanguages();
		Mage::getSingleton('adminhtml/session')->unsTextmasterPricings();
		
		$result = $_api->getAuth2Token($email,$password);
		$this->getResponse()->setHeader('Content-type', 'application/json');
		
		
		if (!isset($result['access_token'])){
			$html = Mage::helper('textmaster')->__('Wrong login / password');
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('errors'=>$html)));
			return;
		}
			
		
		$result = $_api->getAPIKeys($result['access_token']);
		
		if (!isset($result['api_info']['api_key']) || !isset($result['api_info']['api_secret'])) {
			Mage::log('Could not get API key / secret',null,'textmaster.log');
			$html =  Mage::helper('textmaster')->__('Could not get API key / secret');
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('errors'=>$html)));
			return;
		}
		
		Mage::getConfig()->saveConfig('textmaster/textmaster/api_key',$result['api_info']['api_key']);
		Mage::getConfig()->saveConfig('textmaster/textmaster/api_secret',$result['api_info']['api_secret']);
		Mage::app()->cleanCache('config');
		$this->getResponse()->setBody( Mage::helper('core')->jsonEncode(array('html'=> '<div class="success" style="padding:2px 8px;background:#EFF5EA ;border:1px solid #95A486;color:#3D6611;font-weight:bold">'.Mage::helper('textmaster')->__('Connected to textmaster').'</div>')));
		
	}
	public function logoutAction(){
		Mage::getConfig()->saveConfig('textmaster/textmaster/api_key','');
		Mage::getConfig()->saveConfig('textmaster/textmaster/api_secret','');
		Mage::app()->cleanCache('config');
		
		Mage::getSingleton('adminhtml/session')->unsTextmasterUserInfos();
		Mage::getSingleton('adminhtml/session')->unsTextmasterMyAuthors();
		
		$this->getResponse()->setHeader('Content-type', 'application/json');
	}
	
	public function createAction(){
		
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$_api = Mage::helper('textmaster')->getApi();
		
		$email = $this->getRequest()->getPost('login');
		$post = $this->getRequest()->getPost();
		$password = $this->getRequest()->getParam('password');
		$phone = $this->getRequest()->getParam('tel');
		
		Mage::getSingleton('adminhtml/session')->unsTextmasterUserInfos();
		Mage::getSingleton('adminhtml/session')->unsTextmasterMyAuthors();
		
		if (!Zend_Validate::is($email, 'EmailAddress')) {
			$html =  Mage::helper('textmaster')->__('Email invalid');
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('errors'=>$html)));
		}
		if ($password == ''){
			$html =  Mage::helper('textmaster')->__('Password mandatory');
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('errors'=>$html)));
		}
		if(isset($html)){
			return ;
		}

		$token = $_api->getAuth2TokenForCreation();		
		if (!isset($token['access_token'])){
			$html =  Mage::helper('textmaster')->__('Could not get access token');
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('errors'=>$html)));
			return;
		}
		
		//CREATION DU CLIENT 
		$aUserInfo = $_api->createUser($token['access_token'], $email, $password,$phone?$phone:null);
		if (isset($aUserInfo['errors'])){
			$html = '';
			foreach ($aUserInfo['errors'] AS $key => $error)
				foreach ($error AS $error_key => $value)
					$html .= $key.' - '.$value.'<br/>';
			$html = '<div class="success" style="padding:2px 8px;background:#FFEEEE ;border:1px solid #FF9999;color:#CC0000;font-weight:bold">'.$html.'</div>';
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('errors'=>$html)));
			return;
		}
		
		if (!isset($aUserInfo['api_info']['api_key']) || !isset($aUserInfo['api_info']['api_secret'])) {
			Mage::log('Could not get API key / secret',null,'textmaster.log');
			$html =  '<div class="success" style="padding:2px 8px;background:#FFEEEE ;border:1px solid #FF9999;color:#CC0000;font-weight:bold">'.Mage::helper('textmaster')->__('Could not get API key / secret').'</div>';
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('errors'=>$html)));
			return;
		}
		Mage::getModel('core/config')->saveConfig('textmaster/textmaster/api_key',$aUserInfo['api_info']['api_key']);
		Mage::getModel('core/config')->saveConfig('textmaster/textmaster/api_secret',$aUserInfo['api_info']['api_secret']);
		Mage::app()->cleanCache('config');
		$this->getResponse()->setBody( Mage::helper('core')->jsonEncode(array('html'=>'<div class="success" style="padding:2px 8px;background:#EFF5EA ;border:1px solid #95A486;color:#3D6611;font-weight:bold">'.Mage::helper('textmaster')->__('Your account has been successfully created.').'</div>')));
		
	}
	
	public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
}