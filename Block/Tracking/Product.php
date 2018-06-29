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
 * @copyright Copyright (c) 2017 IDEALIAGroup srl (http://www.idealiagroup.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Adspray\Adabra\Block\Tracking;

use Adspray\Adabra\Helper\Data as DataHelper;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class Product extends Template
{

    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var DataHelper
     */
    private $dataHelper;
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * Product constructor.
     * @param Registry $registry
     * @param Template\Context $context
     * @param DataHelper $dataHelper
     * @param AttributeRepositoryInterface $attributeRepository
     * @param array $data
     */
    public function __construct(
        Registry $registry,
        Template\Context $context,
        DataHelper $dataHelper,
        AttributeRepositoryInterface $attributeRepository,
        array $data = []
    ) {
    
        $this->registry = $registry;
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
        $this->attributeRepository = $attributeRepository;
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


    /**
     * Get custom tags list
     *
     * @param Product $product
     * @return string
     * @throws NoSuchEntityException
     */
    public function getCustomTagsList()
    {
        $product = $this->getProduct();
        $tagList = [];
        $tagsListArray = $this->dataHelper->getTrackingTagsList();
        foreach ($tagsListArray as $tag) {
            $tag = trim($tag);
            if ($product->getData($tag) || $product->getAttributeText($tag)) {
                if ($product->getAttributeText($tag) === false) {
                    $tagList[] = $product->getData($tag);
                } else if (
                    $this->attributeRepository
                        ->get(ProductModel::ENTITY, $tag)
                        ->getFrontendInput() === 'boolean'
                ) {
                    if ($product->getAttributeText($tag)->__toString() === 'Yes') {
                        $tagList[] = $tag;
                    }
                } else {
                    $tagList[] = $product->getAttributeText($tag);
                }
            }
        }

        return implode("|", $tagList);
    }
}
