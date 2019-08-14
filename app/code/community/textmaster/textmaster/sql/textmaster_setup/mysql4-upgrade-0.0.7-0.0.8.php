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

$configData = Mage::getModel('core/config_data')->load('textmaster/defaultvalue/type_vocabulary', 'path');
$configData->setPath('textmaster/defaultvalue/type_vocabulary')
->setValue('not_specified')
->save();

$configData = Mage::getModel('core/config_data')->load('textmaster/defaultvalue/target_audience', 'path');
$configData->setPath('textmaster/defaultvalue/target_audience')
->setValue('not_specified')
->save();

$configData = Mage::getModel('core/config_data')->load('textmaster/defaultvalue/grammatical_person', 'path');
$configData->setPath('textmaster/defaultvalue/grammatical_person')
->setValue('not_specified')
->save();



$installer->endSetup();