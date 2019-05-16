<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\GiftSalesRule
 * @author    Pierre Le Maguer <pilem@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\GiftSalesRule\Plugin\Checkout\Block\Cart\Item;

use Magento\Checkout\Block\Cart\Item\Renderer;
use Magento\Quote\Model\Quote\Item\AbstractItem;

/**
 * Class CombinePlugin
 *
 * @author    Pierre Le Maguer <pilem@smile.fr>
 * @copyright 2019 Smile
 */
class RendererPlugin
{
    protected $actionsBlockToRemove = [
        'checkout.cart.item.renderers.default.actions.edit',
        'checkout.cart.item.renderers.simple.actions.edit',
        'checkout.cart.item.renderers.configurable.actions.edit'
    ];

    /**
     * Remove the edit action from the item renderer for gift items.
     *
     * @param Renderer     $subject
     * @param AbstractItem $item
     *
     * @return array
     */
    public function beforeGetActions(
        Renderer $subject,
        AbstractItem $item
    ) {
        $option = $item->getOptionByCode('option_gift_rule');
        if ($option) {
            $actionsBlock = $subject->getChildBlock('actions');
            if ($actionsBlock) {
                foreach ($this->actionsBlockToRemove as $blockName) {
                    if ($actionsBlock->getChildBlock($blockName)) {
                        $actionsBlock->unsetChild($blockName);
                    }
                }
            }
        }

        return [$item];
    }
}
