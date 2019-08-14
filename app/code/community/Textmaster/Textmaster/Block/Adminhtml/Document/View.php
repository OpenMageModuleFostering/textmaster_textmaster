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
class Textmaster_Textmaster_Block_Adminhtml_Document_View extends Mage_Adminhtml_Block_Widget_View_Container {
	
	private $_document;
	
	public function __construct()
	{
		parent::__construct();
		$this->_objectId = 'textmaster_document_view';
		$this->_controller = 'adminhtml_project';
		$this->_blockGroup = 'textmaster';
	
	}
	
	public function setDocument($document){		
		$this->_document = $document;
		$this->removeButton('back');
		$this->addButton('back',array(
				'label'     => Mage::helper('adminhtml')->__('Back'),
				'onclick'   => 'window.location.href=\'' . $this->getUrl('*/*/view',array('id'=>$this->getDocument()->getProject()->getId())) . '\'',
				'class'     => 'back',
		));
		if($this->_document->canComplete()){
			$this->addButton('complete',array(
					'label'     => Mage::helper('adminhtml')->__('Update file'),
					'onclick'   => 'completeDocument(\'' . $this->getUrl('*/*/doccomplete',array('id'=>$this->getDocument()->getId())) . '\','.$this->getDocument()->getId().')',
					'class'     => 'save',
			));
			$btn = $this->addButton('revision',array(
					'label'     => Mage::helper('adminhtml')->__('Put in revision'),
					'onclick'   => "showCompare('" . $this->getUrl('*/*/view',array('id'=>$this->getDocument()->getProject()->getId()))."');",
					'class'     => 'save',
			));
			
		}
		
		return $this;
	}
	
	public function getDocument(){
		
		return $this->_document;
	}


}