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
class Textmaster_Textmaster_Block_Adminhtml_Project_View_Documents extends Mage_Adminhtml_Block_Widget_Grid
{
    private $_is_regex_op = array(
            'title',
            'ref'
    );
	public function __construct()
	{
		parent::__construct();
		$this->setDefaultSort('textmaster_document_id');
		$this->setDefaultDir('DESC');
		$this->setDefaultLimit(20);
		$this->setSaveParametersInSession(true);

	}

	protected function _getStore()
	{
		$storeId = (int) $this->getRequest()->getParam('store', 0);
		return Mage::app()->getStore($storeId);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('textmaster/document')->getCollection()
			->setLoadApi(true)
			->setProjectApiId($this->getProject()->getProjectApiid())
			->addFieldToFilter('textmaster_project_id',$this->getProject()->getId());		
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('textmaster_document_id',
			array(
					'header'=> Mage::helper('textmaster')->__('Reference'),
					'width' => '200px',
					//'type'  => 'number',
					'index' => 'reference',
			        'filter_condition_callback' => array($this, '_apiFilter'),
		));
	

		$this->addColumn('name',
			array(
					'header'=> Mage::helper('textmaster')->__('Name'),
					'index' => 'name',
			        'filter_condition_callback' => array($this, '_apiFilter'),
		));
	 
		/*$this->addColumn('type',
				array(
						'header'=> Mage::helper('textmaster')->__('Type'),
						'width' => '60px',
						'index' => 'type',
						//'type'  => 'options',
						//'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
				));*/

		$this->addColumn('status',
			array(
					'header'=> Mage::helper('textmaster')->__('Status'),
					'width' => '70px',
					'index' => 'status',
					//'renderer'  => 'Textmaster_Textmaster_Block_Adminhtml_Document_Renderer_Status',
					'type'  => 'options',
					'options' => Mage::getSingleton('textmaster/project')->getStatuses(),
					'filter_condition_callback' => array($this, '_apiFilter'),
					//'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
		));
		$this->addColumn('action',
				array(
						'header'    =>  Mage::helper('textmaster')->__('Action'),
						'width'     => '50',
						'type'      => 'action',
						'getter'    => 'getId',
						'actions'   => array(
							array(
									'caption'   => Mage::helper('textmaster')->__('View'),
									'url'       => array('base'=> '*/*/document'),
									'field'     => 'id'
							)
						),
						'filter'    => false,
						'sortable'  => false,
						'index'     => 'stores',
						'is_system' => true,
						'align' => 'right',
				));
		return parent::_prepareColumns();
	}

	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('textmaster_document_id');
		$this->getMassactionBlock()->setFormFieldName('document_id');


		//if($this->getProject()->getStatus()== Textmaster_Textmaster_Model_Project::PROJECT_STATUS_IN_REVIEW)	{	
			$this->getMassactionBlock()->addItem('complete', array(
					'label'=> Mage::helper('textmaster')->__('Complete file'),
					'url'=> $this->getUrl('*/*/masscompletedoc',array('id'=>$this->getProject()->getId())),
			        'complete'=>'
			        completeDocuments
			        '
			));
			$this->getMassactionBlock()->setUseSelectAll(false);
		//}
		 		 
		return $this;
	}

	public function getRowUrl($row)
	{
 		return $this->getUrl('*/*/document', array('id' => $row->getId()));
		return false;
	}
	public function getProject()
	{
		// 		return $this->getUrl('aoadmin/catalog_product/edit', array('id' => $row->getId()));
		return $this->_project;
	}
	public function setProject($project)
	{
		// 		return $this->getUrl('aoadmin/catalog_product/edit', array('id' => $row->getId()));
		$this->_project = $project;
		return $this;
	}
	public function setProjectApiId($id)
	{
		// 		return $this->getUrl('aoadmin/catalog_product/edit', array('id' => $row->getId()));
		$this->_project_api_id = $id;
		return $this;
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
		if($column->getType()=='number'  ){
			if(isset($value['from']))
				$filters[$index]['$gte'] = $value['from'];
			if(isset($value['to']))
				$filters[$index]['$lte'] = $value['to'];
		} elseif($column->getType()=='datetime' ){
			//
			if(isset($value['from']))
				$filters[$index]['$gte'] = date('Y-m-d',$value['from']->getTimestamp());
			if(isset($value['to']))
				$filters[$index]['$lte'] = date('Y-m-d',$value['to']->getTimestamp());
		} elseif(in_array($index,$this->_is_regex_op)) {
		    $filters[$index]['$regex'] = '/'.$value.'/i';
		} else {
			$filters[$index] = $value;
		}
		$this->getCollection()->addFiltersApi($filters);
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
				'renderer'		=> 'Textmaster_Textmaster_Block_Adminhtml_Project_View_Grid_Column_Renderer_Massaction'
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
}