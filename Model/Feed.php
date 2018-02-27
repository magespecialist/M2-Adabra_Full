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

namespace Adspray\Adabra\Model;

use Adspray\Adabra\Api\Data\FeedInterface;
use Adspray\Adabra\Api\Data\SubFeedInterface;
use Adspray\Adabra\Model\Source\SubFeedType;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

class Feed extends AbstractModel implements FeedInterface
{
    protected $subFeedType;
    protected $objectManager;
    protected $storeManager;

    protected $subFeedsRegistry = [];

    public function __construct(
        Context $context,
        Registry $registry,
        SubFeedType $subFeedType,
        StoreManagerInterface $storeManager,
        ObjectManagerInterface $objectManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->subFeedType = $subFeedType;
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
    }

    protected function _construct()
    {
        $this->_init('Adspray\Adabra\Model\ResourceModel\Feed');
    }

    /**
     * Get feed store
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getStore()
    {
        return $this->storeManager->getStore($this->getData('store_id'));
    }

    /**
     * Get feed website
     * @return \Magento\Store\Api\Data\WebsiteInterface
     */
    public function getWebsite()
    {
        return $this->storeManager->getWebsite($this->getStore()->getWebsiteId());
    }

    /**
     * Get current currency code
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->getData('currency');
    }

    /**
     * Change build status
     * @param $feedType
     * @param $status
     * @return $this
     */
    public function changeBuildStatus($feedType, $status)
    {
        $this->getResource()->changeBuildStatus($this->getId(), $feedType, $status);
        return $this;
    }

    /**
     * Return if subfeed type is valid
     * @param $type
     * @return bool
     */
    public function isValidSubFeedType($type)
    {
        $types = $this->subFeedType->toArray();
        return in_array($type, $types);
    }

    /**
     * Throws an exception if sub type is not valid
     * @param $type
     * @return void
     * @throws LocalizedException
     */
    public function checkSubFeedType($type)
    {
        if (!$this->isValidSubFeedType($type)) {
            throw new LocalizedException(__('Unknown sub-feed type '.$type));
        }
    }

    /**
     * Get a sub-feed instance
     * @param $type
     * @return SubFeedInterface|null
     * @throws LocalizedException
     */
    public function getSubFeedInstance($type)
    {
        $this->checkSubFeedType($type);

        if (!isset($this->subFeedsRegistry[$type])) {
            $subFeed = $this->objectManager->create('Adspray\\Adabra\\Model\\SubFeed\\' . ucfirst($type) . 'SubFeed');
            $subFeed->setFeed($this);

            $this->subFeedsRegistry[$type] = $subFeed;
        }

        return $this->subFeedsRegistry[$type];
    }

    /**
     * Get feed code
     * @param string $scope
     * @return string
     */
    public function getCode($scope)
    {
        if ($scope == 'website') {
            return strtolower($this->getStore()->getWebsite()->getCode());
        }

        return strtolower(implode('_', [
            $this->getStore()->getWebsite()->getCode(),
            $this->getStore()->getCode(),
            $this->getCurrencyCode()
        ]));
    }

    /**
     * Export feed
     * @param string $type
     * @return $this
     */
    public function export($type)
    {
        $subFeed = $this->getSubFeedInstance($type);
        $subFeed->export();

        return $this;
    }

    /**
     * Get current build status
     * @param $subFeedType
     * @return string
     */
    public function getBuildStatus($subFeedType)
    {
        return $this->getData('status_' . $subFeedType);
    }

    /**
     * Rebuild all feeds
     * @return void
     */
    public function rebuild()
    {
        $types = $this->subFeedType->toArray();
        foreach ($types as $type) {
            $this->changeBuildStatus($type, SubFeedInterface::STATUS_MARKED_REBUILD);
        }
    }
}
