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
 * @copyright 2020 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\GiftSalesRule\Plugin\Weee\Model\Total\Quote;

use Closure;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Weee\Model\Total\Quote\Weee as Subject;
use Smile\GiftSalesRule\Helper\GiftRule as GiftRuleHelper;

/**
 * Plugin Weee
 *
 * @author    Pierre Le Maguer <pilem@smile.fr>
 * @copyright 2020 Smile
 */
class WeeePlugin
{
    /**
     * @var GiftRuleHelper
     */
    protected $giftRuleHelper;

    /**
     * WeeePlugin constructor.
     *
     * @param GiftRuleHelper $giftRuleHelper Gift rule helper
     */
    public function __construct(
        GiftRuleHelper $giftRuleHelper
    ) {
        $this->giftRuleHelper = $giftRuleHelper;
    }

    /**
     * Around collect weee: Don't collect for gift items.
     *
     * @param Subject                     $subject            Subject
     * @param Closure                     $proceed            Closure
     * @param Quote                       $quote              Quote
     * @param ShippingAssignmentInterface $shippingAssignment Shipping assignment
     * @param Total                       $total              Total
     * @return Subject
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
        foreach ($items as $key => $item) {
            if (!$this->giftRuleHelper->isGiftItem($item)) {
                $itemsForCollect[$key] = $item;
            }
        }
        $shippingAssignment->setItems($itemsForCollect);
        $result = $proceed($quote, $shippingAssignment, $total);
        $shippingAssignment->setItems($items);

        return $result;
    }
}
