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
use Magento\Bundle\Model\ResourceModel\Selection as BundleSelection;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Downloadable\Model\Product\Type as DownloadableType;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\Io\File;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Directory\Helper\Data as DirectoryHelperData;
use Magento\Catalog\Model\Product\Media\Config as MediaConfig;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\UrlInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedType;

class ProductSubFeed extends AbstractSubFeed implements SubFeedInterface
{
    protected $type = 'product';
    protected $exportName = 'products';

    protected $collectionFactory;
    protected $categoryRepository;
    protected $mediaConfig;
    protected $configurableResourceModel;
    protected $bundleSelection;
    protected $groupedType;
    protected $stockState;
    protected $resourceConnection;
    protected $stockConfiguration;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        File $file,
        Csv $csv,
        Filesystem $filesystem,
        DataHelper $dataHelper,
        FtpHelper $ftpHelper,
        DirectoryHelperData $directoryHelperData,
        DateTime $dateTime,
        CollectionFactory $collectionFactory,
        CategoryRepositoryInterface $categoryRepository,
        MediaConfig $mediaConfig,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configurableResourceModel,
        BundleSelection $bundleSelection,
        GroupedType $groupedType,
        StockStateInterface $stockState,
        ResourceConnection $resourceConnection,
        StockConfigurationInterface $stockConfiguration,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($file, $csv, $filesystem, $dataHelper, $ftpHelper, $directoryHelperData, $dateTime);

        $this->collectionFactory = $collectionFactory;
        $this->categoryRepository = $categoryRepository;
        $this->mediaConfig = $mediaConfig;
        $this->configurableResourceModel = $configurableResourceModel;
        $this->bundleSelection = $bundleSelection;
        $this->groupedType = $groupedType;
        $this->stockState = $stockState;
        $this->resourceConnection = $resourceConnection;
        $this->stockConfiguration = $stockConfiguration;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get virtual field value
     * @param Product $product
     * @param $field
     * @return string
     */
    public function getVirtualField(Product $product, $field)
    {
        // TODO: Virtual fields mapping
        return $product->getData($field);
    }

    /**
     * Get headers
     * @return array
     */
    protected function getHeaders()
    {
        return [
            'id_cli_prodotto',
            'id_cli_categoria',
            'link_negozio',
            'nome',
            'descrizione_breve',
            'descrizione',
            'brand',
            'modello',
            'prezzo_spedizione',
            'prezzo_base',
            'prezzo_finale',
            'valuta',
            'info_pagamento',
            'f_spedizione',
            'tempo_spedizione',
            'info_spedizione',
            'immagine',
            'fine_validita',
            'disponibilita',
            'quantita_disponibile',
            'disponibile_dal',
            'priorita',
            'condizione',
            'f_peradulti',
            'f_attivo',
            'SKU',
            'GTIN',
            'UPC',
            'EAN',
            'ISBN',
            'ASIN',
            'PZN',
            'CNET',
            'MUZEID',
            'MPN',
            'correlati',
            'tags',
            'categorie',
        ];
    }

    /**
     * Prepare feed collection
     * @return void
     */
    protected function prepareCollection()
    {
        $this->collection = $this->collectionFactory->create();
        $this->collection
            ->setStoreId($this->getFeed()->getStore()->getId())
            ->addStoreFilter()
            ->addAttributeToSelect('*')
            ->addWebsiteFilter($this->getFeed()->getStore()->getWebsiteId())
            ->addUrlRewrite()
//            ->addPriceData(0, $this->getStore()->getWebsiteId()) // This filters out out-of-stock products
            ->addCategoryIds();

        // Add stock information
        $stockItemTableName = $this->resourceConnection->getTableName('cataloginventory_stock_item');
        $this->collection->getSelect()
            ->joinLeft(
                ['s' => $stockItemTableName],
                's.product_id=e.entity_id'
            )
            ->group('e.entity_id');
    }

    /**
     * Get category name
     * @param $categoryId
     * @return string
     */
    protected function getCategoryName($categoryId)
    {
        $category = $this->categoryRepository->get($categoryId);
        if ($category->getId()) {
            return $category->getName();
        }

        return CategorySubFeed::FAKE_CATEGORY_NAME;
    }

    /**
     * Get product children ids
     * @param Product $product
     * @return array|null
     */
    protected function getProductChildrenIds(Product $product)
    {
        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            $res = $this->configurableResourceModel->getChildrenIds($product->getId());
        } else if ($product->getTypeId() == Grouped::TYPE_CODE) {
            $res = $this->groupedType->getChildrenIds($product->getId(), Grouped::TYPE_CODE);
        } else if ($product->getTypeId() == Product\Type::TYPE_BUNDLE) {
            $res = $this->bundleSelection->getChildrenIds($product->getId(), true);
        } else {
            $res = [];
        }

        // Flatten children
        $out = [];
        foreach ($res as $i) {
            $out = array_merge($out, $i);
        }

