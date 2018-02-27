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

namespace Adspray\Adabra\Block\Tracking;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class Product extends Template
{

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        Registry $registry,
        Template\Context $context,
        array $data = []
    ) {
    
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return null|ProductInterface
     */
    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * @return string
     */
    public function getCategoryId()
    {
        $product = $this->getProduct();

        $ids = $product->getCategoryIds();

        return isset($ids[0])? $ids[0]: '';
    }
}
