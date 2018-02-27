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
use Adspray\Adabra\Api\FeedManagerInterface;
use Adspray\Adabra\Helper\Filesystem;
use Adspray\Adabra\Model\ResourceModel\Feed\Collection;
use Adspray\Adabra\Model\ResourceModel\Feed\CollectionFactory as FeedCollectionFactory;
use Adspray\Adabra\Model\Source\SubFeedType;

class FeedManager implements FeedManagerInterface
{
    protected $filesystem;
    protected $feedCollectionFactory;
    protected $subFeedType;
    protected $feed;

    protected $feedCollection = null;

    public function __construct(
        Filesystem $filesystem,
        FeedCollectionFactory $feedCollectionFactory,
        SubFeedType $subFeedType,
        FeedInterface $feed
    ) {
        $this->filesystem = $filesystem;
        $this->feedCollectionFactory = $feedCollectionFactory;
        $this->subFeedType = $subFeedType;
        $this->feed = $feed;
    }

    /**
     * Get active feeds collection
     * @return Collection
     */
    protected function getFeedsToBuildCollection()
    {
        if (is_null($this->feedCollection)) {
            $this->feedCollection = $this->feedCollectionFactory->create();
            $this->feedCollection->filterToBuild();
        }

        return $this->feedCollection;
    }

    /**
     * Get sub feed instance by code
     * @param $code
     * @return null|SubFeedInterface
     */
    public function getSubFeedInstanceByCode($code)
    {
        $feeds = $this->feedCollectionFactory->create();
        $feeds->filterEnabled();

        $types = $this->subFeedType->toArray();

        foreach ($feeds as $feed) {
            foreach ($types as $type) {
                /** @var $feed FeedInterface */
                $subFeed = $feed->getSubFeedInstance($type);

                if ($subFeed->getCode() == $code) {
                    return $subFeed;
                }
            }
        }

        return null;
    }

    /**
     * Export next feed
     * @return void
     */
    public function run()
    {
        if (!$this->filesystem->acquireLock('feed')) {
            return;
        }

        $feedsCollection = $this->getFeedsToBuildCollection();
        if ($feedsCollection->getSize()) {
            $types = $this->subFeedType->toArray();

            // @codingStandardsIgnoreStart
            $feed = $feedsCollection->getFirstItem();
            // @codingStandardsIgnoreEnd

            foreach ($types as $type) {
                /** @var $feed Feed */
                if ($feed->getData('status_'.$type) == SubFeedInterface::STATUS_READY) {
                    continue;
                }

                $feed->export($type);
                break;
            }
        }

        $this->filesystem->releaseLock('feed');
    }

    /**
     * Mark all feeds to rebuild
     * @return void
     */
    public function rebuild()
    {
        $collection = $this->feedCollectionFactory->create();
        foreach ($collection as $feed) {
            $feed->rebuild();
        }
    }
}
