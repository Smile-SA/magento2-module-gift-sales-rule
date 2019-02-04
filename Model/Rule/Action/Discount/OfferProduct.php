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
namespace Smile\GiftSalesRule\Model\Rule\Action\Discount;

use Magento\Checkout\Model\Session as checkoutSession;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\AbstractDiscount;
use Magento\SalesRule\Model\Rule\Action\Discount\Data as DiscountData;
use Magento\SalesRule\Model\Rule\Action\Discount\DataFactory;
use Magento\SalesRule\Model\Validator;
use Smile\GiftSalesRule\Helper\Cache as GiftRuleCacheHelper;

/**
 * Class OfferProduct
 *
 * @author    Maxime Queneau <maxime.queneau@smile.fr>
 * @copyright 2019 Smile
 */
class OfferProduct extends AbstractDiscount
{
    /**
     * @var checkoutSession
     */
    protected $checkoutSession;

    /**
     * @var GiftRuleCacheHelper
     */
    protected $giftRuleCacheHelper;

    /**
     * OfferProduct constructor.
     *
     * @param Validator              $validator
     * @param DataFactory            $discountDataFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param checkoutSession        $checkoutSession
     * @param GiftRuleCacheHelper    $giftRuleCacheHelper
     */
    public function __construct(
        Validator $validator,
        DataFactory $discountDataFactory,
        PriceCurrencyInterface $priceCurrency,
        checkoutSession $checkoutSession,
        GiftRuleCacheHelper $giftRuleCacheHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->giftRuleCacheHelper = $giftRuleCacheHelper;

        parent::__construct(
            $validator,
            $discountDataFactory,
            $priceCurrency
        );
    }

    /**
     * @param Rule         $rule
     * @param AbstractItem $item
     * @param float        $qty
     *
     * @return DiscountData
     */
    public function calculate($rule, $item, $qty)
    {
        /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
        $discountData = $this->discountFactory->create();

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $item->getQuote();

        $calculateId = 'calculate_gift_rule_'.$rule->getRuleId();
        if (!$quote->getData($calculateId)) {
            // Set only for performance (not save in DB)
            $quote->setData($calculateId, true);

            // Save active gift rule in session
            $giftRuleSessionData = $this->checkoutSession->getGiftRules();
            $giftRuleSessionData[$rule->getRuleId()] = $rule->getRuleId();
            $this->checkoutSession->setGiftRules($giftRuleSessionData);

            /** @var array $giftRuleData */
            $giftRuleData = $this->giftRuleCacheHelper->saveCachedGiftRule(
                $rule->getRuleId(),
                $rule->getActions(),
                (int)$rule->getRuleId()
            );
        }

        return $discountData;
    }
}
