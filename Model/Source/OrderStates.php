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

namespace Adspray\Adabra\Model\Source;

use Magento\Sales\Model\Order;

class OrderStates extends SourceAbstract
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Order::STATE_COMPLETE,
                'label' => __('Complete')
            ], [
                'value' => Order::STATE_CLOSED,
                'label' => __('Closed')
            ], [
                'value' => Order::STATE_HOLDED,
                'label' => __('Holded')
            ], [
                'value' => Order::STATE_CANCELED,
                'label' => __('Canceled')
            ], [
                'value' => Order::STATE_NEW,
                'label' => __('New')
            ], [
                'value' => Order::STATE_PAYMENT_REVIEW,
                'label' => __('Payment Review')
            ], [
                'value' => Order::STATE_PENDING_PAYMENT,
                'label' => __('Pending Payment')
            ], [
                'value' => Order::STATE_PROCESSING,
                'label' => __('Processing')
            ],
        ];
    }
}
