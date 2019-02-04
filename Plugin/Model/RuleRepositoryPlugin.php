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
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Exception\NoSuchEntityException;
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
     * RuleRepositoryPlugin constructor.
     *
     * @param ExtensionAttributesFactory  $extensionFactory
     * @param SearchCriteriaBuilder       $searchCriteriaBuilder
     * @param GiftRuleRepositoryInterface $giftRuleRepository
     * @param GiftRuleFactory             $giftRuleFactory
     */
    public function __construct(
        ExtensionAttributesFactory $extensionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        GiftRuleRepositoryInterface $giftRuleRepository,
        GiftRuleFactory $giftRuleFactory
    ) {
        $this->extensionFactory      = $extensionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->giftRuleRepository    = $giftRuleRepository;
        $this->giftRuleFactory       = $giftRuleFactory;
    }

    /**
     * After save
     *
     * @param RuleRepositoryInterface $subject
     * @param RuleInterface           $rule
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function afterSave(RuleRepositoryInterface $subject, RuleInterface $rule)
    {
        $extensionAttributes = $rule->getExtensionAttributes();
        $giftRule = $extensionAttributes->getGiftRule();
        $this->giftRuleRepository->save($giftRule);

        return $entity;
    }

    /**
     * After get by id
     *
     * @param RuleRepositoryInterface $subject
     * @param RuleInterface           $rule
     *
     * @return RuleInterface
     */
    public function afterGetById(RuleRepositoryInterface $subject, RuleInterface $rule)
    {
        $extensionAttributes = $rule->getExtensionAttributes();

        if ($extensionAttributes === null) {
            /** @var RuleExtensionInterface $extensionAttributes */
            $extensionAttributes = $this->extensionFactory->create(RuleInterface::class);
            $rule->setExtensionAttributes($extensionAttributes);
        }

        try {
            /** @var GiftRuleInterface $giftRule */
            $giftRule = $this->giftRuleRepository->getById($rule->getRuleId());
        } catch (NoSuchEntityException $exception) {
            // Create gift rule if not exist
            $giftRule = $this->giftRuleFactory->create();
            $giftRule->setId($rule->getRuleId());
        }
        $extensionAttributes->setGiftRule($giftRule);
        $rule->setExtensionAttributes($extensionAttributes);

        return $rule;
    }

    /**
     * After get list
     *
     * @param RuleRepositoryInterface $subject
     * @param SearchResults           $searchResults
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
