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

namespace Adspray\Adabra\Model\ResourceModel\Feed;

use Adspray\Adabra\Api\Data\SubFeedInterface;
use Adspray\Adabra\Model\Source\SubFeedType;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Psr\Log\LoggerInterface;

class Collection extends AbstractCollection
{
    protected $subFeedType;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        SubFeedType $subFeedType,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);

        $this->subFeedType = $subFeedType;
    }

    protected function _construct()
    {
        $this->_init(
            'Adspray\Adabra\Model\Feed',
            'Adspray\Adabra\Model\ResourceModel\Feed'
        );
    }

    /**
     * Filter collection to obtain only feeds to build
     * @return $this
     */
    public function filterEnabled()
    {
        $this->addFieldToFilter('enabled', 1);
        return $this;
    }

    /**
     * Filter collection to obtain only feeds to build
     * @return $this
     */
    public function filterToBuild()
    {
        $this->filterEnabled();

        $types = $this->subFeedType->toArray();
        $conditions = [];
        foreach ($types as $type) {
            $conditions[] =
                '(status_'.$type.' != '.$this->getConnection()->quote(SubFeedInterface::STATUS_READY).')';
        }

        $this->getSelect()->where(implode(' OR ', $conditions));
        return $this;
    }
}
