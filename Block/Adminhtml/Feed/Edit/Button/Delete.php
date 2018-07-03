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

namespace Adspray\Adabra\Block\Adminhtml\Feed\Edit\Button;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Context;

class Delete extends Generic
{

    public function __construct(
        Context $context,
        Registry $registry
    ) {
        parent::__construct($context, $registry);
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        $id = $this->context->getRequestParam('id');
        if (!empty($id)) {
            $data = [
                'label' => __('Delete Feed'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' .
                        __('Are you sure you want to do this?') . '\', 
                        \'' . $this->getUrl('*/*/delete',
                            ['id' => $id]) .
                    '\')',
                'sort_order' => 20,
            ];
        }
        return $data;
    }
}
