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
 * @copyright  Copyright (c) 2017 IDEALIAGroup srl (http://www.idealiagroup.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Adspray\Adabra\Model;

use Adspray\Adabra\Model\Tracking\Storage;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Customer\Model\Data\Customer;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Model\Order;

class Tracking
{

    const TRACK_PAGE_VIEW = 'trkPageView';
    const TRACK_CATEGORY_VIEW = "trkCategoryView";
    const TRACK_PRODUCT_VIEW = 'trkProductView';
    const TRACK_ADD_TO_CART = 'trkProductBasketAdd';
    const TRACK_REMOVE_FROM_CART = 'trkProductBasketRemove';
    const TRACK_PRODUCT_SALE = 'trkProductSale';
    const TRACK_SEARCH = 'trkProductLocalSearch';
    const TRACK_USER_REGISTRATION = 'trkUserRegistration';

    protected $queue = [];
    /**
     * @var Storage
     */
    private $storage;
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    public function __construct(
        Storage $storage,
        TimezoneInterface $timezone
    ) {

        $this->storage = $storage;
        $this->timezone = $timezone;
    }

    /**
     * @param $action
     * @param $params
     * @return $this
     */
    protected function add($action, $params)
    {
        $this->storage->addToQueue($action, $params);
        return $this;
    }

    /**
     * @param ProductInterface $product
     */
    public function trackAddToCart(ProductInterface $product)
    {
        $this->add(static::TRACK_ADD_TO_CART, ['productId' => $product->getData('sku')]);
    }

    /**
     * @param Item $item
     */
    public function trackRemoveFromCart(Item $item)
    {
        $this->add(static::TRACK_REMOVE_FROM_CART, ['productId' => $item->getProduct()->getData('sku')]);
    }

    /**
     * @param Order $order
     */
    public function trackOrder(Order $order)
    {

        $now = $this->timezone->date();

        foreach ($order->getAllVisibleItems() as $item) {

            /** @var Order\Item $item */

            if ($item->getProduct()->getTypeId() != 'simple') {
                continue;
            }

            if (!is_null($item->getParentItem())) {
                $item = $item->getParentItem();
            }

            $params = [
                'groupNumber' => $order->getIncrementId(),
                'productId' => $item->getProduct()->getData('sku'),
                'quantity' => $item->getQtyOrdered(),
                'coupon' => $order->getCouponCode(),
                'price' => $item->getBaseRowTotal(),
                'priceWithTax' => $item->getRowTotalInclTax(),
                'deliveryPrice' => $order->getShippingInclTax(),
                'currency' => $order->getStoreCurrencyCode(),
                'ts' => $now->format('Y-m-d\TH:i:s'),
            ];

            $this->add(static::TRACK_PRODUCT_SALE, $params);
        }
    }

    public function trackNewCustomer(Customer $customer)
    {
        $this->add(static::TRACK_USER_REGISTRATION, ["userId" => $customer->getId()]);
    }

    /**
     * @param bool $clear
     * @return array
     */
    public function get($clear = false)
    {
        return $this->storage->getQueue($clear);
    }

    /**
     * @return $this
     */
    public function flushQueue()
    {
        $this->storage->flushAll();

        return $this;
    }
}
