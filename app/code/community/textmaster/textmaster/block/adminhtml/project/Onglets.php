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
 class Textmaster_Textmaster_Block_Adminhtml_Project_Onglets extends Mage_Adminhtml_Block_Widget_Form_Container {
	public function __construct() {
		parent::__construct ();
		$this->_objectId = 'textmaster_project_id';
		$this->_controller = 'adminhtml_project';
		$this->_blockGroup = 'textmaster';
		
		$this->_removeButton ( 'save' );
		$this->_removeButton ( 'delete' );
		$this->_removeButton ( 'back' );
		$this->_removeButton ( 'saveandcontinue' );
		$this->_removeButton ( 'reset' );
		$step = $this->getRequest ()->getParam ( "step" );
		if ($step == 2) {		
			$this->_addButton ( 'save', array (
					'label' => Mage::helper ( 'adminhtml' )->__ ( 'Continue' ),
					'onclick' => 'valideStep2();',
					'class' => 'save' 
			), - 100 );
		}
		if ($step == 3) {
			
		}
	}
	public function getHeaderText() {
		/*
		 * if( Mage::registry('project_data') && Mage::registry('project_data')->getId()) { return Mage::helper('textmaster')->__("Edit Project '%s'", $this->htmlEscape(Mage::registry('project_data')->getId())); } else { return Mage::helper('textmaster')->__('Add Project'); }
		 */
	}
}
