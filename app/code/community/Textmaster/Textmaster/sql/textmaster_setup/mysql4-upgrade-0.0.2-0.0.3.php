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