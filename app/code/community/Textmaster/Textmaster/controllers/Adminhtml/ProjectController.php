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
class Textmaster_Textmaster_Adminhtml_ProjectController extends Mage_Adminhtml_Controller_Action
{
        protected function _isAllowed()
        {
                return Mage::getSingleton('admin/session')->isAllowed('textmaster/projet');
        }

	protected function _initAction() {
		$this->loadLayout()
		->_setActiveMenu('textmaster/project');
		return $this;
	}

	public function indexAction()
	{
	    $api_key = Mage::getStoreConfig('textmaster/textmaster/api_key');;
		$api_secret = Mage::getStoreConfig('textmaster/textmaster/api_secret');
		$user = Mage::helper('textmaster')->getApi()->getUserInfo();
		if(!isset($user['email'])) {
			$this->_redirect('*/*/login');
		} else {
			$this->_initAction()->renderLayout();
		}
	}
	public function loginAction()
	{
		$this->_initAction()->renderLayout();
	}
	
	
	public function editAction()
	{
	    /*$projectId = $this->getRequest()->getParam('id');
		$projectModel = Mage::getModel('textmaster/project')->load($projectId);*/
		
		if (/*$projectModel->getId() || $projectId == 0*/1)
		{
			$step = $this->getRequest()->getParam("step");
			
			//Mage::register('project_data', $projectModel);
			//$this->loadLayout();
			$this->_initAction();

			
			$ongletBlock = $this->getLayout()->createBlock('textmaster/adminhtml_project_onglets')->assign('step', $step);
			$ongletBlock->setTemplate('textmaster/onglet.phtml');
			
			$step1Block = $this->getLayout()->createBlock('textmaster/adminhtml_project_onglets_step1');
			$step2Block = $this->getLayout()->createBlock('textmaster/adminhtml_project_onglets_step2');
			$step3Block = $this->getLayout()->createBlock('textmaster/adminhtml_project_onglets_step3');
			
			$this->_addContent($ongletBlock);
			switch ($step){
				default:
				case 1:
					$project_id = Mage::getSingleton('core/session')->getProjectId();
					$project = Mage::getModel('textmaster/project');
					if($project_id && is_numeric($project_id)){
						$project->load($project_id);
						$docs = $project->getDocuments();
						$pids = array();
						foreach($docs as $doc){
							$pids[] = $doc->getProductId();
						}
						
						$this->getRequest()->setParam('internal_products_id',implode(',',$pids));
						
					}
					
					$ongletBlock->setIntroHtml(Mage::helper('textmaster')->__('Select the list of products to translate or proofread. Use the filter in the last column to select the product descriptions that haven\'t been translated yet. If the product description has already been translated on TextMaster, a flag will appear beside each completed translation.'));
					
					
					$this->_addContent($step1Block);
					break;
				case 2:
					$project_id = Mage::getSingleton('core/session')->getProjectId();
					$project = Mage::getModel('textmaster/project');
					if($project_id && is_numeric($project_id)){
						$project->load($project_id);
						$step2Block->setProject($project);
					}
					
					$summary = $this->getLayout()->createBlock('textmaster/adminhtml_project_onglets_summary');
					$ongletBlock->setChild('textmaster.step2',$step2Block);
					$ongletBlock->setChild('textmaster.summary',$summary);
					//$this->_addContent($step2Block);
					$ongletBlock->setIntroHtml(Mage::helper('textmaster')->__('Fill out the project details and choose the service level and extra options. Give translator or proofreader special instructions (specific terms, layout guidelines, etc.)'));
						
										
					break;
				case 3:
				    $project_id = Mage::getSingleton('core/session')->getProjectId();
				    $post = Mage::app()->getRequest()->getPost();
				    if($post && count($post)){				       
				        $project = Mage::getModel('textmaster/project')->load($project_id, null, false);
				        $r = $project->launch();
				        if(!isset($r['error'])) {
				            $this->_redirect('*/*/');
				        } else {
				            Mage::getSingleton('adminhtml/session')->addError($r['error']);
				            $this->_redirect('*/*/edit', array("step" => 3));
				        }
				        return ;
				    }
				    Mage::getSingleton('adminhtml/session')->unsTextmasterUserInfos();
				    
					
					$project = Mage::getModel('textmaster/project')->load($project_id, null, true);
					if($project->hasDocumentsNotCount() || ($project->getTranslationMemory() && $project->getTranslationMemoryStatus() == Textmaster_Textmaster_Model_Project::PROJECT_TM_STATUS_IN_PROGRESS)){
						$this->_redirect('*/*/edit', array("step" => 2));
					}
					$ongletBlock->setIntroHtml(Mage::helper('textmaster')->__('Verify your project setting before placing your order. If you do not have enough credits on TextMaster, click on the link below to add credits to your account on TextMaster.com'));
					$products_id = Mage::getSingleton('core/session')->getSelectedProducts();
					if(empty($products_id)){
						$products_id = array();
						$documents = $project->getDocuments();
						foreach($documents as $document){
							$products_id[] = $document->getProductId();
						}
						Mage::getSingleton('core/session')->setSelectedProducts($products_id);
					}
					
					if($project->canLaunch()){
						$ongletBlock->addButton ( 'save', array (
								'label' => Mage::helper ( 'textmaster' )->__ ( 'Launch' ),
								'onclick' => 'editForm.submit();',
								'class' => 'save',								
						), - 100 );
					} else {
						$ongletBlock->addButton ( 'save', array (
								'label' => Mage::helper ( 'textmaster' )->__ ( 'Launch' ),
								'onclick' => 'editForm.submit();',
								'class' => 'save',
								'disabled'=>'disabled'
						
						), - 100 );
					}
					
					$step3Block->setProject($project);
					
					$ongletBlock->insert($step3Block);
										
					$this->_addContent($step3Block);
					
					break;
			}
			
			$this->renderLayout();
		}
		else
		{
			Mage::getSingleton('adminhtml/session')
			->addError(Mage::helper('textmaster')->__('Project does not exist'));
			$this->_redirect('*/*/');
		}
	}
	
