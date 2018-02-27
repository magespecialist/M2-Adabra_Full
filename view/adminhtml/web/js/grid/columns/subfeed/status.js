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

define([
    'Magento_Ui/js/grid/columns/column'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html'
        },

        getLabel: function (value) {
            var severity = 'critical';
            var label = 'Unknown';

            if (value[this.index] == 'building') {
                severity = 'major';
                label = 'Building';
            } else if (value[this.index] == 'marked-rebuild') {
                severity = 'minor';
                label = 'On queue';
            } else if (value[this.index] == 'ready') {
                severity = 'notice';
                label = 'Ready';
            }

            return '<span class="grid-severity-' + severity + '"><span>' + label + '</span></span>';
        }
    });
});
