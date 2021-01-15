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
namespace Smile\GiftSalesRule\Plugin\Model;

use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\SalesRule\Api\Data\RuleExtensionInterface;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\SalesRule\Model\RuleFactory;
use Smile\GiftSalesRule\Api\Data\GiftRuleInterface;
use Smile\GiftSalesRule\Api\GiftRuleRepositoryInterface;
use Smile\GiftSalesRule\Model\GiftRuleFactory;

/**
 * Rule Repository Plugin
 *
 * @author    Maxime Queneau <maxime.queneau@smile.fr>
 * @copyright 2019 Smile
 */
class RuleRepositoryPlugin
{
    /**
     * Extension attribute factory
     *
     * @var ExtensionAttributesFactory
     */
    protected $extensionFactory;

    /**
     * Search criteria builder
     *
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Gift rule repository
     *
     * @var GiftRuleRepositoryInterface
     */
    protected $giftRuleRepository;

    /**
     * Gift rule factory
     *
     * @var GiftRuleFactory
     */
    protected $giftRuleFactory;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * RuleRepositoryPlugin constructor.
     *
     * @param ExtensionAttributesFactory  $extensionFactory      Extension factoryuuuuuuuuu
     * @param SearchCriteriaBuilder       $searchCriteriaBuilder Search criteria builder
     * @param GiftRuleRepositoryInterface $giftRuleRepository    Gift rule repository
     * @param GiftRuleFactory             $giftRuleFactory       Gift rule factory
     * @param MetadataPool                $metadataPool          Metadata pool
     * @param RuleFactory                 $ruleFactory           Rule Factory
     */
    public function __construct(
        ExtensionAttributesFactory $extensionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        GiftRuleRepositoryInterface $giftRuleRepository,
        GiftRuleFactory $giftRuleFactory,
        MetadataPool $metadataPool,
        RuleFactory $ruleFactory
    ) {
        $this->extensionFactory      = $extensionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->giftRuleRepository    = $giftRuleRepository;
        $this->giftRuleFactory       = $giftRuleFactory;
        $this->metadataPool          = $metadataPool;
        $this->ruleFactory           = $ruleFactory;
    }

    /**
     * After save
     *
     * @param RuleRepositoryInterface $subject Subject
     * @param RuleInterface           $rule    Rule
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(RuleRepositoryInterface $subject, RuleInterface $rule)
    {
        $extensionAttributes = $rule->getExtensionAttributes();
        $giftRule = $extensionAttributes->getGiftRule();
        $this->giftRuleRepository->save($giftRule);

        return $rule;
    }

    /**
     * After get by id
     *
     * @param RuleRepositoryInterface $subject Subject
     * @param RuleInterface           $rule    Rule
     *
     * @return RuleInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetById(RuleRepositoryInterface $subject, RuleInterface $rule)
    {
        $extensionAttributes = $rule->getExtensionAttributes();

        if ($extensionAttributes === null) {
            /** @var RuleExtensionInterface $extensionAttributes */
            $extensionAttributes = $this->extensionFactory->create(RuleInterface::class);
            $rule->setExtensionAttributes($extensionAttributes);
        }

        $metadata = $this->metadataPool->getMetadata(RuleInterface::class);
        $model = $this->ruleFactory->create()->load($rule->getRuleId());

        try {
            /** @var GiftRuleInterface $giftRule */
            $giftRule = $this->giftRuleRepository->getById($model->getData($metadata->getLinkField()));
        } catch (NoSuchEntityException $exception) {
            // Create gift rule if not exist.
            $giftRule = $this->giftRuleFactory->create();
            $giftRule->setId($model->getData($metadata->getLinkField()));
        }
        $extensionAttributes->setGiftRule($giftRule);
        $rule->setExtensionAttributes($extensionAttributes);

        return $rule;
    }

    /**
     * After get list
     *
     * @param RuleRepositoryInterface $subject       Subject
     * @param SearchResults           $searchResults Search results
     *
     * @return SearchResults
     */
    public function afterGetList(RuleRepositoryInterface $subject, SearchResults $searchResults)
    {
        $newItem = [];
        /** @var RuleInterface $rule */
        foreach ($searchResults->getItems() as $rule) {
            $newItem[] = $this->afterGetById($subject, $rule);
        }

        return $searchResults->setItems($newItem);
    }
}
