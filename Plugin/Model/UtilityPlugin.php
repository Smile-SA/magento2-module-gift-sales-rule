<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\Plugin\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote\Address;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Utility as Subject;
use Smile\GiftSalesRule\Helper\GiftRule as GiftRuleHelper;

/**
 * Plugin Utility
 *
 * @copyright 2020 Smile
 */
class UtilityPlugin
{
    /**
     * UtilityPlugin constructor.
     *
     * @param GiftRuleHelper $giftRuleHelper Gift rule helper
     */
    public function __construct(
        protected GiftRuleHelper $giftRuleHelper
    ) {
    }

    /**
     * After can process rule: if it's a gift rule, add a custom check.
     *
     * @param Subject $subject Subject
     * @param bool    $result  Result
     * @param Rule    $rule    Model
     * @param Address $address address
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCanProcessRule(
        Subject $subject,
        bool $result,
        Rule $rule,
        Address $address
    ): bool {
        try {
            $isValidGiftRule = !$this->giftRuleHelper->isGiftRule($rule)
                || $this->giftRuleHelper->isValidGiftRule($rule, $address->getQuote());

            return $result && $isValidGiftRule;
        } catch (NoSuchEntityException $noSuchEntityException) {
            return $result;
        }
    }
}
