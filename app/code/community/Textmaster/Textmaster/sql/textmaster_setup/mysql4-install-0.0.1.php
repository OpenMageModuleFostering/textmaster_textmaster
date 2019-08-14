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

$installer->run("
	DROP TABLE IF EXISTS `{$this->getTable('textmaster_document')}`;
	CREATE TABLE `{$this->getTable('textmaster_document')}` (
  `textmaster_document_id` int(11) NOT NULL auto_increment,
  `textmaster_project_id` int(11) NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `document_api_id` varchar(60) default NULL,
  `name` varchar(45) default NULL,
  `send` tinyint(1) default '0',
  `updated_at` datetime default NULL,
  `created_at` datetime default NULL,
  `counted` tinyint(1) default '0',
  PRIMARY KEY  (`textmaster_document_id`),
  KEY `fk_textmaster_document_textmaster_project1_idx` (`textmaster_project_id`),
  KEY `fk_textmaster_document_catalog_product_entity1_idx` (`product_id`),
  CONSTRAINT `fk_textmaster_document_catalog_product_entity1` FOREIGN KEY (`product_id`) REFERENCES `{$this->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_textmaster_document_textmaster_project1` FOREIGN KEY (`textmaster_project_id`) REFERENCES `{$this->getTable('textmaster_project')}` (`textmaster_project_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;"
);

$installer->run("
	DROP TABLE IF EXISTS `{$this->getTable('textmaster_project_attribute')}`;
	CREATE TABLE `{$this->getTable('textmaster_project_attribute')}` (
		`textmaster_project_attribute_id` int(11) NOT NULL auto_increment,
  		`textmaster_attribute_id` int(11) default NULL,
  		`textmaster_project_id` int(11) default NULL,
  	PRIMARY KEY  (`textmaster_project_attribute_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1;
");

$installer->run("
DROP TABLE IF EXISTS `{$this->getTable('textmaster_project')}`;
CREATE TABLE `{$this->getTable('textmaster_project')}` (
  `textmaster_project_id` int(11) NOT NULL auto_increment,
  `store_id_origin` smallint(5) unsigned NOT NULL,
  `store_id_translation` smallint(5) unsigned NOT NULL,
  `name` varchar(45) default NULL,
  `status` varchar(45) default NULL,
  `project_apiid` varchar(50) default NULL,
  `textmasters` text,
  `documents_transfert` tinyint(1) default '0',
  PRIMARY KEY  (`textmaster_project_id`),
  KEY `fk_textmaster_project_core_store1_idx` (`store_id_origin`),
  KEY `fk_textmaster_project_core_store2_idx` (`store_id_translation`),
  CONSTRAINT `fk_textmaster_project_core_store1` FOREIGN KEY (`store_id_origin`) REFERENCES `{$this->getTable('core_store')}` (`store_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_textmaster_project_core_store2` FOREIGN KEY (`store_id_translation`) REFERENCES `{$this->getTable('core_store')}` (`store_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
");

// Configation par defaut des category 
$configData = Mage::getModel('core/config_data')->load('textmaster/defaultvalue/category', 'path');
$configData->setPath('textmaster/defaultvalue/category')
->setValue('C019')
->save();


//configuration par defaut du briefing
$admin_locale_code = Mage::getStoreConfig('general/locale/code');
if(substr($admin_locale_code,0,2)=='fr') {
    $t1 = "Bonjour, Merci de traduire aussi fidèlement que possible le texte fourni en veillant à bien adapter les tournures et phrases dans votre langue maternelle pour garantir une bonne fluidité du texte. Merci aussi de conserver la mise en forme et les balises HTML.";
    $t2 = "Bonjour, Merci de corriger le texte fourni en veillant à conserver la structure du contenu. Vous devez corriger toutes les fautes d'orthographe, de grammaire (etc.) sans modifier l'organisation des phrases. Merci aussi de conserver la mise en forme et les balises HTML.";
} else {
    $t1 = "Hello, Please translate as faithfully as possible the text provided while respecting the paragraph structure of the document. Please note that the expected number of words is given as an indication only. Also, please maintain the text format and HTML tags. Thank you.";
    $t2 = "Hello, Thank you for proofreading this text. Please maintain the style and vocabulary level and make sure you correct any grammatical error or typo. Also, please maintain the text format and HTML tags. Thank you";
}
$configData = Mage::getModel('core/config_data')->load('textmaster/defaultvalue/briefing_message_translation', 'path');
$configData->setPath('textmaster/defaultvalue/briefing_message_translation')
->setValue($t1)
->save();

$configData = Mage::getModel('core/config_data')->load('textmaster/defaultvalue/briefing_message_proofreading', 'path');
$configData->setPath('textmaster/defaultvalue/briefing_message_proofreading')
->setValue($t2)
->save();



$installer->endSetup();