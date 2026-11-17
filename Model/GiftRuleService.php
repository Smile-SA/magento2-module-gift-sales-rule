<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\Model;

use Exception;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\Option;
use Smile\GiftSalesRule\Api\Data\GiftRuleDataInterface;
use Smile\GiftSalesRule\Api\Data\GiftRuleDataInterfaceFactory;
use Smile\GiftSalesRule\Api\GiftRuleServiceInterface;
use Smile\GiftSalesRule\Helper\Cache as GiftRuleCacheHelper;
use Smile\GiftSalesRule\Helper\GiftRule as GiftRuleHelper;

/**
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class GiftRuleService implements GiftRuleServiceInterface
{
    /**
     * GiftRuleService constructor.
     *
     * @param CheckoutSession              $checkoutSession     Checkout session
     * @param Cart                         $cart                Cart
     * @param CacheInterface               $cache               Cache
     * @param GiftRuleCacheHelper          $giftRuleCacheHelper Gift rule cache helper
     * @param GiftRuleDataInterfaceFactory $giftRuleDataFactory Gift rule data factory
     * @param GiftRuleHelper               $giftRuleHelper      Gift rule helper
     */
    public function __construct(
        protected CheckoutSession $checkoutSession,
        protected Cart $cart,
        protected CacheInterface $cache,
        protected GiftRuleCacheHelper $giftRuleCacheHelper,
        protected GiftRuleDataInterfaceFactory $giftRuleDataFactory,
        protected GiftRuleHelper $giftRuleHelper
    ) {
    }

    /**
     * Get available gifts
     *
     * @param Quote $quote Quote
     * @return GiftRuleDataInterface[]
     */
    public function getAvailableGifts(Quote $quote): array
    {
        /** @var array $gifts */
        $gifts = [];

        /** @var array $quoteItems */
        $quoteItems = [];

        /** @var array|null $giftRules */
        $giftRules = $this->checkoutSession->getGiftRules();

        /** @var Item $item */
        foreach ($quote->getAllItems() as $item) {
            /** @var Option|null $option */
            $option = $item->getOptionByCode('option_gift_rule');

            if ($option) {
                $quoteItems[$option->getValue()][$item->getProductId()] = $item->getQty();
            }
        }

        if (is_array($giftRules)) {
            foreach ($giftRules as $giftRuleId => $giftRuleCode) {
                $giftRuleCachedData = $this->giftRuleCacheHelper->getCachedGiftRule($giftRuleCode);
                if (!$giftRuleCachedData) {
                    continue;
                }

                $gifts[$giftRuleId] = $giftRuleCachedData;
                $gifts[$giftRuleId][GiftRuleDataInterface::RULE_ID] = $giftRuleId;
                $gifts[$giftRuleId][GiftRuleDataInterface::CODE] = $giftRuleCode;
                $gifts[$giftRuleId][GiftRuleDataInterface::NUMBER_OFFERED_PRODUCT]
                    = $this->giftRuleHelper->getNumberOfferedProduct(
                        $quote,
                        $giftRuleCachedData[GiftRuleCacheHelper::DATA_MAXIMUM_NUMBER_PRODUCT],
                        $giftRuleCachedData[GiftRuleCacheHelper::DATA_PRICE_RANGE]
                    );
                $gifts[$giftRuleId][GiftRuleDataInterface::REST_NUMBER]
                    = $gifts[$giftRuleId][GiftRuleDataInterface::NUMBER_OFFERED_PRODUCT];
                $gifts[$giftRuleId][GiftRuleDataInterface::QUOTE_ITEMS] = [];
                if (isset($quoteItems[$giftRuleId])) {
                    $gifts[$giftRuleId][GiftRuleDataInterface::QUOTE_ITEMS] = $quoteItems[$giftRuleId];
                    $gifts[$giftRuleId][GiftRuleDataInterface::REST_NUMBER]
                        -= array_sum($gifts[$giftRuleId][GiftRuleDataInterface::QUOTE_ITEMS]);
                }
                /** @var GiftRuleDataInterface $giftRuleData */
                $giftRuleData = $this->giftRuleDataFactory->create();
                $gifts[$giftRuleId] = $giftRuleData->populateFromArray($gifts[$giftRuleId]);
            }
        }

        return $gifts;
    }

    /**
     * Add gift product
     *
     * @param Quote    $quote      Quote
     * @param array    $products   Products
     * @param string   $identifier Identifier
     * @param int|null $giftRuleId Gift rule id
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function addGiftProducts(Quote $quote, array $products, string $identifier, ?int $giftRuleId = null): void
    {
        if ($giftRuleId == null) {
            $giftRuleId = $identifier;
        }

        $giftRuleData = $this->giftRuleCacheHelper->getCachedGiftRule($identifier);
        if (!$giftRuleData) {
            throw new Exception(__('The gift rule is not valid.'));
        }

        foreach ($products as $product) {
            if (!(isset($product['id']) && isset($product['qty']))) {
                throw new Exception(__('We found an invalid request for adding gift product.'));
            }

            if ($this->isAuthorizedGiftProduct($quote, $product['id'], $giftRuleData, $product['qty'])) {
                $product['gift_rule'] = $giftRuleId;
                $this->cart->addProduct($product['id'], $product);
            } else {
                throw new Exception(__('We can\'t add this gift item to your shopping cart.'));
            }
        }
    }

    /**
     * Replace gift product
     *
     * @param Quote    $quote      Quote
     * @param array    $products   Product
     * @param string   $identifier Identifier
     * @param int|null $giftRuleId Gift rule id
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function replaceGiftProducts(
        Quote $quote,
        array $products,
        string $identifier,
        ?int $giftRuleId = null
    ): void {
        if ($giftRuleId == null) {
            $giftRuleId = $identifier;
        }

        $giftRuleData   = $this->giftRuleCacheHelper->getCachedGiftRule($identifier);
        if (!$giftRuleData) {
            throw new Exception(__('The gift rule is not valid.'));
        }

        $quoteGiftItems = $this->getQuoteGiftItems($quote, $giftRuleId);

        foreach ($products as $product) {
            if (!(isset($product['id']) && isset($product['qty']))) {
                throw new Exception(__('We found an invalid request for adding gift product.'));
            }
            if ($this->isAuthorizedGiftProduct($quote, $product['id'], $giftRuleData, $product['qty'])) {
                $quoteItem = false;

                $productId = $product['id'];
                if (isset($product['super_attribute'])) {
                    $productId = $product['id'] . json_encode($product['super_attribute']);
                }

                if (isset($quoteGiftItems[$productId])) {
                    /** @var Item $quoteItem */
                    $quoteItem = $quoteGiftItems[$productId];
                    unset($quoteGiftItems[$productId]);
                }

                if ($quoteItem) {
                    $quoteItem->setQty($product['qty']);
                } else {
                    $product['gift_rule'] = $giftRuleId;
                    $this->cart->addProduct($product['id'], $product);
                }
            } else {
                throw new Exception(__('We can\'t add this gift item to your shopping cart.'));
            }
        }

        // Remove old gift items.
        if (count($quoteGiftItems) > 0) {
            /** @var Item $quoteGiftItem */
            foreach ($quoteGiftItems as $quoteGiftItem) {
                $this->cart->removeItem($quoteGiftItem->getId());
            }
        }
    }

    /**
     * Check if is authorized gift product.
     *
     * @param Quote            $quote        Quote
     * @param int|string       $productId    Product id (request param / array key)
     * @param array            $giftRuleData Gift rule data
     * @param float|int|string $qty          Qty (request param)
     */
    protected function isAuthorizedGiftProduct(
        Quote $quote,
        int|string $productId,
        array $giftRuleData,
        float|int|string $qty
    ): bool {
        $isAuthorizedGiftProduct = false;
        if (
            array_key_exists($productId, $giftRuleData[GiftRuleCacheHelper::DATA_PRODUCT_ITEMS])
            && $qty <= $this->giftRuleHelper->getNumberOfferedProduct(
                $quote,
                $giftRuleData[GiftRuleCacheHelper::DATA_MAXIMUM_NUMBER_PRODUCT],
                $giftRuleData[GiftRuleCacheHelper::DATA_PRICE_RANGE]
            )
        ) {
            $isAuthorizedGiftProduct = true;
        }

        return $isAuthorizedGiftProduct;
    }

    /**
     * Get quote gift item
     *
     * @param Quote      $quote      Quote
     * @param int|string $giftRuleId Gift rule id (falls back to the string identifier)
     */
    protected function getQuoteGiftItems(Quote $quote, int|string $giftRuleId): array
    {
        $quoteItem = [];

        /** @var Item $item */
        foreach ($quote->getAllItems() as $item) {
            /** @var Option|null $option */
            $option = $item->getOptionByCode('option_gift_rule');
            if ($option && $option->getValue() == $giftRuleId) {
                $attributesOptionValue = '';
                /** @var Option|null $attributesOption */
                $attributesOption = $item->getOptionByCode('attributes');
                if ($attributesOption) {
                    $attributesOptionValue = $attributesOption->getValue();
                }
                $quoteItem[$item->getProductId()  . $attributesOptionValue] = $item;
            }
        }

        return $quoteItem;
    }
}
