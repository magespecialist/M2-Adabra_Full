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

namespace Adspray\Adabra\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Feed extends AbstractDb
{

    /**
     * @var Context
     */
    private $context;
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * Feed constructor.
     * @param Context $context
     * @param TimezoneInterface $timezone
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        TimezoneInterface $timezone,
        string $connectionName = null)
    {
        parent::__construct($context, $connectionName);
        $this->timezone = $timezone;
    }

    protected function _construct()
    {
        $this->_init('adabra_feed', 'adabra_feed_id');
    }

    /**
     * Change build status
     * @param $feedId
     * @param $subFeedType
     * @param $status
     * @return Feed|string
     */
    public function changeBuildStatus($feedId, $subFeedType, $status)
    {
        $today = $this->timezone->date()->format('Y-m-d H:i:s');
        if (!empty($subFeedType)) {
            try {
                $this->getConnection()->update($this->getMainTable(), [
                    'status_' . $subFeedType => $status,
                    'updated_at' => $today
                ], 'adabra_feed_id = ' . (int)$feedId);
            } catch (LocalizedException $e) {
                return $e->getMessage();
            }
        }
        return $this;
    }

}
