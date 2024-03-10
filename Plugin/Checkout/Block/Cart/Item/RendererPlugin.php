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
use Smile\GiftSalesRule\Helper\GiftRule;

/**
 * Class RendererPlugin
 *
 * @author    Pierre Le Maguer <pilem@smile.fr>
 * @copyright 2019 Smile
 */
class RendererPlugin
{
    /**
     * @var GiftRule
     */
    protected $giftRuleHelper;

    /**
     * @var array
     */
    protected $actionsBlockToRemove = [
        'checkout.cart.item.renderers.default.actions.edit',
        'checkout.cart.item.renderers.simple.actions.edit',
        'checkout.cart.item.renderers.configurable.actions.edit',
    ];

    /**
     * @param GiftRule $giftRuleHelper gift rule helper
     */
    public function __construct(GiftRule $giftRuleHelper)
    {
        $this->giftRuleHelper = $giftRuleHelper;
    }

    /**
     * Remove the edit action from the item renderer for gift items.
     *
     * @param Renderer     $subject Subject
     * @param AbstractItem $item    Item
     *
     * @return array
     */
    public function beforeGetActions(
        Renderer $subject,
        AbstractItem $item
    ): array {
        if ($this->giftRuleHelper->isGiftItem($item)) {
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
