<?php

declare(strict_types=1);

namespace Smile\GiftSalesRule\Block\Cart\GiftRules;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Smile\GiftSalesRule\Api\Data\GiftRuleDataInterface;
use Smile\GiftSalesRule\Api\GiftRuleServiceInterface;
use Smile\GiftSalesRule\Helper\GiftRule as GiftRuleHelper;

/**
 * Class GiftRules
 *
 * @copyright 2019 Smile
 */
class Rule extends Template
{
    protected CollectionFactory $productCollectionFactory;

    /**
     * Cart constructor.
     *
     * @param Context                  $context           Context
     * @param GiftRuleServiceInterface $giftRuleService   Gift rule service
     * @param GiftRuleHelper           $giftRuleHelper    Gift rule helper
     * @param CollectionFactory        $collectionFactory Collection factory
     * @param array                    $data              Data
     */
    public function __construct(
        Context $context,
        protected GiftRuleServiceInterface $giftRuleService,
        protected GiftRuleHelper $giftRuleHelper,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->productCollectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get gift rule
     */
    public function getGiftRule(): ?GiftRuleDataInterface
    {
        return $this->getData('gift_rule');
    }

    /**
     * Set gift rule
     *
     * @param GiftRuleDataInterface $giftRule Gift rule
     * @return $this
     */
    public function setGiftRule(GiftRuleDataInterface $giftRule)
    {
        return $this->setData('gift_rule', $giftRule);
    }

    /**
     * Get product collection.
     *
     * @param array $productItems Product items
     */
    public function getProductCollection(array $productItems): Collection
    {
        $productCollection = $this->productCollectionFactory->create();
        $productCollection
            ->addAttributeToSelect(['small_image', 'name'])
            ->addIdFilter(array_keys($productItems))
            ->addFinalPrice()
        ;

        return $productCollection;
    }

    /**
     * Get add to cart url.
     *
     * @param int|string $giftRuleId   Gift rule id
     * @param int|string $giftRuleCode Gift rule code
     */
    public function getAddToCartUrl($giftRuleId, $giftRuleCode): string
    {
        return $this->giftRuleHelper->getAddUrl($giftRuleId, $giftRuleCode);
    }

    /**
     * Get button label.
     */
    public function getButtonLabel(): Phrase
    {
        $rule = $this->getGiftRule();
        $buttonLabel = __('Choose my gift(s)');
        if ($rule->getRestNumber() !== $rule->getNumberOfferedProduct()) {
            $buttonLabel = __('Edit my choices');
        }

        return $buttonLabel;
    }
}
