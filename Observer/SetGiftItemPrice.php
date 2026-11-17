<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Smile\GiftSalesRule\Helper\GiftRule;

class SetGiftItemPrice implements ObserverInterface
{
    /**
     * @param GiftRule $giftRuleHelper gift rule helper
     */
    public function __construct(protected GiftRule $giftRuleHelper)
    {
    }

    /**
     * Change price for gift product
     *
     * @param Observer $observer Observer
     */
    public function execute(Observer $observer): void
    {
        /** @var \Magento\Quote\Model\Quote\Item $item */
        $item = $observer->getEvent()->getData('quote_item');
        if ($this->giftRuleHelper->isGiftItem($item)) {
            $item->setCustomPrice(0);
            $item->setOriginalCustomPrice(0);
        }
    }
}
