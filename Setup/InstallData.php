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

use Adspray\Adabra\Api\Data\SubFeedInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    protected $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * Setup initial feeds
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    protected function setupFeeds(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $tableName = $setup->getTable('adabra_feed');

        $stores = $this->storeManager->getStores();
        foreach ($stores as $store) {
            $setup->getConnection()->insert($tableName, [
                'store_id' => $store->getId(),
                'enabled' => '1',
                'currency' => $store->getDefaultCurrencyCode(),
                'status_order' => SubFeedInterface::STATUS_MARKED_REBUILD,
                'status_product' => SubFeedInterface::STATUS_MARKED_REBUILD,
                'status_category' => SubFeedInterface::STATUS_MARKED_REBUILD,
                'status_customer' => SubFeedInterface::STATUS_MARKED_REBUILD,
                'updated_at' => new \Zend_Db_Expr('NOW()'),
            ]);
        }
    }

    /**
     * Setup initial vfields configuration
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    protected function setupVfields(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $tableName = $setup->getTable('adabra_feed_vfield');
    }

    /**
     * Installs data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->setupFeeds($setup, $context);
        $this->setupVfields($setup, $context);

        $setup->endSetup();
    }
}
