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

namespace Adspray\Adabra\Controller\Tracking;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class Customer extends Action
{

    /**
     * @var JsonFactory
     */
    private $jsonFactory;
    /**
     * @var CustomerSession
     */
    private $customerSession;
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession
    ) {

        $this->jsonFactory = $jsonFactory;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;

        parent::__construct($context);
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $response = $this->jsonFactory->create();

        $customerData = [];

        if ($this->customerSession->isLoggedIn()) {
            $customerData['logged'] = true;
            $customerData['id'] = $this->customerSession->getCustomerId();
        } else {
            $customerData['logged'] = false;
        }

        $quote = $this->checkoutSession->getQuote();
        $customerData['cart'] = [];

        foreach ($quote->getAllVisibleItems() as $item) {
            $customerData['cart'][] = [
                'sku' => $item->getSku(),
                'qty' => $item->getQty()
            ];
        }

        $response->setData($customerData);

        return $response;
    }
}
