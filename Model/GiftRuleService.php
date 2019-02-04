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
namespace Smile\GiftSalesRule\Model;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Option;
use Smile\GiftSalesRule\Api\GiftRuleServiceInterface;
use Smile\GiftSalesRule\Helper\Cache as GiftRuleCacheHelper;

/**
 * Class GiftRuleService
 *
 * @author    Maxime Queneau <maxime.queneau@smile.fr>
 * @copyright 2019 Smile
 */
class GiftRuleService implements GiftRuleServiceInterface
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var GiftRuleCacheHelper
     */
    protected $giftRuleCacheHelper;

    /**
     * GiftRuleService constructor.
     *
     * @param CheckoutSession     $checkoutSession
     * @param Cart                $cart
     * @param CacheInterface      $cache
     * @param GiftRuleCacheHelper $giftRuleCacheHelper
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        Cart $cart,
        CacheInterface $cache,
        GiftRuleCacheHelper $giftRuleCacheHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->cart = $cart;
        $this->cache = $cache;
        $this->giftRuleCacheHelper = $giftRuleCacheHelper;
    }

    /**
     * Get available gifts
     *
     * @param Quote $quote
     *
     * @return array
     *     {gift_rule_id} => [
     *         maximum_number_product => {number}
     *         code => {gift_rule_code}
     *         items => [
     *             {product_id} => [ {product_data} ]
     *             ...
     *         ]
     *         quote_items => [
     *             {product_id} => {qty}
     *             ...
     *         ]
     *     ]
     */
    public function getAvailableGifts(Quote $quote)
    {
        /** @var array $gifts */
        $gifts = [];

        /** @var array $QuoteItems */
        $QuoteItems = [];

        /** @var array $giftRules */
        $giftRules = $this->checkoutSession->getGiftRules();

        /** @var Item $item */
        foreach ($quote->getAllItems() as $item) {
            /** @var Option $option */
            $option = $item->getOptionByCode('option_gift_rule');
            if ($option) {
                $QuoteItems[$option->getValue()][$item->getProductId()] = $item->getQty();
            }
        }

        if (is_array($giftRules)) {
            foreach ($giftRules as $giftRuleId => $giftRuleCode) {
                $gifts[$giftRuleId] = $this->giftRuleCacheHelper->getCachedGiftRule($giftRuleCode);
                $gifts[$giftRuleId]['code'] = $giftRuleCode;
                if (isset($QuoteItems[$giftRuleId])) {
                    $gifts[$giftRuleId]['quote_items'] = $QuoteItems[$giftRuleId];
                }
            }
        }

        return $gifts;
    }

    /**
     * Add gift product
     *
     * @param Quote    $quote
     * @param array    $products
     * @param string   $identifier
     * @param int|null $giftRuleId
     *
     * @throws LocalizedException
     */
    public function addGiftProducts(Quote $quote, array $products, string $identifier, int $giftRuleId = null)
    {
        if ($giftRuleId == null) {
            $giftRuleId = $identifier;
        }

        $giftRuleData = $this->giftRuleCacheHelper->getCachedGiftRule($identifier);

        foreach ($products as $product) {
            if (!(isset($product['id']) && isset($product['qty']))) {
                throw new \Exception(__('We found an invalid request for adding gift product.'));
            }
            
            if ($this->isAuthorizedGiftProduct($product['id'], $giftRuleData, $product['qty'])) {
                $this->cart->addProduct($product['id'], ['qty' => $product['qty'], 'gift_rule' => $giftRuleId]);
            } else {
                throw new \Exception(__('We can\'t add this gift item to your shopping cart.'));
            }
        }
    }

    /**
     * Replace gift product
     *
     * @param Quote    $quote
     * @param array    $products
     * @param string   $identifier
     * @param int|null $giftRuleId
     *
     * @throws LocalizedException
     */
    public function replaceGiftProducts(Quote $quote, array $products, string $identifier, int $giftRuleId = null)
    {
        if ($giftRuleId == null) {
            $giftRuleId = $identifier;
        }

        $giftRuleData   = $this->giftRuleCacheHelper->getCachedGiftRule($identifier);
        $quoteGiftItems = $this->getQuoteGiftItems($quote, $giftRuleId);

        foreach ($products as $product) {
            if (!(isset($product['id']) && isset($product['qty']))) {
                throw new \Exception(__('We found an invalid request for adding gift product.'));
            }
            if ($this->isAuthorizedGiftProduct($product['id'], $giftRuleData, $product['qty'])) {
                $quoteItem = false;
                if (isset($quoteGiftItems[$product['id']])) {
                    /** @var Item $quoteItem */
                    $quoteItem = $quoteGiftItems[$product['id']];
                    unset($quoteGiftItems[$product['id']]);
                }

                if ($quoteItem) {
                    $quoteItem->setQty($product['qty']);
                } else {
                    $this->cart->addProduct($product['id'], ['qty' => $product['qty'], 'gift_rule' => $giftRuleId]);
                }
            } else {
                throw new \Exception(__('We can\'t add this gift item to your shopping cart.'));
            }
        }

        // Remove old gift items
        if (count($quoteGiftItems) > 0) {
            /** @var Item $quoteGiftItem */
            foreach ($quoteGiftItems as $quoteGiftItem) {
                $this->cart->removeItem($quoteGiftItem->getId());
            }
        }
    }

    /**
     * Check if is authorized grift product
     *
     * @param $productId
     * @param $giftRuleData
     *
     * @return bool
     */
    protected function isAuthorizedGiftProduct($productId, $giftRuleData, $qty)
    {
        $isAuthorizedGiftProduct = false;
        if (array_key_exists($productId, $giftRuleData[GiftRuleCacheHelper::DATA_ITEMS])
            && $qty <= $giftRuleData[GiftRuleCacheHelper::DATA_MAXIMUM_NUMBER_PRODUCT]) {
            $isAuthorizedGiftProduct = true;
        }

        return $isAuthorizedGiftProduct;
    }

    /**
     * Get quote gift item
     *
     * @param Quote $quote
     * @param int   $giftRuleId
     *
     * @return array
     */
    protected function getQuoteGiftItems(Quote $quote, int $giftRuleId)
    {
        $quoteItem = [];

        /** @var Item $item */
        foreach ($quote->getAllItems() as $item) {
            /** @var Option $option */
            $option = $item->getOptionByCode('option_gift_rule');
            if ($option && $option->getValue() == $giftRuleId) {
                $quoteItem[$item->getProductId()] = $item;
            }
        }

        return $quoteItem;
    }
}
