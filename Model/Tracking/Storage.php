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

namespace Adspray\Adabra\Model\Tracking;

use Magento\Framework\Stdlib\Cookie\CookieMetadata;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Zend\Json\Json;

class Storage implements StorageInterface
{

    const COOKIE_NAME = 'adabra_actions';
    const QUEUE_NAME = 'queue';
    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;
    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    public function __construct(
        CookieMetadataFactory $cookieMetadataFactory,
        CookieManagerInterface $cookieManager
    ) {
    

        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieManager = $cookieManager;
    }

    /**
     * @param string $action
     * @param array $params
     * @return $this
     */
    public function addToQueue($action, $params = [])
    {
        $queue = $this->getQueue();

        $queue[] = compact("action", "params");

        $this->cookieManager->setPublicCookie(static::COOKIE_NAME, Json::encode($queue), $this->getMetadata());

        return $this;
    }

    /**
     * @param bool $clear
     * @return array
     */
    public function getQueue($clear = false)
    {

        $queue = $this->cookieManager->getCookie(static::COOKIE_NAME);

        if ($clear) {
            $this->cookieManager->setPublicCookie(static::COOKIE_NAME, Json::encode([]), $this->getMetadata());
        }

        if (empty($queue)) {
            $queue = [];
        } else {
            $queue = Json::decode($queue, Json::TYPE_ARRAY);
        }

        return $queue;
    }


    /**
     * @return $this
     */
    public function flushAll()
    {
        $this->cookieManager->deleteCookie(static::COOKIE_NAME);

        return $this;
    }

    /**
     * @return \Magento\Framework\Stdlib\Cookie\PublicCookieMetadata
     */
    protected function getMetadata()
    {
        return $this->cookieMetadataFactory->createPublicCookieMetadata([CookieMetadata::KEY_PATH => '/']);
    }
}
