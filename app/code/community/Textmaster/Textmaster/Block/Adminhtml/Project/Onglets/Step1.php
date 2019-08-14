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
class Textmaster_Textmaster_Block_Adminhtml_Project_Onglets_Step1 extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('step1');
		$this->setDefaultSort('textmaster_project_id');
		$this->setDefaultDir('DESC');
		//$this->setDefaultLimit(10);
		//$this->setSaveParametersInSession(true);
		//$this->setUseAjax(true);

	}

	protected function _getStore()
	{
		$storeId = (int) $this->getRequest()->getParam('store', 0);
		return Mage::app()->getStore($storeId);
	}

	protected function _prepareCollection()
	{
		$store = $this->_getStore();
		$subcollection = Mage::getModel('core/store')->getCollection();
		$subcollection->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns(new Zend_Db_Expr('GROUP_CONCAT(main_table.store_id SEPARATOR ";") as store_name_to,GROUP_CONCAT(main_table.store_id SEPARATOR ";") as store_id_to, tdocument.product_id'))
			->joinInner(array('tproject'=>'textmaster_project'),'main_table.store_id = tproject.store_id_translation', array())
			->joinInner(array('tdocument'=>'textmaster_document'),'tproject.textmaster_project_id = tdocument.textmaster_project_id', array())
			->where('tproject.store_id_translation!=tproject.store_id_origin')
			->order('tproject.textmaster_project_id')->group('product_id');
		
		$subcollection2 = Mage::getModel('core/store')->getCollection();
		$subcollection2->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns(new Zend_Db_Expr('GROUP_CONCAT(main_table.store_id SEPARATOR ";") as store_name_from, tdocument2.product_id'))
		->joinInner(array('tproject2'=>'textmaster_project'),'main_table.store_id = tproject2.store_id_origin', array())
		->joinInner(array('tdocument2'=>'textmaster_document'),'tproject2.textmaster_project_id = tdocument2.textmaster_project_id', array())
		->where('tproject2.store_id_translation!=tproject2.store_id_origin')
		->order('tproject2.textmaster_project_id')->group('tdocument2.product_id');
		
		
		$collection = Mage::getModel('catalog/product')->getCollection()
			->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('type_id');
		
		if ($store->getId()) {
			$adminStore = Mage_Core_Model_App::ADMIN_STORE_ID;
			$collection->addStoreFilter($store);
			$collection->joinAttribute(
					'name',
					'catalog_product/name',
					'entity_id',
					null,
					'inner',
					$adminStore
			);
			$collection->joinAttribute(
					'custom_name',
					'catalog_product/name',
					'entity_id',
					null,
					'inner',
					$store->getId()
			);
			$collection->joinAttribute(
					'status',
					'catalog_product/status',
					'entity_id',
					null,
					'inner',
					$store->getId()
			);
			$collection->joinAttribute(
					'visibility',
					'catalog_product/visibility',
					'entity_id',
					null,
					'inner',
					$store->getId()
			);
			$collection->joinAttribute(
					'price',
					'catalog_product/price',
					'entity_id',
					null,
					'left',
					$store->getId()
			);
		}
		else {
			$collection->addAttributeToSelect('price');
			$collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');
			$collection->joinAttribute('visibility', 'catalog_product/visibility', 'entity_id', null, 'inner');
		}
		
		$collection->getSelect()->joinLeft(array('ttranslation' => new Zend_Db_Expr('('.$subcollection->getSelect()->__toString().')')),'ttranslation.product_id = e.entity_id');
		$collection->getSelect()->joinLeft(array('ttranslation2'=> new Zend_Db_Expr('('.$subcollection2->getSelect()->__toString().')')),'ttranslation2.product_id = e.entity_id');

		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('entity_id',
				array(
						'header'=> Mage::helper('catalog')->__('ID'),
						'width' => '50px',
						'type'  => 'number',
						'index' => 'entity_id',
				));		

		$this->addColumn('name',
				array(
						'header'=> Mage::helper('catalog')->__('Name'),
						'index' => 'name',
				));
		 
		$this->addColumn('type',
				array(
						'header'=> Mage::helper('catalog')->__('Type'),
						'width' => '60px',
						'index' => 'type_id',
						'type'  => 'options',
						'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
				));


		$sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
		->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
		->load()
		->toOptionHash();

		$this->addColumn('set_name',
				array(
						'header'=> Mage::helper('catalog')->__('Attrib. Set Name'),
						'width' => '100px',
						'index' => 'attribute_set_id',
						'type'  => 'options',
						'options' => $sets,
				));

		$this->addColumn('status',
				array(
						'header'=> Mage::helper('catalog')->__('Status'),
						'width' => '70px',
						'index' => 'status',
						'type'  => 'options',
						'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
				));

		$this->addColumn('visibility',
				array(
						'header'=> Mage::helper('catalog')->__('Visibility'),
						'width' => '70px',
						'index' => 'visibility',
						'type'  => 'options',
						'options' => Mage::getModel('catalog/product_visibility')->getOptionArray(),
				));

		$this->addColumn('sku',
				array(
						'header'=> Mage::helper('catalog')->__('SKU'),
						'width' => '80px',
						'index' => 'sku',
				));

		$store = $this->_getStore();
		$this->addColumn('price',
				array(
						'header'		=> Mage::helper('catalog')->__('Price'),
						'type'  		=> 'price',
						'currency_code' => $store->getBaseCurrency()->getCode(),
						'index' 		=> 'price',
				));

		$aOptions = Mage::getModel('core/store')->getCollection()->toOptionHash();
		foreach ($aOptions as $k=> $aOption){
			$aOptions[$k] = Mage::helper('catalog')->__('Traduction manquante : ').$aOption;
		}
		$this->addColumn('translation',
				array(
						'header'	=> Mage::helper('catalog')->__('Traduction existante'),
						'width' 	=> '80px',
						'index' 	=> 'store_name',						
						'renderer' 	=> 'Textmaster_Textmaster_Block_Adminhtml_Project_Renderer_Store',
						'type'  	=> 'options',
						'options' 	=> $aOptions,
						'filter_condition_callback' => array($this, 'translationFilter'),
				));


		return parent::_prepareColumns();
	}
	public function translationFilter($collection, $column){
		
		if (!$value = $column->getFilter()->getValue()) {
			return $this;
		}
		$store_name = Mage::getModel('core/store')->load($value)->getName(); 
		
		$collection->getSelect()->where(new Zend_Db_Expr("(ttranslation.store_name_to NOT LIKE '%;$value%' AND ttranslation.store_name_to NOT LIKE '%$value;%' AND ttranslation.store_name_to <> '$value') OR ttranslation.store_name_to IS NULL"));
		$sql = $collection->getSelect()->__toString();
		
		return $this;
	}

	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('entity_id');
		$this->getMassactionBlock()->setFormFieldName('products_id');

		$this->getMassactionBlock()->addItem('ajouter', array(
				'label'=> Mage::helper('textmaster')->__('Add'),
				'url'=> $this->getUrl('*/*/massAdd')
		));
		Mage::dispatchEvent('adminhtml_catalog_product_grid_prepare_massaction', array('block' => $this));
		 
		return $this;
	}

	public function getRowUrl($row)
	{
// 		return $this->getUrl('aoadmin/catalog_product/edit', array('id' => $row->getId()));
		return false;
	}
	

}