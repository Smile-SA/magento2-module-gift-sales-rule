<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\Helper;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Rule;
use Smile\GiftSalesRule\Api\Data\GiftRuleInterface;
use Smile\GiftSalesRule\Api\GiftRuleRepositoryInterface;

/**
 * Gift rule helper
 *
 * @copyright 2019 Smile
 */
class GiftRule extends AbstractHelper
{
    /**
     * Gift rule id of the gift product currently added by the gift rule service.
     */
    private ?int $giftRuleIdBeingAdded = null;

    /**
     * GiftRule constructor.
     *
     * @param Context                     $context            Context
     * @param GiftRuleRepositoryInterface $giftRuleRepository Gift rule repository
     * @param array                       $giftRule           Gift rule
     */
    public function __construct(
        Context $context,
        protected GiftRuleRepositoryInterface $giftRuleRepository,
        protected array $giftRule = []
    ) {

        parent::__construct($context);
    }

    /**
     * Is gift sales rule
     *
     * @param Rule $rule Rule
     */
    public function isGiftRule(Rule $rule): bool
    {
        $isGiftRule = false;
        if (in_array($rule->getSimpleAction(), $this->giftRule)) {
            $isGiftRule = true;
        }

        return $isGiftRule;
    }

    /**
     * Check if is valid gift rule for quote
     *
     * @param Rule  $rule  Rule
     * @param Quote $quote Quote
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isValidGiftRule(Rule $rule, Quote $quote): bool
    {
        $valid = true;

        /**
         * Check if quote has at least one quote item (no gift rule item) in quote
         */
        $hasProduct = false;
        foreach ($quote->getAllItems() as $item) {
            if (!$this->isGiftItem($item)) {
                $hasProduct = true;
                break;
            }
        }
        if (!$hasProduct) {
            $valid = false;
        }

        if ($valid && $rule->getSimpleAction() == GiftRuleInterface::OFFER_PRODUCT_PER_PRICE_RANGE) {
            /**
             * Rules load by collection => extension attributes not present in rule entity
             */
            /** @var GiftRuleInterface $giftRule */
            $giftRule = $this->giftRuleRepository->getByRule($rule);

            if ($quote->getShippingAddress()->getBaseSubtotalTotalInclTax() < $giftRule->getPriceRange()) {
                $valid = false;
            }
        }

        return $valid;
    }

    /**
     * Retrieve url for add gift product to cart
     *
     * @param int|string $giftRuleId   Gift rule id
     * @param int|string $giftRuleCode Gift rule code
     */
    public function getAddUrl(int|string $giftRuleId, int|string $giftRuleCode): string
    {
        $routeParams = [
            ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlEncoder->encode($this->_urlBuilder->getCurrentUrl()),
            'gift_rule_id'   => $giftRuleId,
            'gift_rule_code' => $giftRuleCode,
        ];

        return $this->_getUrl('giftsalesrule/cart/add', $routeParams);
    }

    /**
     * Get range of a gift rule for a quote.
     *
     * @param float|int|string|null $total      Total (address data, may be a numeric string)
     * @param float|int|string|null $priceRange Price range (DB decimal, may be a numeric string)
     */
    public function getRange(float|int|string|null $total, float|int|string|null $priceRange): float
    {
        return floor((float) $total / (float) $priceRange);
    }

    /**
     * Get number offered product for a quote.
     *
     * @param Quote                 $quote                Quote
     * @param float|int|string|null $maximumNumberProduct Maximum number product (DB value)
     * @param float|int|string|null $priceRange           Price range (DB decimal)
     */
    public function getNumberOfferedProduct(
        Quote $quote,
        float|int|string|null $maximumNumberProduct,
        float|int|string|null $priceRange
    ): int {
        $numberOfferedProduct = $maximumNumberProduct;
        if (floatval($priceRange) > 0) {
            $shippingAddress = $quote->getShippingAddress();
            // In some weird cases with multi-shipping feature enabled, we need to get the orig data.
            // Example: Go to the checkout and come back to the cart page.
            $total = $shippingAddress->getBaseSubtotalTotalInclTax()
                ?: $shippingAddress->getOrigData('base_subtotal_total_incl_tax');
            $range = $this->getRange($total, $priceRange);
            $numberOfferedProduct = (float) $maximumNumberProduct * $range;
        }

        return (int) $numberOfferedProduct;
    }

    /**
     * Is gift item ?
     *
     * @param AbstractItem $item item
     */
    public function isGiftItem(AbstractItem $item): bool
    {
        return (bool) $item->getOptionByCode('option_gift_rule');
    }

    /**
     * Is the item a top-level quote item (not the child of a composite product) ?
     *
     * @param AbstractItem $item item
     */
    public function isTopLevelItem(AbstractItem $item): bool
    {
        /** @var AbstractItem|null $parentItem */
        $parentItem = $item->getParentItem();

        return !$item->getParentItemId() && !$parentItem;
    }

    /**
     * Set (or reset with null) the gift rule id of the gift product being added by the gift rule service.
     *
     * @param int|null $giftRuleId Gift rule id
     */
    public function setGiftRuleIdBeingAdded(?int $giftRuleId): void
    {
        $this->giftRuleIdBeingAdded = $giftRuleId;
    }

    /**
     * Get the gift rule id of the gift product being added by the gift rule service.
     */
    public function getGiftRuleIdBeingAdded(): ?int
    {
        return $this->giftRuleIdBeingAdded;
    }
}
