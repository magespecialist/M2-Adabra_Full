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
 * @copyright  Copyright (c) 2016 IDEALIAGroup srl (http://www.idealiagroup.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Adspray\Adabra\Block;

use Adspray\Adabra\Helper\Data;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\View\Element\Template;

class Tracking extends Template
{

    const HANDLER_HOME = "cms_index_index";
    const HANDLER_CATEGORY = "catalog_category_view";
    const HANDLER_PRODUCT = "catalog_product_view";
    const HANDLER_CART = "checkout_cart_index";
    const HANDLER_404 = "cms_noroute_index";
    const HANDLER_SEARCH_RESULTS = "catalogsearch_result_index";
    const HANDLER_ADVANCED_SEARCH_RESULTS = "catalogsearch_advanced_result";
    const HANDLER_SEARCH_ADVANCED = "catalogsearch_advanced_index";
    const HANDLER_REGISTRATION = "customer_account_create";
    const HANDLER_CHECKOUT = "checkout_index_index";
    const HANDLER_CHECKOUT_SUCCESS = "checkout_onepage_success";
    const HANDLER_CHECKOUT_FAILURE = "checkout_onepage_failure";

    const PAGE_HOME = 101;
    const PAGE_CATEGORY = 102;
    const PAGE_PRODUCT = 103;
    const PAGE_CART = 104;
    const PAGE_SEARCH = 105;
    const PAGE_LANDING = 106;
    const PAGE_404 = 107;
    const PAGE_OTHER = 108;
    const PAGE_USER = 109;
    const PAGE_CHECKOUT = 110;

    /**
     * @var Data
     */
    protected $helper;

    /** @var Resolver */
    protected $localeResolver;

    public function __construct(
        Template\Context $context,
        Resolver $localeResolver,
        Data $helper,
        array $data = []
    ) {

        $this->localeResolver = $localeResolver;
        $this->helper = $helper;

        parent::__construct($context, $data);
    }

    /**
     * @return null|string
     */
    public function getSiteLanguage()
    {
        return $this->localeResolver->getLocale();
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->helper->getTrackingEnabled();
    }

    /**
     * @return null|string
     */
    public function getTrackingUrl()
    {
        return $this->helper->getTrackingUrl();
    }

    /**
     * @return null|string
     */
    public function getCatalogId()
    {
        return $this->helper->getCatalogId();
    }

    /**
     * @return null|string
     */
    public function getSiteId()
    {
        return $this->helper->getSiteId();
    }

    public function getPageType()
    {
        $handles = $this->getLayout()->getUpdate()->getHandles();

        if (in_array(static::HANDLER_CATEGORY, $handles)) {
            return static::PAGE_CATEGORY;
        }

        if (in_array(static::HANDLER_HOME, $handles)) {
            return static::PAGE_HOME;
        }

        if (in_array(static::HANDLER_PRODUCT, $handles)) {
            return static::PAGE_PRODUCT;
        }

        if (in_array(static::HANDLER_404, $handles)) {
            return static::PAGE_404;
        }

        if (in_array(static::HANDLER_CART, $handles)) {
            return static::PAGE_CART;
        }

        if (in_array(static::HANDLER_SEARCH_RESULTS, $handles)
            || in_array(static::HANDLER_SEARCH_ADVANCED, $handles)
            || in_array(static::HANDLER_ADVANCED_SEARCH_RESULTS, $handles)
        ) {
            return static::PAGE_SEARCH;
        }

        if (in_array(static::HANDLER_REGISTRATION, $handles)) {
            return static::PAGE_USER;
        }

        if (in_array(static::HANDLER_CHECKOUT, $handles)
            || in_array(static::HANDLER_CHECKOUT_SUCCESS, $handles)
            || in_array(static::HANDLER_CHECKOUT_FAILURE, $handles)
        ) {
            return static::PAGE_CHECKOUT;
        }

        return static::PAGE_OTHER;
    }

    /**
     * @return string
     */
    public function getCustomerInfoUrl()
    {
        return $this->getUrl('adabra_tracking/tracking/customer');
    }

    /**
     * @return string
     */
    public function getActionsQueueUrl()
    {
        return $this->getUrl('adabra_tracking/tracking/actions');
    }
}
