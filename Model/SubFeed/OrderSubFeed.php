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

namespace Adspray\Adabra\Model\SubFeed;

use Adspray\Adabra\Api\Data\SubFeedInterface;
use Adspray\Adabra\Helper\Data as DataHelper;
use Adspray\Adabra\Helper\Ftp as FtpHelper;
use Adspray\Adabra\Helper\Filesystem;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\Io\File;
use Magento\Directory\Helper\Data as DirectoryHelperData;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class OrderSubFeed extends AbstractSubFeed implements SubFeedInterface
{
    const INTERVAL_DAYS = 365;
    const ONE_DAY = 86400;

    protected $type = 'order';
    protected $exportName = 'orders';

    protected $collectionFactory;
    protected $dataHelper;
    protected $dateTime;
    protected $productFactory;

    public function __construct(
        File $file,
        Csv $csv,
        Filesystem $filesystem,
        DataHelper $dataHelper,
        FtpHelper $ftpHelper,
        DirectoryHelperData $directoryHelperData,
        DateTime $dateTime,
        CollectionFactory $collectionFactory,
        ProductFactory $productFactory
    ) {
        parent::__construct($file, $csv, $filesystem, $dataHelper, $ftpHelper, $directoryHelperData, $dateTime);

        $this->collectionFactory = $collectionFactory;
        $this->dataHelper = $dataHelper;
        $this->dateTime = $dateTime;
        $this->productFactory = $productFactory;
    }

    /**
     * Get headers
     * @return array
     */
    protected function getHeaders()
    {
        return [
            'id_utente',
            'id_raggrprod',
            'id_cli_categoria',
            'id_cli_prodotto',
            'quantita',
            'valuta',
            'prezzo_notax',
            'prezzo_spedizione',
            'prezzo',
            'coupon',
            'ts',
        ];
    }

    /**
     * Prepare feed collection
     * @return void
     */
    protected function prepareCollection()
    {
        $interval = self::INTERVAL_DAYS * self::ONE_DAY;

        $orderStates = $this->dataHelper->getOrderStates();

        $currentTimestamp = $this->dateTime->gmtTimestamp();
        $dateStart = $this->dateTime->date('Y-m-d', $currentTimestamp - $interval);

        $this->collection = $this->collectionFactory->create();
        $this->collection
            ->addAttributeToFilter('store_id', array('eq' => $this->getFeed()->getStore()->getId()))
            ->addFieldToFilter('created_at', array('gteq' => $dateStart))
            ->addFieldToFilter('state', array('in' => $orderStates));
    }

    /**
     * Get feed row for entity
     * @param $entity
     * @return array
     */
    protected function getFeedRow($entity)
    {
        /** @var $order Order */
        $order = $entity;

        $customerId = $order->getCustomerId();
        if (!$customerId) {
            return array();
        }

        $orderItems = $order->getAllVisibleItems();

        $shippingAmount = $order->getBaseShippingAmount();
        $couponCode = $order->getCouponCode();
        $incrementId = $order->getIncrementId();
        $createdAt = strtotime($order->getCreatedAt());

        $product = $this->productFactory->create();
        $resourceProduct = $product->getResource();

        $storeId = $order->getStoreId();

        $return = array();
        $rowsCount = 0;
        foreach ($orderItems as $orderItem) {
            $isFirstRow = ($rowsCount == 0);

            // Fake product to retrieve categories
            $categoryIds = array();
            if ($orderItem->getProductId()) {
                $product->setId($orderItem->getProductId());
                $categoryIds = $resourceProduct->getCategoryIds($product);
            }

            // @codingStandardsIgnoreStart
            if (!count($categoryIds)) {
                $categoryIds = [CategorySubFeed::FAKE_CATEGORY_ID];
            }
            // @codingStandardsIgnoreEnd

            $productSku = $resourceProduct
                ->getAttributeRawValue($orderItem->getProductId(), 'sku', $storeId);

            if (!$productSku) {
                $productSku = $orderItem->getSku();
            } else {
                $productSku = $productSku['sku'];
            }

            $return[] = [
                $customerId,
                $incrementId,
                $categoryIds[0],
                $productSku,
                $orderItem->getQtyOrdered(),
                $order->getOrderCurrencyCode(),
                $this->toCurrency($orderItem->getPrice(), true),
                ($isFirstRow ? $this->toCurrency($shippingAmount, true) : ''),
                $this->toCurrency($orderItem->getPriceInclTax(), true),
                ($isFirstRow ? $couponCode : ''),
                $this->toTimestamp($createdAt),
            ];
            $rowsCount++;
        }

        return $return;
    }
}
