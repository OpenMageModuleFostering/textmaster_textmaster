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
 class Textmaster_Textmaster_Model_Document extends Mage_Core_Model_Abstract {
	
	private $_product = false;
	
	const DOCUMENT_STATUS_IN_CREATION 	= "in_creation";
	const DOCUMENT_STATUS_IN_PROGRESS 	= "in_progress";
	const DOCUMENT_STATUS_IN_REVIEW		= "in_review";
	const DOCUMENT_STATUS_CANCEL 	 	= "canceled";
	const DOCUMENT_STATUS_COMPLETED 	= "completed";
	const DOCUMENT_STATUS_PAUSED 	 	= "paused";
	
	public function _construct() {
		parent::_construct ();
		$this->_init ( 'textmaster/document' );
	}
	
	public function send() {
		if (! $this->getSend ()) {
			
			$data = $this->prepareData();
			
			$project = Mage::getModel ( 'textmaster/project' )->load ( $this->getTextmasterProjectId (), null, false );
			
			$result = Mage::helper('textmaster')->getApi()->addDocument ( $project->getProjectApiid (), $data );
			if (! isset ( $result ['error'] )) {
				$this->setDocumentApiId ( $result ['id'] );
				$this->setSend ( 1 );
				$this->save ();
			}
		}
	}
	
	public function canComplete() {
		return $this->getStatus()==self::DOCUMENT_STATUS_IN_REVIEW;
	}
	
	public function complete(){
         
       $this->setStatus(self::DOCUMENT_STATUS_COMPLETED);
       $this->setCompleted(1);
       $this->setData('encourscomplete',0);
       $this->save();
       $this->transfert();      
         
        return $this;
	}
	public function prepareToComplete(){
	
	    $this->setData('encourscomplete',1);
	    $this->setSendforcomplete(1);
	    $this->setCompleted(0);
	    $this->save();
	    	
	    return $this;
	}
	public function sendToComplete(){
		
		$result = Mage::helper('textmaster')->getApi()->completeDocument($this->getProject()->getProjectApiid(),$this->getDocumentApiId());
		if(!isset($result['error'])){
		    $this->prepareToComplete();
		}
			
		return $this;
	}
	
	public function canTransfert(){
		return $this->getStatus()==self::DOCUMENT_STATUS_COMPLETED;
	}
	public function transfert(){
		//if($this->canTransfert()) {
			$translations = $this->getTranslations();		
			Mage::getSingleton('catalog/product_action')->updateAttributes(array($this->getProductId()), $translations, $this->getProject()->getStoreIdTranslation());
		//}	
		return $this;
	}
	
	public function getStatusTexte() {
		// Mage::
		$statuses = array (
			'in_creation' 			=> Mage::helper ( 'textmaster' )->__ ( 'In creation' ),
			'waiting_assignment' 	=> Mage::helper ( 'textmaster' )->__ ( 'Waiting assignment' ),
			'in_progress' 			=> Mage::helper ( 'textmaster' )->__ ( 'In progress' ),
			'in_review' 			=> Mage::helper ( 'textmaster' )->__ ( 'In review' ),
			'completed' 			=> Mage::helper ( 'textmaster' )->__ ( 'Completed' ),
			'incomplete' 			=> Mage::helper ( 'textmaster' )->__ ( 'Incomplete' ),
			'paused' 				=> Mage::helper ( 'textmaster' )->__ ( 'Paused' ),
			'canceled' 				=> Mage::helper ( 'textmaster' )->__ ( 'Cancelled' ),
			'copyscape' 			=> Mage::helper ( 'textmaster' )->__ ( 'Copyscape' ),
			'counting_words' 		=> Mage::helper ( 'textmaster' )->__ ( 'Counting words' ),
			'quality_control' 		=> Mage::helper ( 'textmaster' )->__ ( 'Quality control' ) 
		);
		if (isset ( $statuses [parent::getStatus ()] ))
			return $statuses [parent::getStatus ()];
		return $this->getStatus ();
	}
	
	
	public function delete(){
		if($this->getProject() && $this->getProject()->getProjectApiid()!='' && $this->getDocumentApiId()!=''){
			Mage::helper('textmaster')->getApi()->deleteDocument($this->getProject()->getProjectApiid(),$this->getDocumentApiId());
		}
		return parent::delete();
	}
	
	public function load($id, $field = null,$api = true,$project_api=true) {
		$return = parent::load ( $id, $field = null );
		$project_api = $project_api && $api;
		
		$this->setProject(Mage::getModel('textmaster/project')->load($this->getTextmasterProjectId(),null,$project_api));
		
		if($api && $this->getProject() && $this->getDocumentApiId()!='' && $this->getProject()->getProjectApiid()!='') {
			$data_api = Mage::helper('textmaster')->getApi()->getDocument($this->getProject()->getProjectApiid(),$this->getDocumentApiId());
			$this->setStatus($data_api['status']);
			if(isset($data_api['author_work']))
				$this->setTranslations($data_api['author_work']);
		}
		return $return;
	}
	public function loadByApiId($api_id) {
		$documentId = $this->getResource()->loadByApiId($api_id);
		
		if ($documentId) {
			$this->load($documentId);
		} else {
			$this->setData(array());
		}
		return $this;
	}
	public function getSupportMessages(){
		if($this->_support_message) return $this->_support_message;
		$this->_support_message = Mage::helper('textmaster')->getApi()->getSupportMessages($this->getProject()->getProjectApiid(),$this->getDocumentApiId());
		return $this->_support_message;
	}
	
	public function prepareData(){
		$data = $this->getData ();
		$data ['title'] = $data ['name'];
		$data ['type'] = 'key_value';
		unset ( $data ['name'] );
			
		$attributes = Mage::getModel ( 'textmaster/project_attribute' )->getCollection ()->addFieldToFilter ( 'textmaster_project_id', $this->getTextmasterProjectId () );
		$product = Mage::getModel ( 'catalog/product' )->setStoreId( $this->getProject()->getStoreIdOrigin() )->load ( $this->getProductId () );
		$data ['original_content'] = array ();
        $data['markup_in_content'] = false;
		$text = '';
		foreach ( $attributes as $attr ) {
			$attribute = Mage::getModel ( 'catalog/resource_eav_attribute' )->load ( $attr->getTextmasterAttributeId () );
			$text_attr = $product->getData ( $attribute->getName () );			
			$text .= $text_attr . ' ';
			if (! empty ( $text_attr )){
				$data ['original_content'] [$attribute->getName ()] = array (
						'original_phrase' => $text_attr
				);
                if(strip_tags($text_attr) != $text_attr){
                    $data['markup_in_content'] = true;
                }
            }
		}
			
		$data ['word_count'] = Mage::helper ( 'textmaster' )->countWord ( $text );
		$data ['word_count_rule'] = 1;
		$data ['instructions'] = '';
		$data ['keyword_list'] = '';
		$data ['keywords_repeat_count'] = 0;
		$data ['id_product'] = $this->getProductId ();
			
		if(isset( $data ['_project'])) unset ( $data ['_project'] );
		unset ( $data ['send'] );
		unset ( $data ['counted'] );
		unset ( $data ['textmaster_document_id'] );
		unset ( $data ['product_id'] );
		unset ( $data ['updated_at'] );
		unset ( $data ['textmaster_project_id'] );
		unset ( $data ['created_at'] );
		unset ( $data ['document_api_id'] );
		unset ( $data ['original_content'] ['image'] );

		return $data;
	}
	
	public function updateApiData($message){
			$data = $this->prepareData();
			
			$project = Mage::getModel ( 'textmaster/project' )->load ( $this->getTextmasterProjectId (), null, false );
			
			$result = Mage::helper('textmaster')->getApi()->updateDocument ( $project->getProjectApiid (),$this->getProjectApiId(), $data );
						
	}
	
	
	public function setProject($project){
		$this->_project = $project;
		return $this;
	}
	
	public function getProject(){
		return $this->_project;
	}
	public function getProduct(){
		if($this->_product) return $this->_product;
		if(!$this->getProject()){
		    $this->_project = Mage::getModel('textmaster/projet')->load($this->getTextmasterProjectId(),null,false);
		}
		$this->_product = Mage::getModel('catalog/product')->setStoreId( $this->getProject()->getStoreIdOrigin())->load($this->getProductId());
		return $this->_product;
	}
	public function getTranslations(){
	    $all_translations = Mage::getSingleton('adminhtml/session')->getTextmasterTranslations();
	    if(isset($all_translations[$this->getId()])) return $all_translations[$this->getId()];
	    
	    $translations = $this->getData('translations');
	    if($translations == null){
	        $this->load($this->getId());
	    }
 	        
        if($all_translations!=null){
            $all_translations[$this->getId()] = $translations;
        } else {
            $all_translations = array($this->getId()=>$translations);
        }
        if($this->canComplete())
            Mage::getSingleton('adminhtml/session')->setTextmasterTranslations($all_translations);
	    
	    return $all_translations[$this->getId()];
	}
	public function revision($message){
	    $all_translations = Mage::getSingleton('adminhtml/session')->getTextmasterTranslations();
	    if(isset($all_translations[$this->getId()])){
	        unset($all_translations[$this->getId()]);
	        Mage::getSingleton('adminhtml/session')->setTextmasterTranslations($all_translations);
	    }
	    //if($this->canComplete()){
	        $data = $this->prepareData();
	        $result = Mage::helper('textmaster')->getApi()->commentDocument( $this->getProject()->getProjectApiid (),$this->getDocumentApiId(),$message );
	        if ( isset ( $result ['error'] )) {
	            Mage::getSingleton('adminhtml/session')->addError($this->getName().' '.$result['error']);
	        }
	    //}
	}
}