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
class Textmaster_Textmaster_Block_Adminhtml_Renderer_Targetlanguage extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render (Varien_Object $row)
    {
        $store_id = $row->getData('store_id_translation');
        $store = Mage::getModel('core/store')->load($store_id);
        $value = '';
        if ($store->getId()) {
            $value = $store->getWebsite()->getName() . ' - ' . $store->getName();
        }
        return $value;
        
    }
}