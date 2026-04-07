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
    'jquery'
], function ($) {
        'use strict';

        return function (Component) {
            return Component.extend({
                updateCheckoutConfigImageData: function (item) {
                    if (item.hasOwnProperty('extension_attributes') && item.extension_attributes.hasOwnProperty('gift_rule')) {
                        let smileGiftSalesRuleData = item.extension_attributes.gift_rule;
                        window.checkoutConfig.imageData[item.item_id] = {
                            alt   : smileGiftSalesRuleData.image_alt,
                            height: smileGiftSalesRuleData.image_height,
                            src   : smileGiftSalesRuleData.image_src,
                            width : smileGiftSalesRuleData.image_width
                        };
                    }
                },

                getSrc: function (item) {
                    if (!this.imageData.hasOwnProperty(item.item_id)) {
                        this.updateCheckoutConfigImageData(item);
                    }

                    return this._super();
                },

                getWidth: function (item) {
                    if (!this.imageData.hasOwnProperty(item.item_id)) {
                        this.updateCheckoutConfigImageData(item);
                    }

                    return this._super();
                },

                getHeight: function (item) {
                    if (!this.imageData.hasOwnProperty(item.item_id)) {
                        this.updateCheckoutConfigImageData(item);
                    }

                    return this._super();
                },

                getAlt: function (item) {
                    if (!this.imageData.hasOwnProperty(item.item_id)) {
                        this.updateCheckoutConfigImageData(item);
                    }

                    return this._super();
                }
            });
        };
    }
);
