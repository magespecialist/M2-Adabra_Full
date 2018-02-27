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

namespace Adspray\Adabra\Helper;

use Adspray\Adabra\Api\Data\FeedInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Data
{
    const XML_PATH_BATCH_SIZE = 'adabra_feed/batch_size';
    const XML_PATH_ORDER_STATES = 'adabra_feed/order/states';

    const XML_PATH_GENERAL_CRON = 'adabra_feed/general/use_cron';
    const XML_PATH_GENERAL_COMPRESS = 'adabra_feed/general/compress';
    const XML_PATH_GENERAL_REBUILD_TIME = 'adabra_feed/general/rebuild_time';

    const XML_PATH_HTTP_ENABLED = 'adabra_feed/http/enabled';
    const XML_PATH_HTTP_USER = 'adabra_feed/http/user';
    const XML_PATH_HTTP_PASS = 'adabra_feed/http/pass';

    const XML_PATH_FTP_ENABLED = 'adabra_feed/ftp/enabled';
    const XML_PATH_FTP_USER = 'adabra_feed/ftp/user';
    const XML_PATH_FTP_PASS = 'adabra_feed/ftp/pass';
    const XML_PATH_FTP_HOST = 'adabra_feed/ftp/host';
    const XML_PATH_FTP_PATH = 'adabra_feed/ftp/path';
    const XML_PATH_FTP_PORT = 'adabra_feed/ftp/port';
    const XML_PATH_FTP_SSL = 'adabra_feed/ftp/ssl';
    const XML_PATH_FTP_PASSIVE = 'adabra_feed/ftp/passive';

    const XML_PATH_TRACKING_ENABLED = 'adabra_tracking/general/enabled';
    const XML_PATH_TRACKING_URL = 'adabra_tracking/general/tracking_url';
    const XML_PATH_SITE_ID = 'adabra_tracking/general/site_id';
    const XML_PATH_CATALOG_ID = 'adabra_tracking/general/catalog_id';

    protected $scopeConfig;
    protected $feed;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        FeedInterface $feed
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->feed = $feed;
    }

    /**
     * Get products batch size
     * @return bool
     */
    public function getCompress()
    {
        return (bool) $this->scopeConfig->getValue(static::XML_PATH_GENERAL_COMPRESS);
    }

    /**
     * Get batch size for a sub-feed
     * @param $subFeedType
     * @return int
     */
    public function getBatchSize($subFeedType)
    {
        $this->feed->checkSubFeedType($subFeedType);
        $xmlPath = static::XML_PATH_BATCH_SIZE.'/'.$subFeedType;

        return $this->scopeConfig->getValue($xmlPath);
    }

    /**
     * Return true if batch is enabled for a sub-feed
     * @param $subFeedType
     * @return bool
     */
    public function isBatchEnabled($subFeedType)
    {
        return $this->getBatchSize($subFeedType) > 0;
    }

    /**
     * Get order states for export
     * @return array
     */
    public function getOrderStates()
    {
        return explode(',', $this->scopeConfig->getValue(static::XML_PATH_ORDER_STATES));
    }

    /**
     * Is http download enabled
     * @return bool
     */
    public function isHttpEnabled()
    {
        if (!$this->scopeConfig->getValue(static::XML_PATH_HTTP_ENABLED)) {
            return false;
        }

        return ($this->getHttpAuthUser() && $this->getHttpAuthPassword());
    }

    /**
     * Get auth username
     * @return string
     */
    public function getHttpAuthUser()
    {
        return trim($this->scopeConfig->getValue(static::XML_PATH_HTTP_USER));
    }

    /**
     * Get auth password
     * @return string
     */
    public function getHttpAuthPassword()
    {
        return trim($this->scopeConfig->getValue(static::XML_PATH_HTTP_PASS));
    }

    /**
     * Get FTP user
     * @return string
     */
    public function getFtpUser()
    {
        return trim($this->scopeConfig->getValue(static::XML_PATH_FTP_USER));
    }

    /**
     * Get FTP pass
     * @return string
     */
    public function getFtpPass()
    {
        return trim($this->scopeConfig->getValue(static::XML_PATH_FTP_PASS));
    }

    /**
     * Get FTP path
     * @return string
     */
    public function getFtpPath()
    {
        return trim($this->scopeConfig->getValue(static::XML_PATH_FTP_PATH));
    }

    /**
     * Get FTP host
     * @return string
     */
    public function getFtpHost()
    {
        return trim($this->scopeConfig->getValue(static::XML_PATH_FTP_HOST));
    }

    /**
     * Get FTP port
     * @return int
     */
    public function getFtpPort()
    {
        return intval(trim($this->scopeConfig->getValue(static::XML_PATH_FTP_PORT))) ?: 21;
    }

    /**
     * Get SSL mode
     * @return bool
     */
    public function getFtpSsl()
    {
        return (bool) $this->scopeConfig->getValue(static::XML_PATH_FTP_SSL);
    }

    /**
     * Get passive mode
     * @return bool
     */
    public function getFtpPassive()
    {
        return (bool) $this->scopeConfig->getValue(static::XML_PATH_FTP_PASSIVE);
    }

    /**
     * Is ftp enabled
     * @return bool
     */
    public function isFtpEnabled()
    {
        if (!$this->scopeConfig->getValue(static::XML_PATH_FTP_ENABLED)) {
            return false;
        }

        return ($this->getFtpUser() && $this->getFtpPass() && $this->getFtpHost());
    }

    /**
     * Tracking enabled
     * @return bool
     */
    public function getTrackingEnabled()
    {
        return (bool) $this->scopeConfig->getValue(static::XML_PATH_TRACKING_ENABLED);
    }

    /**
     * @return null|string
     */
    public function getTrackingUrl()
    {
        return $this->scopeConfig->getValue(static::XML_PATH_TRACKING_URL);
    }

    /**
     * @return null|string
     */
    public function getSiteId()
    {
        return $this->scopeConfig->getValue(static::XML_PATH_SITE_ID);
    }

    /**
     * @return null|string
     */
    public function getCatalogId()
    {
        return $this->scopeConfig->getValue(static::XML_PATH_CATALOG_ID);
    }
}
