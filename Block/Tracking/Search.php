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

namespace Adspray\Adabra\Block\Tracking;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Zend\View\Helper\EscapeJs;

class Search extends Template
{

    /**
     * @var EscapeJs
     */
    private $escapeJs;

    public function __construct(
        EscapeJs $escapeJs,
        Template\Context $context,
        array $data = []
    ) {
    
        $this->escapeJs = $escapeJs;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->getRequest()->getParam('q');
    }

    /**
     * @return string
     */
    public function getEscapedQuery()
    {
        return $this->escapeJs->getEscaper()->escapeJs($this->getQuery());
    }
}
