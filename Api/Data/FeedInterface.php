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

namespace Adspray\Adabra\Api\Data;

use Magento\Framework\Exception\LocalizedException;

interface FeedInterface
{
    /**
     * Export a sub-feed
     * @param $subFeed
     * @return $this
     */
    public function export($subFeed);

    /**
     * Get current build status
     * @param $subFeedType
     * @return string
     */
    public function getBuildStatus($subFeedType);

    /**
     * Get feed code
     * @param string $scope
     * @return string
     */
    public function getCode($scope);

    /**
     * Get feed store
     * @return \Magento\Store\Api\Data\StoreInterface
     */
    public function getStore();

    /**
     * Get feed website
     * @return \Magento\Store\Api\Data\WebsiteInterface
     */
    public function getWebsite();

    /**
     * Get current currency code
     * @return string
     */
    public function getCurrencyCode();

    /**
     * Return if subfeed type is valid
     * @param $type
     * @return bool
     */
    public function isValidSubFeedType($type);

    /**
     * Throws an exception if sub type is not valid
     * @param $type
     * @return void
     * @throws LocalizedException
     */
    public function checkSubFeedType($type);

    /**
     * Rebuild feed
     * @return void
     */
    public function rebuild();

    /**
     * Change build status
     * @param $subFeedType
     * @param $status
     * @return $this
     */
    public function changeBuildStatus($subFeedType, $status);

    /**
     * Get a sub-feed instance
     * @param $type
     * @return SubFeedInterface|null
     * @throws LocalizedException
     */
    public function getSubFeedInstance($type);
}
