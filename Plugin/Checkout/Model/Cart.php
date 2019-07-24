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
namespace Smile\GiftSalesRule\Plugin\Checkout\Model;

use Magento\Checkout\Model\Cart as Subject;
use Magento\Sales\Model\Order\Item;

/**
 * Class Cart
 *
 * @author    Pierre Le Maguer <pilem@smile.fr>
 * @copyright 2019 Smile
 */
class Cart
{
    /**
     * Avoid to add a gift product when adding an order item.
     *
     * @param Subject  $subject   Subject
     * @param \Closure $proceed   Parent method
     * @param Item     $orderItem Order item
     * @param bool     $qtyFlag   Quantity flag
     *
     * @return Subject
     */
    public function aroundAddOrderItem(
        Subject $subject,
        \Closure $proceed,
        $orderItem,
        $qtyFlag = null
    ) {
        $info = $orderItem->getProductOptionByCode('info_buyRequest');
        if (isset($info['gift_rule']) && $info['gift_rule']) {
            return $subject;
        }

        return $proceed($orderItem, $qtyFlag);
    }
}
