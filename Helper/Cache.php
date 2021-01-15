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

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Rule\Model\Condition\Sql\Builder;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\RuleFactory;
use Smile\GiftSalesRule\Api\Data\GiftRuleInterface;
use Smile\GiftSalesRule\Api\GiftRuleRepositoryInterface;

/**
 * Rule helper
 *
 * @author    Maxime Queneau <maxime.queneau@smile.fr>
 * @copyright 2019 Smile
 */
class Cache extends AbstractHelper
{
    /**
     * Cache
     */
    const CACHE_DATA_TAG   = "gift_rule_cache";
    const CACHE_IDENTIFIER = "gift_rule_product_";

    const DATA_PRODUCT_ITEMS          = "product_items";
    const DATA_LABEL                  = "label";
    const DATA_PRICE_RANGE            = "price_range";
    const DATA_MAXIMUM_NUMBER_PRODUCT = "maximum_number_product";

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var GiftRuleRepositoryInterface
     */
    protected $giftRuleRepository;

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var Builder
     */
    protected $sqlBuilder;

    /**
     * GiftSalesRuleCache constructor.
     *
     * @param Context                     $context                  Context
     * @param CacheInterface              $cache                    Cache
     * @param GiftRuleRepositoryInterface $giftRuleRepository       Gift rule repository
     * @param CollectionFactory           $productCollectionFactory Product collection factory
     * @param Builder                     $sqlBuilder               Sql builder
     * @param RuleFactory                 $ruleFactory              Rule factory
     */
    public function __construct(
        Context $context,
        CacheInterface $cache,
        GiftRuleRepositoryInterface $giftRuleRepository,
        CollectionFactory $productCollectionFactory,
        Builder $sqlBuilder,
        RuleFactory $ruleFactory
    ) {
        $this->cache = $cache;
        $this->giftRuleRepository = $giftRuleRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->sqlBuilder = $sqlBuilder;
        $this->ruleFactory = $ruleFactory;

        parent::__construct($context);
    }

    /**
     * Save cached gift rule
     *
     * @param string                $identifier Identifier
     * @param Rule                  $rule       Rule
     * @param int|GiftRuleInterface $giftRule   Gift rule
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveCachedGiftRule($identifier, $rule, $giftRule)
    {
        $giftRuleData = $this->cache->load(self::CACHE_IDENTIFIER . $identifier);
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
                serialize($giftRuleData),
                self::CACHE_IDENTIFIER . $identifier,
                array_merge([self::CACHE_DATA_TAG], $productCacheTags),
                3600
            );
        }

        if (!is_array($giftRuleData)) {
            $giftRuleData = unserialize($giftRuleData);
        }

        return $giftRuleData;
    }

    /**
     * Get cached gift rule.
     *
     * @param int|string $giftRuleCode Gift rule code
     *
     * @return array
     */
    public function getCachedGiftRule($giftRuleCode)
    {
        $cachedData = unserialize($this->cache->load(self::CACHE_IDENTIFIER . $giftRuleCode));
        if (!$cachedData) {
            $rule = $this->extractRuleFromCode($giftRuleCode);
            if ($rule && $rule->getId()) {
                try {
                    $giftRule = $this->giftRuleRepository->getByRule($rule);
                    $cachedData = $this->saveCachedGiftRule($giftRuleCode, $rule, $giftRule);
                } catch (LocalizedException $localizedException) {
                    $cachedData = null;
                }
            }
        }

        return $cachedData;
    }

    /**
     * Flush cached gift rule.
     */
    public function flushCachedGiftRule()
    {
        $this->cache->clean(self::CACHE_DATA_TAG);
    }

    /**
     * Extract rule from gift rule code.
     *
     * @param string $giftRuleCode Gift rule code
     *
     * @return \Magento\SalesRule\Api\Data\RuleInterface|null
     */
    protected function extractRuleFromCode($giftRuleCode)
    {
        $explodedCode = explode('_', $giftRuleCode);
        $ruleId = array_shift($explodedCode);

        return $this->ruleFactory->create()->load($ruleId);
    }
}
