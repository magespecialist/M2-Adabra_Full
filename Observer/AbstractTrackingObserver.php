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
 * @copyright  Copyright (c) 2017 IDEALIAGroup srl (http://www.idealiagroup.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Adspray\Adabra\Observer;

use Adspray\Adabra\Helper\Data;
use Adspray\Adabra\Model\Tracking;
use Magento\Framework\Event\ObserverInterface;

abstract class AbstractTrackingObserver implements ObserverInterface
{

    /**
     * @var Tracking
     */
    protected $tracking;
    /**
     * @var Data
     */
    protected $helper;

    public function __construct(
        Tracking $tracking,
        Data $helper
    ) {
    
        $this->tracking = $tracking;
        $this->helper = $helper;
    }


    /**
     * @return bool
     */
    protected function isEnabled()
    {
        return $this->helper->getTrackingEnabled();
    }
}
