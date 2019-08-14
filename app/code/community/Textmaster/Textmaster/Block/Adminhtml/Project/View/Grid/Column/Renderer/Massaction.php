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
class Textmaster_Textmaster_Block_Adminhtml_Project_View_Grid_Column_Renderer_Massaction extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Checkbox {
	
	protected function _getCheckboxHtml($value, $checked)
    {
    	$collection = $this->getColumn()->getGrid()->getCollection();
    	foreach($collection as $document){
    		if($document->getId()==$value){
    			if($document->getStatus()!='in_review'){
    				return '';
    			}
    		}
    	}
    	$html = '<input type="checkbox" name="' . $this->getColumn()->getName() . '" ';
        $html .= 'value="' . $this->escapeHtml($value) . '" class="massaction-checkbox"' . $checked . '/>';
        return $html;
    }
}