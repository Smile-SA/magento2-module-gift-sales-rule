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
 * Class GiftRuleService
 *
 * @author    Maxime Queneau <maxime.queneau@smile.fr>
 * @copyright 2019 Smile
 */
class GiftRuleService implements GiftRuleServiceInterface
{
    /**
     * Keys of a gift product request sent by the gift popup: no other key may reach the buy request of a free item.
     */
    private const GIFT_PRODUCT_REQUEST_KEYS = [
        'id',
        'product',
        'qty',
        'super_attribute',
        'selected_configurable_option',
    ];

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
     * @var GiftRuleHelper
     */
    protected $giftRuleHelper;

    /**
     * @var GiftRuleDataInterfaceFactory
     */
    protected $giftRuleDataFactory;

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
        CheckoutSession $checkoutSession,
        Cart $cart,
        CacheInterface $cache,
        GiftRuleCacheHelper $giftRuleCacheHelper,
        GiftRuleDataInterfaceFactory $giftRuleDataFactory,
        GiftRuleHelper $giftRuleHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->cart = $cart;
        $this->cache = $cache;
        $this->giftRuleCacheHelper = $giftRuleCacheHelper;
        $this->giftRuleDataFactory = $giftRuleDataFactory;
        $this->giftRuleHelper = $giftRuleHelper;
    }

    /**
     * Get available gifts
     *
     * @param Quote $quote Quote
     *
     * @return GiftRuleDataInterface[]
     */
    public function getAvailableGifts(Quote $quote)
    {
        /** @var array $gifts */
        $gifts = [];

        /** @var array $quoteItems */
        $quoteItems = [];

        /** @var array $giftRules */
        $giftRules = $this->checkoutSession->getGiftRules();

        /** @var Item $item */
        foreach ($quote->getAllItems() as $item) {
            /** @var Option $option */
            $option = $item->getOptionByCode('option_gift_rule');

            if ($option && $this->giftRuleHelper->isTopLevelItem($item)) {
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
     *
     * @return mixed|void
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function addGiftProducts(Quote $quote, array $products, string $identifier, ?int $giftRuleId = null)
    {
        if ($giftRuleId == null) {
            $giftRuleId = $identifier;
        }

        $giftRuleData = $this->getAppliedGiftRuleData($quote, $identifier, (int) $giftRuleId);
        $products = $this->validateQuantities($quote, $products, $giftRuleData);

        foreach ($products as $product) {
            if ($this->isAuthorizedGiftProduct($quote, $product['id'], $giftRuleData, $product['qty'])) {
                $this->addGiftProductToCart($product, (int) $giftRuleId);
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
     *
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function replaceGiftProducts(Quote $quote, array $products, string $identifier, ?int $giftRuleId = null)
    {
        if ($giftRuleId == null) {
            $giftRuleId = $identifier;
        }

        $giftRuleData = $this->getAppliedGiftRuleData($quote, $identifier, (int) $giftRuleId);

        $products = $this->validateQuantities($quote, $products, $giftRuleData);
        $quoteGiftItems = $this->getQuoteGiftItems($quote, $giftRuleId);

        foreach ($products as $product) {
            if ($this->isAuthorizedGiftProduct($quote, $product['id'], $giftRuleData, $product['qty'])) {
                $quoteItem = false;

                $productId = $product['id'];
                if (isset($product['super_attribute'])) {
                    $productId = $product['id'].json_encode($product['super_attribute']);
                }

                if (isset($quoteGiftItems[$productId])) {
                    /** @var Item $quoteItem */
                    $quoteItem = $quoteGiftItems[$productId];
                    unset($quoteGiftItems[$productId]);
                }

                if ($quoteItem) {
                    $quoteItem->setQty($product['qty']);
                } else {
                    $this->addGiftProductToCart($product, (int) $giftRuleId);
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
     * Get the data of a gift rule, only if it is currently applied to the quote.
     *
     * @param Quote  $quote      Quote
     * @param string $identifier Gift rule code
     * @param int    $giftRuleId Gift rule id
     *
     * @return array
     * @throws Exception
     */
    protected function getAppliedGiftRuleData(Quote $quote, string $identifier, int $giftRuleId): array
    {
        $sessionGiftRules = (array) $this->checkoutSession->getGiftRules();
        $appliedRuleIds = explode(',', (string) $quote->getAppliedRuleIds());

        $giftRuleData = null;
        if (isset($sessionGiftRules[$giftRuleId])
            && (string) $sessionGiftRules[$giftRuleId] === $identifier
            && in_array((string) $giftRuleId, $appliedRuleIds, true)
        ) {
            $giftRuleData = $this->giftRuleCacheHelper->getCachedGiftRule($identifier);
        }

        if (!$giftRuleData) {
            throw new Exception(__('The gift rule is not valid.'));
        }

        return $giftRuleData;
    }

    /**
     * Check the requested quantities: positive integers, summing up to the number of offered products at most.
     *
     * @param Quote $quote        Quote
     * @param array $products     Product request data
     * @param array $giftRuleData Gift rule data
     * @return array Product request data, with integer quantities
     * @throws Exception
     */
    protected function validateQuantities(Quote $quote, array $products, array $giftRuleData): array
    {
        $totalQty = 0;
        foreach ($products as $key => $product) {
            $qty = filter_var($product['qty'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if (!isset($product['id']) || $qty === false) {
                throw new Exception(__('We found an invalid request for adding gift product.'));
            }
            $products[$key]['qty'] = $qty;
            $totalQty += $qty;
        }

        $numberOfferedProduct = $this->giftRuleHelper->getNumberOfferedProduct(
            $quote,
            $giftRuleData[GiftRuleCacheHelper::DATA_MAXIMUM_NUMBER_PRODUCT],
            $giftRuleData[GiftRuleCacheHelper::DATA_PRICE_RANGE]
        );
        if ($totalQty > $numberOfferedProduct) {
            throw new Exception(__('We can\'t add this gift item to your shopping cart.'));
        }

        return $products;
    }

    /**
     * Add a gift product to the cart, flagging it as a gift of the given rule.
     *
     * @param array $product    Product request data
     * @param int   $giftRuleId Gift rule id
     *
     * @throws LocalizedException
     */
    protected function addGiftProductToCart(array $product, int $giftRuleId): void
    {
        $product = array_intersect_key($product, array_flip(self::GIFT_PRODUCT_REQUEST_KEYS));
        $product['gift_rule'] = $giftRuleId;
        $this->giftRuleHelper->setGiftRuleIdBeingAdded($giftRuleId);
        try {
            $this->cart->addProduct($product['id'], $product);
        } finally {
            $this->giftRuleHelper->setGiftRuleIdBeingAdded(null);
        }
    }

    /**
     * Check if is authorized gift product.
     *
     * @param Quote $quote        Quote
     * @param int   $productId    Product id
     * @param array $giftRuleData Gift rule data
     * @param int   $qty          Qty
     * @return bool
     */
    protected function isAuthorizedGiftProduct($quote, $productId, $giftRuleData, $qty)
    {
        $isAuthorizedGiftProduct = false;
        if (array_key_exists($productId, $giftRuleData[GiftRuleCacheHelper::DATA_PRODUCT_ITEMS])
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
     * @param Quote $quote      Quote
     * @param int   $giftRuleId Gift rule id
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
                $attributesOptionValue = '';
                /** @var Option $attributesOption */
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
