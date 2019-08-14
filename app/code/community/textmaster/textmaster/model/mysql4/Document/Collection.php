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
 class Textmaster_Textmaster_Model_Mysql4_document_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
	
	private $_load_api = false;
	private $_project_api_id = false;
	private $_filters_api = array();
	private $_filters_standard = array();
	private $_subsitute = array(
	        'name'                 => 'title',
	        'reference'            => 'ref',
	        'status'               => 'status',
	);
	
	public function addFiltersApi($filters){
		$this->_filters_api = array_merge($this->_filters_api,$filters);
	
	}
	
	public function addFiltersStandard($filters){
		$this->_filters_standard = array_merge($this->_filters_standard,$filters);
	}
	
	public function _construct() {
		parent::_construct ();
		$this->_init ( 'textmaster/document' );
	}
	/*protected function _beforeLoad() {
		
	}*/
	public function load($printQuery = false, $logQuery = false)
	{
	    if(!$this->_load_api){
	        return parent::load($printQuery,$logQuery);	        
	    }
	    if(!$this->_project_api_id){
	        return parent::load($printQuery,$logQuery);
	    }
	    if ($this->isLoaded()) {
	        return $this;
	    }
	    
	    
	    $this->_renderOrders()
	    ->_renderLimit();
	    
	    $this->_beforeLoad();
	    
	    $_api = Mage::helper('textmaster')->getApi();
	    $this->documents_api_result = $_api->getDocuments($this->_project_api_id,array('where'=>$this->_filters_api),array($this->_end ,$this->_start),isset($this->_order)?array($this->_order,$this->_sens):array('ref','asc'));
	    	
	    $this->printLogQuery($printQuery, $logQuery);
	    $data = $this->getData();
	    $this->resetData();
	    $this->_totalRecords = $this->documents_api_result['count'];
	    if (is_array($data) && isset($this->documents_api_result['documents'])) {
	        foreach($this->documents_api_result['documents'] as $doc){
    	        foreach ($data as $row) {
    	            if($doc['id']==$row['document_api_id']){
        	            $item = $this->getNewEmptyItem();
        	            if ($this->getIdFieldName()) {
        	                $item->setIdFieldName($this->getIdFieldName());
        	            }
        	            $item->addData($row);
        	            $item->setAuthor($doc['author_id']);
        	            $item->setName($doc['title']);
        	            $item->setType($doc['ctype']);
        	            $item->setStatus($doc['status']);
        	            $item->setReference($doc['reference']);
        	            $this->addItem($item);
    	            }
    	        }
	        }
	    }
	
	    $this->_setIsLoaded();
	    $this->_afterLoad();
	    return $this;
	}
	
	protected function __afterLoad() {
		if($this->_load_api && $this->documents_api_result){
		    $i=0;
			foreach ( $this->_items as $k=>$item ) {
				$exist = false;
				foreach($this->documents_api_result['documents'] as $doc){
					if($doc['id']==$item->getDocumentApiId()){
						$item->setAuthor($doc['author_id']);
						$item->setType($doc['ctype']);
						$item->setStatus($doc['status']);
						$item->setReference($doc['reference']);
						$i++;
						$exist = true;
						break;
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
				if(!$exist) {				    
				    unset($this->_items[$k]);
				}
			}
			//Mage ::log('count : '. count($this->_items));			
		}
	}
	/*public function getSize(){
		return count($this->_items);
	}*/
	
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
	
	public function setLoadApi($value){
		$this->_load_api = $value;
		return $this;
	}
	public function setProjectApiId($value){
		$this->_project_api_id = $value;
		return $this;
	}
	public function getSubstitute(){
	    return $this->_subsitute;
	}	
}
