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
 class Textmaster_Textmaster_Model_Mysql4_Project_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
	private $_api_loaded = false;
	private $_all_project = false;
	private $_api = null;
	private $_filters_api = array();
	private $_filters_standard = array();
	
	private $_subsitute = array(
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
	
	public function _construct() {
		parent::_construct ();
		$this->_init ( 'textmaster/project' );
	}
	
	public function addFiltersApi($filters){
		$this->_filters_api = array_merge($this->_filters_api,$filters);
		return $this;
	}
	
	public function addFiltersStandard($filters){
		$this->_filters_standard = array_merge($this->_filters_standard,$filters);	
		return $this;
	}
	protected function _beforeLoad() {
		
	}
	public function load($printQuery = false, $logQuery = false) {
	    
	    if ($this->isLoaded()) {
	        return $this;
	    }

	    if (! $this->_api_loaded) {
	        $this->_api_loaded = true;
	        $this->_api = Mage::helper('textmaster')->getApi();
	    }
	    
	    $this->_beforeLoad();
	    
	    $this->_renderOrders()
	       ->_renderLimit();
	    
	    $this->data_api = $this->_api->getProjects(
            false,
            array('where' => $this->_filters_api),
            isset($this->_end) && isset($this->_start) ? array($this->_end, $this->_start) : array(0, 20),
            isset($this->_order) ? array($this->_order, $this->_sens) : array('updated_at','desc')
        );
	    
	    if(isset($this->data_api['projects'])){
	        $this->_totalRecords = $this->data_api['count'];
	        $result = array();
	        foreach ($this->data_api['projects'] as $_api_project){
	            $item = $this->getNewEmptyItem();
	            $item->loadByApiData($_api_project);
	            $this->addItem($item);
	        }	        
	    } 
	    
	    $this->_setIsLoaded();
	    $this->_afterLoad();
	    
	    return $this;
	}
	public function getSize()
	{	    
	    return intval($this->_totalRecords);
	}
	
	
	
	public function addItem(Varien_Object $item)
	{
	    
	    $this->_addItem($item);
	    
	    return $this;
	}
	
	protected function __afterLoad() {
		parent::_afterLoad ();
		if (! $this->_api_loaded) {
			$this->_api_loaded = true;
			$this->_api = Mage::helper('textmaster')->getApi();
			$data_api = $this->_api->getProjects (false,array('where'=>$this->_filters_api) );			
				
		}
		$locale = Mage::app()->getLocale();
		
		$resource = Mage::getSingleton('core/resource');
		$tabledocument = $resource->getTableName('textmaster_document');
		$readConnection = $resource->getConnection('core_read');
		$i=0;
		$order = $this->getSelect()->getPart('order');
		
		if(isset($order[0])){
		    if(strpos((string)$order[0],'progression')!==false){
		        if(strpos((string)$order[0],'ASC')!==false){
		            usort($this->_items,array($this,'callbackSortByProgressionAsc'));
		        } else {
		            usort($this->_items,array($this,'callbackSortByProgressionDesc'));
		        }
		    }
		    if(strpos((string)$order[0],'price')!==false){
		        if(strpos((string)$order[0],'ASC')!==false){
		            usort($this->_items,array($this,'callbackSortByPriceAsc'));
		        } else {
		            usort($this->_items,array($this,'callbackSortByPriceDesc'));
		        }
		    }
		    if(strpos((string)$order[0],'total_word_count')!==false){
		        if(strpos((string)$order[0],'ASC')!==false){
		            usort($this->_items,array($this,'callbackSortByWordAsc'));
		        } else {
		            usort($this->_items,array($this,'callbackSortByWordDesc'));
		        }
		    }
		    if(strpos((string)$order[0],'reference')!==false){
		        if(strpos((string)$order[0],'ASC')!==false){
		            usort($this->_items,array($this,'callbackSortByRefAsc'));
		        } else {
		            usort($this->_items,array($this,'callbackSortByRefDesc'));
		        }
		    }if(strpos((string)$order[0],'level')!==false){
		        if(strpos((string)$order[0],'ASC')!==false){
		            usort($this->_items,array($this,'callbackSortByLevelAsc'));
		        } else {
		            usort($this->_items,array($this,'callbackSortByLevelDesc'));
		        }
		    }
		}
		foreach ( $this->_items as $k=>&$item ) {
			$exist = false;
			if(isset($data_api['projects'])){			   
    			foreach($data_api['projects'] as $data){
    				if($data['id']==$item->getProjectApiid()){
    					$exist = true;
    					$i++;
    					$item->setReference($data['reference']);
    					if($data['status']!=$item->getStatus()) {
    						$item->setStatus($data['status']);
    						//$item->save();
    					} else {
    						$item->setStatus($data['status']);
    					}
    					$item->setTotalWordCount($data['total_word_count']);
    					if(isset($data['total_costs'][0]['amount'])){
    						$item->setPrice($data['total_costs'][0]['amount']);
    						$item->setCurrency($data['total_costs'][0]['currency']);
    					}
    					elseif(isset($data['cost_in_currency']['amount']))  {
    						$item->setPrice($data['cost_in_currency']['amount']);
    						$item->setCurrency($data['cost_in_currency']['currency']);						
    					}
    					if(isset($data['progress']))
    						$item->setProgression(round((float)$data['progress'],0).'%');
    					else $item->setProgression('0%');
    					
    					$item->setLevel($data['options']['language_level']);
    					$item->setUpdatedAt($data['updated_at']['full']);
    			
    					
    				}
    			}
			}
			
			
			foreach($this->_filters_standard as $key => $value){
			    if(is_array($value)){
			        if(isset($value['from'])){
			            if($item->getData($key)<$value['from']) $exist = false;
			        }
			        if(isset($value['to'])){
			            if($item->getData($key)>$value['to']) $exist = false;
			        }
			    } elseif($key=='progression') {
			        $value = str_replace('%','',$value);
			        if(strpos($item->getData($key),$value)===false) $exist = false;
			    } else {
			        if(strpos($item->getData($key),$value)===false) $exist = false;
			    }			    	
			}
			if(!$exist) {
			    if (is_null($this->_totalRecords)) {
			        $this->getSize();
			    }
			    $this->_totalRecords--;
			}
			if($i>($this->getCurPage()-1)*$this->_pageSize && $i<=($this->getCurPage()+0)*$this->_pageSize){
			} else {
			    $exist = false;
			}
			
			if(!$exist ){
			    $item->setIn(false);
			    if(!$this->_all_project) {			       			    
				    $this->removeItemByKey($k);
			    }				
			} else {
			    $item->setIn(true);
			}
			
			//$item->setReference ( 'TEST2' );
		}
		
		//if($this->getOrder())
		return $this;
	}
	/*public function getSize(){
		return count($this->_items);
	}*/
	
	public function callbackSortByProgressionAsc($a,$b){
		return $a->getProgression()>$b->getProgression();
	}
	
	public function callbackSortByProgressionDesc($a,$b){
		return $a->getProgression()<$b->getProgression();
	}
	
	public function callbackSortByPriceAsc($a,$b){
		return $a->getPrice()>$b->getPrice();
	}
	
	public function callbackSortByPriceDesc($a,$b){
		return $a->getPrice()<$b->getPrice();
	}
	public function callbackSortByWordAsc($a,$b){
		return $a->getTotalWordCount()>$b->getTotalWordCount();
	}
	
	public function callbackSortByWordDesc($a,$b){
		return $a->getTotalWordCount()<$b->getTotalWordCount();
	}
	public function callbackSortByRefAsc($a,$b){
		return $a->getTotalReferenceCount()>$b->getTotalReferenceCount();
	}
	
	public function callbackSortByRefDesc($a,$b){
		return $a->getTotalReferenceCount()<$b->getTotalReferenceCount();
	}
	public function callbackSortByLevelAsc($a,$b){
		return $a->getLevel()>$b->getLevel();
	}
	
	public function callbackSortByLevelDesc($a,$b){
		return $a->getLevel()<$b->getLevel();
	}
	protected function _renderLimit()
	{
	    if($this->_pageSize){
	        //$this->_select->limitPage($this->getCurPage(), $this->_pageSize);
	        $page = $this->_curPage ;	        
	        $rowCount = $this->_pageSize;	        
	        $page     = ($page > 0)     ? $page     : 1;
	        $rowCount = ($rowCount > 0) ? $rowCount : 1;
	        $this->_start  = (int) $rowCount;
	        $this->_end = $page;	        
	    }	      
	    return $this;	   
	}
	protected function _renderOrders()
	{
	    	    
	    if (!$this->_isOrdersRendered) {
	        foreach ($this->_orders as $field => $direction) {
	            //$this->_select->order(new Zend_Db_Expr($field . ' ' . $direction));
	            if(isset($this->_subsitute[$field])) {
	               $this->_order = $this->_subsitute[$field];
	               $this->_sens = strtolower($direction);
	               $this->_isOrdersRendered = true;
	               return $this;
	            }
	        }
	    }
	    return $this;
	}
	
	public function setAllProject($val){
	    $this->_all_project = $val;
	    return $this;
	}
	public function getSubstitute(){
	    return $this->_subsitute;
	}
}
