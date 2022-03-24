<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\GiftSalesRule
 * @author    Pierre Le Maguer <pierre.lemaguer@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\GiftSalesRule\Block\Cart;

use Magento\Checkout\Model\Cart as CheckoutCart;
use Magento\Framework\View\Element\Template\Context;
use Smile\GiftSalesRule\Api\Data\GiftRuleDataInterface;
use Smile\GiftSalesRule\Api\GiftRuleServiceInterface;
use Smile\GiftSalesRule\Helper\Config as GiftRuleConfig;

/**
 * Class GiftRules
 *
 * @author    Pierre Le Maguer <pierre.lemaguer@smile.fr>
 * @copyright 2019 Smile
 */
class GiftRules extends \Magento\Framework\View\Element\Template
{
    /**
     * @var GiftRuleServiceInterface
     */
    protected $giftRuleService;

    /**
     * @var CheckoutCart
     */
    protected $cart;

    /**
     * @var GiftRuleConfig
     */
    protected $giftRuleConfig;

    /**
     * Cart constructor.
     *
     * @param Context                  $context         Context
     * @param GiftRuleServiceInterface $giftRuleService Gift rule service
     * @param CheckoutCart             $cart            Cart
     * @param GiftRuleConfig           $giftRuleConfig    Gift rule config
     * @param array                    $data            Data
     */
    public function __construct(
        Context $context,
        GiftRuleServiceInterface $giftRuleService,
        CheckoutCart $cart,
        GiftRuleConfig $giftRuleConfig,
        array $data = []
    ) {
        $this->giftRuleService = $giftRuleService;
        $this->cart = $cart;
        $this->giftRuleConfig = $giftRuleConfig;
        parent::__construct($context, $data);
    }

    /**
     * Get gift rules.
     *
     * @return GiftRuleDataInterface[]
     */
    public function getGiftRules(): array
    {
        return array_filter(
            $this->giftRuleService->getAvailableGifts($this->cart->getQuote()),
            [$this, 'canDisplayGiftRule']
        );
    }

    /**
     * Can display gift rule.
     *
     * @param GiftRuleDataInterface $giftRule Gift rule
     * @return bool
     */
    protected function canDisplayGiftRule(GiftRuleDataInterface $giftRule): bool
    {
        return count($giftRule->getProductItems()) !== 1 || !$this->giftRuleConfig->isAutomaticAddEnabled();
    }
}
