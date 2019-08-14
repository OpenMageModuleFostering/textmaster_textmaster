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
 class Textmaster_Textmaster_Block_Adminhtml_Credit extends  Mage_Adminhtml_Block_Template
{
	private $_noapi = false;
	public function __construct()
	{
		
		$this->_controller = 'adminhtml_project';
		$this->_blockGroup = 'textmaster';
		$api_key = Mage::getStoreConfig('textmaster/textmaster/api_key');;
		$api_secret = Mage::getStoreConfig('textmaster/textmaster/api_secret');
		if($api_key=='' || $api_secret=='') $this->_noapi = true;
		if($this->_noapi){
			
		}
		parent::__construct();
	}
	public function getCredit(){
		$_api = Mage::helper('textmaster')->getApi();
		$user_info = $_api->getUserInfo();
		
		if(isset($user_info['wallet'])){
			$currency = Mage::getModel('directory/currency')->load($user_info['wallet']['currency_code']);
			return $currency->format($user_info['wallet']['current_money']);
		}
		return '';
	}
	public function getEmail(){
		$_api = Mage::helper('textmaster')->getApi();
		$user_info = $_api->getUserInfo();
		if(isset($user_info['email']))
			return $user_info['email'];
		return '';
	}
	 
	 
}