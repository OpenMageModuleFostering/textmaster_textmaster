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
 class Textmaster_Textmaster_Model_Mysql4_Document extends Mage_Core_Model_Mysql4_Abstract
{
     public function _construct()
     {
         $this->_init('textmaster/document', 'textmaster_document_id');
        // $this->setEntityIdField('textmaster_document_id');
     }

     public function loadByApiId($api_id) {
     	$adapter = $this->_getReadAdapter();
     	$bind    = array('api_id' => $api_id);
     	$select  = $adapter->select()
     	->from($this->getTable('document'))
     	->where('document_api_id = :api_id');
     	
     	$documentId = $adapter->fetchOne($select, $bind);
     	return $documentId;
     }
}
