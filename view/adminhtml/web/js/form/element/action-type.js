/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\GiftSalesRule
 * @author    Maxime Queneau <maxime.queneau@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select'
], function ($, _, uiRegistry, select) {
    'use strict';

    return select.extend({

        /**
         * Check if is offer product rule
         *
         * @returns {boolean}
         */
        isOfferProducRule: function () {
            return this.value() == 'offer_product' || this.value() == 'offer_product_per_price_range';
        },

        /**
         * Hide / show fields
         */
        onUpdate: function () {
            this._super();

            this.changeLegend();

            this.updateProductSelect();

            if (!this.isOfferProducRule()) {
                uiRegistry
                    .get('sales_rule_form.sales_rule_form.actions.discount_amount')
                    .show();
                uiRegistry
                    .get('sales_rule_form.sales_rule_form.actions.discount_qty')
                    .show();
                uiRegistry
                    .get('sales_rule_form.sales_rule_form.actions.discount_step')
                    .show();
                uiRegistry
                    .get('sales_rule_form.sales_rule_form.actions.apply_to_shipping')
                    .show();
                uiRegistry
                    .get('sales_rule_form.sales_rule_form.actions.maximum_number_product')
                    .hide();
                uiRegistry
                    .get('sales_rule_form.sales_rule_form.actions.price_range')
                    .hide();
            }
        },

        /**
         * Change legend of product actions fieldset
         */
        changeLegend: function () {
            var legend = $('.fieldset[id^="sales_rule_formrule_actions_fieldset_"] legend');

            if (this.isOfferProducRule()) {
                legend.text(_('Select gift product:'));
            } else {
                legend.text(_('Apply the rule only to cart items matching the following conditions (leave blank for all items).'));
            }
        },

        /**
         * Display or not "Cart Item Attribute" in product select
         */
        updateProductSelect: function () {
            var optgroup = $("optgroup[label='Cart Item Attribute']");

            if (this.isOfferProducRule()) {
                optgroup.hide();
            } else {
                optgroup.show();
            }
        }
    });
});
