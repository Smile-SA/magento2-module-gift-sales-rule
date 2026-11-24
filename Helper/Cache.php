<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\Helper;

use InvalidArgumentException;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Rule\Model\Condition\Sql\Builder;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\RuleFactory;
use Smile\GiftSalesRule\Api\Data\GiftRuleInterface;
use Smile\GiftSalesRule\Api\GiftRuleRepositoryInterface;

/**
 * Rule helper
 *
 * @copyright 2019 Smile
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Cache extends AbstractHelper
{
    /**
     * Cache
     */
    public const CACHE_DATA_TAG   = "gift_rule_cache";
    public const CACHE_IDENTIFIER = "gift_rule_product_";

    public const DATA_PRODUCT_ITEMS          = "product_items";
    public const DATA_LABEL                  = "label";
    public const DATA_PRICE_RANGE            = "price_range";
    public const DATA_MAXIMUM_NUMBER_PRODUCT = "maximum_number_product";

    private Json $serializer;

    /**
     * GiftSalesRuleCache constructor.
     *
     * @param Context                     $context                  Context
     * @param CacheInterface              $cache                    Cache
     * @param GiftRuleRepositoryInterface $giftRuleRepository       Gift rule repository
     * @param CollectionFactory           $productCollectionFactory Product collection factory
     * @param Builder                     $sqlBuilder               Sql builder
     * @param RuleFactory                 $ruleFactory              Rule factory
     * @param Json|null                   $serializer               Serializer
     */
    public function __construct(
        Context $context,
        protected CacheInterface $cache,
        protected GiftRuleRepositoryInterface $giftRuleRepository,
        protected CollectionFactory $productCollectionFactory,
        protected Builder $sqlBuilder,
        protected RuleFactory $ruleFactory,
        ?Json $serializer = null
    ) {
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);

        parent::__construct($context);
    }

    /**
     * Save cached gift rule
     *
     * @param int|string            $identifier Identifier (rule id, or "{rule id}_{range}")
     * @param Rule                  $rule       Rule
     * @param int|GiftRuleInterface $giftRule   Gift rule
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveCachedGiftRule(int|string $identifier, Rule $rule, int|GiftRuleInterface $giftRule): array|false
    {
        $giftRuleData = $this->decode($this->cache->load(self::CACHE_IDENTIFIER . $identifier));
        if (!$giftRuleData) {
            if (is_int($giftRule)) {
                /**
                 * Rules load by collection => extension attributes not present in rule entity
                 */
                /** @var GiftRuleInterface $giftRule */
                $giftRule = $this->giftRuleRepository->getById($giftRule);
            }

            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
            $collection = $this->productCollectionFactory->create();

            $collection->addStoreFilter();

            /** @var \Magento\SalesRule\Model\Rule\Condition\Product\Combine $actions */
            $actions = $rule->getActions();
            $actions->collectValidatedAttributes($collection);
            $this->sqlBuilder->attachConditionToCollection($collection, $actions);

            $items = [];
            $productCacheTags = [];
            foreach ($collection->getItems() as $item) {
                $items[$item->getId()] = $item->getSku();
                $productCacheTags[] = Product::CACHE_TAG . '_' . $item->getEntityId();
            }

            $giftRuleData = [
                self::DATA_LABEL => $rule->getStoreLabel(),
                self::DATA_PRICE_RANGE => $giftRule->getPriceRange(),
                self::DATA_MAXIMUM_NUMBER_PRODUCT => $giftRule->getMaximumNumberProduct(),
                self::DATA_PRODUCT_ITEMS => $items,
            ];

            // Dispatch an event to be able to add/change data in gift rule cache.
            $dataObject = new DataObject($giftRuleData);
            $this->_eventManager->dispatch(
                'before_save_gift_rule_cache',
                ['data_object' => $dataObject, 'rule' => $rule, 'gift_rule' => $giftRule, 'identifier' => $identifier]
            );
            $giftRuleData = $dataObject->getData();

            $this->cache->save(
                $this->serializer->serialize($giftRuleData),
                self::CACHE_IDENTIFIER . $identifier,
                array_merge([self::CACHE_DATA_TAG], $productCacheTags),
                3600
            );
        }

        return $giftRuleData;
    }

    /**
     * Decode cached gift rule data, never as PHP objects.
     *
     * @param string|false $data Cached data
     * @return array|null Null on a cache miss or on data cached in another format
     */
    private function decode(string|false $data): ?array
    {
        if ($data === false || $data === '') {
            return null;
        }
        try {
            $decoded = $this->serializer->unserialize($data);
        } catch (InvalidArgumentException $exception) {
            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Get cached gift rule.
     *
     * @param int|string $giftRuleCode Gift rule code
     * @return array|null
     */
    public function getCachedGiftRule(int|string $giftRuleCode): ?array
    {
        $cachedData = $this->decode($this->cache->load(self::CACHE_IDENTIFIER . $giftRuleCode));
        if (!$cachedData) {
            $rule = $this->extractRuleFromCode($giftRuleCode);
            if ($rule->getId()) {
                try {
                    $giftRule = $this->giftRuleRepository->getByRule($rule);
                    $cachedData = $this->saveCachedGiftRule($giftRuleCode, $rule, $giftRule);
                } catch (LocalizedException $localizedException) {
                    $cachedData = null;
                }
            }
        }

        return is_array($cachedData) ? $cachedData : null;
    }

    /**
     * Flush cached gift rule.
     */
    public function flushCachedGiftRule(): void
    {
        $this->cache->clean([self::CACHE_DATA_TAG]);
    }

    /**
     * Extract rule from gift rule code.
     *
     * @param int|string $giftRuleCode Gift rule code
     */
    protected function extractRuleFromCode(int|string $giftRuleCode): Rule
    {
        $explodedCode = explode('_', (string) $giftRuleCode);
        $ruleId = array_shift($explodedCode);

        return $this->ruleFactory->create()->load($ruleId);
    }
}
