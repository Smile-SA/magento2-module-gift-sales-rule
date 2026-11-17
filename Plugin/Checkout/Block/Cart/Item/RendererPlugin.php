<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\Plugin\Checkout\Block\Cart\Item;

use Magento\Checkout\Block\Cart\Item\Renderer;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Smile\GiftSalesRule\Helper\GiftRule;

class RendererPlugin
{
    protected array $actionsBlockToRemove = [
        'checkout.cart.item.renderers.default.actions.edit',
        'checkout.cart.item.renderers.simple.actions.edit',
        'checkout.cart.item.renderers.configurable.actions.edit',
    ];

    /**
     * @param GiftRule $giftRuleHelper gift rule helper
     */
    public function __construct(protected GiftRule $giftRuleHelper)
    {
    }

    /**
     * Remove the edit action from the item renderer for gift items.
     *
     * @param Renderer     $subject Subject
     * @param AbstractItem $item    Item
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
