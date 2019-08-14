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
 class Textmaster_Textmaster_Model_Project extends Mage_Core_Model_Abstract {
	
	
    const PROJECT_CTYPE_COPYWRITING  = "copywriting";
    const PROJECT_CTYPE_TRANSLATION  = "translation";
    const PROJECT_CTYPE_PROOFREADING = "proofreading";
	
    const PROJECT_LANGUAGE_LEVEL_REGULAR    = "regular";
    const PROJECT_LANGUAGE_LEVEL_PREMIUM    = "premium";
    const PROJECT_LANGUAGE_LEVEL_ENTERPRISE = "enterprise";
	
    const PROJECT_STATUS_IN_LAUNCH_PROCESSING = "in_launch_processing";
    const PROJECT_STATUS_IN_CREATION          = "in_creation";
    const PROJECT_STATUS_IN_PROGRESS          = "in_progress";
    const PROJECT_STATUS_IN_REVIEW            = "in_review";
    const PROJECT_STATUS_CANCEL               = "canceled";
    const PROJECT_STATUS_COMPLETED            = "completed";
    const PROJECT_STATUS_PAUSED               = "paused";

    const PROJECT_TM_STATUS_IN_PROGRESS = "in_progress";
    const PROJECT_TM_STATUS_COMPLETED   = "completed";
	
	private $_store_name_origin = false;
	private $_store_name_translation = false;
	private $_api_loaded = false;
	private $_load_api = true;
	private $_api = null;
	private $_products_id = false;
	private $_nb_author = 0;
	private $_authors = array();
	public $_before_save_api = true;
	private $_project_loaded = true;
	
	public function _construct() {
		parent::_construct ();
		$this->_init ( 'textmaster/project' );
	}
	public function setBeforeSaveApi($val){
		$this->_before_save_api = $val;
	}
	public function getBeforeSaveApi($val){
		return $this->_before_save_api;
	}
	
	public function getStoreNameOrigin() {
		if ($this->_store_name_origin)
			return $this->_store_name_origin;
		$store = Mage::getModel ( 'core/store' )->load ( $this->getStoreIdOrigin () );
		$this->_store_name_origin = $store->getWebsite()->getName().' - '.$store->getName ();
		return $this->_store_name_origin;
		
		/* AFFICHE la langue du STORE au lieu du nom
		 * $localecode = Mage::getStoreConfig('general/locale/code', $this->getStoreIdOrigin ());
		$data = explode('_', $localecode);
		$localeAdmin = Mage::app()->getLocale();
		$tmp = $localeAdmin->getTranslation($data[0], 'language', $localeAdmin);
		
		$tmp = ucwords($tmp);
		$this->_store_name_origin = $tmp;
		
		return $this->_store_name_origin;*/
	}
	public function getStoreOriginLangCode(){
	    $localecode = Mage::getStoreConfig('general/locale/code', $this->getStoreIdOrigin ());
	    $data = explode('_', $localecode);		    
	    return $data[0];
	}
	public function getStoreTranslationLangCode(){
	    $localecode = Mage::getStoreConfig('general/locale/code', $this->getStoreIdTranslation ());
	    $data = explode('_', $localecode);
	    return $data[0];
	}
	
	
	public function getStoreNameTranslation() {
		if ($this->_store_name_translation)
			return $this->_store_name_translation;
		$store = Mage::getModel ( 'core/store' )->load ( $this->getStoreIdTranslation () );
		$this->_store_name_translation = $store->getWebsite()->getName().' - '.$store->getName ();
		return $this->_store_name_translation;
		
		/* AFFICHE la langue du STORE au lieu du nom
		 * $localecode = Mage::getStoreConfig('general/locale/code', $this->getStoreIdTranslation ());
		$locale = new Zend_Locale($localecode);
		$data = explode('_', $localecode);
		$tmp = $locale->getTranslation($data[0], 'language', $locale);
		
		$tmp = ucwords($tmp);
		$this->_store_name_translation = $tmp;
		
		return $this->_store_name_translation;*/
	}
	
	public function load($id, $field = null, $api = true) {
	    $this->_load_api = $api;
	   
		$return = parent::load ( $id, $field = null );
		if($this->getTextmasters()!='') {
			$textmaster = $this->getTextmasters();
			if(!is_array($textmaster)){
				$serializeData = @unserialize($textmaster);
				if($serializeData)
					$this->setTextmasters($serializeData);
				else 
					$this->setTextmasters(null);
			}
		}
		
		
		if($api && $this->getProjectApiid() && !$this->project_loaded) {
			$data_api = Mage::helper('textmaster')->getApi()->getProject($this->getProjectApiid());
			//Mage::log ( $data_api,null,'textmaster.log' );
            if(!isset($data_api['error'])){
                $this->project_loaded = true;
                if(isset($data_api['options']['language_level']))
                    $this->setLanguageLevel($data_api['option_values']['language_level']);
                else 
                    $this->setLanguageLevel('regular');
                if(isset($data_api['reference']))
                    $this->setReference( $data_api['reference'] );
                
                $this->setCategory($data_api['category']);
                $this->setCtype($data_api['ctype']);
                $this->setTemplate($data_api['work_template']['name']);
				$this->setSameAuthorMustDoEntireProject($data_api['same_author_must_do_entire_project']);
				$this->setVocabularyType($data_api['vocabulary_type']);
				$this->setGrammaticalPerson($data_api['grammatical_person']);
				$this->setTotalWordCount($data_api['total_word_count']);
				$this->setTargetReaderGroups($data_api['target_reader_groups']);
				$this->setStatus($data_api['status']);
				$this->setProjectBriefing($data_api['project_briefing']);
				
				if(isset($data_api['options']['specific_attachment']))
					$this->setSpecificAttachment($data_api['option_values']['specific_attachment']);
				else 
					$this->setSpecificAttachment(0);
				
				if(isset($data_api['cost_in_currency'])){
					$this->setPrice($data_api['cost_in_currency']['amount']);
					$this->setCurrency($data_api['cost_in_currency']['currency']);
				} elseif(isset($data_api['total_costs']['0'])){
					$this->setPrice($data_api['total_costs']['0']['amount']);
					$this->setCurrency($data_api['total_costs']['0']['currency']);
				} else {					
					$this->setPrice(0);								
				}
				if(isset($data_api['options']['priority']))
					$this->setPriority($data_api['option_values']['priority']);
				else {
					$this->setPriority(0);
				}
				if(isset($data_api['options']['quality']))
					$this->setQuality($data_api['option_values']['quality']);
				else {
					$this->setQuality(0);
				}
				if(isset($data_api['options']['expertise']))
					$this->setExpertise($data_api['option_values']['expertise']);
				else {
					$this->setExpertise(0);
				}
                if(isset($data_api['options']['translation_memory']))
                    $this->setTranslationMemory($data_api['option_values']['translation_memory']);
                if(isset($data_api['options']['negotiated_contract']))
                    $this->setNegotiatedContract($data_api['option_values']['negotiated_contract']);
                
				//TODO 
				$this->setIsmytextmaster(0);
				$this->setTextmasters($data_api['textmasters']);
				
			} else {
				throw new Exception('ERREUR chargement data API');
			}
		}		
		return $return;
	}
    
    protected function _beforeSave(){
        if(!$this->_before_save_api){
            return parent::_beforeSave();
        }
        if (! $this->_api_loaded) {
            $this->_api = Mage::helper('textmaster')->getApi();
        }
        $data = $this->getData();
        /*if(isset($data['textmasters']) && isset($data['textmasters'][0]) && !strpos($data['textmasters'][0],',')!==false){
            $data['textmasters'] = explode(',',$data['textmasters'][0]);
            
        }*/
        if(isset($data['textmasters']) && is_array($data['textmasters']))
          $this->setTextmasters(serialize($data['textmasters']));
            
        //Create
        if(!$this->getId()){
                            
            //unset($params['_documents']);
            foreach($data as $k=>$v){
                if(gettype($v)!='object')
                    $params[$k]=$v;
            }
            $this->setTextmasterUser(Mage::getStoreConfig('textmaster/textmaster/api_key'));
            // $params['language_from']     = substr(Mage::getStoreConfig('general/locale/code',$params['store_id_origin']),0,2);
            $params['language_from'] = Mage::helper('textmaster')->getFormatedLangCode($params['store_id_origin']);
        
            if($params['ctype']!='translation')
                $params['store_id_translation'] = $params['store_id_origin'];
            // $params['language_to'] = substr(Mage::getStoreConfig('general/locale/code',$params['store_id_translation']),0,2);
            $params['language_to'] = Mage::helper('textmaster')->getFormatedLangCode($params['store_id_translation']);

            //Unset translation_memory from params data when create project
            if(isset($params['translation_memory']))
                unset($params['translation_memory']);

			/*if(!is_array($params['textmasters']) && !empty($params['textmasters']))
				$params['textmasters'] = explode(',',$params['textmasters']);*/
			$result = $this->_api->addProject($params);
			if(!isset($result['error'])){
				$this->setProjectApiid($result['id']);
				$this->setStatus($result['status']);
			} else {
				throw new Exception($result['error']);
			}
			
				
		} else {
			if($this->getStatus()==self::PROJECT_STATUS_IN_CREATION && !$this->getHasChangeMemoryStatus()){
				//$data = $this->getData();
						
				//unset($params['_documents']);
				foreach($data as $k=>$v){
					if(gettype($v)!='object')
						$params[$k]=$v;
				}
				$params['language_from'] = Mage::helper('textmaster')->getFormatedLangCode($params['store_id_origin']);
				$params['language_to'] = Mage::helper('textmaster')->getFormatedLangCode($params['store_id_translation']);

                //Unset translation_memory from params data when create project
                if(isset($params['translation_memory']))
                    unset($params['translation_memory']);
								
				$result = $this->_api->updateProject($this->getProjectApiid(),$params);
				if(!isset($result['error'])){					
					$this->setStatus($result['status']);
				} else {
					throw new Exception($result['error']);
				}
			}			
		}
		
		return parent::_beforeSave();
	}

    public function startTranslationMemory(){
        if(
            $this->getStatus() == self::PROJECT_STATUS_IN_CREATION
            && $this->getTranslationMemory()
            && $this->getTranslationMemoryStatus() == ''
        ){
            if(!$this->_api_loaded) {
                $this->_api = Mage::helper('textmaster')->getApi();
            }
            $this->setTranslationMemoryStatus(self::PROJECT_TM_STATUS_IN_PROGRESS);
            $this->setHasChangeMemoryStatus(true);
            $params['translation_memory'] = $this->getTranslationMemory();
            $params['language_level'] = $this->getLanguageLevel();
            $params['quality'] = $this->getQuantity();
            $params['specific_attachment'] = $this->getSpecificAttachment();
            $params['priority'] = $this->getPriority();
            $params['same_author_must_do_entire_project'] = $this->getSameAuthorMustDoEntireProject();
            $result = $this->_api->updateProject($this->getProjectApiid(), $params);
            if(!isset($result['error'])){
                $this->setStatus($result['status']);
                $this->save();
            } else {
                throw new Exception($result['error']);
            }
        }
    }
	
	public function getStatusTexte(){
		//Mage::
		$statuses = array(
				'in_creation' 		=> Mage::helper('textmaster')->__('In creation'),
				'waiting_assignment'=> Mage::helper('textmaster')->__('Waiting assignment'),
				'in_progress' 		=> Mage::helper('textmaster')->__('In progress'),
				'in_review' 		=> Mage::helper('textmaster')->__('In review'),
				'completed' 		=> Mage::helper('textmaster')->__('Completed'),
				'incomplete' 		=> Mage::helper('textmaster')->__('Incomplete'),
				'paused' 			=> Mage::helper('textmaster')->__('Paused'),
				'canceled' 			=> Mage::helper('textmaster')->__('Cancelled'),
				'copyscape' 		=> Mage::helper('textmaster')->__('Copyscape'),
				'counting_words' 	=> Mage::helper('textmaster')->__('Counting words'),
				'quality_control' 	=> Mage::helper('textmaster')->__('Quality control'));
		if(isset($statuses[parent::getStatus()])) return $statuses[parent::getStatus()];
		return $this->getStatus();
	}
	public function getStatuses(){
		$statuses = array(
				'in_creation' 		=> Mage::helper('textmaster')->__('In creation'),
				'waiting_assignment'=> Mage::helper('textmaster')->__('Waiting assignment'),
				'in_progress' 		=> Mage::helper('textmaster')->__('In progress'),
				'in_review' 		=> Mage::helper('textmaster')->__('In review'),
				'completed' 		=> Mage::helper('textmaster')->__('Completed'),
				'incomplete' 		=> Mage::helper('textmaster')->__('Incomplete'),
				'paused' 			=> Mage::helper('textmaster')->__('Paused'),
				'canceled' 			=> Mage::helper('textmaster')->__('Cancelled'),
				'copyscape' 		=> Mage::helper('textmaster')->__('Copyscape'),
				'counting_words' 	=> Mage::helper('textmaster')->__('Counting words'),
				'quality_control' 	=> Mage::helper('textmaster')->__('Quality control'));
		return $statuses;
	}
	
	public function getPrice(){
		$currency = Mage::getModel('directory/currency')->load($this->getCurrency());
		return $currency->format($this->getData('price'),array(),false);
	}
	
	public function getVocabularyTypeTexte(){
		if(!$this->_api){
			$this->_api = Mage::helper('textmaster')->getApi();
		}
		return Mage::helper ( 'textmaster' )->__ ($this->_api->getVocabularyLevel($this->getVocabularyType()));
	}
	
	public function getGrammaticalPersonTexte(){
		if(!$this->_api){
			$this->_api = Mage::helper('textmaster')->getApi();
		}
		return Mage::helper ( 'textmaster' )->__ ($this->_api->getGrammaticalPerson($this->getGrammaticalPerson()));
	}
	
	public function getLanguageLevelTexte(){
		if(!$this->_api){
			$this->_api = Mage::helper('textmaster')->getApi();
		}
		return Mage::helper ( 'textmaster' )->__ ($this->_api->getServiceLevel($this->getLanguageLevel()));
	}
	
	public function getCategoryTexte(){
		if(!$this->_api){
			$this->_api = Mage::helper('textmaster')->getApi();
		}
		return Mage::helper ( 'textmaster' )->__ ($this->_api->getCategory($this->getCategory()));
	}
	
	protected function _afterSave(){
		if(!$this->_before_save_api){
			return parent::_afterSave();
		}
		if($this->getTextmasters()!='') {
		    $textmasters = $this->getTextmasters();
		      if(is_array($textmasters))		
			     $this->setTextmasters(unserialize($this->getTextmasters()));
		}
		return parent::_afterSave();
	}
	public function saveTextmasters($textmasters){
	    if($textmasters[0]=="aucun"){
	        $textmasters= array();
	    }	    
	    $r =  Mage::helper('textmaster')->getApi()->updateProjectTextmasters($this->getProjectApiid(),$textmasters);   
	}
	
	public function setDocuments($documents){
		$this->_documents = $documents;
	}
	
	public function getDocuments(){
		if(!isset($this->_documents)){
			$this->_documents = Mage::getModel('textmaster/document')->getCollection()->setLoadApi($this->_api_loaded)->addFieldToFilter('textmaster_project_id',$this->getId());
			
			foreach($this->_documents as $doc){
				//$doc->get				
				if(!isset($this->_authors[$doc->getAuthor()])){
					$this->_authors[$doc->getAuthor()] = $doc->getAuthor();
				}
				$doc->setProject($this);
			}
		}
		return $this->_documents;
	}
	public function getAuthor(){
		$this->getDocuments();
		return $this->_authors;
	}
	public function getMyAuthors(){
		return Mage::helper('textmaster')->getApi()->getMyAuthorsByProject($this->getProjectApiid());
	}
	
	public function getNbAuthor(){
		$this->getDocuments();
		return count($this->_authors);
	}
	public function getDocumentsNotSend(){		
	    if(isset($this->_documents_not_send)) return $this->_documents_not_send;
	    $this->_documents_not_send = Mage::getModel('textmaster/document')->getCollection()->setLoadApi($this->_api_loaded)
			->addFieldToFilter('textmaster_project_id',$this->getId())
			->addFieldToFilter('send',0);
		foreach($this->_documents_not_send as $doc){
			$doc->setProject($this);
		}
		
		return $this->_documents_not_send;
	}
	public function getDocumentsNotCount(){
		if(isset($this->_documents_not_count)) return $this->_documents_not_count;
		$this->_documents_not_count = Mage::getModel('textmaster/document')->getCollection()->setLoadApi($this->_api_loaded)
		->addFieldToFilter('textmaster_project_id',$this->getTextmasterProjectId())
		->addFieldToFilter('counted',0);
		foreach($this->_documents_not_count as $doc){
			$doc->setProject($this);
		}
		return $this->_documents_not_count;
	}
	public function getDocumentsSendToCompleted(){
	    if(isset($this->_documents_send_tocompleted)) return $this->_documents_send_tocompleted;
	    $this->_documents_send_tocompleted = Mage::getModel('textmaster/document')->getCollection()->setLoadApi($this->_api_loaded)
	    ->addFieldToFilter('textmaster_project_id',$this->getTextmasterProjectId())
	    ->addFieldToFilter('encourscomplete',1)
	    ->addFieldToFilter('sendforcomplete',1)
	    ;
	    foreach($this->_documents_send_tocompleted as $doc){
	        $doc->setProject($this);
	    }
	    return $this->_documents_send_tocompleted;
	}
	public function getDocumentsSendNotCompleted(){
	    if(isset($this->_documents_send_notcompleted)) return $this->_documents_send_notcompleted;
	    $this->_documents_send_notcompleted = Mage::getModel('textmaster/document')->getCollection()->setLoadApi($this->_api_loaded)
	    ->addFieldToFilter('textmaster_project_id',$this->getTextmasterProjectId())
	    ->addFieldToFilter('encourscomplete',1)
	    ->addFieldToFilter('sendforcomplete',1)
	    ->addFieldToFilter('completed',0);
	    foreach($this->_documents_send_notcompleted as $doc){
	        $doc->setProject($this);
	    }
	    
	    return $this->_documents_send_notcompleted;
	}
    public function getDocumentsCounted(){
        if(isset($this->_documents_completed)) return $this->_documents_completed;
        $this->_documents_completed = Mage::getModel('textmaster/document')->getCollection()->setLoadApi($this->_api_loaded)
            ->addFieldToFilter('textmaster_project_id',$this->getTextmasterProjectId())
            ->addFieldToFilter('counted',1);
        foreach($this->_documents_completed as $doc){
            $doc->setProject($this);
        }
        
        return $this->_documents_completed;
    }
    public function getLocalWordCount(){
        $docs = $this->getDocumentsCounted();
        $totalWordCount = 0;
        foreach ($docs as $doc) {
            $data = $doc->prepareData();
            $totalWordCount += $data['word_count'];
        }
        return $totalWordCount;
    }
    public function getDiffWordCount(){
        $externalWordCount = $this->getTotalWordCount();
        $localWordCount = $this->getLocalWordCount();
        if(($externalWordCount >= $localWordCount) or $localWordCount == 0)
            return 0;
        return $localWordCount-$externalWordCount;
    }
	public function sendDocuments(){
		if($this->hasDocumentsNotSend()){
			$nbDocumentToSend = Mage::getConfig()->getNode('adminhtml/api/documents/send/nb')->asArray();			
			$i=0;
			$dataToSend = array();
			$documents = $this->getDocumentsNotSend();
			foreach ($documents as $document){
				if($nbDocumentToSend<=$i) break;
				$dataToSend[] = $document->prepareData();
				$i++;
			}
			$error = 0;
			$_api = Mage::helper('textmaster')->getApi();				
			$result = $_api->addDocuments($this->getProjectApiid(),$dataToSend);
			$i=0;
			foreach ($documents as $k=>$document){
				if($nbDocumentToSend<=$i) break;
				if (! isset ( $result[$i]['error'] )) {
					$document->setDocumentApiId ( $result[$i] ['id'] );
					$document->setSend ( 1 );
					$document->save();
				} else {
					$error = 1;
					//$document->setStatus ( 'in_error' );
					$document->delete();
				}
				$i++;
			}			
		} 
		return $this;
	}
	public function hasDocumentsNotSend(){	
		$docs = $this->getDocumentsNotSend();	
		return count($docs)>0;
	}
	
	public function hasDocumentsNotCount(){
		// Est ce qu'un document n'a pas été compté
		$docs = $this->getDocumentsNotCount();
		return count($docs)>0;
	}

	public function getProductIds(){
		if($this->_products_id) return $this->_products_id;
		$this->_products_id = array();
		if(isset($this->_documents)){
			foreach($this->_documents as $document){
				$this->_products_id[] = $document->getProductId();
			}
		} else {
			$table = Mage::getSingleton('core/resource')->getTableName('textmaster_document');
			$results = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll("SELECT DISTINCT product_id FROM $table WHERE textmaster_project_id = ".$this->getId());
			
			foreach ($results as $result){
				$this->_products_id[] = $result['product_id'];
			}
		}
		return $this->_products_id;
	}
	
	public function getAttributes(){
		if(!isset($this->_attributes)){
			$this->_attributes = Mage::getModel('textmaster/project_attribute')->getCollection()->addFieldToFilter('textmaster_project_id',$this->getId());
			foreach($this->_attributes as $attribute){
				$attribute->setProject($this);
			}
		}
		return $this->_attributes;
	}
	public function getAttributesFull(){
		if(!isset($this->_attributes_full)){
		    
			$this->_attributes_full = Mage::getModel('textmaster/project_attribute')->getCollection()->addFieldToFilter('textmaster_project_id',$this->getId());
			$this->_attributes_full->getSelect()->joinInner(array('attribute'=>Mage::getSingleton('core/resource')->getTableName('eav_attribute')),'main_table.textmaster_attribute_id = attribute.attribute_id');
			foreach($this->_attributes_full as $attribute){
				$attribute->setProject($this);
			}
		}
		return $this->_attributes_full;
	}

	public function canLaunch() {
		if($this->getStatus()==self::PROJECT_STATUS_IN_CREATION){
			$user_info = Mage::helper('textmaster')->getApi()->getUserInfo();
			if($user_info['wallet']['current_money']>=$this->getPrice()){
				return true;
			}
			return false;
		}
		return false;
	}

	public function delete(){
		foreach ($this->getDocuments() as $doc){
			$doc->delete();
		}
		foreach($this->getAttributes()  as $attr){
			$attr->delete();
		}
		return parent::delete();
	}
	
	public function duplicate(){
		$newProject = Mage::getModel('textmaster/project');
		$data = $this->getData();
		if(isset($data['id'])){
			unset($data['id']);		
		}
		if(isset($data['textmaster_project_id'])){
			unset($data['textmaster_project_id']);		
		}
		if(isset($data['reference'])){
			unset($data['reference']);
		}
		if(isset($data['project_apiid'])){
			unset($data['project_apiid']);
		}
		$newProject->setData($data);		
		$newProject->setBeforeSaveApi(false);
		$newProject->save();
		if($newProject->getId()) {
			
			$result = Mage::helper('textmaster')->getApi()->duplicateProject($this->getProjectApiid());
			if(isset($result['id'])) {
				$newProject->setProjectApiid($result['id']);
				$newProject->save();
			}
		}
		
		
		
		//return parent::delete();
	}
	
	public function launch(){
		if($this->canLaunch()) {
			$result = Mage::helper('textmaster')->getApi()->launchProject($this->getProjectApiid());
			if(!isset($result['error'])){
			    Mage::getSingleton('adminhtml/session')->unsTextmasterUserInfos();
				$this->setStatus(self::PROJECT_STATUS_IN_LAUNCH_PROCESSING);
				$this->save();				
				Mage::getSingleton('adminhtml/session')->addSuccess($this->getName().' '.Mage::helper('textmaster')->__('launched'));
				return true;
			} else {
				Mage::getSingleton('adminhtml/session')->addError($this->getName().' '.$result['error']);
			}
		}
		return false;
	}
	
	public function canPause() {
		return $this->getStatus()==self::PROJECT_STATUS_IN_PROGRESS;
	}
	
	public function pause(){
		//if($this->canPause()) {
			$result = Mage::helper('textmaster')->getApi()->pauseProject($this->getProjectApiid());
			if(!isset($result['error'])){
				$this->setStatus(self::PROJECT_STATUS_PAUSED);
				$this->save();
				Mage::getSingleton('adminhtml/session')->addSuccess($this->getName().' '.Mage::helper('textmaster')->__('paused'));
			} else {
				Mage::getSingleton('adminhtml/session')->addError($this->getName().' '.$result['error']);
			}
		//}
	}
		
	public function canResume() {
		return $this->getStatus()==self::PROJECT_STATUS_PAUSED;
	}
	
	public function resume(){
		//if($this->canResume()) {
			$result = Mage::helper('textmaster')->getApi()->resumeProject($this->getProjectApiid());
			if(!isset($result['error'])){
				$this->setStatus(self::PROJECT_STATUS_IN_PROGRESS);
				$this->save();
				Mage::getSingleton('adminhtml/session')->addSuccess($this->getName().' '.Mage::helper('textmaster')->__('resumed'));
			} else {
				Mage::getSingleton('adminhtml/session')->addError($this->getName().' '.$result['error']);
			}
		//}
	}
	
	public function canCancel() {
		return $this->getStatus()!=self::PROJECT_STATUS_IN_REVIEW && $this->getStatus()!=self::PROJECT_STATUS_COMPLETED;
	}
	
	public function cancel(){
		//if($this->canCancel()) {
			$result = Mage::helper('textmaster')->getApi()->cancelProject($this->getProjectApiid());
			if(!isset($result['error'])){
				$this->setStatus(self::PROJECT_STATUS_CANCEL);
				$this->save();
				Mage::getSingleton('adminhtml/session')->addSuccess($this->getName().' '.Mage::helper('textmaster')->__('canceled'));
			} else {
				Mage::getSingleton('adminhtml/session')->addError($this->getName().' '.$result['error']);
			}
		//}
	}
	
	public function canComplete() {
		if(isset($this->_canComplete)) return $this->_canComplete;
		$this->_canComplete = false;
		if($this->getStatus()==self::PROJECT_STATUS_IN_REVIEW) {
			$this->_canComplete = true;
			foreach($this->getDocuments() as $document){
				if($document->getStatus()!=Textmaster_Textmaster_Model_Document::DOCUMENT_STATUS_COMPLETED){
					$this->_canComplete = false;
				}
			}
		}
		return $this->_canComplete;
	}

	public function canTransfert(){
		if($this->getStatus()==self::PROJECT_STATUS_COMPLETED && $this->getDocumentTransfert()==0) {
			
		}
	}
	public function complete(){
		if($this->canComplete()) {
			$this->setStatus(self::PROJECT_STATUS_COMPLETED);
			$this->transfert();
			$this->save();
			Mage::getSingleton('adminhtml/session')->addSuccess($this->getName().' '.Mage::helper('textmaster')->__('completed'));			
		}
		return $this;
	}
	public function transfert(){
		if($this->canTransfert()){
			foreach($this->getDocuments() as $doc){
				if($doc->canComplete()){
					$doc->complete();
				} elseif($doc->canTransfert()) {
					$doc->transfert();
				}
			}
			$this->setDocumentsTransfert(1);
			$this->setBeforeSaveApi(false);
			$this->save();
		}
		return $this;
	}
	/*
	 * NON UTILISER
	 * */
	public function getTranslation(){
		$documents = $this->getDocuments();
		$tr = array();
		foreach($documents as $document){
			$document->load($document->getId());
			$tr[$document->getProductId()] = $document->getTranslation();			
		}
		return $tr;		
	}
	public function loadByApiData($data){
	    $_subsitute = array(
	            'name'                 => 'name',
	            'reference'            => 'ref',
	            'progression'          => 'progress',
	            'level'                => 'level_name',
	            'store_id_origin'      => 'language_from_code',
	            'store_id_translation' => 'language_to_code',
	            'nb_document'          => 'cached_documents_count',
	            'total_word_count'     => 'total_word_count',
	            'price'                => 'pricing.total_cost_at_launch_time',
	            'updated_at'           => 'updated_at',
	            'status'               => 'status',
	    );
	    $_subsitute = array_flip($_subsitute);
	    $this->_getResource()->load($this, $data['id'], 'project_apiid');
	    
	    if($this->getId()==0) {
	        $this->setName($data['name']);
	        $this->setData('store_id_origin',null) ;       
	        $this->setData('store_id_translation',null);  
	           
	    }
	    
	    $this->setReference($data['reference']);
	    if($data['status']!=$this->getStatus()) {
	        $this->setStatus($data['status']);
	        //$this->save();
	    } else {
	        $this->setStatus($data['status']);
	    }
	    //Nombre de document
	    
	    $nb_document = 0;
	    if(isset($data['documents_statuses'])) {	     	       
	       foreach ($data['documents_statuses'] as $nb) {
	            $nb_document += $nb;
	       }	       
	    }
	   
	    $this->setNbDocument($nb_document);
	    $this->setTotalWordCount($data['total_word_count']);
	    if(isset($data['total_costs'][0]['amount'])){
	        $this->setPrice($data['total_costs'][0]['amount']);
	        $this->setCurrency($data['total_costs'][0]['currency']);
	    }
	    elseif(isset($data['cost_in_currency']['amount']))  {
	        $this->setPrice($data['cost_in_currency']['amount']);
	        $this->setCurrency($data['cost_in_currency']['currency']);
	    }
	    if(isset($data['progress']))
	        $this->setProgression(round((float)($data['progress']*100),0).'%');
	    else $this->setProgression('0%');

	    if(isset($data['options']['language_level']))
	       $this->setLevel($data['options']['language_level']);
	    
	    $this->setUpdatedAt($data['updated_at']['full']);
	    
	}
	
}
