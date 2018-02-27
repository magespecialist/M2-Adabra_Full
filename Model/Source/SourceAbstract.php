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

namespace Adspray\Adabra\Model\Source;

use Magento\Framework\Option\ArrayInterface;

abstract class SourceAbstract implements ArrayInterface
{
    public function toHashArray()
    {
        $return = [];

        $options = $this->toOptionArray();
        foreach ($options as $option) {
            $return[$option['value']] = $option['label'];
        }

        return $return;
    }

    public function toArray()
    {
        return array_keys($this->toHashArray());
    }

    public function toOptionArray()
    {
        $options = $this->getOptionArray();

        $out = [];
        foreach ($options as $idx => $val) {
            $out[] = [
                'value' => $idx,
                'label' => $val,
            ];
        }

        return $out;
    }

    /**
     * Retrieve Option value text
     *
     * @param string $value
     * @return mixed
     */
    public function getOptionText($value)
    {
        $options = $this->getOptionArray();
        if (isset($options[$value])) {
            return $options[$value];
        }

        return '';
    }

    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions()
    {
        return $this->toOptionArray();
    }
}
