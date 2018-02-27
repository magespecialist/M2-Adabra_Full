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

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Feed extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('adabra_feed', 'adabra_feed_id');
    }

    /**
     * Change build status
     * @param $feedId
     * @param $subFeedType
     * @param $status
     * @return $this
     */
    public function changeBuildStatus($feedId, $subFeedType, $status)
    {
        $this->getConnection()->update($this->getMainTable(), [
            'status_' . $subFeedType => $status
        ], 'adabra_feed_id=' . intval($feedId));

        return $this;
    }
}
