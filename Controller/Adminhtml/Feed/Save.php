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
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Adspray\Adabra\Model\ResourceModel\Feed;
use Adspray\Adabra\Model\FeedFactory;

class Save extends Action
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
     * @var DataPersistorInterface
     */
    private $dataPersistor;


    /**
     * Save constructor.
     * @param Action\Context $context
     * @param PageFactory $pageFactory
     * @param DataPersistorInterface $dataPersistor
     * @param Feed $feed
     * @param FeedFactory $feedFactory
     */
    public function __construct(
        Action\Context $context,
        PageFactory $pageFactory,
        DataPersistorInterface $dataPersistor,
        Feed $feed,
        FeedFactory $feedFactory
    ) {
        $this->pageFactory = $pageFactory;

        parent::__construct($context);
        $this->feed = $feed;
        $this->feedFactory = $feedFactory;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            if (empty($data['adabra_feed_id'])) {
                $data['adabra_feed_id'] = null;
            }

            /** @var Feed $model */
            $model = $this->feedFactory->create();

            $id = $this->getRequest()->getParam('adabra_feed_id');
            if (! empty($id)) {
                try {
                    $this->feed->load($model, $id);
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(__('Something went wrong while saving the feed.'));
                    // go to grid
                    return $resultRedirect->setPath('*/*/');
                }
            }

            $model->setData($data);

            try {
                $this->feed->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the feed.'));
                $this->dataPersistor->clear('adabra_feed');
                if ($this->getRequest()->getParam('back')) {
                    // go back to edit form
                    return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
                }
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the feed.'));
            }

            $this->dataPersistor->set('adabra_feed', $data);
            // go back to edit form
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Adspray_Adabra::adabra_feed_save');
    }
}
