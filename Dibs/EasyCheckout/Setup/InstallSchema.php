<?php
namespace Dibs\EasyCheckout\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * @package Dibs\EasyCheckout\Setup
 */
class InstallSchema implements InstallSchemaInterface
{

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $quoteTable = $setup->getTable('quote');

        $orderTable = $setup->getTable('sales_order');

        $connection = $setup->getConnection();

        $connection->addColumn($quoteTable, 'dibs_easy_payment_id', [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'nullable' => true,
            'comment' => 'dibs easy payment id',
        ]);

        $connection->addColumn($quoteTable, 'dibs_easy_grand_total', [
            'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            'length'    => '12,4',
            'nullable'  => true,
            'comment'   => 'dibs easy payment grand total',
        ]);

        $connection->addColumn($orderTable, 'dibs_easy_payment_id', [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'nullable' => true,
            'comment' => 'dibs easy payment id',
        ]);

        $setup->endSetup();
    }
}