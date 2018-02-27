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
namespace Adspray\Adabra\Controller\Feed;

use Adspray\Adabra\Api\FeedManagerInterface;
use Adspray\Adabra\Helper\Data as DataHelper;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\HTTP\Authentication;
use Magento\Framework\Filesystem\Io\File;

class Get extends Action
{
    protected $dataHelper;
    protected $authentication;
    protected $feedManager;
    protected $file;

    public function __construct(
        Context $context,
        DataHelper $dataHelper,
        Authentication $authentication,
        FeedManagerInterface $feedManager,
        File $file
    ) {
        parent::__construct($context);

        $this->dataHelper = $dataHelper;
        $this->authentication = $authentication;
        $this->feedManager = $feedManager;
        $this->file = $file;
    }

    public function execute()
    {
        if (!$this->dataHelper->isHttpEnabled()) {
            return $this->_forward('noroute');
        }

        list($user, $pass) = $this->authentication->getCredentials();

        if (($user != $this->dataHelper->getHttpAuthUser()) || ($pass != $this->dataHelper->getHttpAuthPassword())) {
            $this->authentication->setAuthenticationFailed(__('Adabra Feed'));
            return;
        }

        $code = $this->getRequest()->getParam('code');
        $subFeedInstance = $this->feedManager->getSubFeedInstanceByCode($code);

        if (!$subFeedInstance || !$subFeedInstance->getFeed()->getEnabled()) {
            return $this->_forward('noroute');
        }

        $compress = $this->dataHelper->getCompress();
        $fileName = $subFeedInstance->getExportFile(false, $compress);

        $pathInfo = $this->file->getPathInfo($fileName);

        $this->getResponse()->setHeader('Content-Type', $compress ? 'application/x-gzip' : 'text/csv');
        $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="' . $pathInfo['basename'] . '"');
        $this->getResponse()->setBody($subFeedInstance->getFeedContent($compress));
    }
}
