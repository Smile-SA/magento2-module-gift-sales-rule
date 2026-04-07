/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\GiftSalesRule
 * @author    Pierre Le Maguer <pierre.lemaguer7@gmail.com>
 * @copyright 2022 SmileFriend
 * @license   Open Software License ("OSL") v. 3.0
 */

define([
    'jquery',
    'Magento_Checkout/js/model/quote'
], function ($, quote) {
        'use strict';

        return function (Component) {
            return Component.extend({
                getItemsQty: function () {
                    if (parseInt(quote.totals().items_qty) !== parseInt(this.totals.items_qty)) {
                        this.totals = quote.totals();
                    }

                    return this._super();
                }
            });
        };
    }
);
