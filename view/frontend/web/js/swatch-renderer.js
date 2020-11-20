/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\GiftSalesRule
 * @author    Pierre Le Maguer <pilem@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

define([
    'jquery',
    'jquery-ui-modules/widget',
    'Magento_Swatches/js/swatch-renderer'
], function ($) {
    'use strict';

    $.widget('mage.smileGiftRulesSwatchRenderer', $.mage.SwatchRenderer, {
        /**
         * Redefine the input by adapting the name and id.
         * @param config
         * @returns {string}
         * @private
         */
        _RenderFormInput: function (config) {
            let productId = this.element.parents('.product-item-details').attr('data-product-id');

            return '<input class="' + this.options.classes.attributeInput + ' super-attribute-select" ' +
                'name="products[' + productId + '][super_attribute][' + config.id + ']" ' +
                'type="text" ' +
                'value="" ' +
                'data-selector="super_attribute[' + config.id + ']" ' +
                'data-validate="{required: true}" ' +
                'aria-required="true" ' +
                'aria-invalid="false">';
        }
    });

    return $.mage.smileGiftRulesSwatchRenderer;
});
