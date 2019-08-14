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
 class Textmaster_Textmaster_Block_Adminhtml_Project_Grid extends Mage_Adminhtml_Block_Widget_Grid {
	
	private $_is_regex_op = array(
	        'name',
	        'ref'
	);
    
    public function __construct() {
		parent::__construct ();
		$this->setId ( 'projetGrid' );
		$this->setDefaultSort ( 'textmaster_project_id' );
		$this->setDefaultDir ( 'DESC' );
		//$this->setDefaultLimit(3);
		$this->setSaveParametersInSession ( true );
	}
	
	protected function _prepareCollection() {
		$collection = Mage::getModel ( 'textmaster/project' )->getCollection()->setAllProject(true);		
		$this->setCollection ( $collection );
				
		return  parent::_prepareCollection ();
		
	}
		
	
	protected function _prepareColumns() {
		$this->addColumn ( 'name', array (
				'header' => Mage::helper ( 'textmaster' )->__ ( 'Name' ),
				'align' => 'left',
		        'width' => '150px',
				'index' => 'name',
		        'filter_condition_callback' => array($this, '_apiFilter'),
		));
		
		$this->addColumn ( 'reference', array (
				'header' => Mage::helper ( 'textmaster' )->__ ( 'Reference' ),
				'align' => 'right',
				'width' => '50px',
				'index' => 'reference',
				'filter_condition_callback' => array($this, '_apiFilter'),
		));
		
		$this->addColumn ( 'store_id_origin', array (
				'header' => Mage::helper ( 'textmaster' )->__ ( 'Source language' ),
				'align' 	=> 'right',
				'width' 	=> '150px',
				'index' 	=> 'store_id_origin',
				'type'  	=> 'options',
				'renderer' 	=> 'Textmaster_Textmaster_Block_Adminhtml_Renderer_Sourcelanguage',
		        'filter_condition_callback' => array($this, '_apiFilter'),
				'options' 	=> Mage::getModel('core/store')->getCollection()->toOptionHash()
		));
		
		$this->addColumn ( 'store_id_translation', array (
				'header' => Mage::helper ( 'textmaster' )->__ ( 'Target language' ),
				'align' => 'right',
				'width' => '100px',
				'index' => 'store_id_translation',
				'renderer' 	=> 'Textmaster_Textmaster_Block_Adminhtml_Renderer_Targetlanguage',
				'type'  => 'options',
				'options' => Mage::getModel('core/store')->getCollection()->toOptionHash(),
		        'filter_condition_callback' => array($this, '_apiFilter')
				//'options' => Mage::getModel('textmaster/source_api_lang')->toOptions() 
		));
		
		$this->addColumn ( 'level', array (
				'header' => Mage::helper ( 'textmaster' )->__ ( 'Level' ),
				'align' => 'right',
				'width' => '50px',
				'index' => 'level' ,
				'type'  => 'options',
				'options'=> array (
						Textmaster_Textmaster_Model_Project::PROJECT_LANGUAGE_LEVEL_REGULAR => ucfirst(Textmaster_Textmaster_Model_Project::PROJECT_LANGUAGE_LEVEL_REGULAR),
						Textmaster_Textmaster_Model_Project::PROJECT_LANGUAGE_LEVEL_PREMIUM => ucfirst(Textmaster_Textmaster_Model_Project::PROJECT_LANGUAGE_LEVEL_PREMIUM),
                        Textmaster_Textmaster_Model_Project::PROJECT_LANGUAGE_LEVEL_ENTERPRISE => ucfirst(Textmaster_Textmaster_Model_Project::PROJECT_LANGUAGE_LEVEL_ENTERPRISE),
				),
				'filter_condition_callback' => array($this, '_apiFilter'),
		));
		
		$this->addColumn ( 'nb_document', array (
				'header' => Mage::helper ( 'textmaster' )->__ ( 'Number of documents' ),
				'align' => 'right',
				'width' => '20px',
				'type'  => 'number',
				'index' => 'nb_document',
		        'renderer' => 'Textmaster_Textmaster_Block_Adminhtml_Project_Renderer_Nbdocument' ,
				'filter_condition_callback' => array($this, '_apiFilter'),
		));
		
		$this->addColumn ( 'total_word_count', array (
				'header' => Mage::helper ( 'textmaster' )->__ ( 'Total word count' ),
				'align' => 'right',
				'width' => '40px',
				'index' => 'total_word_count' ,
		        'renderer' => 'Textmaster_Textmaster_Block_Adminhtml_Project_Renderer_Wordcount',
				'type'  => 'number',
				'filter_condition_callback' => array($this, '_apiFilter'),
		));
		
		$this->addColumn ( 'price', array (
				'header' => Mage::helper ( 'textmaster' )->__ ( 'Price' ),
				'align' => 'right',
				'width' => '50px',
				'type'  => 'currency',
				'index' => 'price',
				'renderer' => 'Textmaster_Textmaster_Block_Adminhtml_Project_Renderer_Price' ,
				'filter_condition_callback' => array($this, '_apiFilter'),
		));
		
		$this->addColumn ( 'progression', array (
				'header' => Mage::helper ( 'textmaster' )->__ ( 'Progression' ),
				'align' => 'right',
				'width' => '50px',
				'type'  => 'number',
				'index' => 'progression',
		        'renderer' => 'Textmaster_Textmaster_Block_Adminhtml_Project_Renderer_Progression' ,
				'filter_condition_callback' => array($this, '_apiFilter'),
		));
		
		$this->addColumn ( 'status', array (
				'header' => Mage::helper ( 'textmaster' )->__ ( 'Status' ),
				'align' => 'right',
				'width' => '50px',
				'index' => 'status',
				'renderer' => 'Textmaster_Textmaster_Block_Adminhtml_Project_Renderer_Status' ,
				'type'  => 'options',
				'options' => Mage::getSingleton('textmaster/project')->getStatuses(),
				'filter_condition_callback' => array($this, '_apiFilter'),
		));
		
		$this->addColumn ( 'updated_at', array (
				'header' => Mage::helper ( 'textmaster' )->__ ( 'Last change' ),
				'align' => 'right',
				'width' => '50px',
				'index' => 'updated_at',
				'type'  => 'datetime', 
				'renderer' => 'Textmaster_Textmaster_Block_Adminhtml_Renderer_UpdatedAt' ,
				'filter_condition_callback' => array($this, '_apiFilter'),
		));
		
		$this->addColumn ( 'actions', array (
				'header' 	=> Mage::helper ( 'textmaster' )->__ ( 'Actions' ),
				'align' 	=> 'right',
				'width'     => '50',
				'type'      => 'action',
				'getter'     => 'getId',
		        'filter'    => false,
		        'sortable'  => false,
		        'actions'   => array(
					array(
						'caption'   => Mage::helper('textmaster')->__('View'),
						'url'       => array('base'=> '*/*/view'),
						'field'     => 'id'
					)
				),
				'index' => 'actions' 
		));
		
		return parent::_prepareColumns ();
	}
	protected function _prepareMassaction() {
		$this->setMassactionIdField ( 'textmaster_project_id' );
		$this->getMassactionBlock ()->setFormFieldName ( 'project_id' );
		
		$this->getMassactionBlock ()->addItem ( 'launch', array (
				'label' => Mage::helper ( 'textmaster' )->__ ( 'Launch' ),
				'url' => $this->getUrl ( '*/*/massLaunch' ) 
		));
		$this->getMassactionBlock ()->addItem ( 'pause', array (
				'label' => Mage::helper ( 'textmaster' )->__ ( 'Pause' ),
				'url' => $this->getUrl ( '*/*/massPause' )
		));
		$this->getMassactionBlock ()->addItem ( 'resume', array (
				'label' => Mage::helper ( 'textmaster' )->__ ( 'Resume' ),
				'url' => $this->getUrl ( '*/*/massResume' )
		));
		$this->getMassactionBlock ()->addItem ( 'cancel', array (
				'label' => Mage::helper ( 'textmaster' )->__ ( 'Cancel' ),
				'url' => $this->getUrl ( '*/*/massCancel' )
		));
		$this->getMassactionBlock ()->addItem ( 'complete', array (
				'label' => Mage::helper ( 'textmaster' )->__ ( 'Complete' ),
				'url' => $this->getUrl ( '*/*/massComplete' )
		));
		$this->getMassactionBlock ()->addItem ( 'duplicate', array (
				'label' => Mage::helper ( 'textmaster' )->__ ( 'Duplicate' ),
				'url' => $this->getUrl ( '*/*/massDuplicate' )
		));
		
		return $this;
	}
	protected function _prepareMassactionColumn()
	{
	    $columnId = 'massaction';
	    $massactionColumn = $this->getLayout()->createBlock('adminhtml/widget_grid_column')
	    ->setData(array(
	            'index'        => $this->getMassactionIdField(),
	            'filter_index' => $this->getMassactionIdFilter(),
	            'type'         => 'massaction',
	            'name'         => $this->getMassactionBlock()->getFormFieldName(),
	            'align'        => 'center',
	            'is_system'    => true,
	            'filter'    => false,
	            'renderer'		=> 'Textmaster_Textmaster_Block_Adminhtml_Project_Grid_Column_Renderer_Massaction'
	    ));
	
	    if ($this->getNoFilterMassactionColumn()) {
	        $massactionColumn->setData('filter', false);
	    }
	
	    $massactionColumn->setSelected($this->getMassactionBlock()->getSelected())
	    ->setGrid($this)
	    ->setId($columnId);
	
	    $oldColumns = $this->_columns;
	    $this->_columns = array();
	    $this->_columns[$columnId] = $massactionColumn;
	    $this->_columns = array_merge($this->_columns, $oldColumns);
	    return $this;
	}
	public function getRowUrl($row) {
	    if($row->getId ())
	    return $this->getUrl ( '*/*/view', array (
                'id' => $row->getId ()
        ) );
	}
	public function _apiFilter($collection, $column) {
		if (!$value = $column->getFilter()->getValue()) {	
			return $this;
		}

		$index = $column->getIndex();
		$subsitute = $this->getCollection()->getSubstitute();
		if(isset($subsitute[$index])) $index = $subsitute[$index];
		else return $this;
		
		$filters = array();
		if($index=='progress'){
			if(isset($value['from']))
				$filters[$index]['$gte'] = $value['from']/100;
			if(isset($value['to']))
				$filters[$index]['$lte'] = $value['to']/100;
		} elseif($column->getType()=='number'){
			if(isset($value['from']))
				$filters[$index]['$gte'] = (int)$value['from'];
			if(isset($value['to']))
				$filters[$index]['$lte'] = (int)$value['to'];
		} elseif($column->getType()=='currency'){
			if(isset($value['from']))
				$filters[$index]['$gte'] = (int)$value['from'];
			if(isset($value['to']))
				$filters[$index]['$lte'] = (int)$value['to'];
		} elseif($column->getType()=='datetime' ){
			//
			if(isset($value['from']))
				$filters[$index]['$gte'] = date('Y-m-d',$value['from']->getTimestamp()).'T00:00:00Z';
			if(isset($value['to']))
				$filters[$index]['$lte'] = date('Y-m-d',$value['to']->getTimestamp()).'T23:59:59Z';
		} elseif(in_array($index,$this->_is_regex_op)) {
		    $filters[$index]['$regex'] = '/'.$value.'/i';
		} else {
		    if($index == 'language_from_code' || $index == 'language_to_code') {
		        $localecode = Mage::getStoreConfig('general/locale/code', $value);
	            $data = explode('_', $localecode);
	            $value = $data[0];
		    }
		    $filters[$index] = $value;
		}
		$this->getCollection()->addFiltersApi($filters);
		return $this;
	}
	public function _standardFilter($collection, $column) {
		if (!$value = $column->getFilter()->getValue()) {
			return $this;
		}
		$filters[$column->getIndex()] = $value;
		$this->getCollection()->addFiltersStandard($filters);
	}
	protected function _addColumnFilterToCollection($column)
	{
		if ($this->getCollection()) {
			$field = ( $column->getFilterIndex() ) ? $column->getFilterIndex() : $column->getIndex();
			if ($column->getFilterConditionCallback()) {
				call_user_func($column->getFilterConditionCallback(), $this->getCollection(), $column);
			} else {
				$cond = $column->getFilter()->getCondition();
				if ($field && isset($cond)) {
					$this->getCollection()->addFieldToFilter('main_table.'.$field , $cond);
				}
			}
		}
		return $this;
	}
	public function getRowClass($row) {
	    if(!$row->getId ()) return 'text-disable';
	    return '';
	}
	
	
	
}