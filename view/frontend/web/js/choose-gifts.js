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
    'mage/translate',
    'jquery-ui-modules/widget'
], function ($, $t) {
    'use strict';

    $.widget('mage.smileChooseGifts', {
        options: {
            nbProductToOffer: 0,
            formId: "",
            messageFadeOutDelay: 5000
        },

        qtyInputs: {},
        form: {},
        messageErrorBlock: {},

        /**
         * Initialize the widget.
         * @private
         */
        _create: function () {
            this.qtyInputs = this.element.find('input.qty');
            this.qtyInputs.on('touch click change blur', this.onQtyChange.bind(this));
            this.form = this.element.find(this.options.formId);
            this.validateButton = this.element.find('.validate-choose-gifts');
            this.validateButton.on('click touch', this.onFormSubmit.bind(this));
            this.messageErrorBlock = this.element.find('.messages .message-error');
        },

        /**
         * Method run when the quantity input changes. We add/remove 'active' class to the product item.
         * @param e event
         */
        onQtyChange: function (e) {
            let currentQtyInput = $(e.currentTarget),
                currentProductItem = currentQtyInput.closest('.product-item-info'),
                currentValue = parseInt(currentQtyInput.val() || 0);

            if (currentValue < 1 && !currentQtyInput.is(':focus')) {
                currentProductItem.removeClass('active');
            } else {
                currentProductItem.addClass('active');
            }
        },

        /**
         * Method run when the form is submitted. Add a validation before submitting the form.
         * @param e event
         */
        onFormSubmit: function (e) {
            e.preventDefault();
            if (this.validateForm()) {
                this.form.submit();
            }
        },

        /**
         * Get the number of product to offer.
         * @returns {number}
         */
        getProductToOfferNumber: function () {
            return this.options.nbProductToOffer;
        },

        /**
         * Get the number of gifts asked.
         * @returns {number}
         */
        getAskedGiftsNumber: function () {
            let giftsNumber = 0, i;
            for (i=0; i<this.qtyInputs.length; i++) {
                giftsNumber += (parseInt(this.qtyInputs[i].value || 0));
            }

            return giftsNumber;
        },

        /**
         * Check before submitting the form.
         * @returns {boolean}
         */
        validateForm: function () {
            // Check if the number of gifts asked is not too high.
            if (this.getAskedGiftsNumber() > this.getProductToOfferNumber()) {
                this.showErrorMessage($t('You entered too many gifts. You can only select %1 gifts.').replace('%1', this.getProductToOfferNumber()));
                return false;
            }

            // Check if all options of configurable items chosen are not empty.
            if (!this.checkConfigurableItems()) {
                this.showErrorMessage($t('You need to choose options for your gift.'));
                return false;
            }

            return true;
        },

        /**
         * Check configurable items.
         * @returns {boolean}
         */
        checkConfigurableItems: function () {
            let validate = true;
            $.each(this.element.find('.product-item-info.active'), function (index, element) {
                // Swatch check
                let options = $(element).find('.swatch-attribute'),
                    selectedOptions = $(element).find('.swatch-attribute[option-selected]');

                if (options.length > 0 && options.length !== selectedOptions.length) {
                    validate = false;
                    return;
                }

                // Configurable Check
                $.each($(element).find('.super-attribute-select'), function (index, select) {
                    if (!$(select).val()) {
                        validate = false;
                        return;
                    }
                });
            });

            return validate;
        },

        /**
         * Show error message.
         * @param messageText
         */
        showErrorMessage: function (messageText) {
            this.messageErrorBlock.find('.message-text').text(messageText);
            this.messageErrorBlock.show();
            this.element.closest('.modal-content').animate({ scrollTop: 0 }, "slow");
            setTimeout(function () {
                this.messageErrorBlock.fadeOut();
            }.bind(this), this.options.messageFadeOutDelay);
        }
    });

    return $.mage.smileChooseGifts;
});
