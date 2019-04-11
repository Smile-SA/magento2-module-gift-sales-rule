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
    'uiRegistry',
    'Magento_Ui/js/form/components/fieldset',
    'jquery'
], function (uiRegistry, fieldset, $) {
    'use strict';

    return fieldset.extend({

        /**
         * Check if is offer product rule
         *
         * @returns {boolean}
         */
        isOfferProducRule: function () {
            var value = uiRegistry
                .get('sales_rule_form.sales_rule_form.actions.simple_action')
                .value();
            return value == 'offer_product' || value == 'offer_product_per_price_range';
        },

        /**
         * Sets 'opened' flag to true.
         *
         * @returns {Collapsible} Chainable.
         */
        open: function () {
            this._super();

            setTimeout(this.updateProductSelect, 2000, this);

            return this;
        },

        /**
         * Display or not "Cart Item Attribute" in product select
         */
        updateProductSelect: function (fieldset) {
            var optgroup = $("optgroup[label='Cart Item Attribute']");

            if (optgroup) {
                if (fieldset.isOfferProducRule()) {
                    optgroup.hide();
                } else {
                    optgroup.show();
                }
            }
        }
    });
});
