<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\Plugin\Checkout\Model;

use Closure;
use Magento\Checkout\Model\Cart as Subject;
use Magento\Sales\Model\Order\Item;

class Cart
{
    /**
     * Avoid to add a gift product when adding an order item.
     *
     * @param Subject  $subject   Subject
     * @param \Closure $proceed   Parent method
     * @param Item     $orderItem Order item
     * @param mixed    $qtyFlag   Quantity flag
     */
    public function aroundAddOrderItem(
        Subject $subject,
        Closure $proceed,
        $orderItem,
        $qtyFlag = null
    ): Subject {
        $info = $orderItem->getProductOptionByCode('info_buyRequest');
        if (isset($info['gift_rule']) && $info['gift_rule']) {
            return $subject;
        }

        return $proceed($orderItem, $qtyFlag);
    }
}
