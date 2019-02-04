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
namespace Smile\GiftSalesRule\Block;

use Magento\Checkout\Model\Cart as CheckoutCart;
use Magento\Framework\View\Element\Template\Context;
use Smile\GiftSalesRule\Helper\GiftRule as GiftRuleHelper;
use Smile\GiftSalesRule\Api\GiftRuleServiceInterface;

/**
 * Class Cart
 *
 * @author    Maxime Queneau <maxime.queneau@smile.fr>
 * @copyright 2019 Smile
 */
class Cart extends \Magento\Framework\View\Element\Template
{
    /**
     * @var GiftRuleServiceInterface
     */
    protected $giftRuleService;

    /**
     * @var GiftRuleHelper
     */
    protected $giftRuleHelper;

    /**
     * Cart constructor.
     *
     * @param Context                  $context
     * @param GiftRuleServiceInterface $giftRuleService
     * @param GiftRuleHelper           $giftRuleHelper
     * @param CheckoutCart             $cart
     * @param array                    $data
     */
    public function __construct(
        Context $context,
        GiftRuleServiceInterface $giftRuleService,
        GiftRuleHelper $giftRuleHelper,
        CheckoutCart $cart,
        array $data = []
    ) {
        $this->giftRuleService = $giftRuleService;
        $this->giftRuleHelper = $giftRuleHelper;
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

    /**
     * @param int    $giftRuleId
     * @param string $giftRuleCode
     * @param int    $product
     *
     * @return string
     */
    public function getAddToCartUrl($giftRuleId, $giftRuleCode, $product)
    {
        return $this->giftRuleHelper->getAddUrl($giftRuleId, $giftRuleCode, $product['entity_id']);
    }
}