	public function newAction()
    {
    	Mage::getSingleton('core/session')->setSelectedProducts(array());
    	Mage::getSingleton('core/session')->setProjectId(0);
    	Mage::getSingleton('core/session')->setProjectInfo(array());
		$this->_redirect('*/*/edit');
	}
	
	public function massAddAction()
	{
		$products = $this->getRequest()->getParam('products_id');
		if(!is_array($products) || !count($products) || empty($products[0])) {
		    $this->_redirect('*/*/edit', array("step" => 1));
		    return;
		}
		Mage::getSingleton('core/session')->setSelectedProducts($products);
		
		
		$this->_redirect('*/*/edit', array("step" => 2));
	}
	public function createprojectfromproductAction(){
		Mage::getSingleton('core/session')->setSelectedProducts(array());
		Mage::getSingleton('core/session')->setProjectId(0);
		Mage::getSingleton('core/session')->setProjectInfo(array());
		
		$product = $this->getRequest()->getParam('id');
		Mage::getSingleton('core/session')->setSelectedProducts(array($product));
		$this->_redirect('*/*/edit', array("step" => 2));
	}
	
	public function massSendAction()
	{
		$docs = $this->getRequest()->getParam('document_id');
		
		//$projectId = $this->getRequest()->getParam('id');
		//Mage::getSingleton('core/session')->setSelectedProducts($products);
		foreach($docs as $doc){
			
			Mage::getModel('textmaster/document')->load($doc)->send();
			$projectId = Mage::getModel('textmaster/document')->load($doc)->getTextmasterProjectId();
		}
	
		$this->_redirect('*/*/view', array("id" => $projectId));
	}
	
