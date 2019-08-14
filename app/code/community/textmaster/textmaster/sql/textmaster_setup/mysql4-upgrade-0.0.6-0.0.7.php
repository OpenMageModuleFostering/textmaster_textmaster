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

/**
 * @var $installer Mage_Core_Model_Resource_Setup
 */


$installer = $this;
$installer->startSetup();


$documents = Mage::getModel('textmaster/document')->getCollection();
foreach($documents as $document) {
    $project = Mage::getModel('textmaster/project')->load($document->getTextmasterProjectId(),null,false);
    $document->setProject($project);
    $product = Mage::getModel('catalog/product')->setStoreId( $project->getStoreIdOrigin() )->load($document->getProductId());
    $document->setName($product->getName());
    $document->save();
}



$installer->endSetup();