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
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem\Io\File;
use Magento\Directory\Helper\Data as DirectoryHelperData;
use Magento\Framework\Stdlib\DateTime\DateTime;

class CategorySubFeed extends AbstractSubFeed implements SubFeedInterface
{
    const FAKE_CATEGORY_NAME = 'NONE';
    const FAKE_CATEGORY_ID = 'none';

    protected $collectionFactory;

    protected $type = 'category';
    protected $exportName = 'category';

    public function __construct(
        File $file,
        Csv $csv,
        Filesystem $filesystem,
        DataHelper $dataHelper,
        FtpHelper $ftpHelper,
        DirectoryHelperData $directoryHelperData,
        CollectionFactory $collectionFactory,
        DateTime $dateTime
    ) {
        parent::__construct($file, $csv, $filesystem, $dataHelper, $ftpHelper, $directoryHelperData, $dateTime);

        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get headers
     * @return array
     */
    protected function getHeaders()
    {
        return [
            'category_id',
            'parent_category_id',
            'category_name',
            'category_description',
            'active',
        ];
    }

    /**
     * Get virtual rows
     * @return array
     */
    protected function getVirtualRows()
    {
        return [[
            self::FAKE_CATEGORY_ID,
            0,
            self::FAKE_CATEGORY_NAME,
            '',
            $this->toBoolean(false),
        ]];
    }

    /**
     * Prepare feed collection
     * @return void
     */
    protected function prepareCollection()
    {
        $this->collection = $this->collectionFactory->create();
        $this->collection
            ->addAttributeToSelect('*')
            ->addUrlRewriteToResult()
            ->setStoreId($this->getFeed()->getStore()->getId());
    }

    /**
     * Get feed row for entity
     * @param $entity
     * @return array
     */
    protected function getFeedRow($entity)
    {
        /** @var $category Category */
        $category = $entity;

        return [[
            $category->getId(),
            $category->getParentId(),
            $category->getName(),
            $category->getData('description'),
            $this->toBoolean($category->getIsActive()),
        ]];
    }
}