	public function viewAction() {
	    $projectId = $this->getRequest()->getParam('id');
		$projectModel = Mage::getModel('textmaster/project')->load($projectId);
		if ($projectModel->getId())	{
			if($projectModel->getStatus() == Textmaster_Textmaster_Model_Project::PROJECT_STATUS_IN_CREATION){
				Mage::getSingleton('core/session')->setProjectId($projectModel->getId());
				if(count($projectModel->getDocuments()))
					$this->_redirect('*/*/edit',array('step'=>'3'));
				elseif($projectModel->hasDocumentsNotCount())
					$this->_redirect('*/*/edit',array('step'=>'2'));
		 		else
					$this->_redirect('*/*/edit',array('step'=>'1'));
				return;
			}
			
			//ACTIONS
			$action = $this->getRequest()->getParam('a');
			if($action && is_callable(array($projectModel,$action))){
				call_user_func(array($projectModel,$action));
			}
			
			$this->_initAction();
			$viewBlock =  $this->getLayout()->getBlock('project.view');
			$viewBlock->setProject($projectModel);
			$viewBlock->prepareButton();
			$documentsBlock =  $this->getLayout()->getBlock('project.view.documents');
			$documentsBlock->setProject($projectModel);
			
			$this->_addContent($documentsBlock);
			
			
			
			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')
			->addError(Mage::helper('textmaster')->__('Project does not exist'));
			$this->_redirect('*/*/');
		}
	}
	public function masscompletedocAction() {
	    
	    $projectId = $this->getRequest()->getParam('id');
		$project = Mage::getModel('textmaster/project')->load($projectId,null,false);
		$docs = $this->getRequest()->getParam('document_id');
		$doc_api_ids = array();
		foreach($docs as $doc_id){
			$document = Mage::getModel('textmaster/document')->load($doc_id,null,false);			
			$document->prepareToComplete();
			$doc_api_ids[]=$document->getDocumentApiId();
		}
		
		Mage::helper('textmaster')->getApi()->completeDocuments($project->getProjectApiid(),$doc_api_ids);
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody( Mage::helper('core')->jsonEncode(array('url'=>$this->getUrl('*/*/doccompleteready',array('id'=>$projectId)))));
		//$this->_redirect('*/*/view',array('id'=>$projectId));
	}
	public function doccompleteAction() {
		$documentId = $this->getRequest()->getParam('id');
		$document = Mage::getModel('textmaster/document')->load($documentId,null,false);
		if ($document->getId()){
			$document->sendToComplete();
			$json = Mage::helper('core')->jsonEncode(
			        array('counturl' => $this->getUrl('*/*/doccompleteready',array('id'=>$document->getTextmasterProjectId())))
			);
		} else {
		    $json = Mage::helper('core')->jsonEncode(
		            array()
		    );
		}
		
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$this->getResponse()->setBody($json);
		//$this->_redirect('*/*/document/',array('id'=>$documentId));
	}
	public function doccompletereadyAction() {
	    $timetosleed = 2;		
		sleep($timetosleed);
		
		
		$projectId = $this->getRequest()->getParam('id');
		$project = Mage::getModel('textmaster/project');
		$project->load($projectId,null,false);
		$docs_all = $project->getDocumentsSendToCompleted();
		$docs_notcomp = $project->getDocumentsSendNotCompleted();
		$ndocc = count($docs_notcomp);
		
		
		if($ndocc!=0)	{
		    $ndoc = count($docs_all);
		    
		    			
			$nd = ($ndoc-$ndocc) / $ndoc;
			$r = round($nd,2)*100;
			echo $r;
			exit;
		}
		echo $this->getUrl('*/*/view',array('id'=>$projectId));
		exit;
	}
	public function revisionAction() {
		$documentId = $this->getRequest()->getParam('id');
		$message = $this->getRequest()->getParam('message');
		$document = Mage::getModel('textmaster/document')->load($documentId,null,false);
		if ($document->getId()){
			$documents = $document->revision($message);
		}
		
		$this->_redirect('*/*/document/',array('id'=>$documentId));
	}
	public function documentAction() {
		$docId = $this->getRequest()->getParam('id');					
		$document = Mage::getModel('textmaster/document')->load($docId,null,true,false);
		
		if ($document->getId()){
			$this->_initAction();
			$documentBlock = $this->getLayout()->getBlock('project.document.view');	
			$documentBlock->setDocument($document);
			$supporttBlock = $this->getLayout()->getBlock('project.document.supportmessage');
			$supporttBlock->setDocument($document);
			$this->renderLayout();
		} else {
		    $this->_redirect('*/*/');
		}
	}
	
