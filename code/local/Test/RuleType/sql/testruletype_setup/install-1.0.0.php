<?php

$installer = $this;
$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('salesrule/rule'),
        Test_RuleType_Model_Observer::PRODUCT_ATTRIBUTE_CODE,
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'length' => 255,
            'nullable' => false,
            'default' => 0,
            'comment' => 'The new column'
        )
    );

$installer->endSetup();