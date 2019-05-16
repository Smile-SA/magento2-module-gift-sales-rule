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
use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Class AddGiftRuleOption
 *
 * @author    Maxime Queneau <maxime.queneau@smile.fr>
 * @copyright 2019 Smile
 */
class AddGiftRuleOption implements ObserverInterface
{
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

        if ($giftRuleId = $buyRequest->getData('gift_rule')) {
            $transport = $observer->getEvent()->getTransport();
            $transport->options['gift_rule'] = $giftRuleId;
        }
    }
}
