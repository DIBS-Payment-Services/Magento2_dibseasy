<?php
/**
 * Copyright © 2009-2017 Vaimo Group. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Dibs\EasyCheckout\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '0.2.0', '<')) {

            $orderPaymentTable = $setup->getTable('sales_order_payment');

            $quotePaymentTable = $setup->getTable('quote_payment');

            $connection = $setup->getConnection();

            $connection->addColumn($orderPaymentTable, 'dibs_easy_cc_masked_pan', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'length' => 16,
                'comment' => 'Dibs Easy Credit Card Masked Pan',
            ]);

            $connection->addColumn($quotePaymentTable, 'dibs_easy_cc_masked_pan', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'length' => 16,
                'comment' => 'Dibs Easy Credit Card Masked Pan',
            ]);

            /**
             * 'anet_trans_method',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            32,
            [],
            'Anet Trans Method'
             */

            $setup->endSetup();
        }

        $setup->endSetup();
    }
}
