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
 class Textmaster_Textmaster_Block_Adminhtml_Project_View extends Mage_Adminhtml_Block_Widget_Container {
	
	private $_project;
	
	public function __construct()
	{
		parent::__construct();
		$this->_objectId = 'textmaster_project_view';
		$this->_controller = 'adminhtml_project';
		$this->_blockGroup = 'textmaster';

		
	}
	public function prepareButton()
	{		
		$this->removeButton('edit');
		if($this->getProject()->canLaunch()){
			$this->_addButton ( 'launch', array (
					'label' => Mage::helper ( 'textmaster' )->__ ( 'Launch' ),
					'onclick' => 'window.location.href=\'' . $this->getUrl('*/*/view/',array('id'=>$this->getProject()->getId(),'a'=>'launch')).'\'' ,
					'class' => 'save'
			), - 100 );
		}
		if($this->getProject()->canPause()){
			$this->_addButton ( 'pause', array (
					'label' => Mage::helper ( 'textmaster' )->__ ( 'Pause' ),
					'onclick' => 'window.location.href=\'' . $this->getUrl('*/*/view/',array('id'=>$this->getProject()->getId(),'a'=>'pause')).'\'' ,
					'class' => ''
			), - 100 );
		}
		if($this->getProject()->canResume()){
			$this->_addButton ( 'resume', array (
					'label' => Mage::helper ( 'textmaster' )->__ ( 'Resume' ),
					'onclick' => 'window.location.href=\'' . $this->getUrl('*/*/view/',array('id'=>$this->getProject()->getId(),'a'=>'resume')).'\'' ,
					'class' => ''
			), - 100 );
		}
		if($this->getProject()->canComplete()){
			$this->_addButton ( 'complete', array (
					'label' => Mage::helper ( 'textmaster' )->__ ( 'Complete' ),
					'onclick' => 'completeDocuments(\'' . $this->getUrl('*/*/view/',array('id'=>$this->getProject()->getId(),'a'=>'complete')).'\',0)' ,
					'class' => ''
			), - 100 );
		}
		if($this->getProject()->canCancel()){
			$this->_addButton ( 'cancel', array (
					'label' => Mage::helper ( 'textmaster' )->__ ( 'Cancel' ),
					'onclick' => 'window.location.href=\'' . $this->getUrl('*/*/view/',array('id'=>$this->getProject()->getId(),'a'=>'cancel')).'\'' ,
					'class' => 'delete'
			), - 100 );
		}
		if($this->getProject()->canTransfert()){
			$this->_addButton ( 'translate', array (
					'label' => Mage::helper ( 'textmaster' )->__ ( 'Update product' ),
					'onclick' => 'window.location.href=\'' . $this->getUrl('*/*/view/',array('id'=>$this->getProject()->getId(),'a'=>'translate')).'\'' ,
					'class' => 'delete'
			), - 100 );
		}
		
		
		return $this;		
	}
	
	public function setProject($project){
		$this->_project = $project;
	}
	
	public function getProject(){
		return $this->_project;
	}	
}