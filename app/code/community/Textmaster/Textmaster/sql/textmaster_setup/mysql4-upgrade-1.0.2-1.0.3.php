<?php
/**
 * @var $installer Mage_Core_Model_Resource_Setup
 */

$installer = $this;
$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('textmaster/project'), 'translation_memory', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_BOOLEAN,
        'nullable'  => false,
        'default'   => false,
        'comment'   => 'Translation memory'
));

$installer->getConnection()
    ->addColumn($installer->getTable('textmaster/project'), 'translation_memory_status', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable'  => true,
        'default'   => null,
        'comment'   => 'Translation memory status'
));

$installer->endSetup();