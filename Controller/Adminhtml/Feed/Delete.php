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

namespace Adspray\Adabra\Controller\Adminhtml\Feed;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\PageFactory;
use Adspray\Adabra\Model\ResourceModel\Feed;
use Adspray\Adabra\Model\FeedFactory;

class Delete extends Action
{
    protected $pageFactory;
    /**
     * @var Feed
     */
    private $feed;
    /**
     * @var FeedFactory
     */
    private $feedFactory;


    /**
     * Save constructor.
     * @param Action\Context $context
     * @param PageFactory $pageFactory
     * @param Feed $feed
     * @param FeedFactory $feedFactory
     */
    public function __construct(
        Action\Context $context,
        PageFactory $pageFactory,
        Feed $feed,
        FeedFactory $feedFactory
    ) {
        $this->pageFactory = $pageFactory;

        parent::__construct($context);
        $this->feed = $feed;
        $this->feedFactory = $feedFactory;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface|string
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        if (! empty($id)) {
            try {
                /** @var Feed $model */
                $model = $this->feedFactory->create();
                $this->feed->load($model, $id);
                $this->feed->delete($model);
                $this->messageManager->addSuccessMessage(__('You deleted the feed.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        $this->messageManager->addErrorMessage(__('There aren\'t a feed to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
