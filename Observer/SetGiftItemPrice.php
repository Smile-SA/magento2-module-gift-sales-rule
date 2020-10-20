<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\GiftSalesRule
 * @author    Maxime Queneau <maxime.queneau@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\GiftSalesRule\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Smile\GiftSalesRule\Helper\GiftRule;

/**
 * Class SetGiftItemPrice
 *
 * @author    Maxime Queneau <maxime.queneau@smile.fr>
 * @copyright 2019 Smile
 */
class SetGiftItemPrice implements ObserverInterface
{
    /**
     * @var GiftRule
     */
    protected $giftRuleHelper;

    /**
     * @param GiftRule $giftRuleHelper gift rule helper
     */
    public function __construct(GiftRule $giftRuleHelper)
    {
        $this->giftRuleHelper = $giftRuleHelper;
    }

    /**
     * Change price for gift product
     *
     * @param Observer $observer Observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote\Item $item */
        $item = $observer->getEvent()->getData('quote_item');
        if ($this->giftRuleHelper->isGiftItem($item)) {
            $item->setCustomPrice(0);
            $item->setOriginalCustomPrice(0);
        }
    }
}
