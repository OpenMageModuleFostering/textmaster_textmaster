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
 class Textmaster_Textmaster_CallbackController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	$this->loadLayout();     
  		$this->renderLayout();
    }

    public function documentcountAction()
    {
    	$params = $this->getRequest()->getParams();
    	$postData = $this->getRequest()->getPost();
    	
    	$postData = json_decode(file_get_contents('php://input'));
    	// recupere les donnée poster en JSON
    	Mage::log('Callback documentcount '.(isset($postData->id)?$postData->id:'id inconnu'),null,'textmaster.log');
    	
    	if(is_object($postData) && isset($postData->id) && strlen($postData->id)==24) {
    		$document = Mage::getModel('textmaster/document')->loadByApiId($postData->id);
    		if($document->getId()) {
    			$document->setCounted(1);
    			$document->save();
    			
    			//On renvoie un batch de 20 documents si il en reste à envoyer sur le projet
    			//quand on a "compté" tous ceux qui ont déjà été envoyé
    			$project = $document->getProject();
    			$docs_notsend = $project->getDocumentsNotSend();
    			
    			if($project->hasDocumentsNotSend())	{
    			    $docs_notcount = $project->getDocumentsNotCount();
    			    $ndocc = count($docs_notcount);
    			    $ndocs = count($docs_notsend);
    			    if($ndocc==$ndocs) {
    			        $project->sendDocuments();
    			    }
    			}

    		}   		   		
    	} 
    	$this->loadLayout();
    	$this->renderLayout();
    }

    public function documentcompleteAction()
    {
        $params = $this->getRequest()->getParams();
        $postData = $this->getRequest()->getPost();
         
        $postData = json_decode(file_get_contents('php://input'));
        // recupere les donnée poster en JSON
        Mage::log('Callback documentcomplete '.(isset($postData->id)?$postData->id:'id inconnu'),null,'textmaster.log');
         
        if(is_object($postData) && isset($postData->id) && strlen($postData->id)==24) {
            $document = Mage::getModel('textmaster/document')->loadByApiId($postData->id);
            if($document->getId()) {
                $document->complete();                              
            } else {
                //Mage::log('not loaded',null,'textmaster.log');
            }
        } else {
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    public function projecttmcompleteAction()
    {
        $params = $this->getRequest()->getParams();
        $postData = $this->getRequest()->getPost();

        $postData = json_decode(file_get_contents('php://input'));
        // recupere les données poster en JSON
        Mage::log('Callback projecttmcompleted '.(isset($postData->id) ? $postData->id : 'id inconnu'), null, 'textmaster.log');

        if(is_object($postData) && isset($postData->id)) {
            $project = Mage::getModel('textmaster/project')->getCollection()->addFieldToFilter('project_apiid', $postData->id)->getFirstItem();
            if($project->getId()) {
                $project->setTranslationMemoryStatus(Textmaster_Textmaster_Model_Project::PROJECT_TM_STATUS_COMPLETED);
                $project->setHasChangeMemoryStatus(true);
                $project->save();
            }
        }
        $this->loadLayout();
        $this->renderLayout();        
    }

    public function inprogressAction(){
    	$postData = json_decode(file_get_contents('php://input'));
    	Mage::log('CALLBACK IN PROGRESS',null,'textmaster.log');
    	Mage::log($postData,null,'textmaster.log');
    }
}