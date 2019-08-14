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
 class Textmaster_Textmaster_Block_Adminhtml_Project_Onglets_Summary extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
    	$_api = Mage::helper('textmaster')->getApi();
		$form = new Varien_Data_Form(
			array(
				'id' => 'edit_form',
				'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
				'method' => 'post',
           	)
      	);
		$fieldset = $form->addFieldset ( 'project_pricing', array (
			'legend' => Mage::helper ( 'textmaster' )->__ ( 'Order Summary' )
		) );
		$projetField = $fieldset->addField ( 'projet', 'label', array(
			'label' => Mage::helper ( 'textmaster' )->__ ('Project'),
		));
		$nbmotField = $fieldset->addField ( 'nbmot', 'label', array(
			'label' => Mage::helper ( 'textmaster' )->__ ('Word count'),
		));
		$optionsField = $fieldset->addField ( 'options', 'label', array(
			'label' => Mage::helper ( 'textmaster' )->__ ('Level and options'),
		));
		
		$pricingField = $fieldset->addField ( 'pricing', 'label', array(
			'label' => Mage::helper ( 'textmaster' )->__ ('Price'),
		));
		
		$pricing = $_api->getPricings();
        $negotiatedContractPricing = Mage::helper('textmaster')->getNegotiatedContractsPricing();
		$pricing = Mage::helper('core')->jsonEncode($pricing);
        $negotiatedContractPrices = Mage::helper('core')->jsonEncode($negotiatedContractPricing);
		$projetField->setAfterElementHtml('<span id="textmaster_projet"></span>');
		$optionsField->setAfterElementHtml('<span id="textmaster_options"></span>');
		$nbmotField->setAfterElementHtml('<span id="textmaster_nbmot"></span>');
		$pricingField->setAfterElementHtml('
		<span id="textmaster_total_price"></span>
		<script>
            var textmaster_pricing = '.$pricing.';
            var textmaster_negotiated_contract_prices = '.$negotiatedContractPrices.';
			var textmaster_edit_form_url = \''.$this->getUrl('*/*/edit', array('step' => 2)).'\';
        </script>');
      	$form->setUseContainer(true);
      	$this->setForm($form);
      	return parent::_prepareForm();
   }
}
