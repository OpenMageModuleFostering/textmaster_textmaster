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
 class Textmaster_Textmaster_Model_Observer extends Mage_Core_Model_Abstract {
	
	public function _productCreateBlock(Varien_Event_Observer $observer) {
		
		$action = Mage::app()->getRequest()->getRequestedActionName();
		$block = $observer->getEvent()->getBlock();
		if ($block->getNameInLayout() == 'root') {
			if (Mage::app()->getRequest()->getControllerName() == 'catalog_product' ) {
				
				$extendBlock = new Mage_Core_Block_Template();
		        $extendBlock->setTemplate('textmaster/product/edit.phtml');
		        $extendBlock->setIsAnonymous(true);
		        if ($extendBlock) {
		        	$block->getChild('content')->insert($extendBlock, '', false, 'TM_Translate_form');
		        }
		        //return $block;
			}
		}
	}
	
}