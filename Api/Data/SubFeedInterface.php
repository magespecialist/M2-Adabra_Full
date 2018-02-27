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

interface SubFeedInterface
{
    const STATUS_BUILDING = 'building';
    const STATUS_MARKED_REBUILD = 'marked-rebuild';
    const STATUS_READY = 'ready';

    const TYPE_CATEGORY = 'category';
    const TYPE_PRODUCT = 'product';
    const TYPE_CUSTOMER = 'customer';
    const TYPE_ORDER = 'order';

    /**
     * Export feed
     * @return void
     */
    public function export();

    /**
     * Set parent's feed
     * @param FeedInterface $feed
     * @return $this
     */
    public function setFeed(FeedInterface $feed);

    /**
     * Get parent's feed
     * @return FeedInterface
     */
    public function getFeed();

    /**
     * Get current build status
     * @return string
     */
    public function getBuildStatus();

    /**
     * Get sub-feed type
     * @return string
     */
    public function getType();

    /**
     * Get exported filename
     * @param $chunked
     * @param bool $compressed
     * @return string
     */
    public function getExportFile($chunked, $compressed = false);

    /**
     * Get feed content
     * @param $compress
     * @return string
     */
    public function getFeedContent($compress = false);
}