	public function massLaunchAction() {
		$projects = $this->getRequest()->getParam('project_id');	
		foreach($projects as $project_id){				
			Mage::getModel('textmaster/project')->load($project_id,null,false)->launch();		
		}
		$this->_redirect('*/*/index');
	}
	public function massPauseAction() {
		$projects = $this->getRequest()->getParam('project_id');
		foreach($projects as $project_id){
			Mage::getModel('textmaster/project')->load($project_id)->pause();
		}
		$this->_redirect('*/*/index');
	}
	public function massResumeAction() {
		$projects = $this->getRequest()->getParam('project_id');
		foreach($projects as $project_id){
			Mage::getModel('textmaster/project')->load($project_id)->resume();
		}
		$this->_redirect('*/*/index');
	}
	public function massCancelAction() {
		$projects = $this->getRequest()->getParam('project_id');
		foreach($projects as $project_id){
			Mage::getModel('textmaster/project')->load($project_id)->cancel();
		}
		$this->_redirect('*/*/index');
	}
	public function massCompleteAction() {
		$projects = $this->getRequest()->getParam('project_id');
		foreach($projects as $project_id){
			Mage::getModel('textmaster/project')->load($project_id)->complete();
		}
		$this->_redirect('*/*/index');
	}
	public function massDuplicateAction(){	
		$projects = $this->getRequest()->getParam('project_id');
		foreach($projects as $project_id){
			Mage::getModel('textmaster/project')->load($project_id)->duplicate();
		}
		$this->_redirect('*/*/index');
	}
	
	
	public function createprojectreadyAction(){
		
		//echo $project->hasDocumentsNotSend()?'0':$this->getUrl('*/*/edit',array('step'=>3));
		$timetosleed = 2;		
		sleep($timetosleed);
		
		$projectId = $this->getRequest()->getParam('id');
		$project = Mage::getModel('textmaster/project');
		$project->load($projectId, null, true);
		$docs_all = $project->getDocuments();
		$docs_notcount = $project->getDocumentsNotCount();
		
		if($project->hasDocumentsNotCount())	{
		    $ndoc = count($docs_all);
		    $ndocc = count($docs_notcount);

		    /*
		     * Ceinture de sécurité : au cas où l'appel à $project->sendDocuments()
		     * n'ai pas été déjà fait dans le callBack, on le relance ici 
		     * */
		    $docs_notsend = $project->getDocumentsNotSend();
		    $ndocs = count($docs_notsend);
		    if($ndocc==$ndocs)
		        $project->sendDocuments();
		    
			
			$nd = $ndoc-$ndocc;
			$nd = $nd / $ndoc;
			$r = round($nd,2)*100;
			echo $r;
			exit;
		}

        $project->startTranslationMemory(); // start TM if needed
        if($project->getTranslationMemoryStatus() == Textmaster_Textmaster_Model_Project::PROJECT_TM_STATUS_IN_PROGRESS){
            echo Textmaster_Textmaster_Model_Project::PROJECT_TM_STATUS_IN_PROGRESS;
            exit;
        }

		//echo '100';
		echo $this->getUrl('*/*/edit',array('step'=>3));
		exit;
	}
	public function addprojectAction(){
		$project_id = Mage::getSingleton('core/session')->getProjectId();
		$project = Mage::getModel('textmaster/project');
		if($project_id && is_numeric($project_id)){
			$project->load($project_id);
		}
		
		$post = Mage::app()->getRequest()->getPost();
		
		
		$this->getResponse()->setHeader('Content-type', 'application/json');
		if($post && count($post)){
			Mage::getSingleton('core/session')->setProjectInfo($post);
			$products_id = Mage::getSingleton('core/session')->getSelectedProducts();
			$text = '';
			foreach($products_id as $id){
				$product = Mage::getModel('catalog/product')->load($id);
				if(isset($post['attribute'])) {
					foreach($post['attribute'] as $attr){
						$attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attr);
						$text .= $product->getData($attribute->getName ()).' ';
					}
				}
			}
			$word_count = Mage::helper('textmaster')->countWord($text);
			//$pricing = Mage::helper('textmaster')->getApi()->getPricings($word_count);
			//$project_id = Mage::getSingleton('core/session')->getProjectId();
     
			$update = false;
			if($project_id && is_numeric($project_id)){
				$project->load($project_id);
				$project->setName($post['name']);
				$project->setProjectBriefing($post['project_briefing']);
				$project->setCtype($post['ctype']);
				$project->setCategory($post['category']);
				$project->setStoreIdOrigin($post['store_id_origin']);
					
				if($post['ctype']=='translation')
					$project->setStoreIdTranslation($post['store_id_translation']);
				else
					$project->setStoreIdTranslation($post['store_id_origin']);
				
				$project->setLanguageLevel($post['language_level']);
				if(isset($post['specific_attachment']))
					$project->setSpecificAttachment($post['specific_attachment']);
				$project->setPriority($post['priority']);
				if(!isset($post['quality'])) $post['quality'] = 0;
				$project->setQuality($post['quality']);
				$project->setExpertise($post['expertise']);
				$project->setSameAuthorMustDoEntireProject($post['same_author_must_do_entire_project']);
				if(isset($post['ismytextmaster']))
					$project->setIsmytextmaster($post['ismytextmaster']);
				if(isset($post['mytextmaster'])) {
					$project->setTextmasters($post['mytextmaster']);
				}
                $project->setTranslationMemory($post['translation_memory']);
				
				$update = true;
					
				$documents = $project->getDocuments();
				$products_id = Mage::getSingleton('core/session')->getSelectedProducts();
				foreach($documents as $document){
					$document->delete();
				}
				$project_attributes = $project->getAttributes();
				foreach($project_attributes as $project_attribute){
					$project_attribute->delete();
				}
					
			} else {
				if($post['ctype']!='translation')
					$post['store_id_translation'] = $post['store_id_origin'];		
				$project->setData($post);					
			}
			
			try {
				$project->save();
				if($project->getId()){
					Mage::getSingleton('core/session')->setProjectId($project->getId());
					if(is_array($post['attribute'])) {
						foreach($post['attribute'] as $attr){
							$project_attribute = Mage::getModel('textmaster/project_attribute');
							$project_attribute->setTextmasterAttributeId($attr);
							$project_attribute->setTextmasterProjectId($project->getId());
							$project_attribute->save();
						}
					}
					$products_id = Mage::getSingleton('core/session')->getSelectedProducts();
					
		
					$documents = array();
					$nbsend = 0;
					foreach($products_id as $id){
						$product = Mage::getModel('catalog/product')->setStoreId( $project->getStoreIdOrigin() )->load($id);
						$document = Mage::getModel('textmaster/document');
						$document->setProject($project);
						$document->setName($product->getName());
						$document->setProductId($id);
						$document->setTextmasterProjectId($project->getId());
						$document->setSend(0);
						$document->save();						
						$documents[]=$document;
						$nbsend++;
					}
						
					$project->sendDocuments();
														
					$json = Mage::helper('core')->jsonEncode(
							array(
									'url'=>$this->getUrl('*/*/edit',array('step'=>3)),
									'counturl' => $this->getUrl('*/*/createprojectready',array('id'=>$project->getId()))
									
							)
					);
					$this->getResponse()->setBody($json);
				} else {
					Mage::getSingleton('core/session')->addError(Mage::helper('textmaster')->__('Project creation error'));
					$json = Mage::helper('core')->jsonEncode(array('url'=>$this->getUrl('*/*/edit',array('step'=>2))));
					$this->getResponse()->setBody($json);
				}
			} catch (Exception $e){
				Mage::getSingleton('core/session')->addError($e->getMessage());
				$json = Mage::helper('core')->jsonEncode(array('url'=>$this->getUrl('*/*/edit',array('step'=>2,'reload'=>1))));
				$this->getResponse()->setBody($json);
			}
					
			return;
		}
		$json = Mage::helper('core')->jsonEncode(array('erreur'=>$e->getMessage()));
		//throw new Exception('Project creation error');
		$this->getResponse()->setBody('NO POST');
	}
	
	
	
	public function authorAction (){
		$this->getResponse()->setHeader('Content-type', 'application/json');
		$post = Mage::app()->getRequest()->getPost();
		if($post && count($post)==10){
			$post['options'] = array(
					'language_level' 		=> $post['language_level'],
					//'quality' 				=> $post['quality'],
					//'priority' 				=> $post['priority'],
					//'expertise' 			=> $post['expertise'],
					//'specific_attachment' 	=> $post['specific_attachment'],				
			);
			//$post['total_word_count'] = $post['word_count'];
			unset($post['language_level']);
			unset($post['quality']);
			unset($post['priority']);
			unset($post['expertise']);
			unset($post['specific_attachment']);
			unset($post['form_key']);
			unset($post['word_count']);
			
			$post['language_from'] 	= substr(Mage::getStoreConfig('general/locale/code',$post['language_from']),0,2);
			
			if($post['ctype']!='translation') {
				unset($post['language_to']);
			} else {
				$post['language_to'] 	= substr(Mage::getStoreConfig('general/locale/code',$post['language_to']),0,2);
			}
			try{
				$authors = Mage::helper('textmaster')->getApi()->getAuthorsFilter($post);
				if(isset($authors['error'])){
					throw new Exception($authors['error']);
				}
				$result['authors'] = $authors['authors'];
								
			} catch (Exception $e){
				$result['error'] = $e->getMessage();
			}
			
			
		} else {
			$result['error'] = Mage::helper('textmaster')->__('No post');
		}
		$json = Mage::helper('core')->jsonEncode($result);
		$this->getResponse()->setBody($json);
	}
	

	public function getmyauthorsAction(){

		$projectId = $this->getRequest()->getParam('id');
		$projectModel = Mage::getModel('textmaster/project')->load($projectId);
		if ($projectModel->getId())	{		
			$this->loadLayout();
			$d = $projectModel->getData();
			$post = Mage::app()->getRequest()->getPost();
			if(count($post) && !isset($post['textmasters'])) $post['textmasters']=array();
			try{
				if($post && count($post) && isset($post['textmasters'])){
					if(is_array($post['textmasters'])){
						$projectModel->saveTextmasters($post['textmasters']);						
					} elseif(is_string($post['textmasters'])){
						$projectModel->saveTextmasters(array($post['textmasters']));
					}
					
					Mage::getSingleton('adminhtml/session')
					->addSuccess(Mage::helper('textmaster')->__('Textmasters saved'));
				}
			} catch (Exception $e){
				Mage::log(__LINE__.' : '.$e->getMessage(), null, 'textmaster.log');
			}
			$viewBlock =  $this->getLayout()->createBlock('textmaster/adminhtml_project_onglets_step3_form_author');
			$viewBlock->setProject($projectModel);
			$this->getLayout()->getBlock('step3_author')->setChild('form',$viewBlock);;			
			$this->renderLayout();
			return ;
		}
		exit;
	}
	//ster/adminhtml_project/getmyauthors/id/53/key/bed5288e3b1f1d075710db7ab5b6a207/
}
