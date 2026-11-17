<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json as serializer;
use Magento\SalesRule\Model\Rule;
use Smile\GiftSalesRule\Helper\GiftRule as GiftRuleHelper;

class UpdateGiftRuleActions implements ObserverInterface
{
    public const QUOTE_ATTRIBUTE = 'quote_';

    /**
     * UpdateGiftRuleActions constructor.
     *
     * @param GiftRuleHelper $giftRuleHelper Gift rule helper
     * @param serializer     $serializer     Serializer
     */
    public function __construct(
        protected GiftRuleHelper $giftRuleHelper,
        protected serializer $serializer
    ) {
    }

    /**
     * Remove quote condition if it's gift rule type
     *
     * @param Observer $observer Observer
     */
    public function execute(Observer $observer): void
    {
        /** @var Rule $rule */
        $rule = $observer->getRule();

        if ($this->giftRuleHelper->isGiftRule($rule)) {
            $actions = $rule->getActions()->asArray();
            if (isset($actions['conditions'])) {
                foreach ($actions['conditions'] as $index => $condition) {
                    if (strpos((string) ($condition['attribute'] ?? ''), self::QUOTE_ATTRIBUTE) !== false) {
                        // Remove quote condition for gift rule.
                        unset($actions['conditions'][$index]);
                    }
                }

                $rule->setActionsSerialized($this->serializer->serialize($actions));
            }
        }
    }
}
