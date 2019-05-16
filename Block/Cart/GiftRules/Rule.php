<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\GiftSalesRule
 * @author    Pierre Le Maguer <pierre.lemaguer@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\GiftSalesRule\Block\Cart\GiftRules;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\View\Element\Template\Context;
use Smile\GiftSalesRule\Api\Data\GiftRuleDataInterface;
use Smile\GiftSalesRule\Api\GiftRuleServiceInterface;
use Smile\GiftSalesRule\Helper\GiftRule as GiftRuleHelper;

/**
 * Class GiftRules
 *
 * @author    Pierre Le Maguer <pierre.lemaguer@smile.fr>
 * @copyright 2019 Smile
 */
class Rule extends \Magento\Framework\View\Element\Template
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
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * Cart constructor.
     *
     * @param Context                  $context
     * @param GiftRuleServiceInterface $giftRuleService
     * @param GiftRuleHelper           $giftRuleHelper
     * @param CollectionFactory        $collectionFactory
     * @param array                    $data
     */
    public function __construct(
        Context $context,
        GiftRuleServiceInterface $giftRuleService,
        GiftRuleHelper $giftRuleHelper,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->giftRuleService = $giftRuleService;
        $this->giftRuleHelper = $giftRuleHelper;
        $this->productCollectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get gift rule
     *
     * @return GiftRuleDataInterface
     */
    public function getGiftRule(): GiftRuleDataInterface
    {
        return $this->getData('gift_rule');
    }

    /**
     * Set gift rule
     *
     * @param GiftRuleDataInterface $giftRule
     * @return $this
     */
    public function setGiftRule(GiftRuleDataInterface $giftRule)
    {
        return $this->setData('gift_rule', $giftRule);
    }

    /**
     * Get product collection.
     *
     * @param array $productItems
     * @return Collection
     */
    public function getProductCollection(array $productItems)
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
     * @param int    $giftRuleId
     * @param string $giftRuleCode
     *
     * @return string
     */
    public function getAddToCartUrl($giftRuleId, $giftRuleCode)
    {
        return $this->giftRuleHelper->getAddUrl($giftRuleId, $giftRuleCode);
    }

    /**
     * Get button label.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getButtonLabel()
    {
        $rule = $this->getGiftRule();
        $buttonLabel = __('Choose my gift(s)');
        if ($rule->getRestNumber() !== $rule->getMaximumNumberProduct()) {
            $buttonLabel = __('Edit my choices');
        }

        return $buttonLabel;
    }
}
