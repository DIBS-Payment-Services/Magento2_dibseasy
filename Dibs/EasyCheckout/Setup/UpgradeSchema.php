<?php
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
        }

        if (version_compare($context->getVersion(), '0.2.1', '<')) {

            $orderPaymentTable = $setup->getTable('sales_order_payment');

            $quotePaymentTable = $setup->getTable('quote_payment');

            $connection = $setup->getConnection();

            $connection->addColumn($orderPaymentTable, 'dibs_easy_payment_type', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'length' => 200,
                'comment' => 'Dibs Easy Payment Type',
            ]);

            $connection->addColumn($quotePaymentTable, 'dibs_easy_payment_type', [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'nullable' => true,
                'length' => 200,
                'comment' => 'Dibs Easy Payment Type',
            ]);
        }

        $setup->endSetup();
    }
}
