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
class Textmaster_Textmaster_Block_Adminhtml_Project_Onglets_Step3 extends Mage_Adminhtml_Block_Widget_Form
{
	private $_project = false;
	
	protected function _prepareForm() {
		$_api = Mage::helper('textmaster')->getApi();
		
		$form = new Varien_Data_Form (array(
				'id' => 'summary_form',
				'action' => $this->getUrl('*/*/edit', array('step' => 3)),
				'method' => 'post',
				'enctype' => 'multipart/form-data'
		));
		if($this->getProject()){
			$post = $this->getProject()->getData();
			$post['attribute'] = array();
			foreach($this->getProject()->getAttributes() as $attribute){
				$post['attribute'][] = $attribute->getTextmasterAttributeId();
			}
		} else {
			$post = Mage::getSingleton('core/session')->getProjectInfo();
		}
		
		
		$form->setUseContainer(true);
		$this->setForm ( $form );
		
		$word_count = Mage::getSingleton('core/session')->getWordCount();
		
		
		
		$fieldset = $form->addFieldset ( 'project_information', array (
				'legend' => Mage::helper ( 'textmaster' )->__ ( 'Project Summary' )
		) );
				
		$fieldset->addField ( 'ctype', 'label', array (
				'label' => Mage::helper ( 'textmaster' )->__ ( 'Type of project' ),
				//'value'=>'N mot'
		));
		
		$fieldset->addField ( 'total_word_count', 'label', array (
				'label' => Mage::helper ( 'textmaster' )->__ ( 'Total word count' ),				
		));
		
		$optionsField = $fieldset->addField ( 'options', 'label', array(
				'label' => Mage::helper ( 'textmaster' )->__ ( 'Level and options' ),
		));
		//$pricing = Mage::getSingleton('core/session')->getPricing();
		$fieldset->addField ( 'price', 'label', array (
				'label' => Mage::helper ( 'textmaster' )->__ ( 'Total cost' ),
				'value' => '-'
		));
		/*$fieldset->addField ( 'modifier', 'button', array (
				'label' =>  '' ,
				'value'=>Mage::helper ( 'textmaster' )->__ ('Modifier les propiété du projet');
		) );*/
		$fieldset->addField ( 'name', 'label', array (
				'label' => Mage::helper ( 'textmaster' )->__ ( 'Project\'s name' ),
				//'value'=>'N mot'
		));
		
		/*$fieldset->addField ( 'category', 'label', array (
				'label' => Mage::helper ( 'textmaster' )->__ ( 'Category' ),
				//'value'=>'N mot'
		));*/
		$fieldset->addField ( 'store_id_origin', 'label', array (
				'label' => Mage::helper ( 'textmaster' )->__ ( 'Source language' ),
				//'value'=>'N mot'
		));
		if($post['ctype']=='translation'){
			$fieldset->addField ( 'store_id_translation', 'label', array (
					'label' => Mage::helper ( 'textmaster' )->__ ( 'Target language' ),
					
			));
		}
		
		
		$myauthors = $this->getProject()->getMyAuthors();
		if($myauthors['count']) {
			$mytextmaster = $fieldset->addField ( 'mytextmaster', 'label', array (
					'label' => Mage::helper ( 'textmaster' )->__ ( 'Favorite authors' ),
					'value'=>''
			));
		}
				
		$this->setFormValues();
				
		return parent::_prepareForm ();
	}
	
