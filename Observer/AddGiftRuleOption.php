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
use Smile\GiftSalesRule\Helper\GiftRule as GiftRuleHelper;

/**
 * Class AddGiftRuleOption
 *
 * @author    Maxime Queneau <maxime.queneau@smile.fr>
 * @copyright 2019 Smile
 */
class AddGiftRuleOption implements ObserverInterface
{
    /**
     * @var GiftRuleHelper
     */
    protected $giftRuleHelper;

    /**
     * @param GiftRuleHelper $giftRuleHelper Gift rule helper
     */
    public function __construct(GiftRuleHelper $giftRuleHelper)
    {
        $this->giftRuleHelper = $giftRuleHelper;
    }

    /**
     * Add option for gift item
     *
     * @event checkout_cart_save_after
     *
     * @param Observer $observer Observer
     */
    public function execute(Observer $observer)
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
