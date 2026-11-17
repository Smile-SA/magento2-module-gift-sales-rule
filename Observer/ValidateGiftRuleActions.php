<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\SalesRule\Model\Rule;
use Smile\GiftSalesRule\Helper\GiftRule as GiftRuleHelper;

class ValidateGiftRuleActions implements ObserverInterface
{
    /**
     * ValidateGiftRuleActions constructor.
     *
     * @param GiftRuleHelper $giftRuleHelper Gift rule helper
     */
    public function __construct(protected GiftRuleHelper $giftRuleHelper)
    {
    }

    /**
     * Skip actions validation if the rule is a gift sales rule.
     *
     * @param Observer $observer Observer
     */
    public function execute(Observer $observer): void
    {
        /** @var Rule $rule */
        $rule = $observer->getEvent()->getData('rule');
        /** @var DataObject $skipValidationObject */
        $skipValidationObject = $observer->getEvent()->getData('skip_validation_object');
        $skipValidation = $skipValidationObject->getData('skip_validation');

        if (!$skipValidation) {
            $skipValidation = $this->giftRuleHelper->isGiftRule($rule);
        }

        $skipValidationObject->setData('skip_validation', $skipValidation);
    }
}