	public function setFormValues(){
		$_api = Mage::helper('textmaster')->getApi();
		
		$post = $this->getProject()->getData();
		
		$store_from = Mage::getModel('core/store')->load($post['store_id_origin']);
		$store_to = Mage::getModel('core/store')->load($post['store_id_translation']);
		$post['ctype'] 					= ucfirst($post['ctype']);
		$post['store_id_origin'] 		= $store_from->getWebsite()->getName().' - '.$store_from->getName();
		$post['store_id_translation'] 	= $store_to->getWebsite()->getName().' - '.$store_to->getName();
		$tarifs = $_api->getPricings();
		$currency = Mage::getModel('directory/currency')->load($tarifs['code']);
		
		$userinfo = $_api->getUserInfo();
		
		if(isset($userinfo['wallet']['current_money']))
			$credit = (float) $userinfo['wallet']['current_money'];
		else $credit = 0;
		
		$project_price = (float) $post['price'];
		if($post['total_word_count'])
			$price_per_word = $project_price/$post['total_word_count'];
		else 
			$price_per_word = 0;
		$post['price'] = $currency->format($post['price'],array(),false);
		$html = $post['price'];
		$html .='<br/><span>'.Mage::helper('textmaster')->__('Crédit disponible :').' '.$currency->format($credit).'</span>';
		if($project_price>$credit) {
			$html .='<br/><span style="color:red">'.Mage::helper('textmaster')->__('Crédit manquant :'). $currency->format(($project_price-$credit), array(), false).'</span>';
			$html .='<br/><a href="'.$_api->getInterfaceUri().'clients/payment_requests/new?project_id='.$this->getProject()->getProjectApiid().'" target="_blank">'.Mage::helper('textmaster')->__('Add credits to my TextMaster account').'</a>';
		}
		$this->getForm()->getElement('price')->setAfterElementHtml($html);
		
		$post['price'] = '';
		
		$html = $this->getProject()->getLanguageLevelTexte($post['language_level']).'<br/>';
		if($post['quality']) {
			$html .= Mage::helper( 'textmaster' )->__( 'Contrôle qualité (+%s / mot)' ,$currency->format($tarifs['types']['translation']['quality'],array(),false)).'<br/>';
		}
		if($post['priority']) {
			$html .= Mage::helper( 'textmaster' )->__( 'Commande prioritaire (+%s / mot)',$currency->format($tarifs['types']['translation']['priority'],array(),false) ).'<br/>';
		}
		if($post['expertise']) {
			$html .= Mage::helper( 'textmaster' )->__( 'Expertise (+%s / mot)',$currency->format($tarifs['types']['translation']['expertise'],array(),false) ).'<br/>';
		}
        if($post['translation_memory']) {
            $html .= Mage::helper( 'textmaster' )->__( 'Translation memory (+%s / mot)', $currency->format($tarifs['types']['translation']['translation_memory'],array(),false) ).'<br/>';
        }
		$html .= Mage::helper( 'textmaster' )->__('%s/word',$currency->format($price_per_word,array(),false));
		
		$this->getForm()->getElement('options')->setAfterElementHtml($html);
	
		
		$post['language_level'] 		= $this->getProject()->getLanguageLevelTexte($post['language_level']);
		$post['vocabulary_type'] 		= $this->getProject()->getVocabularyTypeTexte($post['vocabulary_type']);
		$post['target_reader_groups'] 	= $_api->getAudience($post['target_reader_groups']);
		$post['grammatical_person'] 	= $_api->getGrammaticalPerson($post['grammatical_person']);
		
		$post['specific_attachment'] 				= $post['specific_attachment']?Mage::helper( 'textmaster' )->__( 'Oui' ):Mage::helper( 'textmaster' )->__( 'Non' );
		$post['priority'] 							= $post['priority']?Mage::helper( 'textmaster' )->__( 'Oui' ):Mage::helper( 'textmaster' )->__( 'Non' );
		$post['quality'] 							= $post['quality']?Mage::helper( 'textmaster' )->__( 'Oui' ):Mage::helper( 'textmaster' )->__( 'Non' );
		$post['expertise'] 							= $post['expertise']?Mage::helper( 'textmaster' )->__( 'Oui' ):Mage::helper( 'textmaster' )->__( 'Non' );
		
		
		$html = ''.Mage::helper( 'textmaster' )->__( 'Textmaster(s) chosen:' ).' ';
		$textmasters = $this->getProject()->getTextmasters();
		$authors = $_api->getAuthors();
		if(is_array($textmasters) && count($textmasters)>0)
			foreach($textmasters as $t){
				
				foreach ($authors['my_authors'] as $author){
					if($author['author_id']==$t)
						$html .= '<br/>'.$author['description']. ' ( ' . $author['author_ref'] . ' )';
				}
				
			}
		else 
			$html .= Mage::helper( 'textmaster' )->__( 'none' );
		
		$html .= '<br/><a style="cursor:pointer" id="mytextmasterchoice" onclick="showAuthors(\''.$this->getUrl('*/*/getmyauthors',array('id'=>$this->getProject()->getId())).'\',\''.Mage::helper( 'textmaster' )->__( 'My Textmasters' ).'\')">'.Mage::helper( 'textmaster' )->__( 'Choose your TextMasters' ).'</a>';
		if($this->getForm()->getElement('mytextmaster'))
		  $this->getForm()->getElement('mytextmaster')->setAfterElementHtml($html);
		
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