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
     * @param Context                     $context
     * @param GiftRuleRepositoryInterface $giftRuleRepository
     * @param array                       $giftRule
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
     * @param RuleInterface $rule
     *
     * @return bool
     */
    public function isGiftRule(Rule $rule)
    {
        $isgiftRule = false;
        if (in_array($rule->getSimpleAction(), $this->giftRule)) {
            $isgiftRule = true;
        }

        return $isgiftRule;
    }

    /**
     * Check if is valid gift rule for quote
     *
     * @param Rule  $rule
     * @param Quote $quote
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
            $option = $item->getOptionByCode('option_gift_rule');
            if (!$item->getOptionByCode('option_gift_rule')) {
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
            $giftRule = $this->giftRuleRepository->getById($rule->getRuleId());

            if ($quote->getGrandTotal() < $giftRule->getPriceRange()) {
                $valid = false;
            }
        }

        return $valid;
    }

    /**
     * Retrieve url for add gift product to cart
     *
     * @param       $giftRuleId
     * @param       $giftRuleCode
     * @param       $productId
     * @param array $additional
     *
     * @return string
     */
    public function getAddUrl($giftRuleId, $giftRuleCode, $productId)
    {
        $routeParams = [
            ActionInterface::PARAM_NAME_URL_ENCODED => $this->urlEncoder->encode($this->_urlBuilder->getCurrentUrl()),
            'giftrule'     => $giftRuleId,
            'giftrulecode' => $giftRuleCode,
            'product'      => $productId
        ];

        return $this->_getUrl('giftsalesrule/cart/add', $routeParams);
    }
}
