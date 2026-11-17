<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddGiftRuleOption implements ObserverInterface
{
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

        $giftRuleId = $buyRequest->getData('gift_rule');
        if ($giftRuleId) {
            $transport = $observer->getEvent()->getTransport();
            $transport->options['gift_rule'] = $giftRuleId;
        }
    }
}
