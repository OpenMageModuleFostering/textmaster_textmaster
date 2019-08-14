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
 * @author      Addonline (http://www.addonline.fr)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Textmaster_Textmaster
 *
 * @category    Addonline
 * @package     Textmaster_Textmaster
 * @copyright   Copyright (c) 2014 Textmaster
 * @author      Addonline (http://www.addonline.fr)
 */
 class Textmaster_Textmaster_Block_Adminhtml_Project_Onglets_Step2 extends Mage_Adminhtml_Block_Widget_Form {
    
    private $_exclude_attribute = array(
        'ean',
        'sku',
        'custom_layout_update',
        'recurring_profile',
        'small_image',
        'image',
        'thumbnail',
        'media_gallery',
        'gallery',
        'url_path',
        'custom_design',
        'page_layout',
        'options_container',
        'country_of_manufacture',
        'msrp_enabled',
        'msrp_display_actual_price_type',
    );
        
    protected function _prepareForm() {
        $_api = Mage::helper('textmaster')->getApi();
        Mage::helper('textmaster')->getApi();
        $form = new Varien_Data_Form (array(
                                      'id' => 'edit_form',
                                      'action' => $this->getUrl('*/*/addproject', array('step' => 2)),
                                      'method' => 'post',
                                      'enctype' => 'multipart/form-data'
                                   ));
        $form->setUseContainer(true);
        $this->setForm ( $form );
        $fieldset = $form->addFieldset ( 'project_type', array (
                'legend' => Mage::helper ( 'textmaster' )->__ ( 'Project' ) 
        ));
                    
        
        $ctype = $fieldset->addField ( 'ctype', 'select', array (
                'label' => Mage::helper ( 'textmaster' )->__ ( 'Type' ),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'ctype',
                'values' => array (
                        /*'-1' => Mage::helper ( 'textmaster' )->__ ( 'Select type' ),*/
                        //Textmaster_Textmaster_Model_Project::PROJECT_CTYPE_COPYWRITING => ucfirst ( Textmaster_Textmaster_Model_Project::PROJECT_CTYPE_COPYWRITING ),
                        Textmaster_Textmaster_Model_Project::PROJECT_CTYPE_TRANSLATION => Mage::helper ( 'textmaster' )->__ (ucfirst ( Textmaster_Textmaster_Model_Project::PROJECT_CTYPE_TRANSLATION )),
                        Textmaster_Textmaster_Model_Project::PROJECT_CTYPE_PROOFREADING => Mage::helper ( 'textmaster' )->__ (ucfirst ( Textmaster_Textmaster_Model_Project::PROJECT_CTYPE_PROOFREADING )) 
                ),
                'disabled' => false,
                'readonly' => false 
        ));

        $products_id = Mage::getSingleton('core/session')->getSelectedProducts();
        $text = '';
        $products = array();
        foreach($products_id as $id){
            $products[] = Mage::getModel('catalog/product')->load($id);
        }
        
        $fieldset = $form->addFieldset ( 'project_attribute', array (
                'legend' => Mage::helper ( 'textmaster' )->__ ( 'Fields to translate' )
        ) );
        $aAttributes = Mage::getModel ( 'catalog/product' )->getAttributes ();
        $aResult = array();
        $aAfterJs = array();
        $first = true;
        $this->_name_attribute_id = false;
        $this->_description_attribute_id = false;
        $this->_short_description_attribute_id = false;
        foreach(Mage::app()->getStores() as $k => $store){
            
            $productCollection = Mage::getModel('catalog/product')->getCollection();
            $productCollection->addStoreFilter($store->getId());
            foreach ($aAttributes as $attr){
                if($attr->getAttributecode()=='name') $this->_name_attribute_id = $attr->getAttributeId();
                if($attr->getAttributecode()=='description') $this->_description_attribute_id = $attr->getAttributeId();
                if($attr->getAttributecode()=='short_description') $this->_short_description_attribute_id = $attr->getAttributeId();
                    
                if(in_array($attr->getAttributecode(),$this->_exclude_attribute)) continue;
                
                if ($attr->getBackendType () == 'varchar' || $attr->getBackendType () == 'text') {
                    $productCollection->addAttributeToSelect($attr->getAttributecode());
                }
            }
            $productCollection->getSelect()->where('product_id IN ('.implode(',',$products_id).')');
            
            
                
            foreach ( $aAttributes as $attr ) {
                
                if(in_array($attr->getAttributecode(),$this->_exclude_attribute)) continue;
                if ($attr->getBackendType () == 'varchar' || $attr->getBackendType () == 'text') {
                    $word_count = 0;
                    foreach($productCollection as $product){
                        $word_count += Mage::helper ( 'textmaster' )->countWord($product->getData($attr->getAttributecode()));
                    }
                    if($first){
                        $aResult [] = array (
                                'value' => $attr->getAttributeId(),
                                'label' => Mage::helper('catalog')->__($attr->getFrontendLabel ()) . ' - <span class="tprice">'.$word_count.'</span> '.Mage::helper('textmaster')->__('word(s)')
                        );
                    }
                    $aAfterJs[$store->getId()][$attr->getAttributeId()] = $word_count;
                }
            }
            $first = false;
        }
        $tarifs = $_api->getPricings();
        $currency = Mage::getModel('directory/currency')->load($tarifs['code']);
        $curr = Mage::app()->getLocale()->currency($currency->getCode());

        if($this->getProject() && $this->getProject()->hasDocumentsNotCount()) {
            $docs = $this->getProject()->getDocuments();
            $docsc = $this->getProject()->getDocumentsNotCount();
            $ndoc = count($docs);
            $ndocc = count($docsc);
            $nd = $ndoc-$ndocc;
            $nd = $nd / $ndoc;
            $r = round($nd,2)*100;
        } else {
            $r = 100;
        }
        $attributes = $fieldset->addField ( 'attribute', 'checkboxes', array (
                'label' => Mage::helper ( 'textmaster' )->__ ( 'Product informations' ),
                'name' => 'attribute[]',
                'values' => $aResult
        ) )->setAfterElementHtml('
        <button id="all-attributes">'.Mage::helper ( 'textmaster' )->__ ( 'Select all' ).'</button>
        <button id="notall-attributes">'.Mage::helper ( 'textmaster' )->__ ( 'Deselect all' ).'</button>
        <script>
            var attribute_word_count = '.Mage::helper('core')->jsonEncode($aAfterJs).';
            var nouveau_message_loader = "'.Mage::helper ( 'textmaster' )->__ ( 'Nouveau message variable' ).'";
            var message_loader_tm = "'.Mage::helper ( 'textmaster' )->__ ( 'message_loader_tm' ).'";
            var must_display_loader = '.($this->getProject() && ($this->getProject()->hasDocumentsNotCount() || ($this->getProject()->getTranslationMemory() && ($this->getProject()->getTranslationMemoryStatus() == Textmaster_Textmaster_Model_Project::PROJECT_TM_STATUS_IN_PROGRESS || $this->getProject()->getTranslationMemoryStatus() == Textmaster_Textmaster_Model_Project::PROJECT_TM_STATUS_COMPLETED))) ?"true":'false').';
            var pourcent_avance = '.$r.';
            var textmasterurl_count = "'.($this->getProject()?Mage::getSingleton('adminhtml/url')->getUrl('*/*/createprojectready',array('id'=>$this->getProject()->getId())):'').'";
            var currency_symbol = "'.$curr->getSymbol().'";
        </script>');
        
        
        
        $fieldset = $form->addFieldset ( 'project_languages', array (
                'legend' => Mage::helper ( 'textmaster' )->__ ( 'Languages' )
        ) );
        
        $languages = $_api->getLanguages ();
        $used_language = array();
        foreach ($languages as $language){
            $used_language[] = $language['code'];
        }
            
        $stores = Mage::getModel('core/store')->getCollection();
                
        $languagesSelect = array ();
        $correspondances = array ();
        $languagesSelect [''] = Mage::helper ( 'textmaster' )->__ ( 'Select a language' );
        foreach($stores as $store) {
            // $code = Mage::getStoreConfig('general/locale/code',$store->getId());
            // $local = explode('_',$code);
            $local = Mage::helper('textmaster')->getFormatedLangCode($store->getId());
            if(in_array($local,$used_language))
                $languagesSelect [$store->getId()] = $store->getWebsite()->getName().' - '.$store->getName();
            $correspondances[$store->getId()] = $local;
        }
        
        
        
        $fieldset->addField ( 'store_id_origin', 'select', array (
                'label' => Mage::helper ( 'textmaster' )->__ ( 'Source language' ),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'store_id_origin',
                'values' => $languagesSelect,
                'disabled' => false,
                'readonly' => false
        ) );
        
        $language_to = $fieldset->addField ( 'store_id_translation', 'select', array (
                'label' => Mage::helper ( 'textmaster' )->__ ( 'Target language' ),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'store_id_translation',
                'values' => $languagesSelect,
                'disabled' => false,
                'readonly' => false
        ) );
        
        $this->setChild ( 'form_after', $this->getLayout ()->createBlock ( 'adminhtml/widget_form_element_dependence' )->addFieldMap ( $ctype->getHtmlId (), $ctype->getName () )->addFieldMap ( $language_to->getHtmlId (), $language_to->getName () )->addFieldDependence ( $language_to->getName (), $ctype->getName (), Textmaster_Textmaster_Model_Project::PROJECT_CTYPE_TRANSLATION ) );
        
        
        
        
        $fieldset = $form->addFieldset ( 'project_options', array (
                'legend' => Mage::helper ( 'textmaster' )->__ ( 'Pricing options' ) 
        ));
        
        

        $fieldset->addField('language_level', 'select', array(
                'label'     => Mage::helper('textmaster')->__('Service level'),
                'name'      => 'language_level',
                'required'  => true,
                'values' => array(
                        // Textmaster_Textmaster_Model_Project::PROJECT_LANGUAGE_LEVEL_REGULAR    => ucfirst(Textmaster_Textmaster_Model_Project::PROJECT_LANGUAGE_LEVEL_REGULAR),
                        Textmaster_Textmaster_Model_Project::PROJECT_LANGUAGE_LEVEL_PREMIUM    => 'Standard',
                        Textmaster_Textmaster_Model_Project::PROJECT_LANGUAGE_LEVEL_ENTERPRISE => ucfirst(Textmaster_Textmaster_Model_Project::PROJECT_LANGUAGE_LEVEL_ENTERPRISE),
                ),
                // Mage::helper('textmaster')->__('Regular (%s/word): native-speaking translator for word-for-word translations of short texts.',$currency->format($tarifs['types']['translation']['regular'])).'<br/>'.
                'after_element_html' => '<br/><small>'.
                Mage::helper('textmaster')->__('Standard (%s/word): Qualified native-speaking translators - best for short and simple projects.',$currency->format($tarifs['types']['translation']['premium'])) .'<br/>'.
                Mage::helper('textmaster')->__('Enterprise (%s/word): Professional translators - best for complex projects or high volume catalogs.',$currency->format($tarifs['types']['translation']['enterprise'])) .'<br/>'.
                '</small>',
        ));
        
        
        $fieldset->addField ( 'quality', 'select', array (
                'label' => Mage::helper ( 'textmaster' )->__ ( 'Quality Control (+%s/word)', $currency->format($tarifs['types']['translation']['quality'],array(),false)),
                'name' => 'quality',
                'required' => true,
                'values' => array(
                        Mage::helper ( 'textmaster' )->__ ( 'No' ),
                        Mage::helper ( 'textmaster' )->__ ( 'Yes' )
                ),
                'after_element_html' => '<br/><small>'.Mage::helper('textmaster')->__('The translator\'s work will be proofread and corrected by TextMaster').'</small>',
        ) );
        $fieldset->addField ( 'priority', 'select', array (
                'label' => Mage::helper ( 'textmaster' )->__ ( 'Priority Order (+%s/word)', $currency->format($tarifs['types']['translation']['priority'],array(),false) ),
                'name' => 'priority',
                'required' => true,
                'values' => array(
                        Mage::helper ( 'textmaster' )->__ ( 'No' ),
                        Mage::helper ( 'textmaster' )->__ ( 'Yes' )
                ),
                'after_element_html' => '<br/><small>'.Mage::helper('textmaster')->__('Your project will be assigned a higher priority status.').'</small>',
        ) );
        // $fieldset->addField ( 'expertise', 'select', array (
        //      'label' => Mage::helper ( 'textmaster' )->__ ( 'Expertise (+%s/word)', $currency->format($tarifs['types']['translation']['expertise'],array(),false) ),
        //      'name' => 'expertise',
        //      'required' => true,
        //      'values' => array(
        //              Mage::helper ( 'textmaster' )->__ ( 'No' ),
        //              Mage::helper ( 'textmaster' )->__ ( 'Yes' )
        //      ),
        //      'after_element_html' => '<br/><small>'.Mage::helper('textmaster')->__('We provide you with an expert in the selected category.').'</small>',
                
        // ) );
        $fieldset->addField('translation_memory', 'select', array (
                'label'    => Mage::helper('textmaster')->__('Translation memory (+%s/word)', $currency->format($tarifs['types']['translation']['translation_memory'], array(), false)),
                'name'     => 'translation_memory',
                'disabled' => 'disabled',
                'required' => true,
                'values'   => array(
                    Mage::helper ( 'textmaster' )->__ ( 'No' ),
                    Mage::helper ( 'textmaster' )->__ ( 'Yes' )
                ),
                'after_element_html' => '<br/><small>'.Mage::helper('textmaster')->__('We will analyze your project to find repetitions.').'</small>',
                        
        ) );

        if (Mage::helper('textmaster')->getNegotiatedContracts()) {
            $fieldset->addField('negotiated_contract', 'select', array (
                    'label'    => Mage::helper('textmaster')->__('Negotiated contracts'),
                    'name'     => 'negotiated_contract',
                    'required' => false,
                    'values'   => Mage::helper('textmaster')->getNegotiatedContractsFormated(),
            ));
        }
        
        $fieldset = $form->addFieldset ( 'project_name', array (
                'legend' => Mage::helper ( 'textmaster' )->__ ( 'Instructions du projet' )
        ));
        
        $fieldset->addField ( 'name', 'text', array (
                'label' => Mage::helper ( 'textmaster' )->__ ( 'Name' ),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'name'        
        ));
        
        $html_briefing = '<script>
            var briefing_translation = new Array();
            var briefing_proofreading = new Array();'."\n";
        foreach ($languages as $langue) {
            $html_briefing .= 'briefing_translation[\''.$langue['code'].'\'] = \''.addslashes(Mage::getStoreConfig('textmaster/defaultvalue/briefing_message_translation_'.$langue['code'])).'\';'."\n";
            $html_briefing .= 'briefing_proofreading[\''.$langue['code'].'\'] = \''.addslashes(Mage::getStoreConfig('textmaster/defaultvalue/briefing_message_proofreading_'.$langue['code'])).'\';'."\n";
        }
        $html_briefing .= 'store_langue_correspondance = '.json_encode($correspondances).';'."\n";
        
        $html_briefing .= '</script>';
        
        $fieldset->addField ( 'project_briefing', 'textarea', array (
                'label' => Mage::helper ( 'textmaster' )->__ ( 'Briefing' ),
                'class' => 'required-entry',
                'required' => true,
                'name' => 'project_briefing'
        ))->setAfterElementHtml($html_briefing);
        
        $categories = $_api->getCategories();
        $categoriesSelect = array ();
        $categoriesSelect [-1] = Mage::helper ( 'textmaster' )->__ ( 'Select a category' );
        foreach ( $categories as $categorie ) {
            
                $categoriesSelect [$categorie ["code"]] = $categorie ["value"];
        }
        
        $fieldset->addField ( 'category', 'select', array (
                'label' => Mage::helper ( 'textmaster' )->__ ( 'Category (optional)' ),
                'name' => 'category',
                'values' => $categoriesSelect,
                'disabled' => false,
                'readonly' => false
        ) );
        
        $fieldset->addField ( 'same_author_must_do_entire_project', 'select', array (
                'label' => Mage::helper ( 'textmaster' )->__ ( 'Single Author (Assign the entire project to a single TextMaster)' ),
                'name' => 'same_author_must_do_entire_project',
                'required' => true,
                'values' => array(
                        Mage::helper ( 'textmaster' )->__ ( 'No' ),
                        Mage::helper ( 'textmaster' )->__ ( 'Yes' )
                ),
                'after_element_html' => '<br/><small>'.Mage::helper('textmaster')->__('Ensures better continuity, but longer turnaround time').'</small>',
                
        ) );                
        
        $this->setChild ( 'form_after', 
                $this->getLayout ()->createBlock('adminhtml/widget_form_element_dependence')
                    ->addFieldMap($ctype->getHtmlId(),$ctype->getName())
                    ->addFieldMap($language_to->getHtmlId(), $language_to->getName())
                    ->addFieldDependence($language_to->getName(), $ctype->getName(), Textmaster_Textmaster_Model_Project::PROJECT_CTYPE_TRANSLATION )
                );
        
        $this->setFormValues();
        
                
        return parent::_prepareForm ();
    }
    
    public function setFormValues(){
        
        if($this->getProject()){        
            $post = $this->getProject()->getData();
            $authors = $this->getProject()->getTextmasters();
            if(count($authors)) {
                $post['ismytextmaster'] = 1;
            }
            $post['attribute'] = array();
            foreach($this->getProject()->getAttributes() as $attribute){
                $post['attribute'][] = $attribute->getTextmasterAttributeId();
            }           
        } else {
            $post = Mage::getSingleton('core/session')->getProjectInfo();
            if(!$post || count($post)==0){
                $post['quality'] = 1;
                $post['language_level'] = Textmaster_Textmaster_Model_Project::PROJECT_LANGUAGE_LEVEL_PREMIUM;
                $post['attribute'] = array();
                if($this->_name_attribute_id){
                    $post['attribute'][] = $this->_name_attribute_id;
                }
                if($this->_description_attribute_id){
                    $post['attribute'][] = $this->_description_attribute_id;
                }
                if($this->_short_description_attribute_id){
                    $post['attribute'][] = $this->_short_description_attribute_id;
                }
                $post['ctype'] = Mage::getStoreConfig('textmaster/defaultvalue/default_type');
                
                
                $used_language = Mage::getStoreConfig('textmaster/defaultvalue/default_language');
                $stores = Mage::getModel('core/store')->getCollection();
                $current_locale = '';           
                foreach ( $stores as $store ) {
                    $code = Mage::getStoreConfig('general/locale/code',$store->getId());
                    $local = explode('_',$code);
                    if($local[0]==$used_language) {
                        $post['store_id_origin'] = $store->getId();
                        $current_locale = $local[0];
                    }
                }
                if($post['ctype']!='translation' && $post['ctype']!='proofreading')
                    $post['ctype']='translation';
                $post['project_briefing'] = Mage::getStoreConfig('textmaster/defaultvalue/briefing_message_'.$post['ctype'].'_'.$current_locale);
                $post['same_author_must_do_entire_project'] = Mage::getStoreConfig('textmaster/defaultvalue/single_author');
                $post['expertise'] = Mage::getStoreConfig('textmaster/defaultvalue/expertise');
                //Commerce (C019) par default
                $post['category'] = Mage::getStoreConfig('textmaster/defaultvalue/category')?Mage::getStoreConfig('textmaster/defaultvalue/category'):'C019';
                
            }
        }
        
        //$post['word_count'] = $word_count;
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