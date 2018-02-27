<?php
/**
 * IDEALIAGroup srl
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@idealiagroup.com so we can send you a copy immediately.
 *
 * @category   Adspray
 * @package    Adspray_Adabra
 * @copyright  Copyright (c) 2016 IDEALIAGroup srl (http://www.idealiagroup.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Adspray\Adabra\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    protected function setupTableFeed(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $tableName = $setup->getTable('adabra_feed');

        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn('adabra_feed_id', Table::TYPE_INTEGER, null, [
                'auto_increment' => true,
                'identify' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true], 'Id')
            ->addColumn('store_id', Table::TYPE_SMALLINT, null, [
                'nullable' => false,
                'unsigned' => true,
                'default' => '0',
            ], 'Store ID')
            ->addColumn('enabled', Table::TYPE_BOOLEAN, null, [
                'nullable' => false,
            ], 'Enabled')
            ->addColumn('currency', Table::TYPE_TEXT, null, [
                'nullable' => false,
            ], 'Currency')
            ->addColumn('status_order', Table::TYPE_TEXT, null, [
                'nullable' => false,
            ], 'Status order')
            ->addColumn('status_product', Table::TYPE_TEXT, null, [
                'nullable' => false,
            ], 'Status product')
            ->addColumn('status_category', Table::TYPE_TEXT, null, [
                'nullable' => false,
            ], 'Status category')
            ->addColumn('status_customer', Table::TYPE_TEXT, null, [
                'nullable' => false,
            ], 'Status customer')
            ->addColumn('updated_at', Table::TYPE_DATETIME, null, [
                'nullable' => false,
            ], 'Updated at')
            ->addIndex(
                $setup->getIdxName(
                    'adabra_feed',
                    ['currency', 'store_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                [
                    ['name' => 'currency', 'size' => 128]
                    , 'store_id'
                ],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addForeignKey(
                $setup->getFkName('adabra_feed', 'store_id', 'store', 'store_id'),
                'store_id',
                $setup->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )
            ->setOption('type', 'Innodb')
            ->setComment('Adabra Feed');

        $setup->getConnection()->createTable($table);
    }

    protected function setupTableVfield(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $tableName = $setup->getTable('adabra_feed_vfield');

        $table = $setup->getConnection()
            ->newTable($tableName)
            ->addColumn('adabra_feed_vfield_id', Table::TYPE_INTEGER, null, [
                'auto_increment' => true,
                'identify' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true], 'Id')
            ->addColumn('code', Table::TYPE_TEXT, null, [
                'nullable' => false,
            ], 'Code')
            ->addColumn('mode', Table::TYPE_TEXT, null, [
                'nullable' => false,
            ], 'Mode')
            ->addColumn('value', Table::TYPE_TEXT, null, [
                'nullable' => false,
            ], 'Currency')
            ->addIndex(
                $setup->getIdxName(
                    'adabra_feed_vfield',
                    ['code'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                [['name' => 'code', 'size' => 128]],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->setOption('type', 'Innodb')
            ->setComment('Adabra Feed V-Field');

        $setup->getConnection()->createTable($table);
    }

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->setupTableFeed($setup, $context);
        $this->setupTableVfield($setup, $context);

        $setup->endSetup();
    }
}