        return array_unique($out);
    }

    /**
     * Get stock qty for a given product
     * @param Product $product
     * @return float|boolean
     */
    protected function getStockQty(Product $product)
    {
        $manageStock = $this->stockConfiguration->getManageStock();
        $useStock = $product->getUseConfigManageStock() ? $manageStock : $product->getManageStock();

        if ($useStock && !$product->getIsInStock()) {
            return false;
        }

        return $useStock ? $product->getQty() : 999999;
    }

    /**
     * Get stock sum for children products
     * @param Product $product
     * @return float|null
     */
    protected function getStockSum(Product $product)
    {
        $childrenIds = $this->getProductChildrenIds($product);
        if (!count($childrenIds)) {
            return 0;
        }

        $resource = $this->resourceConnection;

        $stockTable = $resource->getTableName('cataloginventory_stock_item');
        $coreRead = $resource->getConnection('core_read');

        // Check for non-managed stock children
        $manageStock = $this->stockConfiguration->getManageStock();

        $conditions = [];
        $conditions[] = 'product_id IN (' . implode(', ', $childrenIds) . ')';
        if ($manageStock) {
            $conditions[] = '(use_config_manage_stock=0 AND manage_stock=0)';
        } else {
            $conditions[] = '(use_config_manage_stock=1 OR manage_stock=0)';
        }

        $qry = $coreRead->select()
            ->from($stockTable, 'product_id')
            ->where('(' . implode(') AND (', $conditions) . ')')
            ->limit(1);

        if ($coreRead->fetchOne($qry)) {
            return 9999;
        }

        // Sum children qty (here we have all children stock managed
        $conditions = [];
        $conditions[] = 'product_id IN (' . implode(', ', $childrenIds) . ')';
        $conditions[] = 'is_in_stock=1';
        $qry = $coreRead->select()
            ->from($stockTable, 'SUM(qty) as total_qty')
            ->where('(' . implode(') AND (', $conditions) . ')')
            ->limit(1);

        return $coreRead->fetchOne($qry);
    }

    /**
     * Get feed row for entity
     * @param $entity
     * @return array
     */
    protected function getFeedRow($entity)
    {
        /** @var $product Product */
        $product = $entity;

        // Fetch categories
        $categories = [];
        $categoryIds = $product->getCategoryIds();

        if (count($categoryIds)) {
            foreach ($categoryIds as $categoryId) {
                $categories[] = $this->getCategoryName($categoryId);
            }
        } else {
            $categoryIds = [CategorySubFeed::FAKE_CATEGORY_ID];
            $categories = [CategorySubFeed::FAKE_CATEGORY_NAME];
        }

        // Fetch product urls
        $productUrl = $product->getProductUrl();

        // Fetch product visibility
        $isVisible = in_array(
            $product->getVisibility(),
            [
                Product\Visibility::VISIBILITY_BOTH,
                Product\Visibility::VISIBILITY_IN_CATALOG,
            ]
        ) && $product->getStatus();

        // Fetch shippable information
        $shippable = !in_array($product->getTypeId(), [
            Product\Type::TYPE_VIRTUAL,
        ]);

        // Fetch product's availability
        $availability = $product->isSaleable() ? '1' : '0';

        // Fetch product image
        $imageUrl = $this->getImageUrl($product);


        // Fetch related skus
        $related = $product->getRelatedProductCollection();
        $relatedSkus = [];
        foreach ($related as $i) {
            $relatedSkus[] = $i->getSku();
        }

        $qty = $this->getStockQty($product);

        // Fetch product qty
        if (!in_array($product->getTypeId(), [
            Product\Type::TYPE_SIMPLE,
            Product\Type::TYPE_VIRTUAL,
            DownloadableType::TYPE_DOWNLOADABLE,
        ])) {
            if ($qty !== false) {
                $qty = $this->getStockSum($product);
            }
        }

        return [[
            $product->getSku(),
            $categoryIds[0],
            $productUrl,
            $product->getName(),
            $product->getData('short_desccription') ?: $product->getName(),
            $product->getData('description'),
            $this->getVirtualField($product, 'brand'),
            $this->getVirtualField($product, 'modello'),
            $this->getVirtualField($product, 'prezzo_spedizione'),
            $this->toCurrency($this->getPrice($product, 'base_price'), true),
            $this->toCurrency($this->getPrice($product, 'final_price'), true),
            $this->getFeed()->getCurrencyCode(),
            $this->getVirtualField($product, 'info_pagamento'),
            $this->toBoolean($shippable),
            $this->getVirtualField($product, 'tempo_spedizione'),
            $this->getVirtualField($product, 'info_spedizione'),
            $imageUrl,
            $this->getVirtualField($product, 'fine_validita'),
            $availability,
            $qty,
            $this->getVirtualField($product, 'disponibile_dal'),
            intval($this->getVirtualField($product, 'priorita')),
            intval($this->getVirtualField($product, 'condizione')),
            $this->toBoolean($this->getVirtualField($product, 'f_peradulti')),
            $this->toBoolean($isVisible),
            $product->getSku(),
            $this->getVirtualField($product, 'GTIN'),
            $this->getVirtualField($product, 'UPC'),
            $this->getVirtualField($product, 'EAN'),
            $this->getVirtualField($product, 'ISBN'),
            $this->getVirtualField($product, 'ASIN'),
            $this->getVirtualField($product, 'PZN'),
            $this->getVirtualField($product, 'CNET'),
            $this->getVirtualField($product, 'MUZEID'),
            $this->getVirtualField($product, 'MPN'),
            implode('|', $relatedSkus),
            '',
            implode('|', $categories),
        ]];
    }

    /**
     * @param ProductInterface $product
     * @return string
     */
    protected function getImageUrl($product)
    {
        $base = $this->scopeConfig->getValue('adabra_feed/general/base_media_url');
        $store = $this->getFeed()->getStore();

        $productImage = $product->getImage();
        if ($productImage && $productImage != 'no_selection') {
            $imageUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_WEB) . $base . '/catalog/product' . $productImage;
        } else {
            $imageUrl = '';
        }

        return $imageUrl;
    }

    /**
     * @param ProductInterface $product
     * @return float
     */
    protected function getPrice($product, $type)
    {
        $prices = $product->getPriceInfo();
        $basePrice = $prices->getPrice($type)->getAmount();

        return $basePrice->getValue();
    }
}
