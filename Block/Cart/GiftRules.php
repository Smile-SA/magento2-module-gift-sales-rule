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
use Smile\GiftSalesRule\Api\GiftRuleServiceInterface;

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
     * Cart constructor.
     *
     * @param Context                  $context         Context
     * @param GiftRuleServiceInterface $giftRuleService Gift rule service
     * @param CheckoutCart             $cart            Cart
     * @param array                    $data            Data
     */
    public function __construct(
        Context $context,
        GiftRuleServiceInterface $giftRuleService,
        CheckoutCart $cart,
        array $data = []
    ) {
        $this->giftRuleService = $giftRuleService;
        $this->cart = $cart;
        parent::__construct($context, $data);
    }

    /**
     * Get gift rules
     *
     * @return array
     */
    public function getGiftRules()
    {
        return $this->giftRuleService->getAvailableGifts($this->cart->getQuote());
    }
}
