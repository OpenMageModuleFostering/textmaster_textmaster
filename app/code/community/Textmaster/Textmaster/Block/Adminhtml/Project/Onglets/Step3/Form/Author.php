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
class Textmaster_Textmaster_Block_Adminhtml_Project_Onglets_Step3_Form_Author extends Mage_Adminhtml_Block_Widget_Form{
	
	/*protected function _prepareLayout()
	{
		
		return parent::_prepareLayout();
	}*/
	private $_project = false;

	protected function _prepareForm() {
		$_api = Mage::helper('textmaster')->getApi();
	
		$form = new Varien_Data_Form (array(
				'id' => 'edit_form',
				'action' => $this->getUrl('*/*/getmyauthors', array('id' => $this->getProject()->getId())),
				'method' => 'post',
				'enctype' => 'multipart/form-data'
		));
		
		$form->setUseContainer(true);
		$this->setForm ( $form );
		
		$fieldset = $form->addFieldset ( 'project_information', array (
				'legend' => ''
		) );
		$myauthors = $this->getProject()->getMyAuthors();
		$used_myauthors = explode(',',Mage::getStoreConfig('textmaster/defaultvalue/default_language'));
		
		
		$myauthorsSelect = array ();
		if($this->getProject()->getSameAuthorMustDoEntireProject()) {
		    $myauthorsSelect[]= array(
						'value' => 'aucun',
						'label' =>  'Aucun'
				);
		}
		if(isset($myauthors['my_authors'])) {
			foreach ($myauthors['my_authors'] as $myauthor) {
				$myauthorsSelect [] = array(
						'value' => $myauthor ["author_id"],
						'label' => $myauthor ["description"] . ' ( ' . $myauthor ["author_ref"] . ' )'
				);
			}
		}
		if(count($myauthorsSelect)) {
    		if(!$this->getProject()->getSameAuthorMustDoEntireProject()) {
    			$input = $fieldset->addField ( 'textmasters', 'checkboxes', array (
    					'label' => Mage::helper ( 'textmaster' )->__ ( 'My Textmasters' ),
    					'name' => 'textmasters[]',
    					'values' => $myauthorsSelect
    			) );
    		} else {
    			
    			$input = $fieldset->addField ( 'textmasters', 'radios', array (
    					'label' => Mage::helper ( 'textmaster' )->__ ( 'My Textmasters' ),
    					'name' => 'textmasters',
    					'values' => $myauthorsSelect,
    				) );
    			$input->setSeparator('<br/>');
    			
    		}
		} else {
		    $fieldset->addField ( 'textmasters', 'label', array (
		            'label' => Mage::helper ( 'textmaster' )->__ ( 'You do not have any authors in your Favorites list' ),
		    ));
		}
		
		$this->setFormValues();
		$post = $this->getRequest()->getPost();
		if(count($post)){
			$input->setAfterElementHtml('
			<script>
				window.top.location.href = window.top.location.href;
			</script>');
		} 
		
		
		return parent::_prepareForm();
	}
	public function setFormValues(){
	    $post = $this->getRequest()->getPost();
	    if(count($post)){
	        $this->getForm()->setValues ( $post );
	        return;
	    }
		$_api = Mage::helper('textmaster')->getApi();
		$post['textmasters'] = $this->getProject()->getTextmasters();
		if($this->getProject()->getSameAuthorMustDoEntireProject()) {
		    if(!(empty($post['textmasters']) || count($post['textmasters'])==0)) {
			     $textmasters=$post['textmasters'][0];
			     $post['textmasters']=$textmasters;
		    }
		}
		
		if(empty($post['textmasters']) || count($post['textmasters'])==0){
		    $used_myauthors = explode(',',Mage::getStoreConfig('textmaster/defaultvalue/author'));
		    $myauthors = $this->getProject()->getMyAuthors();
		    $post['textmasters'] = array();
		    foreach($used_myauthors as $author) {
		        foreach($myauthors['my_authors'] as $author_av) {
		            if($author==$author_av['author_id']) $post['textmasters'][]=$author_av['author_id'];
		        }
		    }		    
		}
		if ($post) {
			$this->getForm()->setValues ( $post );
		}
	}
	
	
	public function setProject($project){
		$this->_project = $project;
		return $this;
	}
	
	public function getProject(){
		return $this->_project;
	}
}