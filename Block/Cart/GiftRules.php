<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\Block\Cart;

use Magento\Checkout\Model\Cart as CheckoutCart;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Smile\GiftSalesRule\Api\GiftRuleServiceInterface;

class GiftRules extends Template
{
    /**
     * Cart constructor.
     *
     * @param Context                  $context         Context
     * @param GiftRuleServiceInterface $giftRuleService Gift rule service
     * @param CheckoutCart             $cart            Cart
     * @param array                    $data            Data
     */
    public function __construct(
        Context $context,
        protected GiftRuleServiceInterface $giftRuleService,
        protected CheckoutCart $cart,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Get gift rules
     */
    public function getGiftRules(): array
    {
        return $this->giftRuleService->getAvailableGifts($this->cart->getQuote());
    }
}
