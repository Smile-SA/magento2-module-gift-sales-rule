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
 * @copyright 2021 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\GiftSalesRule\Plugin\Checkout\Model;

use Magento\Checkout\Model\Session as Subject;
use Magento\Quote\Model\Quote;
use Magento\SalesRule\Model\Data\Rule as DataRule;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\RuleRepository;
use Smile\GiftSalesRule\Api\Data\GiftRuleInterface;
use Smile\GiftSalesRule\Helper\GiftRule;

/**
 * Plugin Session.
 *
 * @author    Pierre Le Maguer <pilem@smile.fr>
 * @copyright 2021 Smile
 */
class Session
{
    /**
     * @var RuleRepository
     */
    protected $ruleRepository;

    /**
     * @var GiftRule
     */
    protected $giftRuleHelper;

    /**
     * Session constructor.
     *
     * @param RuleRepository $ruleRepository Rule repository
     * @param GiftRule       $giftRuleHelper Gift rule helper
     */
    public function __construct(
        RuleRepository $ruleRepository,
        GiftRule $giftRuleHelper
    ) {
        $this->ruleRepository = $ruleRepository;
        $this->giftRuleHelper = $giftRuleHelper;
    }

    /**
     * Set checkout session data after loading customer quote.
     *
     * @param Subject $checkoutSession Checkout session
     * @param Subject $result          Checkout session
     * @return Subject
     */
    public function afterLoadCustomerQuote(
        Subject $checkoutSession,
        Subject $result
    ) {
        $quote = $checkoutSession->getQuote();
        $appliedRuleIds = array_filter(explode(',', $quote->getAppliedRuleIds()));
        $rules = [];
        foreach ($appliedRuleIds as $ruleId) {
            try {
                $rule = $this->ruleRepository->getById($ruleId);
                if (!$this->giftRuleHelper->isGiftRule($rule)) {
                    continue;
                }

                $rules[$rule->getRuleId()] = $this->getGiftRuleCode($rule, $quote);
            } catch (\Exception $exception) {
                continue;
            }
        }

        if (count($rules)) {
            $checkoutSession->setGiftRules($rules);
        }

        return $result;
    }

    /**
     * Get gift rule code.
     *
     * @param Rule|DataRule $rule  Rule
     * @param Quote         $quote Quote
     * @return string
     */
    protected function getGiftRuleCode($rule, $quote)
    {
        $ruleCode = $rule->getRuleId();
        if ($rule->getSimpleAction() === GiftRuleInterface::OFFER_PRODUCT_PER_PRICE_RANGE) {
            $shippingAddress = $quote->getShippingAddress();
            $total = $shippingAddress->getBaseSubtotalTotalInclTax()
                ?: $shippingAddress->getOrigData('base_subtotal_total_incl_tax');
            $ruleCode = $rule->getRuleId() . '_' . $this->giftRuleHelper->getRange(
                $total,
                $rule->getExtensionAttributes()['gift_rule'][GiftRuleInterface::PRICE_RANGE]
            );
        }

        return (string) $ruleCode;
    }
}
