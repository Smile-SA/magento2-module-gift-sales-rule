<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\Plugin\Weee\Model\Total\Quote;

use Closure;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Weee\Model\Total\Quote\Weee as Subject;
use Smile\GiftSalesRule\Helper\GiftRule as GiftRuleHelper;

/**
 * Plugin Weee
 *
 * @copyright 2020 Smile
 */
class WeeePlugin
{
    /**
     * WeeePlugin constructor.
     *
     * @param GiftRuleHelper $giftRuleHelper Gift rule helper
     */
    public function __construct(
        protected GiftRuleHelper $giftRuleHelper
    ) {
    }

    /**
     * Around collect weee: Don't collect for gift items.
     *
     * @param Subject                     $subject            Subject
     * @param Closure                     $proceed            Closure
     * @param Quote                       $quote              Quote
     * @param ShippingAssignmentInterface $shippingAssignment Shipping assignment
     * @param Total                       $total              Total
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCollect(
        Subject $subject,
        Closure $proceed,
        Quote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        Total $total
    ): Subject {
        $items = $shippingAssignment->getItems();
        $itemsForCollect = [];
        /** @var AbstractItem $item */
        foreach ($items as $key => $item) {
            if (!$this->giftRuleHelper->isGiftItem($item)) {
                $itemsForCollect[$key] = $item;
            }
        }
        /** @var CartItemInterface[] $itemsForCollect */
        $shippingAssignment->setItems($itemsForCollect);
        $result = $proceed($quote, $shippingAssignment, $total);
        $shippingAssignment->setItems($items);

        return $result;
    }
}
