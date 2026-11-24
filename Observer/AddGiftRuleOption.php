<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Smile\GiftSalesRule\Helper\GiftRule as GiftRuleHelper;

class AddGiftRuleOption implements ObserverInterface
{
    /**
     * @param GiftRuleHelper $giftRuleHelper Gift rule helper
     */
    public function __construct(protected GiftRuleHelper $giftRuleHelper)
    {
    }

    /**
     * Add option for gift item
     *
     * @event checkout_cart_save_after
     * @param Observer $observer Observer
     */
    public function execute(Observer $observer): void
    {
        /** @var \Magento\Framework\DataObject $buyRequest */
        $buyRequest = $observer->getEvent()->getBuyRequest();

        // The gift_rule value comes from the buy request, which a visitor controls (e.g. checkout/cart/add):
        // only trust it when the gift rule service is the one adding the product.
        $giftRuleId = $buyRequest->getData('gift_rule');
        if ($giftRuleId && (int) $giftRuleId === $this->giftRuleHelper->getGiftRuleIdBeingAdded()) {
            $transport = $observer->getEvent()->getTransport();
            $transport->options['gift_rule'] = $giftRuleId;
        }
    }
}
