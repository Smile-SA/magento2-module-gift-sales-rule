/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\MultiCoupon
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

var config = {
    map: {
        '*': {
            smileChooseGifts: 'Smile_GiftSalesRule/js/choose-gifts'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/summary/item/details/thumbnail': {
                'Smile_GiftSalesRule/js/view/summary/item/details/thumbnail-mixin': true
            },
            'Magento_Checkout/js/view/summary/cart-items': {
                'Smile_GiftSalesRule/js/view/summary/cart-items-mixin': true
            },
        }
    }
};
