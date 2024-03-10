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
 * @author    Maxime Queneau <maxime.queneau@smile.fr>
 * @copyright 2019 Smile
 */
class GiftRule extends AbstractHelper
{
    /**
     * @var array
     */
    protected $giftRule = [];

    /**
     * @var GiftRuleRepositoryInterface
     */
    protected $giftRuleRepository;

    /**
     * GiftRule constructor.
     *
     * @param Context                     $context            Context
     * @param GiftRuleRepositoryInterface $giftRuleRepository Gift rule repository
     * @param array                       $giftRule           Gift rule
     */
    public function __construct(
        Context $context,
        GiftRuleRepositoryInterface $giftRuleRepository,
        array $giftRule = []
    ) {
        $this->giftRuleRepository = $giftRuleRepository;
        $this->giftRule = $giftRule;

        parent::__construct($context);
    }

    /**
     * Is gift sales rule
     *
     * @param Rule $rule Rule
     *
     * @return bool
     */
    public function isGiftRule(Rule $rule)
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
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isValidGiftRule(Rule $rule, Quote $quote)
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
     * @param int    $giftRuleId   Gift rule id
     * @param string $giftRuleCode Gift rule code
     *
     * @return string
     */
    public function getAddUrl($giftRuleId, $giftRuleCode)
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
     * @param float $total      Total
     * @param float $priceRange Price range
     *
     * @return float
     */
    public function getRange($total, $priceRange)
    {
        return floor($total / $priceRange);
    }

    /**
     * Get number offered product for a quote.
     *
     * @param Quote $quote                Quote
     * @param float $maximumNumberProduct Maximum number product
     * @param float $priceRange           Price range
     * @return int
     */
    public function getNumberOfferedProduct($quote, $maximumNumberProduct, $priceRange)
    {
        $numberOfferedProduct = $maximumNumberProduct;
        if (floatval($priceRange) > 0) {
            $shippingAddress = $quote->getShippingAddress();
            // In some weird cases with multi-shipping feature enabled, we need to get the orig data.
            // Example: Go to the checkout and come back to the cart page.
            $total = $shippingAddress->getBaseSubtotalTotalInclTax()
                ?: $shippingAddress->getOrigData('base_subtotal_total_incl_tax');
            $range = $this->getRange($total, $priceRange);
            $numberOfferedProduct = $maximumNumberProduct * $range;
        }

        return (int) $numberOfferedProduct;
    }

    /**
     * Is gift item ?
     *
     * @param AbstractItem $item item
     * @return bool
     */
    public function isGiftItem(AbstractItem $item): bool
    {
        return (bool) $item->getOptionByCode('option_gift_rule');
    }
}
