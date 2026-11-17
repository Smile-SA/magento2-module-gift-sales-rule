<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\Model\SalesRule;

use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Model\Rule;
use Smile\GiftSalesRule\Api\Data\GiftRuleInterface;
use Smile\GiftSalesRule\Model\GiftRuleFactory;
use Smile\GiftSalesRule\Model\GiftRuleRepository;

class SaveHandler implements ExtensionInterface
{
    /**
     * ReadHandler constructor.
     *
     * @param MetadataPool       $metadataPool       Metadata pool
     * @param GiftRuleRepository $giftRuleRepository Gift rule repository
     * @param GiftRuleFactory    $giftRuleFactory    Gift rule factory
     */
    public function __construct(
        protected MetadataPool $metadataPool,
        protected GiftRuleRepository $giftRuleRepository,
        protected GiftRuleFactory $giftRuleFactory
    ) {
    }

    /**
     * Save gift rule value from Sales Rule extension attributes
     *
     * @param Rule  $entity    Entity
     * @param array $arguments Arguments
     * @return Rule
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(mixed $entity, mixed $arguments = []): object
    {
        $metadata = $this->metadataPool->getMetadata(RuleInterface::class);
        $ruleId = $entity->getData($metadata->getLinkField());
        if ($ruleId) {
            $extensionAttributes = $entity->getExtensionAttributes();
            if (isset($extensionAttributes['gift_rule'])) {
                try {
                    /** @var GiftRuleInterface $giftRule */
                    $giftRule = $this->giftRuleRepository->getById($ruleId);
                } catch (NoSuchEntityException $exception) {
                    // Create gift rule if not exist.
                    $giftRule = $this->giftRuleFactory->create();
                    $giftRule->setId($ruleId);
                }

                // Reset the price range value for offer product rules.
                if ($entity->getSimpleAction() === GiftRuleInterface::OFFER_PRODUCT) {
                    $extensionAttributes['gift_rule'][GiftRuleInterface::PRICE_RANGE] = 0;
                }

                $giftRule->addData($extensionAttributes['gift_rule']);

                $this->giftRuleRepository->save($giftRule);
            }
        }

        return $entity;
    }
}
